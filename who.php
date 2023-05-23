<?php
include "local.php";
include "utility.php";
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");

$query2=mysqli_query($con,"select callsign from who where image=''");
for(;;){
  $row2=mysqli_fetch_array($query2);
  if($row2==null)break;
  $Icallsign=$row2[0];
  echo "$Icallsign\n";
  mysqli_query($con,"delete from who where callsign='$Icallsign'");
  $qrzkey=trim(myrcl($con,"qrzkey"));    
  $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?s=$qrzkey;callsign=$Icallsign");
  $q2=simplexml_load_string($q1);
  if(isset($q2->Session->Error)){
    echo "Renew\n";
    $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?username=$qrzuser;password=$qrzpassword;agent=gm01");
    $q2=simplexml_load_string($q1);
    $qrzkey=$q2->Session->Key;
    mysto($con,"qrzkey","$qrzkey\n");
    $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?s=$qrzkey;callsign=$Icallsign");
    $q2=simplexml_load_string($q1);
  }
  $row[0]=mysqli_real_escape_string($con,$q2->Callsign->fname);
  if(strlen($row[0])>0){
    if(isset($q2->Callsign->nickname))$row[0].=' "'.mysqli_real_escape_string($con,$q2->Callsign->nickname).'"';
    $row[1]=mysqli_real_escape_string($con,$q2->Callsign->name);
    $row[2]=mysqli_real_escape_string($con,$q2->Callsign->addr1);
    $row[3]=mysqli_real_escape_string($con,$q2->Callsign->addr2);
    $row[4]=mysqli_real_escape_string($con,$q2->Callsign->state);
    $row[5]=mysqli_real_escape_string($con,$q2->Callsign->zip);
    $row[6]=mysqli_real_escape_string($con,$q2->Callsign->country);
    $row[7]=mysqli_real_escape_string($con,$q2->Callsign->grid);
    $row[8]=mysqli_real_escape_string($con,$q2->Callsign->email);
    $row[9]=(int)$q2->Callsign->cqzone;
    $row[10]=(int)$q2->Callsign->ituzone;
    $row[11]=(int)$q2->Callsign->born;
    $row[12]=mysqli_real_escape_string($con,$q2->Callsign->image);
    $mynow=gmdate('Y-m-d H:i:s');
    mysqli_query($con,"insert into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image,myupdate) value ('$Icallsign','$row[0]','$row[1]','$row[2]','$row[3]','$row[4]','$row[5]','$row[6]','$row[7]','$row[8]',$row[9],$row[10],$row[11],'$row[12]','$mynow')");
  }
}
mysqli_free_result($query2);
mysqli_close($con);

?>
