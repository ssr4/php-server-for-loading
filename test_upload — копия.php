<?php
// кодировка
header('Content-Type: text/html; charset=utf-8');
// mb_internal_encoding("UTF-8");

// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Cors.php';
$cors = new Cors();
function get_directory()
{
  // переданные поля в запросе
  $posts =  array_keys($_POST);
  // директория в которую кладем
  // $dir = 'C:/Users/User/Desktop/programming/programming/php/server-for-loading/';
  $dir = '/usr/share/nginx/html/build/storage/';

  // создаем вложенные папки
  foreach ($posts as $post) {
    if ($post == 'directory') {
      $service = $_POST[$post] . '/';
      $dir .= $service;
    }
  }
  return $dir;
}
function upload_files()
{
  try {
    // если были переданы регионы
    if (isset($_POST['regions'])) {
      // получаем файлы по регионам
      $regions = explode(",", $_POST['regions']);
      $region_number = 0;
      if (count($regions)) {
        foreach ($regions as $region) {
          $region_number += 1;
          if ((int) $region) {
            upload_file($region_number);
          }
        }
      }
    } else upload_file();


    // разрываем соединение
    close_conn();
    echo json_encode('Files are uploaded successfully. ');
  } catch (RuntimeException $e) {
    echo json_encode($e->getMessage());
  }
}

function upload_file($region_number = '')
{
  $ACCEPTABLE_FILE_SIZE = 6 * 1024 * 1024;
  $keys = array_keys($_FILES);
  foreach ($keys as $key) {
    $file = $_FILES[$key];

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
        throw new RuntimeException('Unknown errors. ????? ');
    }
    // размер файла
    if ($file['size'] > $ACCEPTABLE_FILE_SIZE) {
      http_response_code(
        413
      );
      exit();
    }

    $upload_dir = get_directory() . $region_number . '/';
    if (!create_directory_and_upload_file($upload_dir, $file))
      throw new RuntimeException('Failed to move uploaded file.');
  }
}

function create_directory_and_upload_file($dir, $file)
{
  if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
  }
  $uploadfile = $dir . $file['name'];
  if (!copy(
    $file['tmp_name'],
    $uploadfile,
  )) {
    throw new RuntimeException('Failed to move uploaded file.');
    // return false;
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

$cors->cors_policy();
upload_files();
