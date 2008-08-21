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

## Enable debug:
# export CHEMGPS_DEBUG=1

##
## Required parameters:
##
jobdir="$1"
indata="$2"
resdir="$3"
bindir="/usr/local/chemgps/bin"

##
## Options for splitting indata to per subjob data:
## 
split_opts="-l 10000 -d -a 7"

##
## Current stage:
## 
stage=1

##
## Required variables:
##
export SIMCAQLICENSE="/usr/local/share/libsimcaq"
export PATH="$PATH:/usr/local/bin"

##
## Use this function to communicate the state to batchelor.
##
function qsignal()
{
  state="$1"
  msg="$2"
  
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
      errorcode="$?"
      date -u +"%s" > $jobdir/finished
      echo "$msg"   > $jobdir/fatal
      exit $errorcode
      ;;
  esac
}

function next_step()
{
  # Save exit code of previous command:
  exitcode="$?"

  # Save stage message:
  message="$1"
  
  # Test exit code:
  if [ "$exitcode" != "0" ]; then
    qsignal "Error in step $message"
  fi
    
  # Save stdout and stderr from each stage:
  for stream in stdout stderr; do
    if [ -e $jobdir/$stream.stage${stage} ]; then
      cat $jobdir/$stream.stage${stage} >> $jobdir/$stream
      rm -f $jobdir/$stream.stage${stage}
    fi
  done

  # Test stderr:
  if [ "$(wc -c $jobdir/stderr)" != "0" ]; then
    qsignal "Error in step $message"
  fi
    
  # Continue with next stage:
  echo "$message" >> $jobdir/stdout
  let stage=$stage+1
}

function debug()
{
  msg="$1"
  prog="$(basename $0)"
  if [ -z "${CHEMGPS_DEBUG}" ]; then
    CHEMGPS_DEBUG=0
  fi
  if [ "${CHEMGPS_DEBUG}" -ge "1" ]; then
    echo "debug: $prog: $msg" >> $jobdir/stdout
  fi
}

##
## Signal started to batchelor:
##
qsignal "started"

##
## Sanity check:
##
[ -z "$jobdir" -o -z "$indata" -o -z "$resdir" ] && qsignal "fatal" "missing argument for script"

##
## Put the command to run here with stdout and stderr redirected.
## The command must be in PATH or being an absolute path.
##

##
## Transform smiles-file before running dragonX:
##
debug "Transforming smiles-file:"
$bindir/nonisosmi.pl $indata 1> $indata.noniso 2> $jobdir/stderr.stage${stage}
next_step "Finished process indata"

##
## We need to split the large input file into max 10000 lines chunks and
## process each of these smaller files as a subjob.
## 
( mkdir -p $jobdir/workset
  cd $jobdir/workset
  split ${split_opts} $indata.noniso ws
  
  steps="$(ls -1 ws* | wc -l)"
  let steps*=3
  
  for ws in ws*; do 
    mkdir $jobdir/workset/${ws}d && mv $jobdir/workset/${ws} $jobdir/workset/${ws}d && (
      wsdir="$jobdir/workset/${ws}d"
      wsfile="$jobdir/workset/${ws}d/${ws}"
      
      ##
      ## Run dragonX (produces $jobdir/chemgps.output):
      ##
      debug "Running $bindir/chemgps-np-compute.pl $wsfile $wsdir"
      $bindir/chemgps-np-compute.pl $wsfile $wsdir 1> $jobdir/stdout.stage${stage} 2> $jobdir/stderr.stage${stage}
      next_step "${ws}: finished running dragonX ($stage/$steps)"
      
      ##
      ## Validate output from running dragonX (strip lines with error molecules):
      ##
      debug "Running $bindir/chemgps-sqp-prepare.pl $wsdir/chemgps.output $wsdir"
      $bindir/chemgps-sqp-prepare.pl $wsdir/chemgps.output $wsdir 1> $jobdir/stdout.stage${stage} 2> $jobdir/stderr.stage${stage}
      next_step "${ws}: finished prepare for Simca-QP ($stage/$steps)"
      
      ##
      ## Run Simca-QP on dragonX output (using chemgps-sqp):
      ##
      debug "Running $bindir/chemgps-sqp-run.pl $wsdir/chemgps.prepared $wsdir $wsdir"
      $bindir/chemgps-sqp-run.pl $wsdir/chemgps.prepared $wsdir $wsdir 1> $jobdir/stdout.stage${stage} 2> $jobdir/stderr.stage${stage}
      next_step "${ws}: finished running Simca-QP ($stage/$steps)"
      
      ## 
      ## Move result to real result directory:
      ## 
      if [ -e $wsdir/chemgps.txt ]; then
        mv $wsdir/chemgps.txt $resdir/chemgps_${ws}.txt
      fi
      
      ## 
      ## Do next step:
      ##
      let step=$step+1
    )
  done )

##
## Check for fatal errors:
##
if [ "$(grep 'chemgps-sqp: error:' $jobdir/stderr)" != "" ]; then
  qsignal "fatal" "Failed running Simca-QP"
fi

##
## Append result file to stdout:
##
# if [ -e $resdir/chemgps.txt ]; then
#   echo >> $jobdir/stdout
#   echo "<p><b>Result:</b><br>" >> $jobdir/stdout
#   cat $resdir/chemgps.txt >> $jobdir/stdout
#   # echo "<p><b>Indata:</b><br>" >> $jobdir/stdout
#   # cat $indata >> $jobdir/stdout
# fi

##
## Signal finished to batchelor:
##
qsignal "finished"

## 
## Finalize:
## 
( cd $jobdir
  # Insert a hint file for locating result in the workset result:
cat << EOF >> $resdir/README.txt
The input data was split into smaller pieces to support processing of large
number of molecules. Each chemgps_ws0000XXX.txt file in the result contains
the predicted scores for up to 10000 molecules:

result/
  +-- chemgps_ws0000000.txt    // first 10000 molecules in indata
  +-- chemgps_ws0000001.txt    // next 10000 molecules in indata
 ...
  +-- chemgps_ws0000NNN.txt    // the remaining up to NNN * 10^4 molecules
  
Unnamed molecules is prefixed (MOLID) with their line number as they where 
read from the input data (indata).

If you have any questions about the format, send them by email to: 
ChemGPS <chemgps@bmc.uu.se>
EOF
  # Save workset data for debug:
  if [ -d workset ]; then
    tar cfvz workset.tar.gz workset
    rm -rf workset
  fi
  # Cleanup temporary files:
  rm -f indata.noniso )
