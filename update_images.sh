#!/bin/bash

# main config
PLUGINSLUG="jigoshop"
TMPDIR="/tmp/$PLUGINSLUG"

# svn config
SVNURL="http://plugins.svn.wordpress.org/$PLUGINSLUG" # Remote SVN repo on wordpress.org, with no trailing slash

# Let's begin...
echo "  Preparing to update assets in $PLUGINSLUG"

if [ -z "$SVNUSER" ] ; then
	echo -n "[SVN] Enter username:"
	read SVNUSER
fi
if [ -z "$SVNPASS" ] ; then
	echo -n "[SVN] Enter password:"
	read SVNPASS
fi

if [ -z "$SVNPASS" ] || [ -z "$SVNUSER" ]; then
	echo "Please provide SVN username and password!"
	exit 1
fi

echo "Creating local copy of SVN repo ..."
svn co "$SVNURL/assets" $TMPDIR

read -p "Please navigate to $TMPDIR and update images. After you finished press [Enter]."

cd $SVNPATH

echo "Committing assets"
svn status | grep -v "^.[ \t]*\..*" | grep "^?" | awk '{print $2}' | xargs svn add
svn status | grep -v "^.[ \t]*\..*" | grep "^!" | awk '{print $2}' | xargs svn rm

echo "Committing a new version"
svn commit --username=$SVNUSER --password=$SVNPASS -m "Update assets"

echo "Cleaning up"
rm -fr "$TMPDIR/"

echo
echo ".........................................."
echo
echo "  Successfully updated assets for $PLUGINSLUG"
echo
echo ".........................................."
echo
