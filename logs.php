<?php
require_once('Cors.php');
$cors = new Cors();
$cors->cors_policy();
try {
  require_once("auth/db.php");
  $config = parse_ini_file("config.ini", true);
  $db = new DB_Connection($config['DB']);
  $db_connect = $db->db_connect();
  $result_data = array();
  if (!$db_connect) {
    echo json_encode('Couldn`t connect to DB');
    http_response_code(503);
    exit();
  }
  if (isset($_POST['creator']) && isset($_POST['event'])) {
    $creator = $_POST['creator'];
    $event = $_POST['event'];
    $stmt = "select logs.insert_new_logs ('" . $creator . "','" . $event . "')";
    $query = pg_query($db_connect, $stmt);
    if (!$query) {
      throw new Exception('Error during DB query insert into logs');
    }
    $result = pg_fetch_array($query);
    array_push($result_data,  array("status" => "ok", "updated_on" => $result[0]));
    echo json_encode($result_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  } else {
    array_push($result_data,  array("status" => "ok", "updated_on" => 'nothing to update'));
    echo json_encode($result_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  }
} catch (Exception $e) {
  echo json_encode($e->getMessage());
  http_response_code(404);
  $db->closeConn();
  exit();
} finally {
  $db->closeConn();
  exit();
}
