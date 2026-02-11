<?php
include 'config.php';

$from = $_SESSION['user_id'];
$callId = $_GET['call'] ?? ("call_" . time());
$to = $_GET['user'] ?? null;
$callType = $_GET['type'] ?? 'video';
?>
<!DOCTYPE html>
<html>
<head>
<title>Call</title>
</head>
<body>

<h2>Video Call</h2>

<video id="localVideo" autoplay muted playsinline></video>
<video id="remoteVideo" autoplay playsinline></video>

<?php if ($to) { ?>
<button onclick="startCall('video')">Start Video Call</button>
<button onclick="startCall('voice')">Start Voice Call</button>
<?php } ?>

<script>
const CALL_ID = "<?= $callId ?>";
const FROM = "<?= $from ?>";
const TO = "<?= $to ?>";
const CALL_TYPE = "<?= $callType ?>";
</script>

<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.23.0/firebase-database-compat.js"></script>
<script src="assets/js/firebase.js"></script>
<script src="assets/js/webrtc.js"></script>

</body>
</html>
