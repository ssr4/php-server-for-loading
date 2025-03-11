<?php
require_once 'auth/db.php';
$db = new DB_Conncetion(parse_ini_file('config.ini', true)['DB']);
$db->db_connect();
