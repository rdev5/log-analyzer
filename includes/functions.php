<?php
/*
 * functions.php
 *
 * Note:
 * Handling compressed files (*.gz) is 50% slower than handling uncompressed files
 */
define('BROWSCAP_INI', dirname(__FILE__) . '/full_php_browscap.ini');
define('USE_BROWSCAP', @ini_get('browscap') == BROWSCAP_INI);

function benchmark_start() {

  return microtime(true);

}

function benchmark($start_time) {

  return number_format(microtime(true) - $start_time, 4, '.', ',');

}

class FileStream {

  public static $TIME_LIMIT = 0;
  public static $SEARCH_LIMIT = 280000;  // 280,182

  // REMOTE_ADDR - - [01/Jan/1969:00:00:00 -0000] "GET /some-file.php?a=123 HTTP/1.1" 200 - "-" "Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_2 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko)"
  // ([^\s]+) = REMOTE_ADDR, -, 200
  // \[([^\]]+)\] = [01/Jan/1969:00:00:00 -0000]
  // "([^"]+)" = "GET /some-file.php?a=123 HTTP/1.1", "-", "Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_2 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko)"
  private static $FIELDS_REGEX = '/^([^\s]+)\s([^\s]+)\s([^\s]+)\s\[([^\]]+)\]\s"([^"]+)"\s([^\s]+)\s([^\s]+)\s"([^"]+)"\s"([^"]+)"$/';
  private static $FIELDS = array(
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
    'user_agent',
  );

  private static $AGG_BROWSCAP = array(
    'browser',
    'comment',
    'device_name',
    'platform',
  );

  public $filename;
  public $is_compressed;
  public $line_no;
  public $line_count = 0;
  public $results_found = 0;
  public $pointer = 0;
  public $search_results = array();
  public $fp = false;
  public $error = false;

  private $file_opened = false;
  
  public function __construct($filename, $autoload = true) {

    gc_enable();
    set_time_limit($this::$TIME_LIMIT);

    $this->filename = $filename;
    $this->is_compressed = $this->is_compressed($this->filename);

    if($autoload)
      $this->fp = $this->open_file($filename);

  }

  public function __destruct() {

    if($this->fp) {
      if($this->is_compressed)
        gzclose($this->fp);
      else
        fclose($this->fp);
    }

    gc_collect_cycles();

  }


  public function is_compressed($filename) {
    
    return '.gz' == substr($filename, -3);

  }


  public function open_file($filename) {

    if(!$filename) {
      $this->error = 'No file specified.';
      return false;
    }

    if(!file_exists($filename)) {
      $this->error = "File not found: {$filename}";
      return false;
    }
    
    if($this->is_compressed) {

      $this->fp = @gzopen($filename, 'r');

      if(!$this->fp) {
        $this->error = "Can't open compressed file: {$filename}";
      }

    } else {

      $this->fp = @fopen($filename, 'r');

      if(!$this->fp) {
        $this->error = "Can't open normal file: {$filename}";
      }

    }

    $this->file_opened = (bool)$this->fp;

    return $this->fp;

  }

  private function _file_ready() {

    if(!$this->file_opened) {
      $this->open_file($this->filename);
    }

    return $this->error === false;

  }

  public function stream_file_line_count() {

    if(!$this->file_opened || $this->error)
      return false;


    if($this->is_compressed) {

      if(gztell($this->fp) != 0)
        gzrewind($this->fp);

      while( ($buffer = gzgets($this->fp)) !== false ) {

        $this->line_count++;

      }

    } else {

      if(ftell($this->fp) != 0)
        rewind($this->fp);

      while( ($buffer = fgets($this->fp)) !== false ) {

        $this->line_count++;

      }

    }
      
    gc_collect_cycles();

    return $this->line_count;

  }

  public function stream_file_search($search_term) {

    if(!$search_term || !$this->file_opened || $this->error)
      return false;


    $this->results_found = 0;

    if($this->is_compressed) {

      if(gztell($this->fp) != 0)
        gzrewind($this->fp);

      while( ($buffer = gzgets($this->fp)) !== false ) {

        if(strpos($buffer, $search_term) !== false) {

          $search_results[] = $buffer;
          $this->results_found++;

        }

        if($this->results_found >= $this::$SEARCH_LIMIT) {
          $this->error = 'Search limit reached. Try narrowing your search terms.';
          break;
        }

      }

    } else {

      if(ftell($this->fp) != 0)
        rewind($this->fp);

      while( ($buffer = fgets($this->fp)) !== false ) {

        if(strpos($buffer, $search_term) !== false) {

          $search_results[] = $buffer;
          $this->results_found++;

        }

        if($this->results_found >= $this::$SEARCH_LIMIT) {
          $this->error = 'Search limit reached. Try narrowing your search terms.';
          break;
        }

      }

    }

    gc_collect_cycles();

    return $search_results;

  }

  public function stream_file_get_line($line_no) {

    if(!$line_no || !$this->file_opened || $this->error)
      return false;


    $buffer = false;

    $line_no = (int)$line_no;
    if($line_no < 1) return false;

    $this->line_no = 1;

    if($this->is_compressed) {

      if(gztell($this->fp) != 0)
        gzrewind($this->fp);

      while( ($buffer = gzgets($this->fp)) !== false ) {

        if($this->line_no == $line_no) {

          $this->pointer = gztell($this->fp);

          return $buffer;

        }

        $this->line_no++;
      }

    } else {

      if(ftell($this->fp) != 0)
        rewind($this->fp);

      while( ($buffer = fgets($this->fp)) !== false ) {

        if($this->line_no == $line_no) {

          $this->pointer = ftell($this->fp);

          return $buffer;

        }

        $this->line_no++;
      }

    }

    gc_collect_cycles();

    return $buffer;

  }

  public function line_to_fields_array($line) {

    $values = array();
    $field_index = array_keys($this::$FIELDS);

    if(preg_match($this::$FIELDS_REGEX, $line, $matches)) {
      
      array_shift($matches);

      foreach($matches as $k => $value) {

        $key = $field_index[$k];

        if($key == 'user_agent' && USE_BROWSCAP) {

          $value = get_browser($value, true);
          ksort($value);

        }
        
        $values[$key] = $value;
      }

    }

    return $values;

  }

  public function aggregate_search_results($search_results) {

    $aggregate = array();
    foreach($search_results as $l => $result) {

      $values = $this->line_to_fields_array($result);

      foreach($values as $k => $v) {

        if(!in_array($k, $this::$AGG_FIELDS))
          continue;

        if(!array_key_exists($k, $aggregate))
          $aggregate[$k] = array();

        if($k == 'user_agent' && USE_BROWSCAP && is_array($v)) {

          foreach($v as $browscap_k => $browscap_v) {

            if(!in_array($browscap_k, $this::$AGG_BROWSCAP))
              continue;

            if(!array_key_exists($browscap_k, $aggregate[$k]))
              $aggregate[$k][$browscap_k] = array();

            if(!array_key_exists($browscap_v, $aggregate[$k][$browscap_k]))
              $aggregate[$k][$browscap_k][$browscap_v] = 0;
            
            $aggregate[$k][$browscap_k][$browscap_v]++;
          }

        } else {

          if(!array_key_exists($v, $aggregate[$k]))
            $aggregate[$k][$v] = 0;

          $aggregate[$k][$v]++;

        }

      }

      unset($search_results[$l]);

    }

    gc_collect_cycles();

    return $aggregate;
  }

}