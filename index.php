<?php
/*
 * Examples
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(dirname(__FILE__) . '/includes/functions.php');

$target_file = '';

$f = new FileStream($target_file);

// Get a single line by line number
$p = $f->stream_file_get_line(12345);

// Search entire file for a string
$count = $f->stream_file_search('some text string');

?>

<h1>Log Analyzer</h1>

<?php if($f->error) : ?>
  <div class="error"><?php echo $f->error; ?></div>
<?php endif; ?>

<?php if($p) : ?>

  <h2>stream_file_get_line() in <?php echo $f->benchmark_time['stream_file_get_line']; ?> seconds</h2>
  <pre><?php echo $f->buffer; ?></pre>

<?php endif; ?>


<?php if($count) : ?>

<h2>stream_file_search() <?php echo $count; ?> results in <?php echo $f->benchmark_time['stream_file_search']; ?> seconds</h2>
<pre><?php print_r($f->search_results); ?></pre>

<?php endif; ?>