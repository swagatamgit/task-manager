#!/bin/bash
CRON_CMD="0 * * * * php $(pwd)/cron.php"
(crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
echo "Cron job set to run hourly"
