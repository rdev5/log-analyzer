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
  public $lines = 0;
  public $fp = FALSE;
  public $error = FALSE;


  private $file_opened = FALSE;


  public function __construct($filename, $autoload = TRUE) {

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
      return FALSE;
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

  public function stream_file_line($l) {

    if(!$this->file_opened) {
      $this->open_file($this->filename);
    }

    if($this->error) {
      return FALSE;
    }

    $l = (int)$l;
    if($l < 1) return FALSE;

    $this->line_no = 1;

    if($this->is_compressed) {

      if(gztell($this->fp) != 0)
        gzrewind($this->fp);

      while( ($this->buffer = gzgets($this->fp)) !== FALSE ) {

        if($this->line_no == $l)
          return gztell($this->fp);

        $this->line_no++;
      }

    } else {

      if(ftell($this->fp) != 0)
        rewind($this->fp);

      while( ($this->buffer = fgets($this->fp)) !== FALSE ) {

        if($this->line_no == $l)
          return ftell($this->fp);

        $this->line_no++;
      }

    }

    return FALSE;

  }
}