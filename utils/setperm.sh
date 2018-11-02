#!/bin/sh
#
# Fix permission on directories.
#
# Author: Anders Lövgren
# Date:   2018-11-02

# Enable for script debug:
# set -x

# Set directory were batchelor is installed:
tssdir=$(dirname $0)
appdir=$(dirname $tssdir)

# Web server user:
user="apache"

# These directories/files should be writable by the web server:
for d in cache; do
  if [ -d $appdir/$d -o -h $appdir/$d ]; then
    find $appdir/$d -type d | while read d; do setfacl -m u:$user:rwx "$d"; done
    find $appdir/$d -type f | while read f; do setfacl -m u:$user:rw  "$f"; done
  fi
done

# These files contains secrets and should not be world readable:
for f in app/config/config.def app/config/catalog.def; do
  if [ -f $appdir/$f ]; then
    setfacl -m u:$user:r $appdir/$f
    chmod 640 $appdir/$f
  fi
done
