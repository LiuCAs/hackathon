<?php

namespace AppBundle\Command\Parsers;

use AppBundle\Entity\Point;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use AppBundle\Command\ParserAbstract;

class PracaCommand extends ParserAbstract
{
    protected function configure()
    {
        $this
            ->setName('app:praca')
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
                $totalSize = $ite->oferty_pracy->total_size;
                $totalPages = ceil($totalSize / $this->category->getApi()->getItemPerPage());
                for ($i = 1; $i <= $totalPages; $i++) {
                    $newUrl = $this->category->getApi()->getDataAddress() . $i;
                    $returnArray[] = $this->getPage($newUrl);
                }
            }
        }
        return $returnArray;
    }

    private function getPage($url)
    {
        $json = file_get_contents($url);

        $jsonDecoded = json_decode($json);
        $returnArray = [];

        foreach ($jsonDecoded as $item) {
            foreach ($item->data as $ite) {
                foreach ($ite->oferty_pracy->items as $singleItem) {
                    foreach ($singleItem as $offer) {
                        foreach ($offer as $oferta) {
                            $this->savePoint($oferta);
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

        return explode(" ", $subject);
    }

    private function parseDetails($details)
    {
        $content = json_decode(file_get_contents($details));
        $mainKey = "bip.poznan.pl";
        return json_encode($content->{$mainKey}->data[0]->oferty_pracy->items[0]);
    }

    public function savePoint($jsonModel)
    {
        $PointModel = new Point();
        try {
            $PointModel->setCategory($this->category);
            $PointModel->setCity($this->category->getCity());
            $PointModel->setSubject($jsonModel->stanowisko . " - " . $jsonModel->nazwa_organizacja);
            $PointModel->setDate($jsonModel->data_publikacji);
            $PointModel->setInternalId($jsonModel->id);
            $PointModel->setDetails($this->parseDetails($jsonModel->link));
            $str = trim(preg_replace('/\s*\([^)]*\)/', '', $jsonModel->nazwa_organizacja));
            $streetQuery = $this->prepareQuery($str);
            $geo = $this->getLatLong($streetQuery);
            $PointModel->setLat($geo->lat);
            $PointModel->setLng($geo->lng);

            $this->_em->persist($PointModel);
            $this->_em->flush();
        } catch (Exception $e) {

        }
    }
}