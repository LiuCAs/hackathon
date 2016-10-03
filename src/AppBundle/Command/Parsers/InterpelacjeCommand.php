<?php
namespace AppBundle\Command\Parsers;

use AppBundle\Entity\Point;
use stdClass;
use Symfony\Component\Console\Input\InputArgument;
use AppBundle\Command\ParserAbstract;

class InterpelacjeCommand extends ParserAbstract
{
    private $invalidWords = [
        "interpelacja",
        "ws.",
        "na",
        "za",
    ];

    private $allowedWords = [
        'ul.',
        'ulic',
        'ulica',
        'ulicy',
    ];

    protected function configure()
    {
        $this
            ->setName('app:interpelacje')
            ->addArgument('category_id', InputArgument::REQUIRED, 'Category ID')
            ->setDescription('Parser');
    }

    protected function prepareParser()
    {
        $json = file_get_contents($this->category->getApi()->getDataAddress());

        $jsonDecoded = json_decode($json);
        $returnArray = [];

        foreach ($jsonDecoded as $item) {
            foreach ($item->data as $ite) {
                $totalSize = $ite->interpelacje->total_size;
                $totalPages = ceil($totalSize / $this->category->getApi()->getItemPerPage());
                for ($i = 1; $i <= $totalPages; $i++) {
                    $url = $this->category->getApi()->getDataAddress().$i;
                    $returnArray[] = $this->getPage($url);
                }
            }
        }

        return $returnArray;
    }

    private function getPage($dataUrl)
    {
        $json = file_get_contents($dataUrl);

        $jsonDecoded = json_decode($json);
        $returnArray = [];
        foreach ($jsonDecoded as $item) {
            foreach ($item->data as $ite) {
                foreach ($ite->interpelacje->items as $singleItem) {
                    foreach ($singleItem as $interpelacje) {
                        foreach ($interpelacje as $interpelacja) {
                            $this->savePoint($interpelacja);
                        }
                    }
                }
            }
        }

        return $returnArray;
    }

    protected function prepareQuery($subject)
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
        $test = array_filter(
            $explodedSubject,
            function ($elem) {
                return !in_array(strtolower($elem), $this->invalidWords) && strlen($elem) > 2;
            }
        );

        return $test;
    }

    private function getStreet($array)
    {
        $location = $this->_container->get('app.utils.location');
        $response = $location->gdzieJestSeba($this->category->getCity(), implode(" ", $array));

        return $response;
    }

    protected function getLatLong($address)
    {
        $entity = $this->_em->getRepository('AppBundle:Point')->findOneBy(
            [
                'street' => $address[0],
                'city' => $this->category->getCitiy(),
            ]
        );

        if ($entity) {
            $stdClass = new stdClass();
            $stdClass->lat = $entity->getLat();
            $stdClass->lng = $entity->getLng();

            return $stdClass;
        }

        return parent::getLatLong($address);
    }

    protected function savePoint($jsonModel)
    {
        $PointModel = new Point();
        $PointModel->setCategory($this->category);
        $PointModel->setCity($this->category->getCity());
        $PointModel->setSubject($jsonModel->temat);
        $PointModel->setDate($jsonModel->data_wplywu);
        $PointModel->setInternalId($jsonModel->noteid);
        $attachmentArray = [];
        foreach ($jsonModel->zalaczniki->items as $attachment) {
            $attachment = !empty($attachment->zalacznik->link) ? $attachment->zalacznik->link : null;
            if (!is_null($attachment)) {
                $attachmentArray['urls'][] = $attachment;
            }
        }
        if (!empty($attachmentArray)) {
            $PointModel->setAttachments(json_encode($attachmentArray));
        }
        $streetQuery = $this->prepareQuery($jsonModel->temat);
        $street = $this->getStreet($streetQuery);
        if (!empty($street)) {
            $PointModel->setStreet($street);
            $geo = $this->getLatLong([$street]);
            $PointModel->setLat($geo->lat);
            $PointModel->setLng($geo->lng);
        }
        $this->_em->persist($PointModel);
        $this->_em->flush();
    }
}