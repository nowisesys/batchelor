#!/bin/sh
#
# Run job scheduler.
#
# Author: Anders Lövgren
# Date:   2018-10-18

root=$(dirname $0)

if ! [ -e $root/script/scheduler.php ]; then
  echo "$0: The target script scheduler.php is missing"
  exit 1
fi

( cd $root
  export APP_ROOT=$(dirname `pwd`)
  exec php ./script/scheduler.php "$*" )
