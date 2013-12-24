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
