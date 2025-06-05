<?php
// echo 'hi';
// print_r($_SERVER);
// print_r($_SERVER['PHP_AUTH_USER']); 
// Параметры подключения
// $ldapServer = "ORW-DC-05.orw.oao.rzd";  // Global Catalog (порт 3268)
// $ldapUser   = "orw\ivc_yarmolinskiyfa";     // Учётная запись с правами на чтение
// $ldapPass   = "";                // Пароль (лучше хранить в .env)
// $baseDn     = "OU=RVC,DC=orw,DC=oao,DC=rzd";           // Базовый DN леса

// // Подключение
// $ldapConn = ldap_connect($ldapServer);
// ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
// ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);  // Отключаем рефералы
// if (!$ldapConn) {
//     die("Не удалось подключиться к LDAP");
// }

// // Аутентификация
// if (
//     !ldap_bind($ldapConn, $ldapUser, $ldapPass)
// ) {
//     die("Ошибка аутентификации: " . ldap_error($ldapConn));
// }

// $person = $_SERVER['PHP_AUTH_USER'];
// $filter = "(userPrincipalName=$person*)";
// $justthese = array("ou", "sn", "givenname", "mail", "userPrincipalName");
// $search = ldap_search($ldapConn, $baseDn, $filter, $justthese);
// $results = ldap_get_entries($ldapConn, $search);

// // Вывод результатов
// // print_r($results);
// // print_r(array_keys($results));
// // $regexp = "/CN=[а-яёА-ЯЁ]+,OU=/u";

// $regexp = "/[,]/u";
// $parts = preg_split($regexp, $results[0]["dn"], 3);
// // var_dump($parts);
// foreach ($parts as $part) {
//     $regexp = "/[а-яёА-ЯЁ]+/u";
//     // $info = preg_split($regexp, $part);
//     preg_match_all($regexp, $part, $matches);
//     // var_dump($info);
//     // var_dump($matches[0]);
//     $string = implode(" ", $matches[0]);
// }

// // echo $parts[0] . '   ' . $parts[1];
// // echo $results[0]["dn"];
// echo "<h2>Найдено пользователей: " . $results["count"] . "</h2>";
// for ($i = 0; $i < $results["count"]; $i++) {
//     echo "Имя: " . $results[$i]["givenname"][0] . "<br>";
//     echo "Фамилия: " . $results[$i]["sn"][0] . "<br>";
//     echo "Подразделение: " . $string . "<br>";
//     echo "Email: " . $results[$i]["mail"][0] . "<br><hr>";
// }
require_once('../Cors.php');
$cors = new Cors();
$cors->cors_policy();
$array = [
    'fullname' => 'Кульков Вадим Владимирович',
    'sl_full_name' => 'Октябрьская дирекция снабжения',
    'email' => 'dmto_KulkovVV@orw.rzd'
];

echo json_encode(['data' => $array], flags: JSON_UNESCAPED_UNICODE);


// Закрытие соединения
// ldap_unbind($ldapConn);
