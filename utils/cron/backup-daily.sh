!/bin/sh
#
# Make backup of batchelor (cron daily).
#
# Author: Anders Lövgren
# Date:   2011-10-04

r=batchelor
t=$(date +"%Y-%m-%d")
p=daily
k=14    # days to keep

( cd /var/cache/$r
    # Backup data directories:
    for d in *; do
      tar cfz backup/${p}/batchelor-${d}-${t}.tar.gz ${d}
    done
    
    # Cleanup old archives:
    find backup/${p} -mtime +${k} -exec rm {} \;
)
