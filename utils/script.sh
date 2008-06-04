#!/bin/sh
#
# The wrapper script. Runs as a batch job under atd (started by batch).
# The script receives three arguments:
# 
# $1 : The directory where job meta data should be saved.
# $2 : The indata file to process.
# $3 : The result directory where output files should go.
#
# This table describe the relation between batchelors job state and 
# the batch queue (system queue) and files created from this script:
#
# +----------------------------------------------+-------------------+
# | files (created here)                         | queue (system)    |
# +--------+--------+-------+---------+----------+---------+---------+----------+
# | stdout | stderr | fatal | started | finished | waiting | running | state    |
# +--------+--------+-------+---------+----------+---------+---------+----------+------------+
# |        |        |       |         |          |    X    |         | pending  |            |
# +--------+--------+-------+---------+----------+---------+---------+----------+ unfinished |  (1)
# |   (X)  |        |       |   (X)   |          |         |    X    | running  |            |
# +--------+--------+-------+---------+----------+---------+---------+----------+------------+
# |   (X)  |        |       |    X    |     X    |         |         | success  |            |
# +--------+--------+-------+---------+----------+---------+---------+----------+            |
# |   (X)  |   X    |       |    X    |     X    |         |         | warning  |            |
# +--------+--------+-------+---------+----------+---------+---------+----------+ finished   |  (2)
# |   (X)  |   X    |   X   |    X    |     X    |         |         | error    |            |
# +--------+--------+-------+---------+----------+---------+---------+----------+            |
# |   (X)  |        |       |    X    |          |         |         | crashed  |            |
# +--------+--------+-------+---------+----------+---------+---------+----------+------------+
#
#   X == exists, (X) == optional. 
# 
# The states in (1) is out of control of this script, it only depends on batch 
# queue properties. The states in (2) is direct affected by this script. Use the 
# qsignal($state, $msg) function to communicate state to batchelor.
#
# NOTES:
#
# 1. Solaris 8 (and later?)
#    a) You need to compile date.c because the system date don't support -u '%s'
#    b) Change first line in this script from /bin/sh to /usr/bin/bash becaue
#       /bin/sh don't understand function xxx() syntax.
#
# 2. Job control:
#    This script support job control by default thru the bgexec() function. If you 
#    don't want job control, then switch to using the fgexec() function instead.
#    Job control must also be enabled in the file conf/config.inc.
#
#    If a complete job is split into several sub-processes, then remember to update
#    the pid-file after each step.
#

##
## Required parameters:
##
jobdir="$1"
indata="$2"
resdir="$3"

##
## Export environment variables for script.inc
## 
QUEUE_JOBDIR="$jobdir"
QUEUE_INDATA="$indata"
QUEUE_RESDIR="$resdir"
QUEUE_STDOUT="$jobdir/stdout"
QUEUE_STDERR="$jobdir/stderr"
QUEUE_PIDFILE="$jobdir/pid"

export QUEUE_JOBDIR QUEUE_INDATA QUEUE_RESDIR QUEUE_STDOUT QUEUE_STDERR QUEUE_PIDFILE

##
## The meta data gets saved in the root directory of all job directories:
##
metadir="$(dirname $jobdir)"

##
## Exit status of last command:
##
status=0

##
## Use this function to communicate the state to batchelor.
##
function qsignal()
{
  state="$1"
  msg="$2"

  # Job state has changed and its job status needs to be refreshed:
  $jobid="$(basename $jobdir)"
  if [ -z "`grep $jobid $metadir/status.log`" ]; then
    echo $jobid >> $metadir/status.log
  fi
  
  case "$state" in 
    started)
      # Save start timestamp:
      date -u +"%s" > $jobdir/started
      ;;
    finished)
      # Save finished timestamp:
      date -u +"%s" > $jobdir/finished
      exit 0
      ;;
    fatal)
      # This is *really* bad:
      date -u +"%s" > $jobdir/finished
      echo "$msg"  >> $jobdir/stderr
      touch $jobdir/fatal
      exit $status
      ;;
  esac
}

##
## Make sure we got a date command that supports -u '%s' in PATH. For Solaris 8
## (and later?) the utils/date.c can be compiled and used.
##
if [ "`uname -s`" == "SunOS" ]; then
  if ! [ -e $(dirname $0)/date ]; then
    qsignal "fatal" "Sun OS detected but date replacement is missing"
  else
    export PATH=$(dirname $0):$PATH
  fi
fi

##
## Sanity check:
##
[ -z "$jobdir" -o -z "$indata" -o -z "$resdir" ] && qsignal "fatal" "missing argument for script"

##
## Signal started to batchelor:
##
qsignal "started"

##
## Start running the job:
##
scriptinc="$(dirname $0)/script.inc"
if ! [ -e $scriptinc ]; then
  qsignal "fatal" "The user defined job execution script (script.inc) do not exist"
  exit $status
else 
  source $scriptinc
  jobexec
fi

##
## Signal finished to batchelor:
##
if [ "$status" == "0" ]; then
  qsignal "finished"
else
  qsignal "fatal" "The command exited with error state"
fi
