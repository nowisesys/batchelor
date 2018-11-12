#!/bin/bash
#
# Common code for bash scripts.
#
# Author: Anders Lövgren
# Date:   2018-10-26

# Sanity checks first:
if [ -z "$script" ]; then
    echo "$0: The script variable is undefined"
    exit 1
fi

if [ -z "$tssdir" ]; then
    echo "$0: The tssdir variable is undefined"
    exit 1
fi

# Try resolve top directory:
if [ "$tssdir" == "." ]; then
    cd .. && topdir=$(pwd)
else
    cd $tssdir && topdir=$(dirname `pwd`)
fi

# Check that PHP script exists:
if ! [ -e $topdir/utils/script/$script ]; then
  echo "$0: The target script $script is missing"
  exit 1
fi

# Export variable used by PHP code:
export BATCHELOR_APP_ROOT=$topdir
