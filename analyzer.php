<?php
// Dependency
require_once(dirname(__FILE__) . '/includes/functions.php');


// Generate file selection from logs directory
$target_dir = dirname(__FILE__) . '/logs';
$file_selection = scandir($target_dir);


// Filenames to exclude
$exclude_names = array('.', '..', '.DS_Store');
$valid_names = array();
foreach($file_selection as $name) {

  if(!in_array($name, $exclude_names))
    $valid_names[$name] = $target_dir . '/' . $name;

}


// Query
$line_no = @(int)$_GET['line_no'] ? (int)$_GET['line_no'] : false;
$search_term = @!empty($_GET['search_term']) ? $_GET['search_term'] : false;


// Open requested file
$target_file = array_key_exists(@$_GET['f'], $valid_names) ? $valid_names[$_GET['f']] : false;
$target_name = $target_file ? basename($target_file) : false;
$f = new FileStream($target_file);


// Benchmarks
$p_start = benchmark_start();
$p = $f->stream_file_get_line($line_no);
$p_benchmark = benchmark($p_start);

$c_start = benchmark_start();
$count = $f->stream_file_search($search_term);
$c_benchmark = benchmark($c_start);

?>

<style type="text/css">

  .error {
    color: #e00;
  }

  #file-selection {
    height: 140px;
    width: 400px;
    overflow: auto;
    border: 1px solid #eee;
    padding: 10px;
    margin: 0;
  }

</style>

<h1>Log Analyzer</h1>

<?php if($f->error) : ?>
  <div class="error"><?php echo $f->error; ?></div>
<?php endif; ?>

<h2>File Selection</h2>
<p>Found <?php echo count($valid_names); ?> files in <?php echo $target_dir; ?>:</p>
<ul id="file-selection">
  <?php foreach($valid_names as $name => $file_path) : ?>
    <li><a href="?f=<?php echo $name; ?>"><?php echo $name; ?></a></li>
  <?php endforeach; ?>
</ul>

<?php if($target_name): ?>
  <h2>Query</h2>
  <form action="" method="get">
    <p>Get line no.:<br /><input type="text" name="line_no" size="5" value="<?php echo @$_GET['line_no']; ?>" /></p>
    <p>
      Search term:<br /><input type="text" name="search_term" value="<?php echo @$_GET['search_term']; ?>" />
      <small>(Limit <?php echo number_format($f::$SEARCH_LIMIT, 0, '', ','); ?> results)</small>
    </p>
    <p>
      <input type="hidden" name="f" value="<?php echo $target_name; ?>" />
      <input type="Submit" />
    </p>
  </form>
<?php endif; ?>


<?php if($p) : ?>

  <h3>stream_file_get_line(<?php echo $line_no; ?>)</h3>
  <p>Finished in <?php echo $p_benchmark ?> seconds</p>

<?php endif; ?>


<?php if($count) : ?>

  <h3>stream_file_search("<?php echo $search_term; ?>")</h3>
  <p><?php echo number_format($count, 0, '', ','); ?> results <?php echo "in {$c_benchmark} seconds" ?></p>

<?php endif; ?>