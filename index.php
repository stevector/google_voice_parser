<?php

use Symfony\Component\DomCrawler\Crawler;




function singleMessageParser($message) {
  $return = array();

  // A dependency injection container could be used instead of hard-coding
  // This class name.
  $messageCrawler = new Crawler($message);

  $return['time'] = $messageCrawler->filter('div abbr')->attr('title');
  $return['sender_number'] = $messageCrawler->filter(' div cite a')->attr('href');
  $return['sender_name'] = $messageCrawler->filter('div cite span.fn')->text();
  $return['message'] = $messageCrawler->filter('div q')->text();

  return $return;
}