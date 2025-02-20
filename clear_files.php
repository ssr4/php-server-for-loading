<?php
// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'Cors.php';
$cors = new Cors();
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
  // cors_policy();
  $cors->cors_policy();
  // $dir =
  //   '/usr/share/nginx/html/build/storage/services_test/';
  $services_array = array('services_test', 'orders_test');
  foreach ($services_array as $service) {
    $dir = '/usr/share/nginx/html/build/storage/' . $service . '/';
    deleteAllFilesInDirectory($dir);
  }
} catch (InvalidArgumentException  $e) {
  http_response_code(403);
  echo "Ошибка: " . $e->getMessage();
} catch (Exception $e) {
  http_response_code(400);
  echo "Ошибка при удалении: " . $e->getMessage();
}
