#!/bin/sh
#
# A simple script to put in /etc/cron.{hourly|daily|weekly|monthly}
#
# Run periodic tasks for batchelor, the batch queue manager.
#
# Author: Anders Lövgren
# Date:   2008-03-19

# Update statistics:

appdir="/var/www/retrotector.neuro.uu.se/apps/retrotector-pub"

if [ -d $appdir/utils ]; then
   ( cd $appdir/utils ; php -q collect.php -q 2 )
else
   echo "$0: failed update batchelor statistics"
fi

# Cleanup job directories (older that 3 month):

if [ -d $appdir/utils ]; then
   ( cd $appdir/utils ; php -q cache.php -c -a 3M )
else
   echo "$0: failed cleanup job directories"
fi
