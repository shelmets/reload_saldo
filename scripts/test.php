<?php
	include "settings.php";
	$mysqli = new mysqli($host, $user, $password, $database);
	$res = $mysqli->query("select min(number_flat) as min, max(number_flat) as max from payments")->fetch_assoc();
	echo json_encode(array('min' =>$res['min'],'max'=>$res['max']));
?>
