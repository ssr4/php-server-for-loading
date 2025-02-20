<?php
require_once('../Cors.php');
$cors = new Cors();
$cors->cors_policy();
$config = parse_ini_file("../config.ini");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    require_once 'db.php';
    $db = new DB_Conncetion();
    $db->db_connect('postgres', 'postgres');
    $username = $_POST['username'];
    $password = $_POST['password'];
    // echo json_encode($hashed_password);
    $result = pg_prepare($db->get_conn(), "my_query_select", 'SELECT * FROM  test.users where username = $1');
    $result = pg_execute($db->get_conn(), "my_query_select", array($username));
    if ($result === false) {
      http_response_code(403);
      close_conn();
      throw new RuntimeException('Error request while execute');
    }
    if (pg_num_rows($result) === 0) {
      http_response_code(403);
      close_conn();
      throw new RuntimeException('The are no users with such username !');
    }
    $result = pg_fetch_assoc($result);
    if ($username && password_verify($password, $result['password_hash'])) {
      $header = [
        "alg" => "HS256",
        "typ" => "JWT"
      ];
      $payload = [
        'iss' => 'inform_department', // Издатель
        'name' => $username,   // Субъект
        'iat' => time(),        // Время создания
        'exp' => time() + 3600, // Срок действия (1 час)
      ];
      // пока тест test
      $secret_key = ['secret' => $config['secret_key']];
      $jwt = jwtEncode($header)  .  jwtEncode($payload) . '\n' . jwtEncode($secret_key);
      http_response_code(200);
      echo json_encode(['token' => $jwt, 'role' => $result['role']]);
      close_conn();
    } else {
      http_response_code(405);
      close_conn();
      throw new RuntimeException('Wrong username!');
    }
  } catch (Exception $e) {
    http_response_code(response_code: 401);
    close_conn();
    throw new RuntimeException($e->getMessage());
  }
}

function jwtEncode($str)
{
  return base64_encode(json_encode(value: $str));
}

function close_conn()
{
  session_write_close();
  exit();
}

function decodeString($str)
{
  return base64_decode($str);
}
