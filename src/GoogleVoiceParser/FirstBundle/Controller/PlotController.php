<?php

namespace GoogleVoiceParser\FirstBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

  private function sampleChartGenerator($library = null) {

        $chartsFactory=$this->get('charts_factory');
        if (!is_null($library)) {
            $chartsFactory->setLibrary($library);
        };
        $charts = array();

        for ($i=1; $i<=1;$i++) {
            $charts[]=$chartsFactory->createChart('chart' . $i);
        }

        $stats_getter = $this->container->get('google_voice_parser_first.stats_getter');

        $points_sets = array(
          array(
            'points' => $stats_getter->getMonthlyStatsForString('!'),
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
}
