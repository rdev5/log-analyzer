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

echo '<h1>Log Analyzer</h1>';

$f = new FileStream($target_file, true);

echo '<h2>stream_file_line()</h2>';
for($i = 2084665; $i <= 2084665; $i++) {
  $p = $f->stream_file_line($i);
  if($p) echo "Line {$i}: {$f->buffer}<br />";
}


echo '<hr />';
echo 'Time: ' . (microtime(true) - $start_time) . 's<br />';
echo "Pointer location: {$p}";