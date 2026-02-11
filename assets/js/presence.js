const userRef = db.ref("users_status/" + CURRENT_USER);

userRef.set({ status: "online" });

window.addEventListener("beforeunload", () => {
  userRef.set({
    status: "offline",
    last_seen: Date.now()
  });
});

db.ref("users_status").on("value", snap => {
  snap.forEach(child => {
    const id = child.key;
    const status = child.val().status;
    const el = document.getElementById("status-" + id);
    if (el) el.innerText = status;
  });
});