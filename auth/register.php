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

  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  try {
    $result = pg_prepare($db->get_conn(), "my_query_select", 'SELECT * FROM  test.temp_users where username = $1');
    $result = pg_execute($db->get_conn(), "my_query_select", array($username));
    if ($result === false) {
      echo json_encode('Error request while execute');
      exit();
    }

    if (pg_num_rows($result) > 0) {
      echo json_encode('Such user already exists !');
    } else {
      $stmt =  pg_prepare($db->get_conn(), "my_query_insert", 'INSERT INTO  test.temp_users (username, password_hash) VALUES ($1, $2)');
      $insert_result = pg_execute($db->get_conn(), "my_query_insert", array($username, $hashed_password));
      // var_dump($insert_result);
      echo json_encode(['message' => 'User is successfully created']);
    }
  } catch (Exception $e) {
    echo json_encode($e->getMessage());
  }
}
