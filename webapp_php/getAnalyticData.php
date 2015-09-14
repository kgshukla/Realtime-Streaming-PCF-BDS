<?php 

$vcap_services2 =  $_ENV["VCAP_SERVICES"];
$db_host=json_decode($vcap_services2)->{'user-provided'}[0]->{'credentials'}->{'DB_HOST'};
$db_port=json_decode($vcap_services2)->{'user-provided'}[0]->{'credentials'}->{'DB_PORT'};

$conn_string = "host=$db_host port=$db_port dbname=gpadmin user=gpadmin password=gpadmin";
$conn = pg_connect($conn_string) or die('connection failed');

$table = array();

if (isset($_POST["type"])) {
	if ($_POST["type"] == "INCORRECT_DATA") {
		$result = pg_query($conn, "select medallion, count(medallion) total_count from taxi_stream.taxi_data_pxf where pickup_long = 0.000000 or pickup_lat = 0.000000 or dropoff_long = 0.000000 or dropoff_lat = 0.000000 group by medallion order by total_count desc limit 10;");
		if (!$result) {
			echo "An error occurred.\n";
			exit;
		}

		$table['cols'] = array(
				array('id' => "", 'label' => 'Taxi ID', 'pattern' => "", 'type' => 'string'),
				array('id' => "", 'label' => '# Counts', 'pattern' => "", 'type' => 'number')
				);

		$rows = array();
		while ($nt = pg_fetch_row($result))
		{
			$temp = array();
			$temp[] = array('v' => $nt[0], 'f' =>NULL);
			$temp[] = array('v' => $nt[1], 'f' =>NULL);
			$rows[] = array('c' => $temp);
		}
		$table['rows'] = $rows;
		$jsonTable = json_encode($table);
		echo $jsonTable;
	} else if ($_POST["type"] == "TOPTAXID_FARE") {
		$result = pg_query($conn, "select medallion, sum(total_amt) total_fare from taxi_stream.taxi_data_pxf group by medallion order by total_fare desc limit 10");
		if (!$result) {
			echo "An error occurred.\n";
			exit;
		}

		$table = array();
		$table['cols'] = array(
				array('id' => "", 'label' => 'Taxi Driver', 'pattern' => "", 'type' => 'string'),
				array('id' => "", 'label' => '# Fare', 'pattern' => "", 'type' => 'number')
				);

		$rows = array();
		while ($nt = pg_fetch_row($result))
		{
			$temp = array();
			$temp[] = array('v' => $nt[0], 'f' =>NULL);
			$temp[] = array('v' => $nt[1], 'f' =>NULL);
			$rows[] = array('c' => $temp);
		}
		$table['rows'] = $rows;
		$jsonTable = json_encode($table);
		echo $jsonTable;
	}
}
?>
