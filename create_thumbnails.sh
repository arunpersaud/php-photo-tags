#!/bin/bash

# init some variables
removemeta=0

# parse ini file 
CONFIG_FILE="config.ini"

# copied and modified from http://mark.aufflick.com/blog/2007/11/08/parsing-ini-files-with-sed
eval `sed -e 's/[[:space:]]*\=[[:space:]]*/=/g' \
    -e 's/;.*$//' \
    -e 's/\[.*\]//' \
    -e 's/[[:space:]]*$//' \
    -e 's/^[[:space:]]*//' \
    -e "s/^\(.*\)=\([^\"']*\)$/\1=\"\2\"/" \
   < $CONFIG_FILE `

LOCALDB=$fspotdb
dbprefix=${dbprefix//\//\\\/}

# parse command line
while getopts ":r" opt; do
  case $opt in
    r)
      removemeta=1
      ;;
    \?)
      echo "Invalid option: -$OPTARG" >&2
      ;;
  esac
done


#make sure we are in the correct directory

#make sure we can access the database
if [ ! -s $LOCALDB ] ; then
    echo "Can't find database...exiting."
    exit 1
fi

#create directories, iff they don't exist
if [ ! -d "Photos-small" ] ; then
    echo "creating local directory to store photos of reduced size"
    mkdir Photos-small
fi

if [ ! -d "Photos-tiny" ] ; then
    echo "creating local directory to store thumbnails"
    mkdir Photos-tiny
fi

done=0
offset=0
FILES=""

while [ $done -lt 100 ] ; do
   echo "skipping $offset pics, getting 50 new pics to work on..."
   # handle white space in filename correctly
   FILES=`sqlite3 $LOCALDB "select replace(base_uri||'/'||filename,' ','%20') from photos limit $offset,100"`
   
   if [ "x$FILES" != "x" ] ; then
     #found some files, process them
     for file in $FILES; do
	 # handle white space in file names
	 file=${file/file:\/\/$dbprefix/}
	 file=${file//\%20/ }
	 dir=`dirname "$file"`
	 base=`basename "$file"`
	 
	 if [ ! -s "Photos-tiny/$dir/$base" ] ; then
	     mkdir -p Photos-tiny/$dir
	     nice -19 convert "$dirprefix/$file" -auto-orient -resize x100 -quality 80% "Photos-tiny/$dir/$base"
	     if [ $removemeta=1 ]; then
		 jhead -q -se -purejpg "Photos-tiny/$dir/$base"
		 jhead -q -se -dt "Photos-tiny/$dir/$base"
		 jhead -q -se -mkexif "Photos-tiny/$dir/$base"
		 jhead -q -se -cl "This photo belongs to $admin and was taken from $webbase. If you want to use this photo, please contact him." "Photos-tiny/$dir/$base"
	     fi
	     done=$((done+1))
	 fi 
	 if [ ! -s "Photos-small/$dir/$base" ] ; then
	     mkdir -p Photos-small/$dir
	     nice -19 convert "$dirprefix/$file" -auto-orient -resize x600 -quality 80% "Photos-small/$dir/$base"
	     if [ $removemeta=1 ]; then
		 jhead -q -se -purejpg "Photos-small/$dir/$base"
		 jhead -q -se -dt "Photos-small/$dir/$base"
		 jhead -q -se -mkexif "Photos-small/$dir/$base"
		 jhead -q -se -cl "This photo belongs to $admin and was taken from $webbase. If you want to use this photo, please contact him." "Photos-small/$dir/$base"
	     fi
	     done=$((done+1))
	 fi 

	 echo -n -e "$((done/2))       \r"
     done
   else
       break;
   fi

   #get ready to get the next 100
   offset=$((offset+50))
done
