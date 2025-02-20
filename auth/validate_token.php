<?php
require_once('../Cors.php');
$cors = new Cors();
$cors->cors_policy();
try {
  // пока тест доделать механизм refresh
  $token = $_POST['Authorization'] ?? null;
  // echo json_encode($token);
  if ($token) {
    // Удаление префикса "Bearer "
    $token = str_replace('Bearer ', '', $token);
    // echo $token;
    // Проверка токена
    if (parseToken($token)) {
    } else {
      // Токен недействителен, возвращаем ошибку
      http_response_code(401);
      echo json_encode(['error' => 'Invalid token']);
      exit;
    }
  } else {
    // Токен не передан, возвращаем ошибку
    http_response_code(401);
    echo json_encode(['error' => 'Token not provided']);
    exit;
  }
} catch (Exception $e) {
  echo json_encode($e->getMessage());
}

function parseToken($token)
{
  if (!validateToken($token))
    return false;
  return true;
}

function validateToken($token)
{
  // подключаем файл конфига
  $config = parse_ini_file("../config.ini");
  $text = str_replace('\'', '', decodeString($token));
  if (preg_match_all('/{([^}]*)}/', $text, $matches, PREG_OFFSET_CAPTURE)) {
    foreach ($matches[1] as $match) {
      // echo "{$match[0]}\n";
      $obj = json_decode("{" . $match[0] . "}");
      if (isset($obj->exp))
        $expiration_time = $obj->exp;
      if (isset($obj->secret))
        $secret =  $obj->secret;
    }
    if (!$expiration_time || !$secret) {
      return false;
      // throw new Exception('The token has expired or token is not valid');
    } else {
      if ($expiration_time < time())
        return false;

      if ($secret !== $config['secret_key'])
        return false;
      // throw new Exception('The token is not valid');
    }
  } else {
    return false;
    // throw new Exception('The token has expired or token is not valid');
  }
}

function decodeString($str)
{
  return
    base64_decode($str);
}
