<?php
/*
 * functions.php
 *
 * Note:
 * Handling compressed files (*.gz) is 50% slower than handling uncompressed files
 */

function microtime_benchmark($start_time)
{

  return microtime(true) - $start_time;

}

function gz_file($target_file) {
  
  return '.gz' == substr($target_file, -3);

}

function open_file($target_file, $is_compressed = FALSE) {

  if(!file_exists($target_file)) die("File not found: {$target_file}");
  
  $fp = FALSE;
  if($is_compressed) {

    $fp = @gzopen($target_file, 'r');
    if(!$fp) die("Can't open compressed file: {basename($target_file)}");

  } else {

    $fp = @fopen($target_file, 'r');
    if(!$fp) die("Can't open normal file: {basename($target_file)}");

  }

  return $fp;
}

function stream_file($fp, $is_compressed = FALSE, $limit = 5, $suppress_output = FALSE) {

  $line_no = 1;
  $limit = (int)$limit;

  if($is_compressed) {

    if(gztell($fp) != 0) rewind($fp);

    while( ($buffer = gzgets($fp)) !== false ) {

      if(!$suppress_output) handle_line($line_no, $buffer);

      $line_no++;
      if($line_no > $limit) break;

    }

  } else {

    if(ftell($fp) != 0) rewind($fp);

    while( ($buffer = fgets($fp)) !== false ) {

      if(!$suppress_output) handle_line($line_no, $buffer);

      $line_no++;
      if($line_no > $limit) break;

    }

  }

}

function stream_file_line($l, $fp, $is_compressed, $suppress_output = FALSE) {

  $line_no = 1;
  $l = (int)$l;

  if($l < 1) return FALSE;

  if($is_compressed) {

    if(gztell($fp) != 0) gzrewind($fp);

    while( ($buffer = gzgets($fp)) !== false ) {

      if($line_no == $l) {

        if(!$suppress_output) handle_line($l, $buffer);
        return gztell($fp);

      }

      $line_no++;
    }

  } else {

    if(ftell($fp) != 0) rewind($fp);

    while( ($buffer = fgets($fp)) !== false ) {

      if($line_no == $l) {

        if(!$suppress_output) handle_line($l, $buffer);
        return ftell($fp);

      }

      $line_no++;
    }

  }

  return FALSE;

}