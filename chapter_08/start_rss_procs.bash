# start_rss_procs.bash
Count=40
Pids=''
for ((i = 1; i <= Count; i++)) ;
do
  Log=parse_out_$i.txt
  echo $Log
  ./ch7_rss_process.php -q > $Log 2>&1 &
  Pids="$Pids $!"
done
echo $Pids

