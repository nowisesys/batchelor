#!/bin/bash
# 
# Usage: See --help or usage() function.
# 
# Author: Anders Lövgren
# Date:   2018-10-18

# This script name and version:
prog=$(basename $0)
vers="1.0"

# The source directory:
srcdir="$(dirname $(realpath $0))"

# Default HTTP request location:
location="/"

function setup_public()
{
    cp -a $srcdir/public .
    rm -rf public/example
}

function setup_config()
{
    cp -a $srcdir/config .
    for file in defaults.site; do 
        if [ -e config/defaults.site ]; then
            relocate config/defaults.site
        fi
    done
    echo "(i) Directory config has been setup (please modify the defaults.* files)."
}

function setup_utils()
{
    cp -a $srcdir/utils .
}

function setup_template()
{
    cp -a $srcdir/template .
}

function setup_docs()
{
    cp -a $srcdir/docs .
}

function setup_basic()
{
    setup_public
    setup_config
    setup_utils
    setup_template
    setup_dispatcher
}

function setup_examples()
{
    cp -a $srcdir/public/example public
}

function setup_develop() 
{
    for dir in public utils template; do 
        rm -rf $dir && mkdir $dir && ln -sf $srcdir/$dir/* $dir
    done
}

function setup_dispatcher()
{
    for file in .htaccess dispatch.php; do
        rm -f public/$file
        cp -a $srcdir/public/$file public/$file 
        relocate public/$file
#        sed -i -e s%'/../../vendor/'%'/../vendor/'%1 \
#               -e s%"/batchelor2"%"${location}"%g public/$file
        echo "(i) File public/$file has been installed (please modify)."
    done
}

function cleanup()
{
    rm -rf public utils config docs template
}

function relocate() 
{
    local file=$1

    sed -i -e s%'/../../vendor/'%'/../vendor/'%1 \
           -e s%"/batchelor2"%"${location}"%g $file
}

function usage()
{
    echo "$prog - Setup and management tool."
    echo 
    echo "Usage: $prog --setup [--public] [--config] [--utils] [--docs]"
    echo "       $prog --examples"
    echo "       $prog --develop"
    echo 
    echo "Options:"
    echo "  --setup     : Setup batchelor web application."
    echo "  --public    : Setup public directory."
    echo "  --config    : Setup config directory."
    echo "  --utils     : Setup utils directory."
    echo "  --docs      : Install documentation (recommended)."
    echo "  --examples  : Install examples in public."
    echo "  --develop   : Configure for development."
    echo "  --cleanup   : Removes public, utils, config and docs directory."
    echo "  --verbose   : Be verbose about executed commands."
    echo "  --version   : Display version of this script."
    echo 
    echo "Example:"
    echo "  # Setup for location /myapp"
    echo "  $prog --location /batchelor-simula --setup"
    echo
    echo "  # Install examples and enable developer mode"
    echo "  $prog --location /batchelor-simula --examples --develop"
    echo
    echo "Notice:"
    echo "  1. The --location or --verbose options must be used before any other option."
    echo "  2. The --setup option implies --public --config --utils."
    echo 
    echo "Copyright (C) 2018 Nowise Systems and Uppsala University (Anders Lövgren, BMC-IT)"
}

function version()
{
    echo "$prog v$vers"
}

# Relocate srcdir when running in bootstrap mode:
if [ -d vendor/bmc/batchelor ]; then
    srcdir="$(pwd)/vendor/bmc/batchelor"
fi

while [ -n "$1" ]; do
    case "$1" in
        --verbose|-v)
            set -x
            ;;
        --help|-h)
            usage
            exit 0
            ;;
        --version|-V)
            version
            exit 0
            ;;
        --setup)
            setup_basic
            ;;
        --public)
            setup_public
            ;;
        --config)
            setup_config
            ;;
        --utils)
            setup_utils
            ;;
        --docs)
            setup_docs
            ;;
        --examples)
            setup_examples
            ;;
        --develop)
            setup_develop
            setup_dispatcher
            ;;
        --cleanup)
            cleanup
            ;;
        --location)
            shift
            location="$(realpath -sm $1)/"
            ;;
        *)
            usage
            exit 1
            ;;
    esac
    shift
done
