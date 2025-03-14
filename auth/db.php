<?php

class DB_Conncetion
{
  private $db_host = '';
  private $port = '5432';
  private $db_name     = "";
  private $db_username = "";
  private $db_password = "";
  private $conn;

  public function __construct($dbparams)
  {
    $this->db_host = $dbparams['db_host'];
    $this->db_name = $dbparams['db_name'];
    $this->db_username = $dbparams['db_username'];
    $this->db_password = $dbparams['db_password'];
  }

  public function db_connect()
  {
    $this->conn = null;
    try {
      $this->conn = pg_connect("host=$this->db_host port=$this->port dbname=$this->db_name  user=$this->db_username password=$this->db_password");
    } catch (PDOException $e) {
      exit;
    }
    return $this->conn;
  }

  public function get_conn()
  {
    return $this->conn;
  }


  public function closeConn()
  {
    pg_close($this->conn);
  }
}
