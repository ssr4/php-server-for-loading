<?php
require_once('../Cors.php');
$cors = new Cors();
$cors->cors_policy();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    require_once 'db.php';
    $config = parse_ini_file('../config.ini', true);
    $db = new DB_Connection($config['DB']);
    $db_connect = $db->db_connect();
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_role = $_POST['role'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $result = pg_prepare($db->get_conn(), "my_query_select", 'SELECT * FROM  accounts.temp_users where role = $1');
    $result = pg_execute($db->get_conn(), "my_query_select", array($user_role));
    if ($result === false) {
      echo json_encode('No data');
      http_response_code(401);
      exit();
    }
    $parsed_result = pg_fetch_assoc($result);
    $result_data = array();
    if (pg_num_rows($result) > 0) {
      // добавление в таблицу пользователей
      $stmt = "select accounts.f_insert_new_users ('" . $username . "','" . $hashed_password  . "','" . $user_role  . "')";
      $query = pg_query($db_connect, $stmt);
      if (!$query) {
        throw new Exception('Error during DB query insert into temp_users');
      }
      $result = pg_fetch_array($query);
      array_push($result_data,  array("status" => "ok", "inserted_on" => $result[0]));
      // удаляем из временной таблицы
      $req_delete = 'DELETE FROM accounts.temp_users
        WHERE username=$1;
      ';
      // удаление
      $stmt_del =  pg_prepare($db->get_conn(), "delete", $req_delete);
      pg_execute(
        $db->get_conn(),
        "delete",
        array($username)
      );
      echo json_encode($result_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      http_response_code(200);
    } else {
      http_response_code(403);
      echo json_encode(['Error' => 'There are no users!']);
      session_write_close();
      $db->closeConn();
      exit();
    }
  } catch (Exception $e) {
    $db->closeConn();
    http_response_code(403);
    echo json_encode($e->getMessage());
    exit();
  } finally {
    $db->closeConn();
    exit();
  }
}
