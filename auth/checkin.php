<?php
require_once('../Cors.php');
$cors = new Cors();
$cors->cors_policy();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    require_once 'db.php';
    $config = parse_ini_file('../config.ini', true);
    $db = new DB_Conncetion($config['DB']);
    $db->db_connect();
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
    if (pg_num_rows($result) > 0) {
      // добавление в таблицу пользователей
      $req_insert = 'INSERT INTO accounts.users (username, password_hash, created_at, role)
            VALUES ($1, $2, current_timestamp, $3)
            ON CONFLICT(role)
            DO UPDATE SET
            username = EXCLUDED.username,
            password_hash = EXCLUDED.password_hash,
            created_at = EXCLUDED.created_at';

      $stmt =  pg_prepare($db->get_conn(), "upsert", $req_insert);
      $upsert_result = pg_execute($db->get_conn(), "upsert", array($username, $hashed_password, $user_role));

      // удаляем из временной таблицы
      $req_delete = 'DELETE FROM accounts.temp_users
        WHERE role=$1;
      ';
      // удаление
      $stmt_del =  pg_prepare($db->get_conn(), "delete", $req_delete);
      pg_execute(
        $db->get_conn(),
        "delete",
        array($user_role)
      );
      http_response_code(200);
      echo json_encode(['message' => 'Successfull']);
      session_write_close();
      exit();
    } else {
      http_response_code(403);
      echo json_encode(['Error' => 'There are no users!']);
      session_write_close();
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
