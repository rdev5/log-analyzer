<?php
/*
 * functions.php
 *
 * Note:
 * Handling compressed files (*.gz) is 50% slower than handling uncompressed files
 */

class FileStream {

  public $filename;
  public $is_compressed;
  public $line_no;
  public $buffer;
  public $benchmark_time = array(
    'stream_file_get_line' => 0,
    'stream_file_search' => 0,
  );
  public $search_results = array();
  public $fp = false;
  public $error = false;

  private $file_opened = false;
  private $start_time = array(
    'stream_file_get_line' => 0,
    'stream_file_search' => 0,
  );
  private $end_time = array(
    'stream_file_get_line' => 0,
    'stream_file_search' => 0,
  );

  public function __construct($filename, $autoload = true) {

    gc_enable();

    $this->filename = $filename;
    $this->is_compressed = $this->is_compressed($this->filename);

    if($autoload) {
      $this->fp = $this->open_file($filename);
    }

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

  public function stream_file_search($keyword) {

    if(!$this->file_opened) {
      $this->open_file($this->filename);
    }

    if($this->error) {
      return false;
    }

    $this->start_time['stream_file_search'] = microtime(true);

    if($this->is_compressed) {

      if(gztell($this->fp) != 0)
        gzrewind($this->fp);

      while( ($buffer = gzgets($this->fp)) !== false ) {

        if(strpos($buffer, $keyword) !== false) {
          $this->search_results[] = $buffer;
        }

      }

    } else {

      if(ftell($this->fp) != 0)
        rewind($this->fp);

      while( ($buffer = fgets($this->fp)) !== false ) {

        if(strpos($buffer, $keyword) !== false) {
          $this->search_results[] = $buffer;
        }

      }

    }

    $this->end_time['stream_file_search'] = microtime(true);
    $this->benchmark_time['stream_file_search'] = $this->end_time['stream_file_search'] - $this->start_time['stream_file_search'];
      
    gc_collect_cycles();

    return count($this->search_results);

  }

  public function stream_file_get_line($line_no) {

    if(!$this->file_opened) {
      $this->open_file($this->filename);
    }

    if($this->error) {
      return false;
    }

    $line_no = (int)$line_no;
    if($line_no < 1) return false;

    $this->start_time['stream_file_get_line'] = microtime(true);
    $this->line_no = 1;

    if($this->is_compressed) {

      if(gztell($this->fp) != 0)
        gzrewind($this->fp);

      while( ($this->buffer = gzgets($this->fp)) !== false ) {

        if($this->line_no == $line_no) {

          $this->end_time['stream_file_get_line'] = microtime(true);
          $this->benchmark_time['stream_file_get_line'] = $this->end_time['stream_file_get_line'] - $this->start_time['stream_file_get_line'];

          return gztell($this->fp);

        }

        $this->line_no++;
      }

    } else {

      if(ftell($this->fp) != 0)
        rewind($this->fp);

      while( ($this->buffer = fgets($this->fp)) !== false ) {

        if($this->line_no == $line_no) {

          $this->end_time['stream_file_get_line'] = microtime(true);
          $this->benchmark_time['stream_file_get_line'] = $this->end_time['stream_file_get_line'] - $this->start_time['stream_file_get_line'];

          return ftell($this->fp);

        }

        $this->line_no++;
      }

    }

    $this->end_time['stream_file_get_line'] = microtime(true);
    $this->benchmark_time['stream_file_get_line'] = $this->end_time['stream_file_get_line'] - $this->start_time['stream_file_get_line'];
    return false;

  }
}