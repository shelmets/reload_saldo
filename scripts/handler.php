<?php
	include "handler_lib.php";
	include "settings.php";
	$result = array();
	$mysqli = new mysqli($host, $user, $password, $database);
	if ($mysqli->connect_errno){
		$result['error'] = "Connection error: "."(".$mysqli->connect_errno.") ". $mysqli->connect_error; 
	}
	else
	{
		if (array_key_exists('action',$_REQUEST)){
			switch($_REQUEST['action'])
			{
				case "payments":
					$result = show_payments($mysqli);
					break;
				case "charges":
					$result = show_charges($mysqli);
					break;
				case "saldo":
					$result = show_saldo($mysqli);
					break;
				case "sheet1":
					$result = show_sheet1($mysqli, $_REQUEST['']);
					break;
				case "sheet2":
					$result = show_sheet2($mysqli);
					break;
				case "rangeFlats":
					$result = getRangeFlats($mysqli, $_REQUEST['table']);
				default:
					break;
			}
		}
		else{
			$result['error'] = "Key action do not exists";
		}
	}
	echo json_encode($result);
?>