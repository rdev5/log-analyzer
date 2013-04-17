<?php
/*
 * functions.php
 *
 * Note:
 * Handling compressed files (*.gz) is 50% slower than handling uncompressed files
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../lib/LogAnalyzer.php';
require __DIR__ . '/../lib/Aggregator.php';
require __DIR__ . '/../lib/Browser.php';
require __DIR__ . '/../lib/OS.php';
require __DIR__ . '/../lib/Language.php';

function benchmark_start() {

  return microtime(true);

}

function benchmark($start_time) {

  return number_format(microtime(true) - $start_time, 4, '.', ',');

}

function aggregate_user_agents($data) {

  $user_agents = array();
  foreach($data as $line) {
    $ua = Aggregator::extract_field($line, 'user_agent', Aggregator::$FIELDS, Aggregator::$FIELDS_REGEX);
    if($ua) {
      array_push($user_agents, $ua);
    }
  }

  $agg = array(
    'browsers' => array(),
    'os' => array(),
  );

  foreach($user_agents as $line) {

    Browser\Browser::setUserAgent($line);

    $browser = Browser\Browser::getBrowser();
    $os = Browser\OS::getOS();

    if(!array_key_exists($browser, $agg['browsers'])) {
      $agg['browsers'][$browser] = 0;
    }

    if(!array_key_exists($os, $agg['os'])) {
      $agg['os'][$os] = 0;
    }

    $agg['browsers'][$browser]++;
    $agg['os'][$os]++;
    
  }

  gc_collect_cycles();

  return $agg;

}