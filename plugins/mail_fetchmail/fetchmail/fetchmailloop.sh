#!/bin/sh
#
# This script makes the following assumption
#
# 	All users home directories are in /home
#	The scripts live in /usr/local/sbin
#

HOME=/home
BINDIR=/usr/local/sbin

cd $HOME
for i in *
do
	if [ -f $i/.fetchmailrc ]; then
		su - $i -c "$BINDIR/dothefetch.sh $i $HOME/$i/.fetchmailrc" &
	fi
done
