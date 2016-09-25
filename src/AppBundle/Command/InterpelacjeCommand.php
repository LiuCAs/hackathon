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


class InterpelacjeCommand extends ContainerAwareCommand
{
    const ITEM_PER_PAGE = 50;

    const CITY_CODE = "3064";

    const GOOGLE_API_KEY = "AIzaSyAVmwCNz1smHBx6C1I1h-lXzs6U2HdHQUo";

    const DATA_ADDRESS = 'http://bip.poznan.pl/api-json/bip/interpelacje/';

    private $invalidWords = [
        "interpelacja", "ws.", "na", "za"
    ];

    private $allowedWords = [
        'ul.', 'ulic', 'ulica', 'ulicy'
    ];

    private $categoryId;

    protected function configure()
    {
        $this
            ->setName('app:interpelacje')
            ->addArgument('category_id', InputArgument::REQUIRED, 'ID Kategori')
            ->setDescription('Parsowanie interpelacji');
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
                            $PointModel = new Point();
                            try {
                                $PointModel->setCategory($this->categoryId);
                                $PointModel->setCity(self::CITY_CODE);
                                $PointModel->setSubject($interpelacja->temat);
                                $PointModel->setDate($interpelacja->data_wplywu);
                                $PointModel->setInternalId($interpelacja->noteid);
                                $attachmentArray = [];
                                foreach ($interpelacja->zalaczniki->items as $attachment) {
                                    $attachment = !empty($attachment->zalacznik->link) ? $attachment->zalacznik->link : null;
                                    if (!is_null($attachment)) {
                                        $attachmentArray['urls'][] = $attachment;
                                    }
                                }
                                if (!empty($attachmentArray)) {
                                    $PointModel->setAttachments(json_encode($attachmentArray));
                                }
                                $streetQuery = $this->prepareQuery($interpelacja->temat);
                                $street = $this->getStreet($streetQuery);
                                if (!empty($street)) {
                                    $PointModel->setStreet($street);
                                    $geo = $this->getLatLong([$street]);
                                    $PointModel->setLat($geo->lat);
                                    $PointModel->setLng($geo->lng);
                                }
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
        $location = new \AppBundle\Utils\Location();
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
        $addressString .= ",+Poznań";
        $addressString .= ",+Poland";
        $addressString .= "&key=" . self::GOOGLE_API_KEY;
        $url = $baseAddress . $addressString;
        $json = json_decode(file_get_contents($url));
        return $json->results[0]->geometry->location;
    }
}