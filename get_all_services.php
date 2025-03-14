<?php
// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'Cors.php';
require_once 'auth/db.php';
require_once 'auth/validate_token.php';
$cors = new Cors();
$cors->cors_policy();
$config = parse_ini_file('config.ini', true);
$db = new DB_Conncetion($config['DB']);
$db->db_connect();
try {
  // here test
  $header_auth = apache_request_headers()['Authorization'];
  if (isset($header_auth)) {
    // получаем токен и подключаем файл конфига
    $token = new Token($header_auth, $config['Secret']);
    if (!$token->isValidToken()) {
      http_response_code(401);
      throw new RuntimeException('is not a valid token!');
    }
  } else throw new RuntimeException('there is no token!');
  $username = $_POST['username'];
  $stmt = 'SELECT d.sl_code, d.sl_name, d.directory, regions,s.sl_full_name, d.order_description  from accounts.directories d inner join accounts.service s on d.sl_code = s.sl_code';
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
