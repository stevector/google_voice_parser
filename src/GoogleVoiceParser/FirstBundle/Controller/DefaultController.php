<?php

namespace GoogleVoiceParser\FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use APY\DataGridBundle\Grid\Source\Vector;
use APY\DataGridBundle\Grid\Export\JSONExport;
use APY\DataGridBundle\Grid\Export\CSVExport;

class DefaultController extends Controller
{
  public function indexAction()
  {


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
    $data_getter = $this->container->get('google_voice_parser_first.data_getter');
    $all_messages = $data_getter->getAllMessages();
    return $all_messages;
  }
}
