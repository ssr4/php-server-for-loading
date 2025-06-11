<?php
// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);
// require_once 'Cors.php';
// $cors = new Cors();
// $cors->cors_policy();
$config = parse_ini_file("config.ini", true);
require_once 'auth/validate_token.php';
try {
  // TODO 
  if (upload_files()) {
    echo json_encode('Files are uploaded successfully. ');
    http_response_code(200);
    session_write_close();
  } else {
    echo json_encode('Couldn`t upload files');
    http_response_code(405);
    session_write_close();
  }
} catch (InvalidArgumentException  $e) {
  http_response_code(403);
  echo "Ошибка: " . $e->getMessage();
} finally {
  exit();
}

function get_directory()
{
  // создаем вложенные папки через переданные поля в запросе
  global $config;
  $dir = $config['DIR']['dir'] . $_POST['directory'] . '/';
  // $dir = $_POST['directory'] . '/';
  return $dir;
}
function upload_files()
{
  try {
    $is_correct_uploading = 0;
    // если были переданы регионы
    $keys = array_keys($_FILES);
    foreach ($keys as $key) {
      $file = $_FILES[$key];

      if (isset($_POST['regions'])) {
        // получаем файлы по регионам
        $regions = explode(",", $_POST['regions']);
        $region_number = 0;

        if (count($regions)) {

          foreach ($regions as $region) {
            $region_number += 1;
            if ((int) $region) {
              $is_correct_uploading = upload_file($region_number, $file);
            }
          }
        }
      } else $is_correct_uploading = upload_file('', $file);
    }
    if ($is_correct_uploading)
      return 1;
    else return 0;
  } catch (RuntimeException $e) {
    echo json_encode($e->getMessage());
  }
}

function upload_file($region_number = '', $file)
{
  $ACCEPTABLE_FILE_SIZE = 6 * 1024 * 1024;
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
  if (!create_directory_and_upload_file($upload_dir, $file)) {
    throw new RuntimeException('Failed to move uploaded file.');
  } else {
    // TODO storm_ddmmYYYY.xlsx get_directory()
    $match = [];
    preg_match("/storm_[0-9]{2}.[0-9]{1}.[0-9]{3}.xlsx/", $file['name'], $match);
    if (count($match) == 1) {
      launch_python_script($upload_dir . $file['name']);
    }
    return 1;
  }
}
// }

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



function launch_python_script($file)
{
  // Запуск Python-скрипта в фоне (не блокирует ответ)
  // TODO
  $python_script = "/usr/share/nginx/html/build/orw_tablo/py/storm/upd_load_storm_tst.py";
  $output = shell_exec("/usr/bin/python3 $python_script $file > /dev/null 2>&1 &");
}
