<?php
// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'Cors.php';
$cors = new Cors();
function deleteAllFilesInDirectory(string $dir, bool $withoutDate = false)
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
      if ($withoutDate) {
        // удаляем файл в независимости от времени создания
        unlink($item->getRealPath());
      } else {
        // получение вчерашних суток
        // получение времени создания файла
        $yesterdayEnd = strtotime('yesterday 23:59:59');
        $fileCreationTime = filectime($item);
        if ($fileCreationTime <= $yesterdayEnd) {
          // удаляем если файл старый
          unlink($item->getRealPath());
        }
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
  $cors->cors_policy();
  // C:/Users/User/Desktop/programming/programming/php/server-for-loading/
  // если была передана директория
  if (isset($_POST['dir'])) {
    $dir =
      'C:/Users/User/Desktop/programming/programming/php/server-for-loading/' . $_POST['dir'] . '/';
    deleteAllFilesInDirectory($dir, true);
  } else {
    $services_array = array('services_test', 'orders_test');
    foreach ($services_array as $service) {
      $dir = '/usr/share/nginx/html/build/storage/' . $service . '/';
      deleteAllFilesInDirectory($dir);
    }
  }
} catch (InvalidArgumentException  $e) {
  http_response_code(403);
  echo "Ошибка: " . $e->getMessage();
} catch (Exception $e) {
  http_response_code(400);
  echo "Ошибка при удалении: " . $e->getMessage();
}
