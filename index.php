<?php

require 'singleMessageParser.php';
require 'singleFileParser.php';
require 'vendor/autoload.php';
use Symfony\Component\DomCrawler\Crawler;

$test_dir = '/Users/stevepersch/Sites/google_voice_parser/source_files';




$scanned = scandir($test_dir);
$all_messages = array();


foreach ($scanned as $file_name) {

  if (strpos($file_name, '.html') === (strlen($file_name)-5)) {

    // @todo only caring about texts for now.

    if (strpos($file_name, ' - Text - ')) {
      $single_file_parser = new singleFileParser($test_dir . '/' . $file_name);
      $derived_array = $single_file_parser->getOutputArray();
      $all_messages = array_merge($all_messages, $derived_array);
    }
  }
}


$texts_from_me = array();
foreach ($all_messages as $message) {
  if ($message['sender_name'] === 'Me') {
    $texts_from_me[] = $message;
  }
}


$results = array();

foreach ($texts_from_me as $message) {
  $month = substr($message['time'], 0, 7);

  if (!isset($results[$month])) {
    $results[$month] = array(
      'total_texts' => 0,
      'texts_with_exclamation' => 0,
    );
  }

  $results[$month]['total_texts']++;
  if (strpos($message['message'], '!') !== FALSE) {
    $results[$month]['texts_with_exclamation']++;
  }
}

ksort($results);

foreach($results as $month => $numbers) {
  $ratio = $numbers['texts_with_exclamation']/$numbers['total_texts'];

  $results[$month]['percentage'] = round($ratio, 2) * 100;

  print_r($month);
  print_r(',');
  print_r(implode(',', $results[$month]));
  print_r("\n");
}
