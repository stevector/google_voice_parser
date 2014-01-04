<?php

namespace GoogleVoiceParser\FirstBundle;

class StatsGetter
{

    public function __construct(DataGetter $dataGetter) {
      $this->data_getter = $dataGetter;
    }

    public function getMonthlyStatsForString($string_to_find) {
      $messages_stats = $this->getMessages($string_to_find);
      $points = array();
      foreach ($messages_stats as $month => $stats) {
        $points[] = array(
          // Add a string to indicate that the date is the middle of the month.
          // For example "2013-02-15". This is done because "2013-02" is getting
          // rendered as January in the chart. I'm guessing because 2013-02
          // is interpretted as midnight on February 1st UTC, which is still
          // January in a behind timezone.
          // @todo, Come up with a better workaround.
          $month . '-15',
          $stats['percentage'],
          // @todo The formatting for this string should be configurable.
          $stats['hits'] . ' out of ' .  $stats['total'],
        );
      }

      return $points;
    }

  function getMessages($string_to_find) {
    // @todo, ini_setting is a hack here to avoid timeouts. do something better.
    ini_set('max_execution_time', '300');
    $all_messages = $this->data_getter->getAllMessages();

    $results = array();
    foreach ($all_messages as $message) {
      $month = substr($message['time'], 0, 7);

      if (!isset($results[$month])) {
        $results[$month] = array(
          'total' => 0,
          'hits' => 0,
        );
      }

      $results[$month]['total']++;
      if (strpos($message['message'], $string_to_find) !== FALSE) {
        $results[$month]['hits']++;
      }
    }

    ksort($results);

    foreach($results as $month => $numbers) {
      $ratio = $numbers['hits'] / $numbers['total'];
      $results[$month]['percentage'] = round($ratio, 2) * 100;
    }

    return $results;
  }
}
