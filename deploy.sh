# main config
CURRENTDIR=`pwd`
MAINFILE="jigoshop.php" # this should be the name of your main php file in the wordpress plugin

# git config
GITPATH="$CURRENTDIR/" # this should be the path to your git repository (trailing slash required)

# svn config
SVNPATH="$CURRENTDIR/../jigoshop-svn/" # path to your checked out SVN repo. Trailing slash is required and don't add trunk.
SVNUSER="jigowatt" # your svn username

# Let's begin...
echo ".........................................."
echo 
echo "Preparing to deploy wordpress plugin"
echo 
echo ".........................................."
echo 

# check version in readme.txt is the same as plugin file
NEWVERSION1=`grep "^Stable tag" $GITPATH/readme.txt | awk '{ print $NF}'`
echo $NEWVERSION1;
NEWVERSION2=`grep "^Version" $GITPATH/$MAINFILE | awk '{ print $NF}'`
echo $NEWVERSION2;
if [ "$NEWVERSION1" != "$NEWVERSION2" ]; then echo "Versions don't match. Exiting...."; exit 1; fi

echo "Versions match in readme.txt and PHP file. Let's proceed..."

# change into the git dir and get a commit message
cd $GITPATH
echo -e "Enter a commit message describing the changes made: \c"
read COMMITMSG
git commit -a -m "$COMMITMSG"

# push to origin
echo "Push latest commit to origin"
git push origin master

# Export git contents to svn directory
echo 
echo "Exporting the HEAD of master from git to the trunk of SVN"
git checkout-index -a -f --prefix=$SVNPATH/trunk/

# Change to SVN dir and commit changes
echo "Changing directory to SVN and committing to trunk"
cd $SVNPATH/trunk
svn commit --username=$SVNUSER -m "$COMMITMSG"

# Create a new tag and commit it :)
echo "Creating new SVN tag"
cd $SVNPATH
svn copy trunk/ tags/$NEWVERSION1
svn commit --username=$SVNUSER -m "Updating tag to $NEWVERSION1"

# Update the version number
echo "Updating version number for future executions"
cd $CURRENTDIR
echo $NEWVERSION1 > version.txt