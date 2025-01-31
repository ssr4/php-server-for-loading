<?php
// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);
function cors_policy()
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

function deleteAllFilesInDirectory(string $dir)
{
  // ошибка это не директория
  if (!is_dir($dir)) {
    throw new InvalidArgumentException('$directory is not a directory.');
  }

  ### RecursiveIteratorIterator и RecursiveDirectoryIterator - эти классы 
  ### позволяют рекрсивнро обй  ти все файлы и директории внутри заданной 
  ### FilesystemIterator::SKIP_DOTS - пропуск вложенной и родительской дир
  ### RecursiveIteratorIterator::CHILD_FIRST - удаление директорий и поддир. до родительской 

  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
    if ($item->isFile()) {
      ### получение вчерашних суток
      ### получение времени создания файла
      $yesterdayEnd = strtotime('yesterday 23:59:59');
      $fileCreationTime = filectime($item);
      if ($fileCreationTime <= $yesterdayEnd) {
        // удаляем если файл старый
        unlink($item->getRealPath());
      }
    } elseif ($item->isDir()) {
      if (count(scandir($dir)) == 2) {
        rmdir($item->getRealPath());
      }
    }
  }
  if (count(scandir($dir)) == 2) {
    rmdir($dir);
  }
}

try {
  cors_policy();
  $dir =
    'C:/Users/User/Desktop/programming/programming/php/server-for-loading/services/';
  deleteAllFilesInDirectory($dir);
} catch (InvalidArgumentException  $e) {
  http_response_code(403);
  echo "Ошибка: " . $e->getMessage();
} catch (Exception $e) {
  http_response_code(400);
  echo "Ошибка при удалении: " . $e->getMessage();
}
