<?php

class Aggregator {

  // REMOTE_ADDR - - [01/Jan/1969:00:00:00 -0000] "GET /some-file.php?a=123 HTTP/1.1" 200 - "-" "Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_2 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko)"
  // ([^\s]+) = REMOTE_ADDR, -, 200
  // \[([^\]]+)\] = [01/Jan/1969:00:00:00 -0000]
  // "([^"]+)" = "GET /some-file.php?a=123 HTTP/1.1", "-", "Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_2 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko)"

  public static $FIELDS_REGEX = '/^([^\s]+)\s([^\s]+)\s([^\s]+)\s\[([^\]]+)\]\s"([^"]+)"\s([^\s]+)\s([^\s]+)\s"([^"]+)"\s"([^"]+)"$/';
  public static $FIELDS = array(
    'source' => 'Source',
    'field_2' => 'Field 2',
    'field_3' => 'Field 3',
    'timestamp' => 'Timestamp',
    'request' => 'Request',
    'status' => 'Status Code',
    'field_7' => 'Field 7',
    'field_8' => 'Field 8',
    'user_agent' => 'User Agent',
  );

  private static $AGG_FIELDS = array(
    'request',
    'timestamp',
    'user_agent',
  );

  private static $AGG_BROWSCAP = array(
    'browser',
    'comment',
    'device_name',
    'platform',
    'version',
    'user_agent',
  );
  
  public function __construct($filename, $autoload = true) {

    gc_enable();

  }

  public function __destruct() {

    gc_collect_cycles();

  }

  public static function line_to_fields_array($line, $fields, $regex) {

    $values = array();
    $field_index = array_keys($fields);

    if(preg_match($regex, $line, $matches)) {
      
      array_shift($matches);

      foreach($matches as $k => $value) {

        $key = $field_index[$k];
        
        $values[$key] = $value;
      }

    }

    gc_collect_cycles();

    return $values;

  }

  public static function extract_field($line, $field, $fields, $regex) {

    $values = array();
    $field_index = array_keys($fields);

    if(preg_match($regex, $line, $matches)) {
      
      array_shift($matches);

      foreach($matches as $k => $value) {

        $key = $field_index[$k];
        if($key == $field) return $value;
      }

    }

    return false;

  }

  public static function aggregate_data($data) {

    $agg = array();

    foreach($data as $l => $result) {

      $values = self::line_to_fields_array($result, self::$FIELDS, self::$FIELDS_REGEX);

      foreach($values as $k => $v) {

        if(!in_array($k, self::$AGG_FIELDS))
          continue;

        if(!array_key_exists($k, $agg))
          $agg[$k] = array();

        switch($k) {
          case 'timestamp':

            $v = date('Y-m-d', strtotime($v));


          default:

            if(!array_key_exists($v, $agg[$k])) {
              $agg[$k][$v] = 0;
            }

            $agg[$k][$v]++;

          break;

        }

      }

      unset($data[$l]);

    }

    gc_collect_cycles();

    ksort($agg);

    return $agg;

  }

}
