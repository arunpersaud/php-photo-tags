#!/bin/bash

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
REMOTEDB=$origdb
dbprefix=${dbprefix//\//\\\/}
# end parsing


function usage () {
    echo <<EOF
f-spot-db-reduce.sh <options>

The script uses config.ini for most of it's input data.

Options:
     --include=<comma separated taglist>      include pictures with these tags
     --exclude=<comma separated taglist>      remove pictures with these tags
     --hide=<comma separated taglist>         remove these tags, but leave pictures
EOF

}

# parse command line
CL_INCLUDE=""
CL_EXCLUDE=""
CL_HIDE=""

for i in "$@"; do
    case $i in
	--include=*)
	   CL_INCLUDE=${i#*=}
	   ;;
	--exclude=*)
	   CL_EXCLUDE=${i#*=}
	   ;;
	--hide=*)
	   CL_HIDE=${i#*=}
	   ;;
	-h|--help)
	   usage
	   ;;
    esac
done

# check database version, exit if different from what we expect
DBVERSION=`sqlite3 $REMOTEDB 'select data from meta where name="F-Spot Database Version"'`

if [ x"$DBVERSION" != "x18" ] ; then
    echo "The database is from a different F-spot version (database version $DBVERSION) that is not implemented."
    echo "Check TODO for a newer version or file a bug-report at: TODO"
    exit 2
fi


# join tags together in a list of comma seperated quoted strings, so that they can be used in DB queries
INCLUDE="'${CL_INCLUDE/,/','}'"
EXCLUDE="'${CL_EXCLUDE/,/','}'"
HIDE="'${CL_HIDE/,/','}'"

if [ "x$INCLUDE" != "x" ] ; then
    echo "only including pictures with tags: $INCLUDE"
fi

if [ "x$EXCLUDE" != "x" ] ; then
    echo "excluding all pictures with tags: $EXCLUDE"
fi

if [ "x$HIDE" != "x" ] ; then
    echo "removing tags: $HIDE"
fi

if [ "x$EXCLUDE" == "x" -a "x$INCLUDE" == "x" ] ; then
    echo "using all pictures from the database"
fi

# test if localdb already exist:
# yes:
#     see if photo-db is newer? yes: merge them; no: do nothing
# no:
#     create it

cp $REMOTEDB  $LOCALDB

ExcludeTags=""
if [ "x$EXCLUDE" != "x" ] ; then
    ExcludeTags="insert into rmphotoids select photo_id from photo_tags where tag_id in (select id from tags where name in ($EXCLUDE));"
fi

HideTags=""
if [ "x$HIDE" != "x" ] ; then
    HideTags="delete from photo_tags where tag_id in (select id from tags where name in ($HIDE));
              delete from tags where name in ($HIDE);"
fi

# delete all photo related information that we don't want to keep in case INCLUDE tags were given
sqlite3 $LOCALDB <<EOF
create temp table rmphotoids as select id from photos where id not in (select photo_id from photo_tags where tag_id in (select id from tags where name in ($INCLUDE)));

$ExcludeTags
$HideTags

delete from photos where id in (select * from rmphotoids);
delete from photo_tags where photo_id in (select * from rmphotoids);
delete from photo_versions where photo_id in (select * from rmphotoids);

delete from tags where id not in (select distinct tag_id from photo_tags asc);
delete from rolls where id not in (select distinct roll_id from photos asc);

drop table rmphotoids;
drop table jobs;
drop table exports;

vacuum;

EOF
