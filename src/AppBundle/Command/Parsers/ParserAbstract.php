<?php
namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

abstract class ParserAbstract extends ContainerAwareCommand
{
    protected $category;
    protected $itemPerPage;
    protected $_container;
    protected $_doctrine;
    protected $_em;
    protected $_googleApiKey;
    protected $googleBaseAddress;


    protected function configure($code)
    {
        $this
            ->setName('app:' . $code)
            ->addArgument('category_id', InputArgument::REQUIRED, 'Category ID')
            ->setDescription('Parser');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->_container = $this->getContainer();
        $this->_doctrine = $this->_container->get('doctrine');
        $this->_em = $this->_doctrine->getManager();
        $this->_googleApiKey = $this->_container->getParameter('google_api_key');
        $this->googleBaseAddress = "https://maps.googleapis.com/maps/api/geocode/json?address=";
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $categoryId = $input->getArgument('category_id');
        $this->category = $this->_em->getRepository('AppBundle:Category')->find($categoryId);
        if ($this->category) {
            $this->prepareParser();
        }
    }

    /**
     * Prepare parser
     */
    protected function prepareParser()
    {
        throw new MethodNotImplementedException("prepareParser method not implemented");
    }


    /**
     * @param $string
     */
    protected function prepareQuery($string)
    {
        throw new MethodNotImplementedException("prepareQuery method not implemented");
    }

    /**
     * @param $address
     * @return \stdClass
     */
    protected function getLatLong($address)
    {
        $addressString = implode("+", $address);
        $addressString .= ",+" . $this->category->getCity();
        $addressString .= ",+" . $this->category->setCountry();
        $addressString .= "&key=" . $this->_googleApiKey;
        $url = $this->googleBaseAddress . $addressString;
        $json = json_decode(file_get_contents($url));
        return $json->results[0]->geometry->location;
    }

    /**
     * @param $jsonModel
     */
    protected function savePoint($jsonModel)
    {
        throw new MethodNotImplementedException("savePoint Method not implemented");
    }
}