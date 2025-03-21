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
          return true;
        } else {
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
        if ($expiration_time < time()) {
          // http_response_code(413);
          return false;
        }
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
    $str_arr = explode('.', $str);
    $new_str = '';
    foreach ($str_arr as $str_item) {
      $new_str .= base64_decode($str_item);
    }
    return $new_str;
  }
}
