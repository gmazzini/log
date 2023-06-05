<?php

function qrz($Icallsign){
  $qrzkey=trim(myrcl($con,"qrzkey"));    
  $q1=mycurlget("http://xmldata.qrz.com/xml/current/?s=$qrzkey;callsign=$Icallsign");
  $q2=simplexml_load_string($q1);
  if(isset($q2->Session->Error)&&$q2->Session->Error=="Session Timeout"){
    $q1=mycurlget("http://xmldata.qrz.com/xml/current/?username=$qrzuser;password=$qrzpassword;agent=gm01");
    $q2=simplexml_load_string($q1);
    $qrzkey=$q2->Session->Key;
    mysto($con,"qrzkey","$qrzkey\n");
    $q1=mycurlget("http://xmldata.qrz.com/xml/current/?s=$qrzkey;callsign=$Icallsign");
    $q2=simplexml_load_string($q1);
    if($myshow)echo "Renewed qrz.com key\n";
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
    if($myshow)echo "replace into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image,myupdate,src) value ('$Icallsign','$gfname','$gname','$gaddr1','$gaddr2','$gstate','$gzip','$gcountry','$ggrid','$gemail',$gcqzone,$gituzone,$gborn,'$gimage','$mynow','QRZ')\n";
    mysqli_query($con,"replace into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image,myupdate,src) value ('$Icallsign','$gfname','$gname','$gaddr1','$gaddr2','$gstate','$gzip','$gcountry','$ggrid','$gemail',$gcqzone,$gituzone,$gborn,'$gimage','$mynow','QRZ')");
  }
}

function ru($Icallsign){
  $rukey=trim(myrcl($con,"rukey"));
  $q1=mycurlget("https://api.qrz.ru/callsign?id=$rukey&callsign=$Icallsign");
  $q2=simplexml_load_string($q1);
  if(isset($q2->session->errorcode)&&$q2->session->errorcode==403){
    $q1=mycurlget("https://api.qrz.ru/login?u=$ruuser&p=$rupassword&agent=LZH23");
    $q2=simplexml_load_string($q1);
    $rukey=$q2->Session->session_id;
    mysto($con,"rukey","$rukey\n");
    $q1=mycurlget("https://api.qrz.ru/callsign?id=$rukey&callsign=$Icallsign");
    $q2=simplexml_load_string($q1);
    if($myshow)echo "Renewed qrz.ru key\n";
  }
  $gfname=mysqli_real_escape_string($con,$q2->Callsign->name);
  if(strlen($gfname)>0){
    if(isset($q2->Callsign->name2))$gfname.=' "'.mysqli_real_escape_string($con,$q2->Callsign->name2).'"';
    $gname=mysqli_real_escape_string($con,$q2->Callsign->surname);
    $gaddr1=mysqli_real_escape_string($con,$q2->Callsign->street);
    $gaddr2=mysqli_real_escape_string($con,$q2->Callsign->city);
    $gstate=mysqli_real_escape_string($con,$q2->Callsign->state);
    if(isset($q2->Callsign->region_id))$gstate.=' :'.mysqli_real_escape_string($con,$q2->Callsign->region_id);
    $gzip=mysqli_real_escape_string($con,$q2->Callsign->zip);
    $gcountry=mysqli_real_escape_string($con,$q2->Callsign->country);
    $ggrid=mysqli_real_escape_string($con,$q2->Callsign->qthloc);
    $gemail="";
    $gcqzone=(int)$q2->Callsign->cq_zone;
    $gituzone=(int)$q2->Callsign->itu_zone;
    $gborn=(int)substr($q2->Callsign->birthday,6,4);
    $gimage=mysqli_real_escape_string($con,$q2->Files->file);
    $mynow=gmdate('Y-m-d H:i:s');
    if($myshow)echo "replace into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image,myupdate,qrz) value ('$Icallsign','$gfname','$gname','$gaddr1','$gaddr2','$gstate','$gzip','$gcountry','$ggrid','$gemail',$gcqzone,$gituzone,$gborn,'$gimage','$mynow','RU')\n";
    mysqli_query($con,"replace into who (callsign,firstname,lastname,addr1,addr2,state,zip,country,grid,email,cqzone,ituzone,born,image,myupdate,src) value ('$Icallsign','$gfname','$gname','$gaddr1','$gaddr2','$gstate','$gzip','$gcountry','$ggrid','$gemail',$gcqzone,$gituzone,$gborn,'$gimage','$mynow','RU')");
  }
}

?>
