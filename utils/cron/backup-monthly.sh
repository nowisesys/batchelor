#!/bin/sh
#
# Make backup of batchelor (cron monthly).
#
# Author: Anders Lövgren
# Date:   2011-10-04

r=batchelor
t=$(date +"%Y-%m-%d")
p=monthly

( cd /var/cache/$r
    # Backup data directories:
    for d in *; do
      tar cfz backup/${p}/batchelor-${d}-${t}.tar.gz ${d}
    done
)
