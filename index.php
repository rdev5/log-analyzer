<?php
/*
 * Log Analyzer
 */
require_once(dirname(__FILE__) . '/includes/functions.php');

$target_file = '';

echo '<h1>Log Analyzer</h1>';
echo '<h2>stream_file_get_line()</h2>';

$f = new FileStream($target_file);
$p = $f->stream_file_get_line(2084665);
if($p) echo "Line {$f->line_no}: {$f->buffer}<br />";

echo '<hr />';
echo "Time: {$f->benchmark_time}s<br />";
echo "Pointer location: {$p}";