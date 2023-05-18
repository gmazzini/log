<?php

echo "<pre>";
$myrow=0;
$query=mysqli_query($con,"select dx,spotter,freq,timespot from dxc order by timespot desc limit 1000");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $query2=mysqli_query($con,"select band,mode from bpfreq where $row[2]>=fromfreq and $row[2]<tofreq");
  $row2=mysqli_fetch_array($query2);
  mysqli_free_result($query2);
  echo "$row2[0] $row2[1]\n";
  if(isset($dxcsel[$row2[0]])&&isset($dxcsel[$row2[1]])){
    printf("%s %12s ",$row[3],$row[0]);
    printf("<button type=\"button\" onclick=\"myfreqcall(%s,'%s')\">%7.1f</button> ",$row[2],$row[0],$freq);
    printf("%10s %s\n",$row[1],myqso($con,$mycall,$row[0]));
    $myrow++;
    if($myrow>$mypage)break;
  }
}
mysqli_free_result($query);
echo "</pre>";

?>
<script>
function myfreqcall(freq,call){
  var xmlhttp=new XMLHttpRequest();
  xmlhttp.open("GET","setfreq.php?freq="+freq+"&rigIP=<?php echo $rigIP; ?>&rigPORT=<?php echo $rigPORT; ?>",true);
  xmlhttp.send();
  document.getElementById("xcall").value=call;
}
</script>
