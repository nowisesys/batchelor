#!/bin/sh
#
# Run job processor.
#
# Author: Anders Lövgren
# Date:   2018-10-02

root=$(dirname $0)

if ! [ -e $root/script/processor.php ]; then
  echo "$0: The target script processor.php is missing"
  exit 1
fi

( cd $root
  export APP_ROOT=$(dirname `pwd`)
  exec php ./script/processor.php $* )
