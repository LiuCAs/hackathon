<?php
namespace AppBundle\Command\Parsers;

use AppBundle\Entity\Point;
use stdClass;
use Symfony\Component\Console\Input\InputArgument;
use AppBundle\Command\ParserAbstract;

class InterpelacjeLublinCommand extends ParserAbstract
{
    private $invalidWords = [];

    private $allowedWords = [
        'ul.',
        'ulic',
        'ulica',
        'ulicy',
    ];

    protected function configure()
    {
        $this
            ->setName('app:interpelacjeLublin')
            ->addArgument('category_id', InputArgument::REQUIRED, 'Category ID')
            ->setDescription('Parser');
    }

    private function parseItem($item)
    {
        if (empty($item->sprawa)) {
            return;
        }

        $pointModel = new Point();
        $pointModel->setCategory($this->category);
        $pointModel->setCity($this->category->getCity());
        $pointModel->setSubject($item->sprawa);
        $pointModel->setDate($item->data_wp);
        $pointModel->setInternalId($item->id);
        $attachmentArray = [
            'urls' => [
                $item->link,
            ],
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

    protected function prepareParser()
    {
        $first = 0;
        $parsedItems = 0;
        do {
            $url = sprintf(
                '%s?first=%s&limit=%s',
                $this->category->getApi()->getDataAddress(),
                $first,
                $this->category->getApi()->getItemsPerPage()
            );
            $json = file_get_contents($url);
            $jsonDecoded = json_decode($json);
            foreach ($jsonDecoded->items as $item) {
                $this->parseItem($item);
                $parsedItems++;
            }
            $first += $this->category->getApi()->getItemsPerPage();
        } while ($parsedItems < $jsonDecoded->count);
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
}