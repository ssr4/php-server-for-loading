<?php
require_once('../Cors.php');
$cors = new Cors();
$cors->cors_policy();
$config = parse_ini_file("../config.ini", true);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    require_once 'db.php';
    $db = new DB_Conncetion($config['DB']);
    $db->db_connect();
    $username = $_POST['username'];
    $password = $_POST['password'];
    // echo json_encode($hashed_password);
    $result = pg_prepare($db->get_conn(), "my_query_select", 'SELECT * FROM  accounts.users where username = $1');
    $result = pg_execute($db->get_conn(), "my_query_select", array($username));
    if ($result === false) {
      http_response_code(403);
      close_conn();
      throw new RuntimeException('Error request while execute');
    }
    if (pg_num_rows($result) === 0) {
      if (!checkInTempTable($db, $username, $password)) {
        http_response_code(403);
        closeConn();
        throw new RuntimeException('The are no users with such username !');
      } else closeConn();
    }
    $result = pg_fetch_assoc($result);
    if ($username && password_verify($password, $result['password_hash'])) {
      $curr_time = time();
      $expires_at = $curr_time + 3600;
      $header = [
        "alg" => "HS256",
        "typ" => "JWT"
      ];
      $payload = [
        'iss' => 'inform_department', // Издатель
        'name' => $username,   // Субъект
        'iat' => $curr_time,        // Время создания
        'exp' => $expires_at, // Срок действия (1 час)
      ];
      // пока тест test
      $secret_key = ['secret' => $config['Secret']['secret_key']];
      $jwt = jwtEncode($header) . '.' .  jwtEncode($payload)  . '.' . jwtEncode($secret_key);
      http_response_code(200);
      echo json_encode(['token' => $jwt, 'role' => $result['role'], 'expires_at' =>  $expires_at]);
      closeConn();
    } else {
      http_response_code(406);
      closeConn();
      throw new RuntimeException('Wrong username!');
    }
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

function checkInTempTable($db, $username, $password)
{
  $result = pg_prepare($db->get_conn(), "my_query_select_from_temp", 'SELECT * FROM  accounts.temp_users where username = $1');
  $result = pg_execute($db->get_conn(), "my_query_select_from_temp", array($username));

  if (pg_num_rows($result) === 0)
    return false;
  $result = pg_fetch_assoc($result);
  if (!password_verify($password, $result['password_hash']))
    return false;
  echo json_encode(['registration' => 'register', 'role' => $result['role']]);
  return true;
}

function jwtEncode($str)
{
  return base64_encode(json_encode($str));
}

function closeConn()
{
  session_write_close();
  exit();
}

function decodeString($str)
{
  return base64_decode($str);
}
