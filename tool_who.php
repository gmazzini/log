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
  $qrzkey=trim(myrcl($con,"qrzkey"));    
  $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?s=$qrzkey;callsign=$Icallsign");
  $q2=simplexml_load_string($q1);
  if(isset($q2->Session->Error)){
    $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?username=$qrzuser;password=$qrzpassword;agent=gm01");
    $q2=simplexml_load_string($q1);
    $qrzkey=$q2->Session->Key;
    mysto($con,"qrzkey","$qrzkey\n");
    $q1=file_get_contents("http://xmldata.qrz.com/xml/current/?s=$qrzkey;callsign=$Icallsign");
    $q2=simplexml_load_string($q1);
  }
  $gfname=mysqli_real_escape_string($con,$q2->Callsign->fname);
  if(strlen($gfname)>0){
    if(isset($q2->Callsign->nickname))$gfname.=' "'.mysqli_real_escape_string($con,$q2->Callsign->nickname).'"';
    $gname=mysqli_real_escape_string($con,$q2->Callsign->name);
    $gaddr1=mysqli_real_escape_string($con,$q2->Callsign->addr1);
    $gaddr2=mysqli_real_escape_string($con,$q2->Callsign->addr2);
    $gstate=mysqli_real_escape_string($con,$q2->Callsign->state);
    $gzip=mysqli_real_escape_string($con,$q2->Callsign->zip);
    $gcountry=mysqli_real_escape_string($con,$q2->Callsign->country);
    $ggrid=mysqli_real_escape_string($con,$q2->Callsign->grid);
    $gemail=mysqli_real_escape_string($con,$q2->Callsign->email);
    $gcqzone=(int)$q2->Callsign->cqzone;
    $gituzone=(int)$q2->Callsign->ituzone;
    $gborn=(int)$q2->Callsign->born;
    $gimage=mysqli_real_escape_string($con,$q2->Callsign->image);
    $mynow=gmdate('Y-m-d H:i:s');
    mysqli_query($con,"replace into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image,myupdate) value ('$Icallsign','$gfname','$gname','$gaddr1','$gaddr2','$gstate','$gzip','$gcountry','$ggrid','$gemail',$gcqzone,$gituzone,$gborn,'$gimage','$mynow')");
  }
}
mysqli_free_result($query2);
mysqli_close($con);

?>
