<?php

namespace GoogleVoiceParser\FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GoogleVoiceParser\FirstBundle\Controller\singleFileParser;
use GoogleVoiceParser\FirstBundle\Controller\singleMessageParser;
use Symfony\Component\DomCrawler\Crawler;
use APY\DataGridBundle\Grid\Source\Vector;

use APY\DataGridBundle\Grid\Export\JSONExport;
use APY\DataGridBundle\Grid\Export\CSVExport;
// @todo, Direct use of TwoDimensionalPoint was added because the factory
// didn't have a method to handle the additional label data.
use \Altamira\ChartDatum\TwoDimensionalPointFactory;
use Altamira\ChartDatum\TwoDimensionalPoint;

class DefaultController extends Controller
{
  public function indexAction()
  {



  //return $this->jqplotAction();

    $all_messages = $this->getAllMessages();


$source = new Vector($all_messages);
$grid = $this->get('grid');
$grid->setSource($source);


$grid->addExport(new CSVExport('CSV Export'));
$grid->addExport(new JSONExport('JSON Export'));


$grid->setPermanentFilters(array(
    'message' => '!', // Use the default operator of the column
//    'your_column_to_filter1' => array('from' => 'your_init_value1'), // Use the default operator of the column
//    'your_column_to_filter2' => array('operator' => 'eq', 'from' => 'your_init_value_from2'), // Define an operator
//    'your_column_to_filter3' => array('from' => 'your_init_value_from3', 'to' => 'your_init_value_to3'), // Range filter with the default operator 'btw'
//    'your_column_to_filter4' => array('operator' => 'btw', 'from' => 'your_init_value_from4', 'to' => 'your_init_value_to4') // Range filter with the operator 'tbw'
//    'your_column_to_filter4' => array('operator' => 'isNull') // isNull operator
));

//$grid->prepare();
      //  print_r($grid->getSource()->getData());

//$grid->addExport(new CSVExport('CSV Export'));

return $grid->getGridResponse('GoogleVoiceParserFirstBundle:Default:grid.html.twig');

return $this->render('GoogleVoiceParserFirstBundle:Default:index.html.twig', array('name' => $name));
    }


  function getAllMessages() {
    // This string should be coming from a config file.
    $test_dir = '/Users/stevepersch/Sites/google_voice_parser/source_files';

    $scanned = scandir($test_dir);
    $all_messages = array();

    foreach ($scanned as $file_name) {

      if (strpos($file_name, '.html') === (strlen($file_name)-5)) {

        // @todo only caring about texts for now.
        if (strpos($file_name, ' - Text - ')) {
        if (strpos($file_name, 'Garn')) {
          $single_file_parser = new singleFileParser($test_dir . '/' . $file_name);
          $derived_array = $single_file_parser->getOutputArray();
          $all_messages = array_merge($all_messages, $derived_array);
        }
        }
      }
    }
    return $all_messages;
  }
}
