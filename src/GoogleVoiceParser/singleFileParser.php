<?php

namespace GoogleVoiceParser;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelector;


class singleFileParser {

  protected $rawArray;
  protected $fileUri;

  function __construct($uri) {
    $this->fileUri = $uri;
  }

  protected function parseFile() {
    $return = array();

    $contents = file_get_contents($this->fileUri);
    $crawler = new Crawler();
    $crawler->addHtmlContent($contents);

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
