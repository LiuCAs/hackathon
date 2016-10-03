<?php
namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PointAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('subject', 'text')
            ->add('city', 'text');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('id')
            ->add('subject')
            ->add('date')
            ->add('street')
            ->add('lat')
            ->add('lng')
            ->add('city');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('id')
            ->addIdentifier('subject')
            ->addIdentifier('date')
            ->addIdentifier('street')
            ->addIdentifier('lat')
            ->addIdentifier('lng')
            ->addIdentifier('city');
    }
}