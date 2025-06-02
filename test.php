<?php

try {
  $keys = array_keys($_FILES);
  foreach ($keys as $key) {
    $file = $_FILES[$key];
    // print_r($file);
    print_r($file['name']);
    print_r($file['tmp_name']);
    print_r($file);
  }
} catch (InvalidArgumentException  $e) {
  http_response_code(403);
  echo "Ошибка: " . $e->getMessage();
} finally {
  exit();
}
