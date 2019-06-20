<?php
function change($mysql_conn, $action_change, $table ,$flat, $cash, $date){
	if ($action_change=='add'){
		$mysql_conn->query("insert into {$table}(number_flat,date,cash) values ({$flat},'{$date}-01',{$cash})");
	} else if ($action_change=='delete'){
		$mysql_conn->query("delete from {$table} where number_flat={$flat} and date='{$date}-01' and cash={$cash}");
	}
}
function getRangeFlats($mysql_conn, $table){
	return $mysql_conn->query("select min(number_flat) as min, max(number_flat) as max from {$table}")->fetch_assoc();
}
function execute_sql_script($script_path){
	$command = "mysql -u{$user} -p{$pass} "
	. "-h {$host} < {$script_path}";

	$output = shell_exec($command . '/database_saldo.sql');
}
function show_payments($mysql_conn){
	$result  = array("action"=>"payments","id"=>array(),"flat"=>array(), "date"=>array(), "cash"=>array());
	$res_payments = $mysql_conn->query("select * from payments");
	while ($row = $res_payments->fetch_assoc())
	{
		$result["id"][] = $row["id_payment"];
		$result["flat"][] = $row["number_flat"];
		$result["date"][] = $row["date"];
		$result["cash"][] = $row["cash"];
	}
	return $result;
}
function show_charges($mysql_conn){
	$result  = array("action"=>"charges","id"=>array(),"flat"=>array(), "date"=>array(), "cash"=>array());
	$res_payments = $mysql_conn->query("select * from charges");
	while ($row = $res_payments->fetch_assoc())
	{
		$result["id"][] = $row["id_charge"];
		$result["flat"][] = $row["number_flat"];
		$result["date"][] = $row["date"];
		$result["cash"][] = $row["cash"];
	}
	return $result;
}
function show_saldo($mysql_conn){
	$result  = array("action"=>"saldo","id"=>array(),"flat"=>array(), "date"=>array(), "payment"=>array(), "charge"=>array());
	$res_payments = $mysql_conn->query("select * from saldo");
	while ($row = $res_payments->fetch_assoc())
	{
		$result["id"][] = $row["id_saldo"];
		$result["flat"][] = $row["number_flat"];
		$result["date"][] = $row["month"];
		$result["payment"][] = $row["payment"];
		$result["charge"][] = $row["charge"];
	}
	return $result;
}
function show_sheet1($mysql_conn, $year){
	$result = array("action" => "sheet1", "Flats"=>array(), "Saldo_begin"=>array(), "Saldo_end"=>array());
	for ($i=1;$i<=12;$i++){
		$result["{$i}"] = array();}
	$res_flats = $mysql_conn->query("select distinct number_flat from saldo");
	while($row_flats = $res_flats->fetch_assoc()){
		$result["Flats"][] = $row_flats["number_flat"];
		$curr_saldo = sum_saldo($mysql_conn, $row_flats["number_flat"], $year);
		if (is_null($curr_saldo)){
			for ($i=1;$i<=12;$i++)
				$result["{$i}"][] = array("charge"=>"error", "payment"=>"error");
		}
		else{
			for ($i=1;$i<=12;$i++){
				$result_flats_info = $mysql_conn->query((sprintf("select payment, charge from saldo where year(month)=%d and number_flat=%d and month(month)=%d", $year, $value, $i)));
				if ($result_flats_info){
					$row_info = $result_flats_info->fetch_assoc();
					$charge = ($row_info["charge"]!=NULL)? $row_info["charge"]:0;
					$payment = ($row_info["payment"]!=NULL)? $row_info["payment"]:0;	
					$curr_saldo+=$payment - $charge;
					$result["{$i}"][] = array("charge" => $charge, "payment"=>$payment, "saldo"=>$curr_saldo);
				}
					else{
						$result["{$i}"][] = "error";
				}
			}
		}
		$result["Saldo_end"][] = $curr_saldo;
	};
	return $result;
}
function show_sheet2($mysql_conn, $flat, $year){
	$result = array("action" => "sheet2", "total"=>array());
	for ($i=1;$i<=12;$i++){
		$result["{$i}"] = array();}
	$curr_saldo = sum_saldo($mysql_conn, $flat, $year);
	if ($curr_saldo!=NULL){
		$result["Saldo_begin"] = $curr_saldo;
		$total_charge = 0;
		$total_payment = 0; 
		for ($i=1;$i<=12;$i++){
			$result_flat_info = $mysql_conn->query((sprintf("select payment, charge from saldo where year(month)=%d and number_flat=%d and month(month)=%d", $year, $flat, $i)));
			if ($result_flat_info){
				$row_info = $result_flat_info->fetch_assoc();
				$charge = ($row_info["charge"]!=NULL)? $row_info["charge"]:0;
				$total_charge+=$charge;
				$payment = ($row_info["payment"]!=NULL)? $row_info["payment"]:0;
				$total_payment+=$payment;
				$curr_saldo+=$payment - $charge;
				$result["{$i}"] = array("charge" => $charge, "payment"=>$payment);
			}
				else{
					$result["{$i}"] = array("charge" => "error", "payment"=>"error");
			}
			$result["total"] = array("charge"=>$total_charge, "payment"=>$total_payment);
		}
	}
	else{
		$curr_saldo = "NULL";
		$result["Saldo_begin"] = $curr_saldo;
		for ($i=1;$i<=12;$i++){
			$result["{$i}"]  = array("charge" => "error","payment"=>"error");
		}
	}
	$result["Saldo_end"] = $curr_saldo;
	return $result;
}
function sum_saldo($mysql_conn, $flat, $year){
	$saldo = 0;
	$res_saldo = $mysql_conn->query(sprintf("select payment-charge as sal from saldo where number_flat = %d and year(month) < %d",$flat,$year));
	if ($res_saldo){
		while($row = $res_saldo->fetch_assoc())
			$saldo+=$row['sal'];
	}
	else{
		return null;
	}
	return $saldo;
}
function insert_saldo($mysql_conn)
{
	$max_date = $mysql_conn->query("select max(t.mx) as result from ((select max(date) as mx from payments) union (select max(date) as mx from charges)) as t")->fetch_assoc()['result']; //получаем месяц до которого есть информация по платежам-долгам
	$min_date = $mysql_conn->query("select min(t.mn) as result from ((select min(date) as mn from payments) union (select min(date) as mn from charges)) as t")->fetch_assoc()['result']; // получаем месяц с которого есть инорфмация по платежам-долгам

	$res_flats = $mysql_conn->query("(select number_flat from payments) union (select number_flat from charges)");

	if (!is_null($max_date)){
		echo sprintf("Max date: %s, Min date: %s <br>",$max_date, $min_date);
		while($row = $res_flats->fetch_assoc()){
			$value = $row['number_flat'];
			for ($cur_date = strtotime($min_date), $max = strtotime($max_date); $cur_date<=$max;){

				$saldo_row = $mysql_conn->query(sprintf("select payment, charge from saldo where month='%s' and number_flat=%d",date('Y-m-d', $cur_date), $value))->fetch_assoc();
				$charge = $mysql_conn->query(sprintf("select cash from charges where date='%s' and number_flat=%d",date('Y-m-d', $cur_date), $value))->fetch_assoc()['cash'];
				$payment = $mysql_conn->query(sprintf("select cash from payments where date='%s' and number_flat=%d",date('Y-m-d', $cur_date), $value))->fetch_assoc()['cash'];
				$charge = (!is_null($charge))? $charge:0; 
				$payment = (!is_null($payment))? $payment:0;
				
				if (!is_null($saldo_row)){
					if ($saldo_row['payment']!=$payment || $saldo_row['charge']!=$charge)
						if ($mysql_conn->query(sprintf("update saldo set payment = %d, charge = %d where month='%s' number_flat = %d",$payment, $charge, date('Y-m-d',$cur_date), $value)))
							echo sprintf("update number_flat: %d, month: '%s', payment: %d, charge: %d Successful!<br>",$value, date('Y-m-d', $cur_date), $payment, $charge);
						else{
							echo sprintf("update number_flat: %d, month: '%s', payment: %d, charge: %d - Failure, errno: %d, error: %s<br> ",$value, date('Y-m-d', $cur_date),$payment, $charge, $mysqli->errno, $mysqli->error);
							break;
						}
				}
				else{
					if ($mysql_conn->query(sprintf("insert saldo(number_flat, month, payment, charge) value(%d, '%s', %d, %d)",$value, date('Y-m-d',$cur_date),$payment, $charge))){
						echo sprintf("insert number_flat: %d, month: '%s', payment: %d, charge: %d - Successful!<br>",$value, date('Y-m-d', $cur_date), $payment, $charge);
					}
					else{
						echo sprintf("insert number_flat: %d, month: '%s', payment: %d, charge: %d - Failure, errno: %d, error: %s<br> ",$value, date('Y-m-d', $cur_date),$payment, $charge, $mysqli->errno, $mysqli->error);
						break;
					}
				}
				$cur_date = mktime(0, 0, 0, date("m", $cur_date)+1 , date("d", $cur_date), date("Y", $cur_date));
			};
		};
	}
	else
	{
		echo "Max, Min date: NULL";
	}
};
?>