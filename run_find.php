<?php

include "def_list.php";
mylist($con,"where callsign like '$Icallsign' and mycall='$mycall' order by start desc limit $mypage offset $page",$mycall,$md5passwd);

?>
