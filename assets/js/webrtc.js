let pc = null;
let localStream = null;
let pendingRemoteCandidates = [];
let answered = false;
let resolvedCallType = (typeof CALL_TYPE !== "undefined" && CALL_TYPE) ? CALL_TYPE : "video";

const localVideo = document.getElementById("localVideo");
const remoteVideo = document.getElementById("remoteVideo");

const config = {
  iceServers: [{ urls: "stun:stun.l.google.com:19302" }]
};

function getLocalIcePath(isCaller) {
  return "signals/" + CALL_ID + "/ice/" + (isCaller ? "caller" : "receiver");
}

function getRemoteIcePath(isCaller) {
  return "signals/" + CALL_ID + "/ice/" + (isCaller ? "receiver" : "caller");
}

async function resolveCallType() {
  if (resolvedCallType === "voice" || resolvedCallType === "video") return resolvedCallType;

  const typeSnap = await db.ref("calls/" + CALL_ID + "/type").get();
  resolvedCallType = typeSnap.exists() ? typeSnap.val() : "video";
  return resolvedCallType;
}

function flushPendingCandidates() {
  if (!pc || !pc.remoteDescription) return;

  pendingRemoteCandidates.forEach(candidate => {
    pc.addIceCandidate(candidate).catch(err => console.error("ICE apply failed", err));
  });
  pendingRemoteCandidates = [];
}

function bindRemoteIceListener(isCaller) {
  db.ref(getRemoteIcePath(isCaller)).on("child_added", async snap => {
    const rawCandidate = snap.val();
    if (!rawCandidate || !pc) return;

    const candidate = new RTCIceCandidate(rawCandidate);

    if (!pc.remoteDescription) {
      pendingRemoteCandidates.push(candidate);
      return;
    }

    try {
      await pc.addIceCandidate(candidate);
    } catch (err) {
      console.error("Error adding ICE candidate", err);
    }
  });
}

// CALLER
async function startCall(callType = "video") {
  resolvedCallType = (callType === "voice") ? "voice" : "video";

  await db.ref("signals/" + CALL_ID).remove();
  await db.ref("calls/" + CALL_ID).set({
    from: FROM,
    to: TO,
    type: resolvedCallType,
    status: "ringing",
    created_at: Date.now()
  });

  db.ref("calls/" + CALL_ID + "/status").on("value", snap => {
    if (snap.val() === "accepted" && !pc) {
      startWebRTC(true);
    }
    if (snap.val() === "rejected") {
      alert("Call rejected");
    }
  });
}

// BOTH SIDES
async function startWebRTC(isCaller = false) {
  if (pc) return;

  const callType = await resolveCallType();

  pc = new RTCPeerConnection(config);
  bindRemoteIceListener(isCaller);

  localStream = await navigator.mediaDevices.getUserMedia({
    video: callType !== "voice",
    audio: true
  });

  localStream.getTracks().forEach(track => pc.addTrack(track, localStream));
  localVideo.srcObject = localStream;

  pc.ontrack = event => {
    remoteVideo.srcObject = event.streams[0];
  };

  pc.onicecandidate = event => {
    if (event.candidate) {
      db.ref(getLocalIcePath(isCaller)).push(event.candidate.toJSON());
    }
  };

  if (isCaller) {
    const offer = await pc.createOffer();
    await pc.setLocalDescription(offer);
    await db.ref("signals/" + CALL_ID + "/offer").set(offer);
  }
}

// RECEIVER
db.ref("signals/" + CALL_ID + "/offer").on("value", async snap => {
  if (!snap.exists()) return;

  const offer = snap.val();

  if (!pc) {
    await startWebRTC(false);
  }

  if (!pc.currentRemoteDescription) {
    await pc.setRemoteDescription(new RTCSessionDescription(offer));
    flushPendingCandidates();
  }

  if (!answered) {
    const answer = await pc.createAnswer();
    await pc.setLocalDescription(answer);
    await db.ref("signals/" + CALL_ID + "/answer").set(answer);
    answered = true;
  }
});

// CALLER
db.ref("signals/" + CALL_ID + "/answer").on("value", async snap => {
  if (!snap.exists() || !pc || pc.currentRemoteDescription) return;

  await pc.setRemoteDescription(new RTCSessionDescription(snap.val()));
  flushPendingCandidates();
});
