<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // demo login
}

$user_id = $_SESSION['user_id'];
$users = $conn->query("SELECT * FROM users WHERE id != $user_id");
?>
<!DOCTYPE html>
<html>
<head>
<title>Users</title>
</head>
<body>

<h2>Available Users</h2>
<ul>
<?php while($u = $users->fetch_assoc()) { ?>
  <li>
    <?= $u['name'] ?>
    <span id="status-<?= $u['id'] ?>">offline</span>
    <a href="call.php?user=<?= $u['id'] ?>">Call</a>
  </li>
<?php } ?>
</ul>

<!-- Incoming Call Popup -->
<div id="incomingCall" style="display:none;position:fixed;top:30%;left:35%;background:#fff;padding:20px;border:1px solid #000">
  <h3>Incoming Call</h3>
  <p id="callerName"></p>
  <button onclick="acceptCall()">Accept</button>
  <button onclick="rejectCall()">Reject</button>
</div>

<script>
const CURRENT_USER = "<?= $user_id ?>";
let CURRENT_CALL_ID = null;
</script>

<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
<script src="assets/js/firebase.js"></script>
<script src="assets/js/presence.js"></script>

<script>
// listen for incoming calls
db.ref("calls").on("child_added", snap => {
  const call = snap.val();
  if (call.to == CURRENT_USER && call.status === "ringing") {
    CURRENT_CALL_ID = snap.key;
    document.getElementById("callerName").innerText =
      "User " + call.from + " is calling you";
    document.getElementById("incomingCall").style.display = "block";
  }
});

function acceptCall() {
  db.ref("calls/" + CURRENT_CALL_ID + "/status").set("accepted");
  window.location.href = "call.php?call=" + CURRENT_CALL_ID;
}

function rejectCall() {
  db.ref("calls/" + CURRENT_CALL_ID + "/status").set("rejected");
  document.getElementById("incomingCall").style.display = "none";
}
</script>

</body>
</html>