<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Api
 *
 * @ORM\Table(name="api")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ApiRepository")
 */
class Api
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="item_per_page", type="integer", length=10, nullable=true)
     */
    private $item_per_page;

    /**
     * @var string
     *
     * @ORM\Column(name="data_address", type="text")
     */
    private $data_address;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="api")
     */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set itemPerPage
     *
     * @param \int $itemPerPage
     *
     * @return Api
     */
    public function setItemPerPage($itemPerPage)
    {
        $this->item_per_page = $itemPerPage;

        return $this;
    }

    /**
     * Get itemPerPage
     *
     * @return \int
     */
    public function getItemPerPage()
    {
        return $this->item_per_page;
    }

    /**
     * Set dataAddress
     *
     * @param string $dataAddress
     *
     * @return Api
     */
    public function setDataAddress($dataAddress)
    {
        $this->data_address = $dataAddress;

        return $this;
    }

    /**
     * Get dataAddress
     *
     * @return string
     */
    public function getDataAddress()
    {
        return $this->data_address;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Api
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add category
     *
     * @param \AppBundle\Entity\Category $category
     *
     * @return Api
     */
    public function addCategory(\AppBundle\Entity\Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \AppBundle\Entity\Category $category
     */
    public function removeCategory(\AppBundle\Entity\Category $category)
    {
        $this->categories->removeElement($category);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
