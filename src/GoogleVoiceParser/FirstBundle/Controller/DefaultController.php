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

    /**
     * specify the jqplot library
     */
    public function jqplotAction() {
        return $this->sampleChartGenerator("jqPlot");
    }


    public function getPointsArray($string_to_find) {
      $messages_stats = $this->getMessages($string_to_find);
      $points = array();
      foreach ($messages_stats as $month => $stats) {
        $points[] = array(
          $month,
          $stats['percentage'],
        );
      }

      return $points;
    }

    private function sampleChartGenerator($library = null) {

        $chartsFactory=$this->get('charts_factory');
        if (!is_null($library)) {
            $chartsFactory->setLibrary($library);
        };
        $charts = array();

        for ($i=1; $i<=1;$i++) {
            $charts[]=$chartsFactory->createChart('chart'.$i);
        }


        $points_sets = array(
          array(
            'points' => $this->getPointsArray('!'),
            'title' => 'Percentage Of texts that used an exclamation point',
          ),
          array(
            'points' => $this->getPointsArray('?'),
            'title' => 'Percentage Of texts that used a question point',
          ),


        );

        foreach ($points_sets as $set) {
          $seriesPoints = TwoDimensionalPointFactory::getFromNested($set['points']);
          $series = $charts[0]->createSeries($seriesPoints, $set['title']);
          $series->setLabelSetting('timeformat', '%b %Y');
          $charts[0]->addSeries($series);
        }

        $charts[0]->setTitle('Line Chart With Highlights and Labels')
          ->useDates()
          ->setAxisOptions('x', 'formatString', '%b %Y')
          ->setAxisOptions('y', 'formatString', '%d%')
          ->setLegend(array('on'=>true))
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

        if (!strpos($file_name, 'Penrod')) {
        if (!strpos($file_name, 'Friedman')) {
        // if (strpos($file_name, 'Garn')) {
          $single_file_parser = new singleFileParser($test_dir . '/' . $file_name);
          $derived_array = $single_file_parser->getOutputArray();
          $all_messages = array_merge($all_messages, $derived_array);
       // }
        }
        }
        }
      }
    }
    return $all_messages;
  }

  function getMessages($string_to_find) {
    ini_set('max_execution_time', '300');
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
      if (strpos($message['message'], $string_to_find) !== FALSE) {
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
