<?php

use Symfony\Component\DomCrawler\Crawler;



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

