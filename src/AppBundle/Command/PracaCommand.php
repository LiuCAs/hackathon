<?php
// src/AppBundle/Command/InterpelacjeCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Point;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class PracaCommand extends ContainerAwareCommand
{
    const ITEM_PER_PAGE = 20;

    const CITY_CODE = 3064;

    const DATA_ADDRESS = 'http://bip.poznan.pl/api-json/bip/oferty-pracy/';

    private $categoryId;
    private $_container;
    private $_doctrine;
    private $_em;
    private $_googleApiKey;

    protected function configure()
    {
        $this
            ->setName('app:praca')
            ->addArgument('category_id', InputArgument::REQUIRED, 'ID Kategori')
            ->setDescription('Parsowanie ofert pracy');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->_container = $this->getContainer();
        $this->_doctrine = $this->_container->get('doctrine');
        $this->_em = $this->_doctrine->getManager();
        $this->_googleApiKey = $this->_container->getParameter('google_api_key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->categoryId = $input->getArgument('category_id');
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

        foreach ($jsonDecoded as $item) {
            foreach ($item->data as $ite) {
                foreach ($ite->oferty_pracy->items as $singleItem) {
                    foreach ($singleItem as $offer) {
                        foreach ($offer as $oferta) {
                            $PointModel = new Point();
                            try {
                                $PointModel->setCategory($this->categoryId);
                                $PointModel->setCity(self::CITY_CODE);
                                $PointModel->setSubject($oferta->stanowisko . " - " . $oferta->nazwa_organizacja);
                                $PointModel->setDate($oferta->data_publikacji);
                                $PointModel->setInternalId($oferta->id);
                                $PointModel->setDetails($this->parseDetails($oferta->link));
                                $str = trim(preg_replace('/\s*\([^)]*\)/', '', $oferta->nazwa_organizacja));
                                $streetQuery = $this->prepareQuery($str);
                                $geo = $this->getLatLong($streetQuery);
                                $PointModel->setLat($geo->lat);
                                $PointModel->setLng($geo->lng);

                                $em = $this->getContainer()->get('doctrine')->getManager('default');
                                $em->persist($PointModel);
                                $em->flush();
                            } catch (Exception $e) {
                                return 0;
                            }
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
        $addressString .= "&key=" . $this->_googleApiKey;
        $url = $baseAddress . $addressString;
        $json = json_decode(file_get_contents($url));
        return $json->results[0]->geometry->location;
    }

    private function parseDetails($details) {
        $content = json_decode(file_get_contents($details));
        $bipKey = "bip.poznan.pl";
        return json_encode($content->{$bipKey}->data[0]->oferty_pracy->items[0]);
    }
}