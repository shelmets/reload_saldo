<?php
include 'settings.php';
$mysqli = new mysqli($host, $user, $password, $data_base);

if ($mysqli->connect_errno) {
	echo "Could not connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

//пуллим общие данные
$action = $_REQUEST['select'];
$month = $_REQUEST['date'];
$date = sprintf('%s-01',$month);
$flag = true;

echo sprintf("Action: %s\n", $action).'<br>';
echo "Count: ".$count_requests.'<br>';
echo "Month: ".$month."\n".'<br>';
echo "Date: ".$date."\n".'<br>';

//пробуем сделать запрос к базе
if ($res_old_data = $mysqli->query(sprintf("select number_flat, cash, date from %s",$action))){
//пушим в базу данных 
	for ($i=0; $i < $count_requests; $i++){
	//пуллим данные с формы для строк
		$flat = $_REQUEST[sprintf('flat%d',$i)];
		$charge = $_REQUEST[sprintf('cash%d',$i)];

		echo sprintf('%d)<br>', $i+1);
	//проверяем на идентичность номера квартиры и даты счета или оплаты (в зависимости от таблицы)
	if ($res_old_data->num_rows!=0){ //если в таблице что то есть
		$res_old_data->data_seek(0);
		while ($row = $res_old_data->fetch_assoc())
		{
			if ($row["number_flat"]==$flat && $row["date"]==$date){
				if ($mysqli->query(sprintf("delete from %s where number_flat=%d and date='%s'",$action,$flat, $date)))
				{
					echo sprintf("Delete %d %d row Successful<br>", $flat, $row["cash"]);
				}
				else
				{
					echo sprintf("Delete %d %d row, Failure, errno: %d, error: %s <br>", $flat, $row["cash"], $mysqli->errno, $mysqli->error);
					$flag = false;
					break;
				};
			};
		};
	};
	if ($flag){
		if ($mysqli->query(sprintf("insert into %s(number_flat,date,cash) values (%d,'%s',%d)",$action, $flat, $date, $charge)))
		{
			echo sprintf("Insert %d %d Successful<br>",$flat, $charge);
		}
		else
		{
			echo sprintf("Insert %d %d Failure, errno: %d, error: %s <br>",$flat, $charge, $mysqli->errno, $mysqli->error);
			$flag = false;
			break;
		}
	}
	else
	{
		break;
	};
};
}
else
{
	echo sprintf("Select number_flat, cash, date from %s Failure, errno: %d, error: %s<br>",$action, $mysqli->errno, $mysqli->error);
	$flag = false;
};
if ($flag)
{
	echo "Successful!!! :) <br>";
}
else
{
	echo "Failure, something wrong  :( <br>";
}
//ссылочка назад к формам
echo '<a href="http://localhost:8888/saldo_lab">back</a><br>';
$mysqli->close();
?>