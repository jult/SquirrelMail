#!/bin/bash

version=$1
smbase=$2
if test -z "$smbase"; then smbase='1.4.0'; fi

if test "x$version" == "x-"; then
   version=`tail -1 < version`
   echo "version " $version
fi

if test -z "$version"; then
   echo
   echo "Usage: $0 <version number> <sm base>"
   echo "       $0 - (to read from 'version')"
   echo
   echo "Versions/bases should look like: 2.0.1   1.4.0"
   echo "or 2.0.1   1.4.0-beta2"
   echo
   exit 1

fi

echo 
echo "making release of proon, version $version..."
echo 
rm -f "proon-$version-$smbase.tar.gz"
mv site-config.php ..

cd ..
tar czvf "proon-$version-$smbase.tar.gz" proon

# mv "proon-$version-$smbase.tar.gz" 
mv site-config.php proon

cd proon

echo
echo done
echo

# a reminder
find . -name '*~'
