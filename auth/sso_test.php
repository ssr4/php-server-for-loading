<?php
require_once('../Cors.php');
$cors = new Cors();
$cors->cors_policy();
$array = [
    'fullname' => 'Кульков Вадим Владимирович',
    'sl_full_name' => 'Октябрьская дирекция снабжения',
    'email' => 'dmto_KulkovVV@orw.rzd'
];

echo json_encode(['data' => $array]);
