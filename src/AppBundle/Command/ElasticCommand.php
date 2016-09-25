<?php
namespace AppBundle\Command;

use FSi\Bundle\TerytDatabaseBundle\Entity\Community;
use FSi\Bundle\TerytDatabaseBundle\Entity\District;
use FSi\Bundle\TerytDatabaseBundle\Entity\Place;
use FSi\Bundle\TerytDatabaseBundle\Entity\Street;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ElasticCommand extends ContainerAwareCommand
{
    protected $_container;
    protected $_doctrine;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;
    protected $_elasticHost;
    protected $_elasticPort;

    protected function configure()
    {
        $this
            ->setName('app:elastic:build')
            ->setDescription('Budowanie indeksu elastica');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->_container = $this->getContainer();
        $this->_doctrine = $this->_container->get('doctrine');
        $this->_em = $this->_doctrine->getManager();
        $this->_elasticHost = $this->_container->getParameter('elastic_host');
        $this->_elasticPort = $this->_container->getParameter('elastic_port');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $places = array();
        $streets = array();

        $repoDistrict = $this->_em->getRepository('FSiTerytDbBundle:District');
        $district = $repoDistrict->find(3064);//Poznań
        $communities = $district->getCommunities();

        foreach ($communities as $community) {
            /** @var Community $community */
            $places[] = $community->getPlaces();
        }

        foreach ($places as $placesCollection) {
            foreach ($placesCollection as $place) {
                /** @var Place $place */
                $streets[] = $place->getStreets();
            }
        }

        $this->initElastic();
        
        foreach ($streets as $streetCollection) {
            foreach ($streetCollection as $street) {
                /** @var Street $street */
                $this->addStreetToIndex($district, $street);
            }
        }
    }

    public function initElastic()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->_elasticHost . '/teryt');
        curl_setopt($ch, CURLOPT_PORT, $this->_elasticPort);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        curl_close($ch);

        $ch = curl_init();
        $jsonString = file_get_contents(__DIR__ . '/../../../ES_Config.json');

        curl_setopt($ch, CURLOPT_URL, $this->_elasticHost . '/teryt/ulice/');
        curl_setopt($ch, CURLOPT_PORT, $this->_elasticPort);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonString)
        ));

        curl_exec($ch);
        curl_close($ch);

    }

    public function addStreetToIndex(District $district, Street $street)
    {
        $ch = curl_init();
        $timeout = 0;
        $jsonString = sprintf('{"code":"%s","name":"%s"}', $district->getCode(), $street->getName());

        curl_setopt($ch, CURLOPT_URL, $this->_elasticHost . '/teryt/ulice/');
        curl_setopt($ch, CURLOPT_PORT, $this->_elasticPort);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonString)
        ));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        curl_exec($ch);
        curl_close($ch);
    }
}