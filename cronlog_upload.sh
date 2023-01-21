#!/bin/bash
CRONLOG_URL='https://cronlog.example.com/uploaded.php'

TMP=$(mktemp -d)
OUTFILE=$TMP/cronlog_upload.out

FULL_CMD="$@"
OS_FROM="`whoami`@`hostname`"

date >$OUTFILE
echo >>$OUTFILE
"$@" &>>$OUTFILE
#{ date; echo; time $FULL_CMD ; } &>$OUTFILE

curl -s -o /dev/null --form "subject=$FULL_CMD" --form "from=$OS_FROM" --form "body=@$OUTFILE" $CRONLOG_URL
