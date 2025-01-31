<?php
// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'Cors.php';
$cors = new Cors();
function get_directory()
{
  $dir =  'C:/Users/User/Desktop/programming/programming/php/server-for-loading/services/';
  $matching_services_and_directories  = array(
    'ДМВ' => 'dmv',
    'ДАВС' => 'davs',
    'Д' => 'd',
    'ДИ' => 'di',
    'ДМ' => 'dm',
    'ДМС' => 'dms',
    'ДПО' => 'dpo',
    'РДЖВ' => 'rdzv',
    'НТЭ' => 'nte',
    'ДЭЗ' => 'dez',
    'НС' => 'ns',
    'СЗДОСС' => 'szdoss',
    'Т' => 't',
  );
  // переданные поля в запросе
  $posts =  array_keys($_POST);
  foreach ($posts as $post) {
    if ($post === 'services') {
      $services = $_POST['services'];
      $service = $matching_services_and_directories[$services];
      $dir .= $service . '/';
    }
  }
  return $dir;
}
function upload_files()
{
  $ACCEPTABLE_FILE_SIZE = 8 * 1024 * 1024;
  $keys = array_keys($_FILES);
  try {
    foreach ($keys as $key) {
      $file = $_FILES[$key];
      // echo ini_get('post_max_size') . PHP_EOL;
      // echo $ACCEPTABLE_FILE_SIZE . PHP_EOL;
      // echo $file['size'] . PHP_EOL;

      // Undefined | Multiple Files | $_FILES Corruption Attack
      // If this request falls under any of them, treat it invalid.
      if (
        !isset($file['error']) ||
        is_array($file['error'])
      ) {
        http_response_code(403);
        throw new RuntimeException('Invalid parameters.');
      }

      // код ошибки файла
      switch ($file['error']) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_NO_FILE:
          http_response_code(400);
          throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          http_response_code(413);
          // header('HTTP/1.0 404 Internal Server Error');
          throw new RuntimeException('Exceeded filesize limit.');
        default:
          http_response_code(409);
          throw new RuntimeException('Unknown errors.');
      }
      // размер файла
      if ($file['size'] > $ACCEPTABLE_FILE_SIZE) {
        http_response_code(
          413
        );
        // header('HTTP/1.0 404 Internal Server Error');
        exit();
        // throw new RuntimeException('Exceeded filesize limit.');
      }

      $upload_dir = get_directory();
      // получаем номер региона
      $region = explode("_", $key)[0];
      $upload_dir .= $region . '/';
      if (!create_directory_and_upload_file($upload_dir, $file))
        throw new RuntimeException('Failed to move uploaded file.');
    }
    // разрываем соединение
    close_conn();
    echo json_encode('Files are uploaded successfully. ');
  } catch (RuntimeException $e) {
    echo json_encode($e->getMessage());
  }
}
function create_directory_and_upload_file($dir, $file)
{
  if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
  }
  // $uploadfile = $dir . convert_date() . '_' . basename($file['name']);
  $uploadfile = $dir . basename($file['name']);
  if (!move_uploaded_file(
    $file['tmp_name'],
    $uploadfile,
  )) {
    // throw new RuntimeException('Failed to move uploaded file.');
    return false;
  } else {
    chmod($uploadfile, 0777);
    return true;
  };
}
function convert_date()
{
  $date = new DateTime();
  return $date->format('d-m-Y');
}

// разрываем соединение
function close_conn()
{
  session_write_close();
}

function cors()
{
  // Allow from any origin
  if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
  }

  // Access-Control headers are received during OPTIONS requests
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
      // may also be using PUT, PATCH, HEAD etc
      header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
      header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
  }
}
$cors->cors_policy();
// cors();
upload_files();
