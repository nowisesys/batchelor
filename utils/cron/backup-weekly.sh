#!/bin/sh
#
# Make backup of batchelor (cron weekly).
#
# Author: Anders Lövgren
# Date:   2011-10-04

r=batchelor
t=$(date +"%Y-%m-%d")
p=weekly
k=56    # days to keep

( cd /var/cache/$r
    # Backup data directories:
    for d in *; do
      tar cfz backup/${p}/batchelor-${d}-${t}.tar.gz ${d}
    done
    
    # Cleanup old archives:
    find backup/${p} -mtime +${k} -exec rm {} \;
)
