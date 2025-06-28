<?php
require 'functions.php';
unsubscribeEmail($_GET['email']);
?>
<!DOCTYPE html>
<html>
<body>
    <h1>You've been unsubscribed</h1>
    <a href="index.php">Back to Task Manager</a>
</body>
</html>
