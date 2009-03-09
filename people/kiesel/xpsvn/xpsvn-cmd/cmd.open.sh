#!/bin/sh
#
# $Id$
#

TAGDIR=$(tmpTagDir)
TAG=$1

[ -z $TAG ] && exit 1

if [ ! -d "$TAGDIR" ]; then
  mkdir -p "$TAGDIR";
fi

svn co $(repositoryRoot .)/tags/$1 "$TAGDIR"/current-tag