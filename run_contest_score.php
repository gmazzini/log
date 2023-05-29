<?php

$ll=array();
$ll[]=array("ARIDX","https://ik4lzh.mazzini.org/aridx.php");
$ll[]=array("CQWWSSB","https://ik4lzh.mazzini.org/cqww.php");
$ll[]=array("CQWWCW","https://ik4lzh.mazzini.org/cqww.php");

echo "<pre>";
echo "$Icontest\n";
foreach($ll as $v){
  if(str_starts_with($Icontest,$v[0])){
    echo "$v[0]\n";
    break;
  }
}
echo "</pre>";

?>
