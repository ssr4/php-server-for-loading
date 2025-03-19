<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
require_once '../Cors.php';
$cors = new Cors();
$cors->cors_policy();
try {
  $result_data = array();
  $sl_code = $_GET["sl_code"];
  if (!isset($sl_code)) {
    throw new Exception('This parameter is not a numeric!');
  };
  require_once '../auth/db.php';
  $config = parse_ini_file('../config.ini', true);
  $db = new DB_Connection($config['DB']);
  $db_connect = $db->db_connect();
  if (!$db_connect) {
    echo json_encode('Couldn`t connect to DB');
    http_response_code(503);
    exit();
  }
  $query = pg_query(
    $db_connect,
    "select row_id, region, sl_name, 
                par_name, places, action, action_date_begin, action_date_end, 
                check_date, status, sl_code, param_code  from tablo_content.actual_storm_actions
                where sl_code=(select sl.code  from (select * from accounts.service where sl_code=" . "'$sl_code'" .  ") acc 
                JOIN tablo_content.sp_sluzhba sl ON acc.sl_name = sl.naim)"
  );



  while ($result = pg_fetch_array($query)) {
    $row_id = trim($result['row_id']);
    $region = trim($result['region']);
    $sl_name = trim($result['sl_name']);
    $par_name = trim($result['par_name']);

    $places = trim($result['places']);
    $places_arr = explode(' / ', $places);

    $action = trim($result['action']);
    $action_date_begin = trim($result['action_date_begin']);
    $action_date_end = trim($result['action_date_end']);
    $check_date = trim($result['check_date']);
    $status = trim($result['status']);
    $sl_code = trim($result['sl_code']);
    $param_code = trim($result['param_code']);

    $info = array();

    array_push($result_data,  array("row_id" => $row_id, "region" => $region, "sl_name" => $sl_name, "par_name" => $par_name, "places" => $places_arr, "action" => $action, "action_date_begin" => $action_date_begin, "action_date_end" => $action_date_end, "check_date" => $check_date, "status" => $status, "sl_code" => $sl_code, "param_code" => $param_code));
    unset($info);
  }

  echo json_encode($result_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Exception $e) {
  $db->closeConn();
  http_response_code(404);
  exit();
} finally {
  $db->closeConn();
  exit();
}
