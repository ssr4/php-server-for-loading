<?php
// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'Cors.php';
$cors = new Cors();
$cors->cors_policy();
function deleteAllFilesInDirectory(string $dir, bool $withoutDate = false)
{
  // ошибка это не директория
  if (!is_dir($dir) || !file_exists($dir)) {
    throw new InvalidArgumentException($dir . ' - is not a dir or it doesn` t exist.');
  }

  ### RecursiveIteratorIterator и RecursiveDirectoryIterator - эти классы 
  ### позволяют рекрсивно обойти все файлы и директории внутри заданной 
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
  if (count(scandir($dir)) == 1) {
    rmdir($dir);
  }
}

try {
  $config = parse_ini_file('config.ini', true);
  $dir = $config['DIR']['dir'];
  if (isset($_POST['dir'])) {
    $dir .= $_POST['dir'] . '/';
    deleteAllFilesInDirectory($dir, true);
  } else {
    $services_array = array('services', 'orders');
    foreach ($services_array as $service) {
      $dir .=  $service . '/';
      deleteAllFilesInDirectory($dir);
    }
  }
} catch (InvalidArgumentException  $e) {
  // http_response_code(404);
  header("HTTP/1.0 404 " . $e->getMessage());
} catch (Exception $e) {
  http_response_code(400);
  echo "Ошибка при удалении: " . $e->getMessage();
} finally {
  exit();
}
