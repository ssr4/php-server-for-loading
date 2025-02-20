<?php
class Token
{
  private $_token, $_config;
  public function
  __construct($token, $config)
  {
    // получаем токен пока test тест доделать механизм refresh
    $this->_token = isset($token) ? $token : null;
    // подключаем файл конфига
    $this->_config = $config;
  }
  function isValidToken()
  {
    try {
      if ($this->_token) {
        // Удаление префикса "Bearer "
        $this->_token = str_replace('Bearer ', '', $this->_token);
        if ($this->parseToken($this->_token)) {
          // echo json_encode(['isValid' => true]);
          return true;
        } else {
          // echo json_encode(['isValid' => false]);
          return false;
        }
      } else {
        return false;
      }
    } catch (Exception $e) {
      throw new RuntimeException('Error ' . $e->getMessage());
    }
  }

  public function parseToken($token)
  {
    // $config = parse_ini_file("../config.ini");
    $text = str_replace('\'', '', $this->decodeString($token));
    if (preg_match_all('/{([^}]*)}/', $text, $matches, PREG_OFFSET_CAPTURE)) {
      foreach ($matches[1] as $match) {
        // echo "{$match[0]}\n";
        $obj = json_decode("{" . $match[0] . "}");
        if (isset($obj->exp))
          $expiration_time = $obj->exp;
        if (isset($obj->secret))
          $secret =  $obj->secret;
      }
      if (!$expiration_time || !$secret) {
        return false;
        // throw new Exception('The token has expired or token is not valid');
      } else {
        if ($expiration_time < time())
          return false;
        if ($secret !== $this->_config['secret_key'])
          return false;
        return true;
      }
    } else {
      return false;
      // throw new Exception('The token has expired or token is not valid');
    }
  }

  public function decodeString($str)
  {
    return base64_decode($str);
  }
}
