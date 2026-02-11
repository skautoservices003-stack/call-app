<?php
include 'config.php';

$conn->query("
INSERT INTO call_logs (from_user,to_user,type)
VALUES ($_POST[from],$_POST[to],'video')
");