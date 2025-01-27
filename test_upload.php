<?php
// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);
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

function get_directory()
{
  $dir =  'C:/Users/User/Desktop/programming/programming/php/server-for-loading/';
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
  $ACCEPTABLE_FILE_SIZE = 5 * 1024 * 1024;
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
        header('HTTP/1.0 404 Internal Server Error');
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

// function return_response($code)
// {
//   if (!function_exists('http_response_code')) {
//     function http_response_code($code = NULL)
//     {

//       if ($code !== NULL) {

//         switch ($code) {
//           case 100:
//             $text = 'Continue';
//             break;
//           case 101:
//             $text = 'Switching Protocols';
//             break;
//           case 200:
//             $text = 'OK';
//             break;
//           case 201:
//             $text = 'Created';
//             break;
//           case 202:
//             $text = 'Accepted';
//             break;
//           case 203:
//             $text = 'Non-Authoritative Information';
//             break;
//           case 204:
//             $text = 'No Content';
//             break;
//           case 205:
//             $text = 'Reset Content';
//             break;
//           case 206:
//             $text = 'Partial Content';
//             break;
//           case 300:
//             $text = 'Multiple Choices';
//             break;
//           case 301:
//             $text = 'Moved Permanently';
//             break;
//           case 302:
//             $text = 'Moved Temporarily';
//             break;
//           case 303:
//             $text = 'See Other';
//             break;
//           case 304:
//             $text = 'Not Modified';
//             break;
//           case 305:
//             $text = 'Use Proxy';
//             break;
//           case 400:
//             $text = 'Bad Request';
//             break;
//           case 401:
//             $text = 'Unauthorized';
//             break;
//           case 402:
//             $text = 'Payment Required';
//             break;
//           case 403:
//             $text = 'Forbidden';
//             break;
//           case 404:
//             $text = 'Not Found';
//             break;
//           case 405:
//             $text = 'Method Not Allowed';
//             break;
//           case 406:
//             $text = 'Not Acceptable';
//             break;
//           case 407:
//             $text = 'Proxy Authentication Required';
//             break;
//           case 408:
//             $text = 'Request Time-out';
//             break;
//           case 409:
//             $text = 'Conflict';
//             break;
//           case 410:
//             $text = 'Gone';
//             break;
//           case 411:
//             $text = 'Length Required';
//             break;
//           case 412:
//             $text = 'Precondition Failed';
//             break;
//           case 413:
//             $text = 'Request Entity Too Large';
//             break;
//           case 414:
//             $text = 'Request-URI Too Large';
//             break;
//           case 415:
//             $text = 'Unsupported Media Type';
//             break;
//           case 500:
//             $text = 'Internal Server Error';
//             break;
//           case 501:
//             $text = 'Not Implemented';
//             break;
//           case 502:
//             $text = 'Bad Gateway';
//             break;
//           case 503:
//             $text = 'Service Unavailable';
//             break;
//           case 504:
//             $text = 'Gateway Time-out';
//             break;
//           case 505:
//             $text = 'HTTP Version not supported';
//             break;
//           default:
//             exit('Unknown http status code "' . htmlentities($code) . '"');
//         }

//         $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

//         header($protocol . ' ' . $code . ' ' . $text);

//         $GLOBALS['http_response_code'] = $code;
//       } else {

//         $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
//       }

//       return $code;
//     }
//   }
// }


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

cors();
upload_files();
