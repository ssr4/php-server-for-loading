<?php
require_once('../Cors.php');
$cors = new Cors();
$cors->cors_policy();
$config = parse_ini_file("../config.ini", true);
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  try {
    require_once 'db.php';
    $db = new DB_Connection($config['DB']);
    $db->db_connect();

    // остановился здесь надо сделать запрос к серверу после успешной аутентификации, после чего 
    // понять пользователь админ или кто вообще 
    // это будет вывод из новой вьюшки
    $email = $_GET['email'];
    $req = "select * from accounts.select_services('" . $email . "')";

    $result = pg_prepare($db->get_conn(), "my_query_select", $req);
    $result = pg_execute($db->get_conn(), "my_query_select", array());
    if ($result === false) {
      http_response_code(403);
      close_conn();
      throw new RuntimeException('Error request while execute');
    }
    if (pg_num_rows($result) === 0) {
      closeConn();
      throw new RuntimeException('The are no users with such username !');
    }
    $result = pg_fetch_all($result);
    echo json_encode($result, flags: JSON_UNESCAPED_UNICODE);
  } catch (Exception $e) {
    $db->closeConn();
    http_response_code(401);
    closeConn();
    throw new RuntimeException($e->getMessage());
  } finally {
    $db->closeConn();
    exit();
  }
}

function closeConn()
{
  session_write_close();
  exit();
}
