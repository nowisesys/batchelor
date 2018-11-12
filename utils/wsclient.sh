#!/bin/bash
#
# Run web service test.
#
# Author: Anders Lövgren
# Date:   2018-10-18

script=wsclient.php
tssdir=$(dirname $0)
topdir=$(dirname $tssdir)

source $tssdir/script/common.sh
exec php $topdir/utils/script/$script "$@"
