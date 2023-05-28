<?php

if(isset($_FILES['myfile']['tmp_name'])){
  $hh=fopen($_FILES['myfile']['tmp_name'],"r");
  $aux="";
  echo "<pre>";
  while(!feof($hh)){
    $line=trim(fgets($hh));
   
  }
  echo "</pre>";
  fclose($hh);
}

?>
