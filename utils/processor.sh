#!/bin/bash
#
# Run job processor.
#
# Author: Anders Lövgren
# Date:   2018-10-02

script=processor.php
tssdir=$(dirname $0)
topdir=$(dirname $tssdir)

source $tssdir/script/common.sh
exec php $topdir/utils/script/$script "$@"
