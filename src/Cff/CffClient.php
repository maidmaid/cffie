<?php

namespace Cffie\Cff;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Crawler;

class CffClient
{
    private $client;

    public function __construct($debug = false)
    {
        $jar = new CookieJar();
        $this->client = new Client(array(
            'base_uri' => 'http://fahrplan.sbb.ch',
            'cookies' => $jar,
            'headers' => array(
                'User-Agent' => 'CFFie',
            ),
            'debug' => $debug,
        ));
    }

    public function getHomepage()
    {
        return $this->client->get('bin/query.exe/dn');
    }

    public function getStop($stop, $firstResult = true)
    {
        $stop = $firstResult ? $stop : $stop.'?';

        $response = $this->client->get('bin/ajax-getstop.exe/fny', array(
            'query' => array(
                'start' => '1',
                'REQ0JourneyStopsS0A'=>'1',
                'getstop' => '1',
                'noSession' => 'yes',
                'REQ0JourneyStopsB' => '10',
                'REQ0JourneyStopsS0G' => $stop,
                'js' => 'false',
            ),
        ));

        $body = utf8_encode((string) $response->getBody());
        preg_match('/({.*})/', $body, $matches);
        $match = json_decode($matches[1], true);

        return $firstResult ? $match['suggestions'][0] : $match['suggestions'];
    }

    public function query($departure, $arrival, \DateTime $date = null)
    {
        $date = $date ? $date : new \DateTime();
        $fmt = new \IntlDateFormatter('en_EN', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, date_default_timezone_get(), \IntlDateFormatter::TRADITIONAL);

        $response = $this->client->post('bin/query.exe/fn', array(
            'headers' => array(
                'host' => 'fahrplan.sbb.ch',
                'origin' => 'http://fahrplan.sbb.ch',
                'referer' => 'http://fahrplan.sbb.ch/bin/query.exe/fn',
            ),
            'form_params' => array(
                'queryPageDisplayed' => 'yes',
                'HWAI=JS!ajax' => 'yes',
                'HWAI=JS!js' => 'yes',
                'HWAI' => '~CONNECTION!',
                'REQ0Total_KissRideMotorClass' => '404',
                'REQ0Total_KissRideCarClass' => '5',
                'REQ0Total_KissRide_maxDist' => '10000000',
                'REQ0Total_KissRide_minDist' => '0',
                'REQComparisonCarload' => '0',
                'REQ0JourneyStopsS0G' => $departure['value'],
                'REQ0JourneyStopsS0ID' => $departure['id'],
                'REQ0JourneyStopsS0A' => '255',
                'REQ0JourneyStopsZ0G' => $arrival['value'],
                'REQ0JourneyStopsZ0ID' => $arrival['id'],
                'REQ0JourneyStopsZ0A' => '255',
                'REQ0JourneyStops1.0G' => '',
                'REQ0JourneyStops1.0A' => '1',
                'REQ0JourneyStopover1' => '',
                'date' => sprintf('%s, %s', substr($fmt->format($date), 0, 2), $date->format('d-m-y')),
                'REQ0JourneyTime' => $date->format('H:i'),
                'REQ0HafasSearchForw' => '1',
                'REQ0JourneyStops2.0G' => '',
                'REQ0JourneyStops2.0A' => '1',
                'REQ0JourneyStopover2' => '',
                'REQ0JourneyStops3.0G' => '',
                'REQ0JourneyStops3.0A' => '1',
                'REQ0JourneyStopover3' => '',
                'REQ0JourneyStops4.0G' => '',
                'REQ0JourneyStops4.0A' => '1',
                'REQ0JourneyStopover4' => '',
                'REQ0JourneyStops5.0G' => '',
                'REQ0JourneyStops5.0A' => '1',
                'REQ0JourneyStopover5' => '',
                'existOptimizePrice' => '0',
                'existUnsharpSearch' => 'yes',
                'REQ0HafasChangeTime' => '0:1',
                'existHafasAttrExc' => 'yes',
                'REQ0JourneyProduct_prod_0' => '1',
                'existProductBits0' => 'yes',
                'REQ0JourneyProduct_prod_1' => '1',
                'REQ0JourneyProduct_prod_2' => '1',
                'REQ0JourneyProduct_prod_3' => '1',
                'REQ0JourneyProduct_prod_4' => '1',
                'REQ0JourneyProduct_prod_5' => '1',
                'REQ0JourneyProduct_prod_6' => '1',
                'REQ0JourneyProduct_prod_7' => '1',
                'REQ0JourneyProduct_prod_8' => '1',
                'REQ0JourneyProduct_prod_9' => '1',
                'REQ0JourneyProduct_opt_section_0_list' => '0:0000',
                'disableBaim' => 'yes',
                'REQ0HafasHandicapLimit' => '4:4',
                'changeQueryInputData' => 'yes',
                'start' => 'Chercher correspondance',
            )
        ));

        $crawler = new Crawler(utf8_encode((string) $response->getBody()));
        $timesD = $crawler->filter('.hfs_overview .overview .time.departure')->each(function ($node, $i) { return trim($node->text()); });
        $timesA = $crawler->filter('.hfs_overview .overview .time.arrival')->each(function ($node, $i) { return trim($node->text()); });
        $durations = $crawler->filter('.hfs_overview .overview .duration')->each(function ($node, $i) { return trim($node->text()); });
        $changes = $crawler->filter('.hfs_overview .overview .changes')->each(function ($node, $i) { return trim($node->text()); });
        $products = $crawler->filter('.hfs_overview .overview .products')->each(function ($node, $i) { return trim($node->text()); });

        $overviews = array();
        for ($t = 0; $t < count($timesD); $t++) {
            $overviews[] = array(
                'departure' => $timesD[$t],
                'arrival' => $timesA[$t],
                'duration' => $durations[$t],
                'change' => $changes[$t],
                'product' => $products[$t],
            );
        }

        return $overviews;
    }
}