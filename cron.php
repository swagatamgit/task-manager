<?php
require 'functions.php';
sendTaskReminders();
file_put_contents('cron.log', date('Y-m-d H:i:s')." - Sent reminders\n", FILE_APPEND);
?>
