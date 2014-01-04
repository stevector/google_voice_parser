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

class PlotController extends DefaultController
{
  public function indexAction()
  {
        return $this->sampleChartGenerator("jqPlot");
    }

    public function getPointsArray($string_to_find) {
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
          $stats['texts_with_exclamation'] . ' out of ' .  $stats['total_texts'],
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
            $charts[]=$chartsFactory->createChart('chart' . $i);
        }

        $points_sets = array(
          array(
            'points' => $this->getPointsArray('!'),
            'title' => 'Percentage of texts that used an exclamation point',
          ),
          // array(
          //   'points' => $this->getPointsArray('?'),
          //   'title' => 'Percentage Of texts that used a question mark',
          // ),
        );

        foreach ($points_sets as $set) {
          // The factor can't hanle the additional label data.
          // $seriesPoints = TwoDimensionalPointFactory::getFromNested($set['points']);
          foreach ($set['points'] as $point) {
            $seriesPoints[] = new TwoDimensionalPoint( array('x' => $point[0], 'y' => $point[1]), $point[2]);
          }


          $series = $charts[0]->createSeries($seriesPoints, $set['title']);
          $series->setLabelSetting('timeformat', '%b %Y');
          $series->setLabelSetting('pointLabels', 'show');
          $charts[0]->addSeries($series);
        }

        $charts[0]->setTitle('Percentage of texts that used an exclamation point')
          ->useDates()
          ->setAxisOptions('x', 'formatString', '%b %Y')
          ->setAxisOptions('y', 'formatString', '%d%')
          // ->setLegend(array('on' => true))
          ->useHighlighting();

        $jsWriter = $charts[0]->getJsWriter();

        $highlighter_options = $jsWriter->getOption('highlighter');
        $highlighter_options['yvalues'] = 2;
        $highlighter_options['formatString'] = '%s, %s (%s)';
        $highlighter_options['tooltipLocation'] = 'e';


        $highlighter_options[ 'useAxesFormatters'] = TRUE;
        $jsWriter->setOption('highlighter', $highlighter_options);

       $series_defaults =  $jsWriter->getOption('seriesDefaults');
       $series_defaults['pointLabels'][ 'show'] = TRUE;
       $jsWriter->setOption('seriesDefaults', $series_defaults);

       // @todo Add trendline.js properly.
       //$this->files = array_merge_recursive( array( 'jqplot.trendline.js' ), $this->files);

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



  // This functionality should move out to a service.
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
