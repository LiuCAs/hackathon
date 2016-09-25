<?php
// src/AppBundle/Command/InterpelacjeCommand.php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PracaCommand extends Command
{
    const ITEM_PER_PAGE = 20;

    const GOOGLE_API_KEY = "AIzaSyAVmwCNz1smHBx6C1I1h-lXzs6U2HdHQUo";

    const DATA_ADDRESS = 'http://bip.poznan.pl/api-json/bip/oferty-pracy/';

    protected function configure()
    {
        $this
            ->setName('app:praca')
            ->setDescription('Parsowanie ofert pracy');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepareParser();
    }

    private function prepareParser()
    {
        $json = file_get_contents(self::DATA_ADDRESS);

        $jsonDecoded = json_decode($json);
        $returnArray = [];

        foreach ($jsonDecoded as $item) {
            foreach ($item->data as $ite) {
                $totalSize = $ite->oferty_pracy->total_size;
                $totalPages = ceil($totalSize / self::ITEM_PER_PAGE);
                for ($i = 1; $i <= $totalPages; $i++) {
                    $returnArray[] = $this->getPage($i);
                }
            }
        }
        return $returnArray;
    }

    private function getPage($p)
    {
        $json = file_get_contents(self::DATA_ADDRESS . $p);

        $jsonDecoded = json_decode($json);
        $returnArray = [];
        $i = 0;
        foreach ($jsonDecoded as $item) {
            foreach ($item->data as $ite) {
                foreach ($ite->oferty_pracy->items as $singleItem) {
                    foreach ($singleItem as $offer) {
                        foreach ($offer as $oferta) {

                            $returnArray[$i]['subject'] = $oferta->stanowisko . " - " . $oferta->nazwa_organizacja;
                            $returnArray[$i]['date'] = $oferta->data_publikacji;
                            $returnArray[$i]['internal_id'] = $oferta->id;
                            $returnArray[$i]['details'] = $oferta->link;
                            $str = trim(preg_replace('/\s*\([^)]*\)/', '', $oferta->nazwa_organizacja));
                            $streetQuery = $this->prepareQuery($str);
                            $geo = $this->getLatLong($streetQuery);
                            $returnArray[$i]['lat'] = $geo->lat;
                            $returnArray[$i]['lng'] = $geo->lng;
                            var_dump($geo);
                            die;
                        }
                    }
                }
            }
        }
        return $returnArray;
    }

    private function prepareQuery($subject)
    {
        //basic clean
        $subject = trim($subject);
        $subject = str_replace('-', " ", $subject);
        $subject = stripslashes($subject);

        return explode(" ", $subject);
    }

    private function getLatLong($address)
    {
        $baseAddress = "https://maps.googleapis.com/maps/api/geocode/json?address=";
        $addressString = implode("+", $address);
        $addressString .= ",+Poznań";
        $addressString .= ",+Poland";
        $addressString .= "&key=" . self::GOOGLE_API_KEY;
        $url = $baseAddress . $addressString;
        $json = json_decode(file_get_contents($url));
        return $json->results[0]->geometry->location;
    }
}