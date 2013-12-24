<?php
require 'vendor/autoload.php';
use Symfony\Component\DomCrawler\Crawler;

$test_dir = 'data_source';


class singleMessageParser {

  // Upon construction the message will be turned into an array.
  // Those values may be santized.
  protected $rawArray;

  public function __construct($message) {
    $this->rawArray = $this->tranformToRawArray($message);
  }

  public function tranformToRawArray($message) {
    $return = array();

    // A dependency injection container could be used instead of hard-coding
    // This class name.
    $messageCrawler = new Crawler($message);

    $return['time'] = $messageCrawler->filter('div abbr')->attr('title');
    $return['sender_number'] = $messageCrawler->filter(' div cite a')->attr('href');
    $return['sender_name'] = $messageCrawler->filter('div cite .fn')->text();
    $return['message'] = $messageCrawler->filter('div q')->text();

    return $return;
  }

  public function getOutputArray() {
    if (!empty($this->outputArray)) {
      $this->outputArray = $this->rawArray;
    }
    return $this->rawArray;
  }
}


class singleFileParser {

  protected $rawArray;
  protected $fileUri;

  function __construct($uri) {
    $this->fileUri = $uri;
  }

  protected function parseFile() {
    $return = array();

    $contents = file_get_contents($this->fileUri);
    $crawler = new Crawler($contents);

    $messages = $crawler->filter('div.message')->each(function ($node, $i) {
      $single_message_parser= new singleMessageParser($node);
      return $single_message_parser->getOutputArray();
    });

    return $messages;
  }

  public function getOutputArray() {
    return $this->parseFile();
  }
}

class directoryParser {

  protected function __construct($directory_path) {
  }
}


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
