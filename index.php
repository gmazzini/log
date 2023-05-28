<title>LZH Logger V0.63 by IK4LZH</title>
<style><?php include "style.css"; ?></style>
<?php
include "local.php";
include "utility.php";

$act=(int)mypost("act");
$con=mysqli_connect("127.0.0.1",$dbuser,$dbpassword,$dbname);
mysqli_query($con,"SET time_zone='+00:00'");
$mypage=30;
if($act>=1){
  $mycall=strtoupper(mypost("mycall"));
  if($act==1)$md5passwd=md5(mypost("mypasswd"));
  else $md5passwd=mypost("md5passwd");
  $query=mysqli_query($con,"select cluster,rigconnect from user where mycall='$mycall' and md5passwd='$md5passwd'");
  $row=mysqli_fetch_assoc($query);
  if($row!=null){
    $aux=explode(":",$row["rigconnect"]);
    $rigIP=$aux[0];
    $rigPORT=$aux[1];
    $aux=explode(",",$row["cluster"]);
    if($act==1)foreach($aux as &$vv)$dxcsel[$vv]=1;
  }
  else $act=0;
  mysqli_free_result($query);
}
?>

<script>
  function updategeneral() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        aaa=this.responseText.split("\n");
        document.getElementById("rigrx").textContent=aaa[0];
        document.getElementById("Prigrx").value=aaa[0];
        document.getElementById("rigtx").textContent=aaa[1];
        document.getElementById("Prigtx").value=aaa[1];
        document.getElementById("rigm").textContent=aaa[2];
        document.getElementById("Prigm").value=aaa[2];
      }
    }
    xhttp.open("GET","rig.php?rigIP=<?php echo $rigIP;?>&rigPORT=<?php echo $rigPORT;?>",true);
    xhttp.send();
  }
  function nextgeneral(){
    updategeneral();
    setTimeout(nextgeneral,1000);
  }  
  updategeneral();
  setTimeout(nextgeneral,1000);
</script>

<?php
if($act==0){
  echo "<form method=\"post\">";
  echo "<input type=\"text\" name=\"mycall\">";
  echo "<input type=\"password\" name=\"mypasswd\" autocomplete=\"off\">";
  echo "<input type=\"hidden\" name=\"act\" value=\"1\">";
  echo "<input type=\"submit\">";
  echo "</form>";
}
else {
  $Icallsign=strtoupper(mypost("Icallsign"));
  $Ifreq=mypost("Ifreq");
  $Imode=strtoupper(mypost("Imode"));
  $Isignaltx=mypost("Isignaltx");
  if($Isignaltx=="")$Isignaltx="59";
  $Isignalrx=mypost("Isignalrx");
  if($Isignalrx=="")$Isignalrx="59";
  $Icontest=mypost("Icontest");
  $Icontesttx=mypost("Icontesttx");
  $Icontestrx=mypost("Icontestrx");
  $run=mypost("run");
  $page=(int)mypost("page");
  $qsostart=mypost("qsostart");
  $runcontest=(int)mypost("runcontest");
  $modecontest=(int)mypost("modecontest");
  $riglink=(int)mypost("riglink");
  
  switch($run){
    case "list": $page=0; break;
    case "list up": $run="list"; $page+=$mypage; break;
    case "list dw": $run="list"; $page-=$mypage; if($page<0)$page=0; break;
    case "find": $page=0; break;
    case "find up": $run="find"; $page+=$mypage; break;
    case "find dw": $run="find"; $page-=$mypage; if($page<0)$page=0; break;
    case "contest": $runcontest=1; break;
    case "contest off": $runcontest=0; break;
    case "auto": $modecontest=1; break;
    case "auto off": $modecontest=0; break;
    case "riglink": $riglink=1; break;
    case "riglink off": $riglink=0; break;
  }
  
  echo "<form method=\"post\" enctype=\"multipart/form-data\">";
  echo "<input type=\"hidden\" name=\"mycall\" value=\"$mycall\">";
  echo "<input type=\"hidden\" name=\"md5passwd\" value=\"$md5passwd\">";
  echo "<input type=\"hidden\" name=\"act\" value=\"2\">";
  
  echo "<table>";
  
  echo "<td>";
  echo "<label id=\"myf1\">Call </label>";
  echo "<input id=\"xcall\" type=\"text\" name=\"Icallsign\" value=\"$Icallsign\" maxlength=\"20\" size=\"10\"><br>";
  if(!$riglink){
    echo "<label id=\"myf1\">Freq </label>";
    echo "<input type=\"text\" id=\"myt1\" name=\"Ifreq\" value=\"$Ifreq\" maxlength=\"10\" size=\"10\"><br>";
    echo "<label id=\"myf1\">Mode </label>";
    echo "<input type=\"text\" id=\"myt1\" name=\"Imode\" value=\"$Imode\" maxlength=\"8\" size=\"4\"><br>";
  }
  else {
    echo "<input type=\"hidden\" name=\"Ifreq\" value=\"$Ifreq\">";
    echo "<input type=\"hidden\" name=\"Imode\" value=\"$Imode\">";
  }
  echo "<label id=\"myf1\">SigTX</label>";
  echo "<input type=\"text\" id=\"myt1\" name=\"Isignaltx\" value=\"$Isignaltx\" maxlength=\"8\" size=\"4\"><br>";
  echo "<label id=\"myf1\">SigRX</label>";
  echo "<input type=\"text\" id=\"myt1\" name=\"Isignalrx\" value=\"$Isignalrx\" maxlength=\"8\" size=\"4\">";
  if($runcontest){
    echo "<input type=\"submit\" name=\"run\" value=\"contest off\">&nbsp;";
    if($modecontest){
      $query=mysqli_query($con,"select max(cast(contesttx as unsigned)) from log where mycall='$mycall' and contest='$Icontest'");
      $row=mysqli_fetch_row($query);
      $Icontesttx=$row[0]+1;
      mysqli_free_result($query);
    }
    echo "<label id=\"myf1\">ContestTX</label>";
    echo "<input type=\"text\" id=\"myt1\" name=\"Icontesttx\" value=\"$Icontesttx\" maxlength=\"6\" size=\"6\"><br>";
    echo "<label id=\"myf1\">ContestRX</label>";
    echo "<input type=\"text\" id=\"myt1\" name=\"Icontestrx\" value=\"$Icontestrx\" maxlength=\"6\" size=\"6\"><br>";
    echo "<label id=\"myf1\">Contest</label>";
    echo "<input type=\"text\" id=\"myt1\" name=\"Icontest\" value=\"$Icontest\" maxlength=\"12\" size=\"12\"><br>";
    if($modecontest)echo "<input type=\"submit\" name=\"run\" value=\"auto off\">&nbsp;";
    else echo "<input type=\"submit\" name=\"run\" value=\"auto\">&nbsp;";
  }
  else {
    echo "<input type=\"hidden\" name=\"Icontesttx\" value=\"$Icontesttx\">";
    echo "<input type=\"hidden\" name=\"Icontestrx\" value=\"$Icontestrx\">";
    echo "<input type=\"hidden\" name=\"Icontest\" value=\"$Icontest\">";
  }
  echo "</td>";
 
  echo "<td id=\"myq1\">";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"list\">List</button>&nbsp;";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"list up\">&#8679;</button>&nbsp;";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"list dw\">&#8681;</button>";
  echo "<br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"find\">Find</button>&nbsp;";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"find up\">&#8679;</button>&nbsp;";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"find dw\">&#8681;</button>";
  echo "<br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"normalize\">Apply</button>&nbsp;";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"report\">Report</button>";
  echo "<br>";
  if($riglink)echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"riglink off\">RigOff</button>";
  else echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"riglink\">RigOn</button>";    
  echo "</td>";
  
  echo "<td id=\"myq1\">";
  echo "<input type=\"file\" id=\"myb1\" name=\"myfile\" style=\"width: 300px\">";
  echo "<br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"importadi\">adi&#8680;</button>&nbsp;";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"importlzh\">lzh&#8680;</button>&nbsp;";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"exportadi\">&#8680;adi</button>&nbsp;";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"exportcbr\">&#8680;cbr</button>";
  echo "<br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"qsl_lotw\">QSL.lotw</button>&nbsp;";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"qsl_eqsl\">QSL.eqsl</button>&nbsp;";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"qsl_qrz\">QSL.qrz</button>";
  echo "</td>";
  
  echo "<td id=\"myq1\">";
  foreach($_POST['dxcsel'] as &$vv)$dxcsel[$vv]=1;
  $x=0;
  foreach(array("160","80","60","40","30","20","17","15","12","10","6","2","PH","CW","DG") as &$vv){
    echo "<input type=\"checkbox\" id=\"myc1\" name=\"dxcsel[]\" value=\"$vv\"";
    if(isset($dxcsel[$vv]))echo " checked";
    echo ">";
    printf("<label id=\"myf1\">%-3s</label>",$vv);
    $x++;
    if($x==3){$x=0; echo "<br>";}
  }
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"cluster\">Cluster</button>";
  echo "</td>";
  
  echo "</table>";

    

  
 
  
 
  
  echo "<input id=\"xstart\" type=\"submit\" name=\"run\" value=\"start\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"end\">&nbsp;";
  if(!$runcontest)echo "<input type=\"submit\" name=\"run\" value=\"contest\">&nbsp;";
  

  echo "<br>";
  

  echo "<input type=\"submit\" name=\"run\" value=\"sto1\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"rcl1\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"sto2\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"rcl2\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"sto3\">&nbsp;";
  echo "<input type=\"submit\" name=\"run\" value=\"rcl3\">&nbsp;";
  echo "<font size=5>RX:<span id=\"rigrx\"></span>&nbsp;";
  echo "TX:<span id=\"rigtx\"></span>&nbsp;";
  echo "M:<span id=\"rigm\"></span>&nbsp;</font>";
  echo "<br>";  
  
  echo "<h1>$mycall $page</h1>";
  
  switch($run){
    case "list": include "run_list.php"; break;
    case "find": include "run_find.php"; break;
    case "report": include "run_report.php"; break;
    case "normalize": include "run_normalize.php"; break;
    case "cluster": include "run_cluster.php"; break;      
    case "exportcbr": include "run_exportcbr.php"; break;
    case "exportadi": include "run_exportadi.php"; break;
    case "end": include "run_end.php"; break;
    case "start": include "run_start.php"; break;
    case "importlzh": include "run_importlzh.php"; break;
    case "importadi": include "run_importadi.php"; break; 
    case "qsl_lotw": include "run_qsl_lotw.php"; break;
    case "qsl_eqsl": include "run_qsl_eqsl.php"; break;
    case "qsl_qrz": include "run_qsl_qrz.php"; break;
    case "sto1": case "sto2": case "sto3": case "rcl1": case "rcl2": case "rcl3": include "run_storcl.php"; break;
  }
  
  echo "<input type=\"hidden\" name=\"qsostart\" value=\"$qsostart\">";
  echo "<input type=\"hidden\" name=\"page\" value=\"$page\">";
  echo "<input type=\"hidden\" name=\"runcontest\" value=\"$runcontest\">";
  echo "<input type=\"hidden\" name=\"modecontest\" value=\"$modecontest\">";
  echo "<input type=\"hidden\" name=\"riglink\" value=\"$riglink\">";
  echo "<input type=\"hidden\" name=\"Prigrx\" id=\"Prigrx\">";
  echo "<input type=\"hidden\" name=\"Prigtx\" id=\"Prigtx\">";
  echo "<input type=\"hidden\" name=\"Prigm\" id=\"Prigm\">";
  echo "</form>";
}

mysqli_close($con);

?>

<script>
  var xcall=document.getElementById("xcall");
  xcall.addEventListener("keypress",function(event){
    if(event.key==="Enter"){
      event.preventDefault();
      document.getElementById("xstart").click();
    }
  });
</script>
