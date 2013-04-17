<?php

class LogAnalyzer {

  public static $TIME_LIMIT = 0;
  public static $SEARCH_LIMIT = 280000;  // 280,182

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

}
