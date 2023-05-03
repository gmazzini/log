<?php
include "local.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query=mysqli_query($con,"select distinct(callsign) from log order by callsign desc");
for(;;){
  $row=mysqli_fetch_array($query);
  if($row==null)break;
  $Icallsign=$row[0];
  echo "$Icallsign\n";
  $query2=mysqli_query($con,"select callsign from who where callsign='$Icallsign'");
  $row2=mysqli_fetch_array($query2);
  if($row2==null){
    $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?s=$qrzs;callsign=$Icallsign");
    $q2=simplexml_load_string($q1);
    sleep(1);
    $row[0]=$q2->Callsign->fname;
    if(strlen($row[0])>0){
      if(isset($q2->Callsign->nickname))$row[0].=' "'.$q2->Callsign->nickname.'"';
      $row[1]=$q2->Callsign->name;
      $row[2]=$q2->Callsign->addr1;
      $row[3]=$q2->Callsign->addr2;
      $row[4]=$q2->Callsign->state;
      $row[5]=$q2->Callsign->zip;
      $row[6]=$q2->Callsign->country;
      $row[7]=$q2->Callsign->grid;
      $row[8]=$q2->Callsign->email;
      $row[9]=(int)$q2->Callsign->cqzone;
      $row[10]=(int)$q2->Callsign->ituzone;
      $row[11]=(int)$q2->Callsign->born;
    }
    else {
      $row[0]="";
      $row[1]="";
      $row[2]="";
      $row[3]="";
      $row[4]="";
      $row[5]="";
      $row[6]="";
      $row[7]="";
      $row[8]="";
      $row[9]="";
      $row[10]="";
      $row[11]="";
    }
    echo "...$row[0]\n";
    $mynow=gmdate('Y-m-d H:i:s');
    mysqli_query($con,"insert into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,myupdate) value ('$Icallsign','$row[0]','$row[1]','$row[2]','$row[3]','$row[4]','$row[5]','$row[6]','$row[7]','$row[8]',$row[9],$row[10],$row[11],'$mynow')");
  }
  mysqli_free_result($query2);
}
mysqli_free_result($query);
mysqli_close($con);

?>
