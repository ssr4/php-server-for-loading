<?php
try {
  require_once "../Cors.php";
  require_once "../auth/db.php";
  $cors = new Cors();
  $cors->cors_policy();
  $config = parse_ini_file("../config.ini", true);

  $db = new DB_Connection($config['DB']);
  if (isset($_POST["filename"])) {
    $result_data = array();
    $db_connect = $db->db_connect();
    $filename = $_POST['filename'];
    $stmt = "select files_uploading.insert_new_metadata ('" . $filename . "', 'UPLOADED')";
    $query = pg_query($db_connect, $stmt);
    if (!$query) {
      $db->closeConn();
      throw new Exception('Error during DB query insert into files_uploading.file_metadata');
    }
    $result = pg_fetch_array($query);
    array_push($result_data,  array("status" => "ok", "inserted_on" => $result[0]));
    echo json_encode($result_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $db->closeConn();
    http_response_code(200);
    exit();
  } else throw new Exception("Filename not provided");
} catch (Exception $e) {
  // ;
  echo json_encode("Error: " . $e->getMessage());
  http_response_code(403);
  exit();
}
