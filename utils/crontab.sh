#!/bin/bash
#
# A simple script to put in /etc/cron.{hourly|daily|weekly|monthly}
#
# Run periodic tasks for Batchelor (the batch job queue manager).
#
# Author: Anders Lövgren
# Date:   2008-03-19

# Update statistics:

# You
appdir="/var/www/localhost/apps/batchelor"

if [ -d $appdir/utils ]; then
   ( cd $appdir/utils ; php -q collect.php -q 2 )
else
   echo "$0: failed update batchelor statistics (check the appdir variable in $0)"
fi

# Cleanup job directories (older that 3 month):

if [ -d $appdir/utils ]; then
   ( cd $appdir/utils ; php -q cache.php -c -a 3M )
else
   echo "$0: failed cleanup job directories (check the appdir variable in $0)"
fi
