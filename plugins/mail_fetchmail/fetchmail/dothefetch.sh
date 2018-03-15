#!/bin/sh
#
# This script does the actual fetching of mail.  It checks the return code
# and sends mail to root if there is an error fetching the mail.
# It also sends mail to the user with the same error
#

POSTMASTER=root

# Do it again in case it was a temporary blip.
# we repeat 5 times, then decide it is an error

for i in 1 2 3 4 5; do
	/usr/bin/fetchmail -s -f $2 
	rc=$?
	if [ $rc == 0 -o $rc == 1 ]; then
		exit 0;
	fi
	sleep 5
done


echo "Error retrieving mail:  Error code: " $rc >/tmp/err

echo $1  $2 >>/tmp/err

mail $POSTMASTER -s "Mail Retrieval Error" </tmp/err
#mail $1 -s "Mail Retrieval Error" </tmp/err

rm -f /tmp/err
