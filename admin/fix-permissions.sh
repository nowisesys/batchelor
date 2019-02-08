#!/bin/bash
#
# Fix directory permissions. Require filesystem mounted with ACL support.
#
# Author: Anders Lövgren
# Date:   2018-11-12

user=apache

tssdir=$(dirname $0)
topdir=$(dirname $tssdir)

find $topdir/data -type d | xargs setfacl -d -m u:$user:rwx
find $topdir/data -type d | xargs setfacl -m u:$user:rwx
find $topdir/data -type f | xargs setfacl -m u:$user:rw

chown -R $user $topdir/data
