<?php

namespace GoogleVoiceParser;

use GoogleVoiceParser\singleFileParser;


// @todo, come up with an Interface for this class to implement.
class DataGetter
{

  public function __construct($directory) {
    $this->directory = $directory;
  }
  // @todo, add caching.
  public function getAllMessages() {

    $scanned = scandir($this->directory);
    $all_messages = array();

    foreach ($scanned as $file_name) {

      if (strpos($file_name, '.html') === (strlen($file_name) - 5)) {

        // @todo only caring about texts for now.
        if (strpos($file_name, ' - Text - ')) {
        // @todo, remove the if statement which is present to keep low the
        // of files returned so that pages load fast while developing.
        // if (strpos($file_name, '')) {
          $single_file_parser = new singleFileParser($this->directory . '/' . $file_name);
          $derived_array = $single_file_parser->getOutputArray();
          $all_messages = array_merge($all_messages, $derived_array);
        // }
        }
      }
    }
    // @todo, some kind of abstraction so as not to hard code texts_from_me.
    // return $all_messages;
    foreach ($all_messages as $message) {
      if ($message['sender_name'] === 'Me') {
        $texts_from_me[] = $message;
      }
    }

    return $texts_from_me;
  }
}
