<?php
// вывод ошибок при отладке
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'Cors.php';
$cors = new Cors();
$cors->cors_policy();

function delete_files()
{
  // $dir = '';
  $dir = 'C:/Users/User/Desktop/programming/programming/php/server-for-loading/services/';
  $scanned_directory = array_diff(scandir($dir), array('..', '.'));
  // print_r($scanned_directory);
  $services = json_decode(file_get_contents('service/service.json', true));
  $array = json_decode(json_encode($services), true);
  foreach ($scanned_directory as $directory) {
    print_r($directory);
    remove_directory($dir . $directory);
  }
  // foreach ($array['services'] as $item) {

  //   foreach ($array['extensions'] as $extension) {
  //     echo $dir . $item . "/*." . $extension;

  //   }
  // }

  // if (file_exists($dir)) {
  //   $file_date = date_create('@'  . filemtime($dir));
  //   $current_date = new DateTime('tomorrow');
  //   $interval = $current_date->diff($file_date);
  //   echo $interval->days;
  //   remove_directory($dir);
  // }


  // foreach ($services as $service) {
  //   print_r($service);
  // }
  // print_r($services);
  // array_map('unlink', glob($dir . "*.txt"));



}

function remove_directory($dir)
{

  // if (!is_dir($file)) {
  //   //удаление файла в случае. если джата создания меньше текущей даты на сутки
  //   // return unlink($file);
  //   $yesterdayEnd = strtotime('yesterday 23:59:59');
  //   $fileModificationTime = filectime($file);
  //   if ($fileModificationTime <= $yesterdayEnd) {
  //     echo "Yes";
  //   }
  //   return unlink($file);
  // }

  if (!is_dir($dir)) {
    $file = $dir;
    $yesterdayEnd = strtotime('yesterday 23:59:59');
    $fileModificationTime = filectime($file);
    if ($fileModificationTime <= $yesterdayEnd) {
      echo 'Yes';
      return;
    }
    echo 'No';
    return;
    // return unlink($file);
  }

  foreach (scandir($dir) as $item) {
    if ($item == '.' || $item == '..') continue;
    if (!remove_directory($dir . '/' . $item)) {
      echo $dir . $item;
      chmod($dir . $item, 0777);
      if (!remove_directory($dir . '/' . $item)) return false;
    }
  }
  if (count(scandir($dir)) == 2) {
    return rmdir($dir);
  }
}

delete_files();
