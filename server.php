<?php
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

  // echo "You have CORS!";
}
function upload_files()
{
  $ACCEPTABLE_FILE_SIZE = 5 * 1024 * 1024;
  $dir =  'C:/Users/User/Desktop/programming/programming/php/';

  $services = htmlspecialchars($_POST["services"]);
  $matching_services_and_directories  = array(
    'ДМВ' => 'dmv',
    'ДАВС' => 'davs',
    'Д' => 'd',
    'ДИ' => 'di',
    'ДМС' => 'dms',
    'ДПО' => 'dpo',
    'РДЖВ' => 'rdzv',
    'НТЭ' => 'nte',
    'ДЭЗ' => 'dez',
    'НС' => 'ns',
    'СЗДОСС' => 'szdoss',
    'Т' => 't',
  );

  $dir .= $matching_services_and_directories[$services] . '/';
  $service = $matching_services_and_directories[$services];
  $keys = array_keys($_FILES);
  $uploaddir = '/test';
  try {
    foreach ($keys as $key) {
      $file = $_FILES[$key];
      // Undefined | Multiple Files | $_FILES Corruption Attack
      // If this request falls under any of them, treat it invalid.
      if (
        !isset($file['error']) ||
        is_array($file['error'])
      ) {
        throw new RuntimeException('Invalid parameters.');
      }
      // код ошибки файла
      switch ($file['error']) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new RuntimeException('Exceeded filesize limit.');
        default:
          throw new RuntimeException('Unknown errors.');
      }
      // размер файла
      if ($file['size'] > $ACCEPTABLE_FILE_SIZE)
        throw new RuntimeException('Exceeded filesize limit.');

      $uploaddir = './test/';
      $uploadfile = $uploaddir . basename($file['name']);
      if (!move_uploaded_file(
        $file['tmp_name'],
        $uploadfile,
      )) {
        throw new RuntimeException('Failed to move uploaded file.');
      }

      echo 'File is uploaded successfully.';
    }
  } catch (RuntimeException $e) {
    echo $e->getMessage();
  }
}

cors();
upload_files();
