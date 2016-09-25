<?php
// src/AppBundle/Command/InterpelacjeCommand.php
namespace AppBundle\Command;

use AppBundle\Entity\Point;
use stdClass;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;


class InterpelacjeLublinCommand extends ContainerAwareCommand
{
    const ITEM_PER_PAGE = 100;

    const CITY_CODE = 663;

    const DATA_ADDRESS = 'http://bip.lublin.eu/api-json/in_rm_vii/';

    private $invalidWords = [];

    private $allowedWords = [
        'ul.', 'ulic', 'ulica', 'ulicy'
    ];

    private $categoryId;
    private $_container;
    private $_doctrine;
    private $_em;
    private $_googleApiKey;

    protected function configure()
    {
        $this
            ->setName('app:interpelacjeLublin')
            ->addArgument('category_id', InputArgument::REQUIRED, 'ID Kategori')
            ->setDescription('Parsowanie interpelacji');
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
        if (empty($item->sprawa)) {
            return;
        }

        $pointModel = new Point();
        $pointModel->setCategory($this->categoryId);
        $pointModel->setCity(self::CITY_CODE);
        $pointModel->setSubject($item->sprawa);
        $pointModel->setDate($item->data_wp);
        $pointModel->setInternalId($item->id);
        $attachmentArray = [
            'urls' => [
                $item->link
            ]
        ];
        $streetQuery = $this->prepareQuery($item->sprawa);
        $street = $this->getStreet($streetQuery);
        $pointModel->setAttachments(json_encode($attachmentArray));

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
        $first = 0;
        $parsedItems = 0;
        do {
            $url = sprintf('%s?first=%s&limit=%s', self::DATA_ADDRESS, $first, self::ITEM_PER_PAGE);
            $json = file_get_contents($url);
            $jsonDecoded = json_decode($json);
            foreach ($jsonDecoded->items as $item) {
                $this->parseItem($item);
                $parsedItems++;
            }
        } while ($parsedItems < $jsonDecoded->count);
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
        $addressString = implode("+", $address);
        $addressString .= ",+Lublin";
        $addressString .= ",+Poland";
        $addressString .= "&key=" . $this->_googleApiKey;
        $url = $baseAddress . $addressString;
        $json = json_decode(file_get_contents($url));
        return $json->results[0]->geometry->location;
    }
}