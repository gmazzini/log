<?php

echo "<pre>";
$query=mysqli_query($con,"select distinct contest from log where mycall='$mycall' order by start");
for(;;){
  $row=mysqli_fetch_assoc($query);
  if($row==null)break;
  $contest=$row["contest"];
  $query2=mysqli_query($con,"select min(start),max(start) from log where mycall='$mycall' and contest='$contest'");
  $row2=mysqli_fetch_row($query2);
  printf("%20s %s %s\n",$contest,$row2[0],$row2[1]);
  mysqli_free_result($query2);
}
mysqli_free_result($query);
echo "</pre>";

?>
