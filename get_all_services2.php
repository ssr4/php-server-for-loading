<?php
// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);
// require_once 'Cors.php';
require_once 'auth/db.php';
require_once 'auth/validate_token.php';
// $cors = new Cors();
// $cors->cors_policy();
$config = parse_ini_file('config.ini', true);
$db = new DB_Connection($config['DB']);
$db->db_connect();
try {
  $username = $_POST['username'];
  $user_email = $_POST['user_email'];
  $stmt = 'SELECT d.sl_code, d.sl_name, d.directory, regions,s.sl_full_name, d.order_description, d.allow_extensions, 
  (select permissions from accounts.persons p  where email =' . "'" . $user_email . "'" . ') as permissions
  from accounts.directories d inner join accounts.service s on d.sl_code = s.sl_code';


  // "select accounts.f_insert_new_users ('" . $username . "','" . $hashed_password  . "','" . $user_role  . "')";
  if ($username === 'admin') {
    $result = pg_prepare(
      $db->get_conn(),
      "my_query_select",
      $stmt
    );
    $result = pg_execute($db->get_conn(), "my_query_select", array());
  } else {
    $stmt .= ' and d.sl_code = $1';
    $result = pg_prepare(
      $db->get_conn(),
      "my_query_select",
      $stmt
    );
    $result = pg_execute($db->get_conn(), "my_query_select", array($username));
  }
  if ($result === false) {
    http_response_code(403);
    echo json_encode('Error request while execute');
    exit();
  }
  $parsed_result = pg_fetch_all($result);
  if ($parsed_result === false) {
    http_response_code(403);
    echo json_encode('Error request while execute');
    exit();
  }
  http_response_code(200);
  echo json_encode(['data' => $parsed_result]);
} catch (InvalidArgumentException  $e) {
  $db->closeConn();
  http_response_code(403);
  echo "Ошибка: " . $e->getMessage();
} finally {
  $db->closeConn();
  exit();
}
