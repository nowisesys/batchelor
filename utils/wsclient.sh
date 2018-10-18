#!/bin/sh
#
# Run web service test.
#
# Author: Anders Lövgren
# Date:   2018-10-18

root=$(dirname $0)

if ! [ -e $root/script/ws.php ]; then
  echo "$0: The target script ws.php is missing"
  exit 1
fi

( cd $root
  export APP_ROOT=$(dirname `pwd`)
  exec php ./script/ws.php "$*" )
