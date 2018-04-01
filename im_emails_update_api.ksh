#!/bin/ksh
# execute im_emails_update_api sas extract script
# set variables
. ~/.profile
PGMNAME='im_emails_update_api'
export PGMNAME
export CONT_RTN=0
export HOSTNAME=`uname -n`
export FILENAME=$(date +%Y%m%d)
export MAINLOG=./"$PGMNAME`date '+%Y%m%d-%H%M%S'`.log"
echo "Begin - $(date)"					> $MAINLOG	
php ./imodules_all_email_message_headers.php  >>$MAINLOG
php ./imodules_all_email_recipients_driver.php  >>$MAINLOG
php ./imodules_all_email_opens_driver.php  >>$MAINLOG
php ./imodules_all_email_clicks_driver.php  >>$MAINLOG
