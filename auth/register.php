<?php
require_once('../Cors.php');
$cors = new Cors();
$cors->cors_policy();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once 'db.php';
  $config = parse_ini_file('../config.ini', true);
  $db = new DB_Conncetion($config['DB']);
  $db->db_connect();


  $username = $_POST['username'];
  $password = $_POST['password'];
  $role = $_POST['role'];

  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  try {
    $stmt =  pg_prepare($db->get_conn(), "my_query_insert", "INSERT INTO  accounts.temp_users (username, password_hash, role) select com.username, com.password_hash, com.role from
        (select username,password_hash,role from
          (select $1 as username, $2 as password_hash, $3 as role) sel
          join accounts.service srv on srv.sl_code= sel.role
          ) com");
    $insert_result = pg_execute($db->get_conn(), "my_query_insert", array($username, $hashed_password, $role));
    if ($insert_result === false) {
      // print pg_last_error($db->db_connect('postgres', 'postgres'));
      throw new RuntimeException('It`s impossible to create user');
    }
    // var_dump($insert_result);
    echo json_encode(['message' => 'User is successfully created']);
  } catch (Exception $e) {
    echo json_encode($e->getMessage());
  }
}
