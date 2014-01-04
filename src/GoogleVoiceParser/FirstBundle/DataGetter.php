<?php

namespace GoogleVoiceParser\FirstBundle;

use GoogleVoiceParser\FirstBundle\singleFileParser;


// @todo, come up with an Interface for this class to implement.
class DataGetter
{

  public function __construct($directory) {
    $this->directory = $directory;
  }

  public function getAllMessages() {

    $scanned = scandir($this->directory);
    $all_messages = array();

    foreach ($scanned as $file_name) {

      if (strpos($file_name, '.html') === (strlen($file_name)-5)) {

        // @todo only caring about texts for now.
        if (strpos($file_name, ' - Text - ')) {
        if (strpos($file_name, 'Garn')) {
          $single_file_parser = new singleFileParser($this->directory . '/' . $file_name);
          $derived_array = $single_file_parser->getOutputArray();
          $all_messages = array_merge($all_messages, $derived_array);
        }
        }
      }
    }
    return $all_messages;
  }
}
