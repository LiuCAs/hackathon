<?php
namespace AppBundle\Command\Elastic;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class QueryCommand extends ContainerAwareCommand
{
    protected $_container;
    protected $_doctrine;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    protected function configure()
    {
        $this
            ->setName('app:elastic:query')
            ->setDescription('Zapytanie do elastica')
            ->addOption(
                'code',
                null,
                InputOption::VALUE_REQUIRED,
                'Code'
            )
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'Name'
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->_container = $this->getContainer();
        $this->_doctrine = $this->_container->get('doctrine');
        $this->_em = $this->_doctrine->getManager();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getOption('code');
        $name = $input->getOption('name');

        if ($code && $name) {
            /** @var \AppBundle\Utils\Location $location */
            $location = $this->_container->get('app.utils.location');
            $response = $location->gdzieJestSeba($code, $name);
            echo $response.PHP_EOL;
        }
    }
}