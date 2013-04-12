<?php
/*
 * Log Analyzer
 */
require_once(dirname(__FILE__) . '/includes/functions.php');

$target_file = '';

function handle_line($l, $line) {
  echo "Line {$l}: {$line}<br /><br />";
}

$is_compressed = gz_file($target_file);
$fp = open_file($target_file, $is_compressed);
stream_file($fp, $target_file, $is_compressed);

fclose($fp);