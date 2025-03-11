<?php

$dir =  '/usr/share/nginx/html/build/storage/orders/cusi/';
$array_of_extensions = ['.pdf'];

$html = '<!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/css/bootstrap-3.4.1-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="tree.css">
      </head>' .
      "<h3 style='text-align:center;'>Материалы к штормовому предупреждению</h3>" .
      "<div style='margin:1%;padding:1%;'>" .
      "<table class='table table-striped table-condensed table-bordered'>" .
      "<thead>
        <tr>
            <th style='text-align: center;'>Файл</th>
            <th style='text-align: center;'>Дата загрузки</th>
        </tr>
        </thead>";

foreach ($array_of_extensions as $extention) {
  $max_date_of_create = 0;
  $required_file = '';
  $new_name_of_required_file = '';
  foreach (glob($dir . '*' . $extention) as $filename) {
    if (file_exists($filename)) {
      //if (filectime($filename) > $max_date_of_create) {
        $max_date_of_create = filectime($filename);
        $required_file = str_replace($dir, '', $filename);
        $new_name_of_required_file = str_replace($extention, '', $required_file);
      //}
if ($required_file && $new_name_of_required_file) {
    $html .= render_html($new_name_of_required_file, $required_file, $max_date_of_create);
  }

    }
  }

  
}

echo $html .= "</table>
      </div>
      </html>";

function render_html($name, $name_with_ext, $date_of_create)
{
  return
    "<tr><td style='text-align:left;'><a href='/storage/orders/cusi/" . $name_with_ext. "'>" . $name . "</a></td><td>" . "Дата загрузки файлов: " . date('d.m.Y H:i:s.', $date_of_create) . "</td></tr>". PHP_EOL;
}
?>
