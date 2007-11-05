#!/bin/sh
#
# The wrapper script. Runs as a batch job under atd (started by batch).
# The script receives three arguments:
# 
# $1 : The directory where job meta data should be saved.
# $2 : The sequence file to process.
# $3 : The result directory where output files should go.
#
# We save a timestamp when we start and when we are finished.

jobdir="$1"
seqfile="$2"
resdir="$3"

# Save start timestamp:
date -u +"%s" > $jobdir/started

# Put the command to run between the ( ... ):
( 
  sleep 30
  cd $resdir
  cp ../* . 
  ) 1> $jobdir/stdout 2> $jobdir/stderr

# Save finished timestamp:
date -u +"%s" > $jobdir/finished
