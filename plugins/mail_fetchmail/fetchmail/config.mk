# This is the configuration file for wfetch
#

# The binary should be installed in /usr/local/sbin
# Change this to fit with your system.
#
BINDIR = /usr/local/sbin

# Webserver user
# This is the only user that should be allowed to run the
# wfwd program.
WEBUSER = apache

# To disable WEBUSER this check just comment out the 
# following line
#RESTRICTUSE = -D RESTRICTUSE

## Compile time flags
LIBDIR =
CFLAGS = -g
LFLAGS = -g
CCM = cc -Em

