<?php
require 'functions.php';
$verified = verifySubscription($_GET['email'], $_GET['code']);
?>
<!DOCTYPE html>
<html>
<body>
    <h1><?= $verified ? 'Email verified!' : 'Invalid link' ?></h1>
    <a href="index.php">Back to Task Manager</a>
</body>
</html>
