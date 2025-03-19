<?php
require_once '../Cors.php';
$cors = new Cors();
$cors->cors_policy();
try {
	require_once '../auth/db.php';
	$config = parse_ini_file('../config.ini', true);
	$db = new DB_Connection($config['DB']);
	$db_connect = $db->db_connect();
	$result_data = array();

	$arr_on = $_POST["arr_on"];
	if (isset($arr_on) && $arr_on) {
		$sql = "select tablo_content.f_storm_action_check_on( ARRAY[" . "$arr_on" . "] )";
		$query = pg_query($db_connect, $sql);
		if (!$query) {
			throw new Exception('Error during DB query insert into wk_action_check');
		}

		$result = pg_fetch_array($query);
		array_push($result_data,  array("status" => "ok", "updated_on" => $result[0]));
	}
	$arr_off = $_POST["arr_off"];
	if (isset($arr_off) && $arr_off) {
		$sql = "select tablo_content.f_storm_action_check_off( ARRAY[" . "$arr_off" . "] )";
		$query = pg_query($db_connect, $sql);
		if (!$query) {
			throw new Exception('Error during DB query insert into wk_action_check');
		}
		$result = pg_fetch_array($query);
		array_push($result_data,  array("status" => "ok", "updated_off" => $result[0]));
	}
	echo json_encode($result_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
	$db->closeConn();
	echo json_encode($e->getMessage());
	http_response_code(404);
	exit();
} finally {
	$db->closeConn();
	exit();
}
