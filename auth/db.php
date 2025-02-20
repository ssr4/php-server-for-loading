<?php

class DB_Conncetion
{
  private $db_host = 'localhost';
  private $port = '5432';
  private $db_name     = "postgres"; //change to your db
  private $db_username = ""; //change to your db username
  private $db_password = ""; //enter your password
  private $conn;

  public function db_connect($user, $password)
  {
    $this->conn = null;
    try {
      // $this->conn = new PDO("mysql:host=" . $this->db_host . ";dbname=" . $this->db_name, '', '');
      $this->conn = pg_connect("host=$this->db_host port=$this->port dbname=$this->db_name  user=$user password=$password");
      // echo json_encode($this->conn);
      // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      echo "Error " . $e->getMessage();
    }
    return $this->conn;
  }

  public function get_conn()
  {
    return $this->conn;
  }
}
