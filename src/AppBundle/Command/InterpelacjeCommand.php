<?php
// src/AppBundle/Command/InterpelacjeCommand.php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InterpelacjeCommand extends Command
{
    const ITEM_PER_PAGE = 50;

    const CITY_CODE = 3064;

    const GOOGLE_API_KEY = "AIzaSyAVmwCNz1smHBx6C1I1h-lXzs6U2HdHQUo";

    const DATA_ADDRESS = 'http://bip.poznan.pl/api-json/bip/interpelacje/';

    private $invalidWords = [
        "interpelacja", "ws.", "na", "za"
    ];

    private $allowedWords = [
        'ul.', 'ulic', 'ulica', 'ulicy'
    ];

    protected function configure()
    {
        $this
            ->setName('app:interpelacje')
            ->setDescription('Parsowanie interpelacji');
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
                $totalSize = $ite->interpelacje->total_size;
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
                foreach ($ite->interpelacje->items as $singleItem) {
                    foreach ($singleItem as $interpelacje) {
                        foreach ($interpelacje as $interpelacja) {
                            $returnArray[$i]['subject'] = $interpelacja->temat;
                            $returnArray[$i]['date'] = $interpelacja->data_wplywu;
                            $returnArray[$i]['internal_id'] = $interpelacja->noteid;
                            $attachmentArray = [];
                            foreach ($interpelacja->zalaczniki->items as $attachment) {
                                $attachment = $attachment->zalacznik->link ?? null;
                                if (!is_null($attachment)) {
                                    $attachmentArray['urls'][] = $attachment;
                                    $attachmentArray['urls'][] = $attachment;
                                }
                            }
                            if (!empty($attachmentArray)) {
                                $returnArray[$i]['attachments'] = $attachmentArray;
                            }
                            $streetQuery = $this->prepareQuery($interpelacja->temat);
                            $street = $this->getStreet($streetQuery);
                            if (!empty($street)) {
                                $returnArray[$i]['street'] = $street;
                                $geo = $this->getLatLong([$street]);
                                $returnArray[$i]['lat'] = $geo->lat;
                                $returnArray[$i]['lng'] = $geo->lng;
                                var_dump($returnArray);
                            }
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
        $explodedSubject = explode(" ", $subject);

        //looking for allowed words, then return max 4 words after that
        foreach ($explodedSubject as $key => $subjectWord) {
            if (in_array($subjectWord, $this->allowedWords)) {
                $subjectCount = count($explodedSubject);
                if ($key < $subjectCount) {
                    $maxIterations = $subjectCount - $key;
                    $returnArray = [];
                    for ($i = 1; $i < $maxIterations; $i++) {
                        $returnArray[] = $explodedSubject[$key + $i];
                        if ($i >= 4) {
                            break;
                        }
                    }
                    return $returnArray;
                }
            }
        }

        //remove invalid and too short
        $test = array_filter($explodedSubject, function ($elem) {
            return !in_array(strtolower($elem), $this->invalidWords) && strlen($elem) > 2;
        });

        return $test;
    }

    private function getStreet($array)
    {
        $return = $array[0] ?? [];
        return $return;
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