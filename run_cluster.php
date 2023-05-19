<?php

echo "<pre>";
$myrow=0;
$query=mysqli_query($con,"select dx,spotter,freq,timespot from dxc order by timespot desc limit 1000");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $freq=(float)$row["freq"]/1000;
  $query2=mysqli_query($con,"select band,mode from bpfreq where $row["freq"]>=fromfreq and $row["freq"]<tofreq");
  $row2=mysqli_fetch_assoc($query2);
  mysqli_free_result($query2);
  if(isset($dxcsel[$row2["band"]])&&isset($dxcsel[$row2["mode"]])){
    printf("%s %12s ",$row["timespot"],$row["dx"]);
    printf("<button type=\"button\" onclick=\"myfreqcall(%s,'%s')\">%7.1f</button> ",$row["freq"],$row["dx"],$freq);
    printf("%10s %s\n",$row[1],myqso($con,$mycall,$row["dx"]));
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
