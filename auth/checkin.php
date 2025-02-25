<?php
require_once('../Cors.php');
$cors = new Cors();
$cors->cors_policy();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once 'db.php';
  $db = new DB_Conncetion();
  $db->db_connect('postgres', 'postgres');

  $username = $_POST['username'];
  $password = $_POST['password'];
  $user_role = $_POST['role'];

  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  try {
    $result = pg_prepare($db->get_conn(), "my_query_select", 'SELECT * FROM  test.temp_users where role = $1');
    $result = pg_execute($db->get_conn(), "my_query_select", array($user_role));
    if ($result === false) {
      echo json_encode('Error request while execute');
      exit();
    }
    $parsed_result = pg_fetch_assoc($result);
    if (pg_num_rows($result) > 0) {
      // добавление в таблицу пользователей
      $req_insert = 'INSERT INTO test.users (username, password_hash, created_at, role)
            VALUES ($1, $2, current_timestamp, $3)
            ON CONFLICT(role)
            DO UPDATE SET
            username = EXCLUDED.username,
            password_hash = EXCLUDED.password_hash,
            created_at = EXCLUDED.created_at';

      $stmt =  pg_prepare($db->get_conn(), "upsert", $req_insert);
      $upsert_result = pg_execute($db->get_conn(), "upsert", array($username, $hashed_password, $user_role));

      // удаляем из временной таблицы
      $req_delete = 'DELETE FROM test.temp_users
        WHERE role=$1;
      ';
      // удаление
      $stmt_del =  pg_prepare($db->get_conn(), "delete", $req_delete);
      pg_execute(
        $db->get_conn(),
        "delete",
        array($user_role)
      );

      echo json_encode(['message' => 'Successfull']);
      http_response_code(200);
      session_write_close();
      exit();
    } else {
      http_response_code(403);
      echo json_encode(['Error' => 'There are no users!']);
      session_write_close();
      exit();
    }
  } catch (Exception $e) {
    echo json_encode($e->getMessage());
  }
} else {
  http_response_code(405);
  session_write_close();
  exit();
}
