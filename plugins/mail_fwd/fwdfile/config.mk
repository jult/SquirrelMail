# This is the configuration file for wfwdfile
#

# The binary should be installed in /usr/local/sbin
# Change this to fit with your system.
#
BINDIR = /usr/bin

# Webserver user
# !!!IMPORTANT!!!
# This is the only user that should be allowed to run the
# wfwd program.
# Change this to the user your webserver run as.
WEBUSER = apache

## Compile time flags
LIBDIR =
CFLAGS = -g
LFLAGS = -g
