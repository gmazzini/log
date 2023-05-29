<?php

$ll=array();
$ll[]=array("ARIDX","https://ik4lzh.mazzini.org/aridx.php");
$ll[]=array("CQWWSSB","https://ik4lzh.mazzini.org/cqww.php");
$ll[]=array("CQWWCW","https://ik4lzh.mazzini.org/cqww.php");

echo "<pre>";
$go="";
if(strlen($Icontest)>0){
  foreach($ll as $v){
    if(strstr($Icontest,$v[0])){
      $go=$v[1];
      break;
    }
  }
}

echo "$go\n";
echo "</pre>";

?>
