<title>LZH Logger V0.88 by IK4LZH</title>
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
  $query=mysqli_query($con,"select cluster,rigconnect,translate from user where mycall='$mycall' and md5passwd='$md5passwd'");
  $row=mysqli_fetch_assoc($query);
  if($row!=null){
    $tra=$row["translate"];
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
    xhttp.open("GET","act_rig.php?rigIP=<?php echo $rigIP;?>&rigPORT=<?php echo $rigPORT;?>",true);
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
  $Icontest=strtoupper(mypost("Icontest"));
  $Icontesttx=strtoupper(mypost("Icontesttx"));
  $Icontestrx=strtoupper(mypost("Icontestrx"));
  $run=mypost("run");
  $page=(int)mypost("page");
  $qsostart=mypost("qsostart");
  $runcontest=(int)mypost("runcontest");
  $modecontest=(int)mypost("modecontest");
  $riglink=(int)mypost("riglink");
  
  switch($run){
    case "renew qrz": $run="start"; $act_start="qrz"; break;
    case "renew ru": $run="start"; $act_start="ru"; break;
    case "start": $act_start=""; break;
    case "list": $page=0; break;
    case "list up": $run="list"; $page+=$mypage; break;
    case "list dw": $run="list"; $page-=$mypage; if($page<0)$page=0; break;
    case "list go": $run="list"; $page=-(int)$Icallsign; break;
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
  
  echo "<td><pre>";
  echo "<label id=\"myf1\">Call </label>";
  echo "<input id=\"xcall\" type=\"text\" name=\"Icallsign\" value=\"$Icallsign\" maxlength=\"20\" size=\"10\" autocomplete=\"off\"><br>";
  if(!$riglink){
    echo "<label id=\"myf1\">Freq </label>";
    echo "<input type=\"text\" id=\"myt1\" name=\"Ifreq\" value=\"$Ifreq\" maxlength=\"10\" size=\"10\" autocomplete=\"off\"><br>";
    echo "<label id=\"myf1\">Mode </label>";
    echo "<input type=\"text\" id=\"myt1\" name=\"Imode\" value=\"$Imode\" maxlength=\"8\" size=\"6\" autocomplete=\"off\"><br>";
  }
  else {
    echo "<input type=\"hidden\" name=\"Ifreq\" value=\"$Ifreq\">";
    echo "<input type=\"hidden\" name=\"Imode\" value=\"$Imode\">";
  }
  echo "<label id=\"myf1\">SigTX</label>";
  echo "<input type=\"text\" id=\"myt1\" name=\"Isignaltx\" value=\"$Isignaltx\" maxlength=\"10\" size=\"6\" autocomplete=\"off\"><br>";
  echo "<label id=\"myf1\">SigRX</label>";
  echo "<input type=\"text\" id=\"myt1\" name=\"Isignalrx\" value=\"$Isignalrx\" maxlength=\"10\" size=\"6\" autocomplete=\"off\">";
  if($runcontest){
    if($modecontest){
      $query=mysqli_query($con,"select max(cast(contesttx as unsigned)) from log where mycall='$mycall' and contest='$Icontest'");
      $row=mysqli_fetch_row($query);
      $Icontesttx=$row[0]+1;
      mysqli_free_result($query);
    }
    echo "<br><label id=\"myf1\">ConTX</label>";
    echo "<input type=\"text\" id=\"myt1\" name=\"Icontesttx\" value=\"$Icontesttx\" maxlength=\"6\" size=\"6\" autocomplete=\"off\"><br>";
    echo "<label id=\"myf1\">ConRX</label>";
    echo "<input type=\"text\" id=\"myt1\" name=\"Icontestrx\" value=\"$Icontestrx\" maxlength=\"6\" size=\"6\" autocomplete=\"off\"><br>";
    echo "<label id=\"myf1\">Con  </label>";
    echo "<input type=\"text\" id=\"xcontest\" name=\"Icontest\" value=\"$Icontest\" maxlength=\"12\" size=\"12\" autocomplete=\"off\">";
  }
  else {
    echo "<input type=\"hidden\" name=\"Icontesttx\" value=\"$Icontesttx\">";
    echo "<input type=\"hidden\" name=\"Icontestrx\" value=\"$Icontestrx\">";
    echo "<input type=\"hidden\" name=\"Icontest\" value=\"$Icontest\">";
  }
  echo "<br><button type=\"submit\" id=\"xstart\" name=\"run\" value=\"start\">Start</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"end\">End</button>";
  echo "</pre></td>";
 
  echo "<td id=\"myq1\"><pre>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"list\">List</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"list up\">&#8679;</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"list dw\">&#8681;</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"list go\">G</button><br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"find\">Find</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"find up\">&#8679;</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"find dw\">&#8681;</button><br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"normalize\">Apply</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"report\">Report</button><br>";
  if($runcontest)echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"contest off\">cOFF</button>";
  else echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"contest\">cON</button>";
  if($modecontest)echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"auto off\">c#OFF</button>";
  else echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"auto\">c#ON</button>";
  echo "<br><button type=\"submit\" id=\"myb1\" name=\"run\" value=\"contestlist\">cList</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"contestscore\">cScore</button>";
  echo "<br><button type=\"submit\" id=\"myb1\" name=\"run\" value=\"curio\">Curio</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"activity\">Activity</button>";
  echo "</pre></td>";
  
  echo "<td id=\"myq1\"><pre>";
  echo "<label id=\"myf1\">RX:</label><span id=\"rigrx\"></span><br>";
  echo "<label id=\"myf1\">TX:</label><span id=\"rigtx\"></span><br>";
  echo "<label id=\"myf1\">M:</label><span id=\"rigm\"></span><br>";
  if($riglink)echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"riglink off\">RigOff</button><br>";
  else echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"riglink\">RigOn</button><br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"sto1\">S1</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"rcl1\">R1</button><br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"sto2\">S2</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"rcl2\">R2</button><br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"sto3\">S3</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"rcl3\">R3</button>";
  echo "</pre></td>";
  
  echo "<td id=\"myq1\"><pre>";
  echo "<input type=\"file\" id=\"myb1\" name=\"myfile\" style=\"width: 300px\"><br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"importadi\">adi&#8680;</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"importlzh\">lzh&#8680;</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"exportadi\">&#8680;adi</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"exportcbr\">&#8680;cbr</button><br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"qsl_lotw\">QSL.lotw</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"qsl_eqsl\">QSL.eqsl</button>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"qsl_qrz\">QSL.qrz</button><br>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"cbrtocontest\">cbr&#8680;c</button><br>";
  echo "</pre></td>";
  
  echo "<td id=\"myq1\"><pre>";
  foreach($_POST['dxcsel'] as &$vv)$dxcsel[$vv]=1;
  $x=0;
  foreach(array("PH","CW","DG","10","15","20","40","80","160","12","17","30","60") as &$vv){
    echo "<input type=\"checkbox\" id=\"myc1\" name=\"dxcsel[]\" value=\"$vv\"";
    if(isset($dxcsel[$vv]))echo " checked";
    echo ">";
    printf("<label id=\"myf1\">%-3s</label>",$vv);
    $x++;
    if($x==3){$x=0; echo "<br>";}
  }
  echo "<br><button type=\"submit\" id=\"myb1\" name=\"run\" value=\"cluster\">Cluster</button>";
  echo "</pre></td>";
  
  echo "<td><pre>";
  echo "<h1>$mycall</h1>";
  echo "<button type=\"submit\" id=\"myb1\" name=\"run\" value=\"delock\">Delock</button>";
  echo "</pre></td>";
  
  echo "</table>";
  
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
    case "cbrtocontest": include "run_cbrcontest.php"; break;
    case "contestlist": include "run_contest_list.php"; break;
    case "contestscore": include "run_contest_score.php"; break;
    case "curio": include "run_curio.php"; break;
    case "delock": include "run_delock.php"; break;
    case "activity": include "run_activity.php"; break;
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
