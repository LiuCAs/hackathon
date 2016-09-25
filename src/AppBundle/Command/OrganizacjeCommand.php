<?php
namespace AppBundle\Command;

use AppBundle\Entity\Point;
use stdClass;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class OrganizacjeCommand extends ContainerAwareCommand
{
    const CITY_CODE = 3064;

    const DATA_ADDRESS = 'http://bip.poznan.pl/api-json/bip/miejskie-jednostki-organizacyjne/';

    private $categoryId;
    private $_container;
    private $_doctrine;
    private $_em;
    private $_googleApiKey;

    protected function configure()
    {
        $this
            ->setName('app:organizacje')
            ->addArgument('category_id', InputArgument::REQUIRED, 'ID Kategori')
            ->setDescription('Parsowanie organizacji');
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

    private function parseItem($item)
    {
        $jsonDecoded = json_decode($item);
        $item = $jsonDecoded->{'bip.poznan.pl'}->data[0]->organizacje->items[0]->organizacja;

        $pointModel = new Point();
        $pointModel->setCategory($this->categoryId);
        $pointModel->setCity(self::CITY_CODE);
        $pointModel->setSubject($item->nazwa);
        $pointModel->setInternalId($item->id);
        $street = !empty($item->adres_drugi) ? $item->adres_drugi : $item->adres;

        $streetQuery = explode(' ', $street);
        $street = $this->getStreet($streetQuery);

        if (!empty($street)) {
            $pointModel->setStreet($street);
            $geo = $this->getLatLong([$street]);
            $pointModel->setLat($geo->lat);
            $pointModel->setLng($geo->lng);
        }
        $this->_em->persist($pointModel);
        $this->_em->flush();
    }

    private function prepareParser()
    {
        $json = file_get_contents(self::DATA_ADDRESS);
        $jsonDecoded = json_decode($json);
        $items = $jsonDecoded->{'bip.poznan.pl'}->data[0]->organizacje->items[0]->organizacja;
        foreach ($items as $item) {
            $itemData = file_get_contents($item->link);
            $this->parseItem($itemData);
        }
    }

    private function getStreet($array)
    {
        $location = $this->_container->get('app.utils.location');
        $response = $location->gdzieJestSeba(self::CITY_CODE, implode(" ", $array));

        return $response;
    }

    private function getLatLong($address)
    {
        $em = $this->getContainer()->get('doctrine')->getManager('default');

        $entity = $em->getRepository('AppBundle:Point')->findOneBy(['street' => $address[0], 'city' => self::CITY_CODE]);

        if ($entity) {
            $stdClass = new stdClass();
            $stdClass->lat = $entity->getLat();
            $stdClass->lng = $entity->getLng();
            return $stdClass;
        }

        $baseAddress = "https://maps.googleapis.com/maps/api/geocode/json?address=";
        $addressString = rawurlencode(implode("+", $address));
        $addressString .= ",+Poznań";
        $addressString .= ",+Poland";
        $addressString .= "&key=" . $this->_googleApiKey;
        $url = $baseAddress . $addressString;
        $json = json_decode(file_get_contents($url));
        return $json->results[0]->geometry->location;
    }
}