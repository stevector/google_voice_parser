<?php

namespace GoogleVoiceParser\FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GoogleVoiceParser\FirstBundle\Controller\singleFileParser;
use GoogleVoiceParser\FirstBundle\Controller\singleMessageParser;
use Symfony\Component\DomCrawler\Crawler;
use APY\DataGridBundle\Grid\Source\Vector;

use APY\DataGridBundle\Grid\Export\JSONExport;
use APY\DataGridBundle\Grid\Export\CSVExport;

use \Altamira\ChartDatum\TwoDimensionalPointFactory;




class DefaultController extends Controller
{
    public function indexAction($name)
    {



        return $this->jqplotAction();

$test_dir = '/Users/stevepersch/Sites/google_voice_parser/source_files';





$scanned = scandir($test_dir);
$all_messages = array();


//print_r($scanned);
foreach ($scanned as $file_name) {

  if (strpos($file_name, '.html') === (strlen($file_name)-5)) {

    // @todo only caring about texts for now.

    if (strpos($file_name, ' - Text - ')) {
    if (strpos($file_name, 'Garn')) {
      $single_file_parser = new singleFileParser($test_dir . '/' . $file_name);
      $derived_array = $single_file_parser->getOutputArray();

//      print_r($derived_array);
      $all_messages = array_merge($all_messages, $derived_array);
    }
    }
  }
}


// print_r($all_messages);




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


    /**
     * specify the flot library
     */
    public function flotAction() {
        return $this->sampleChartGenerator("flot");
    }

    /**
     * specify the jqplot library
     */
    public function jqplotAction() {
        return $this->sampleChartGenerator("jqPlot");
    }



    private function sampleChartGenerator($library=null) {


       // print_r();

        $chartsFactory=$this->get('charts_factory');
        if ( !is_null($library) ) {
            $chartsFactory->setLibrary($library);
        };
        $charts=array();

        for ($i=1; $i<=2;$i++) {
            $charts[]=$chartsFactory->createChart('chart'.$i);
        }


        $messages_stats = $this->getMessages();
        $points = array();
        foreach ($messages_stats as $month => $stats) {
          $points[] = $stats['percentage'];

        }


      //  $points = array(2, 8, 5, 3, 8, 9, 7, 8, 4, 2, 1, 6);

        $series1Points = TwoDimensionalPointFactory::getFromYValues($points);

        $charts[0]->addSeries($charts[0]->createSeries($series1Points, 'Percentage Of texts Using an exclamation point'))->


        setTitle('Basic Line Chart')->
        setAxisOptions('y', 'formatString', '%d%')->
        setAxisOptions('x', 'tickInterval', 1)->
        setAxisOptions('x', 'min', 0)->
        setLegend(array('on'=>true))
        ->setAxisOptions( 'x', 'min', 0)
        ->setAxisOptions( 'x', 'max', count($points))
        ->setAxisOptions( 'y', 'min', 0)
        ->setAxisOptions( 'y', 'max', 100)
                ->useHighlighting();


        $chartIterator = $chartsFactory->getChartIterator($charts);

        $altamiraJSLibraries=$chartIterator->getLibraries();
        $altamiraCSS=$chartIterator->getCSSPath();
        $altamiraJSScript=$chartIterator->getScripts();
        $altamiraPlugins=$chartIterator->getPlugins();

        while ($chartIterator->valid() ) {
            $altamiraCharts[]=$chartIterator->current()->getDiv();
            $chartIterator->next();
        }


        //print_r($charts);
        return $this->render('MalwarebytesAltamiraBundle:Default:example.html.twig', array('altamiraJSLibraries'=> $altamiraJSLibraries, 'altamiraCSS'=> $altamiraCSS, 'altamiraScripts' =>  $altamiraJSScript, 'altamiraCharts' => $altamiraCharts, 'altamiraJSPlugins' => $altamiraPlugins));
    }

  function getAllMessages() {
    $test_dir = '/Users/stevepersch/Sites/google_voice_parser/source_files';

    $scanned = scandir($test_dir);
    $all_messages = array();

    foreach ($scanned as $file_name) {

      if (strpos($file_name, '.html') === (strlen($file_name)-5)) {

        // @todo only caring about texts for now.

        if (strpos($file_name, ' - Text - ')) {
       // if (strpos($file_name, 'Ga')) {
          $single_file_parser = new singleFileParser($test_dir . '/' . $file_name);
          $derived_array = $single_file_parser->getOutputArray();

          $all_messages = array_merge($all_messages, $derived_array);
       // }
        }
      }
    }
    return $all_messages;
  }

  function getMessages() {
    $all_messages = $this->getAllMessages();

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
    }

    return $results;
  }
}
