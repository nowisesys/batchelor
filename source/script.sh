#!/bin/sh
#
# Example script. Runs as a batch job under atd (started by batch).
# The script receives two arguments:
# 
# $1 : The catalog where we should save the result to.
# $2 : The sequence file to process.
#
# We save a timestamp when we start and when we are finished.

resdir="$1"
seqfile="$2"

# Save start timestamp:
date -u +"%s" > $resdir/started

# Simulate busy:
sleep 30

# Testa att spara:
# set 1> $resdir/stdout 2> $resdir/stderr
set 1> $resdir/stdout
ls -l saknas 2> $resdir/stderr

# Save finished timestamp:
date -u +"%s" > $resdir/finished
