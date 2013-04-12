<?php
/*
 * functions.php
 */

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

function stream_file($fp, $target_file, $is_compressed = FALSE, $limit = 5) {

  $l = 1;
  $limit = (int)$limit;
  if($is_compressed) {

    while( ($buffer = gzgets($fp)) !== false ) {

      handle_line($l, $buffer);

      $l++;
      if($l >= $limit) break;

    }

  } else {

    while( ($buffer = fgets($fp)) !== false ) {

      handle_line($l, $buffer);

      $l++;
      if($l >= $limit) break;
    }

  }

}