<?php
/*
 * Log Analyzer
 */
require_once(dirname(__FILE__) . '/includes/functions.php');

$target_file = '';

function handle_line($l, $line) {
  echo "Line {$l}: {$line}<br /><br />";
}

$start_time = microtime(true);

$is_compressed = gz_file($target_file);
$fp = open_file($target_file, $is_compressed);

echo '<h1>Log Analyzer</h1>';

echo '<h2>stream_file()</h2>';
stream_file($fp, $is_compressed);

echo '<h2>stream_file_line()</h2>';
for($i = 1; $i <= 600; $i++) {
  $p = stream_file_line($i, $fp, $is_compressed);
}

echo '<hr />';
echo 'Time: ' . microtime_benchmark($start_time) . 'ms<br />';
echo "Pointer location: {$p}";

fclose($fp);