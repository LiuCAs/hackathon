<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CategoryRepository")
 */
class Category
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
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=100)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="lat", type="string", length=50)
     */
    private $lat;

    /**
     * @var string
     *
     * @ORM\Column(name="lng", type="string", length=50)
     */
    private $lng;

    /**
     * @var string
     *
     * @ORM\Column(name="city_name", type="string", length=100)
     */
    private $city_name;

    /**
     * @ORM\ManyToOne(targetEntity="Api", inversedBy="category")
     * @ORM\JoinColumn(name="api_id", referencedColumnName="id")
     */
    private $api;

    /**
     * @ORM\OneToMany(targetEntity="Point", mappedBy="category")
     */
    private $points;

    public function __construct()
    {
        $this->points = new ArrayCollection();
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
     * Set city
     *
     * @param string $city
     *
     * @return Category
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Category
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
     * Set lat
     *
     * @param string $lat
     *
     * @return Category
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param string $lng
     *
     * @return Category
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return string
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set cityName
     *
     * @param string $cityName
     *
     * @return Category
     */
    public function setCityName($cityName)
    {
        $this->city_name = $cityName;

        return $this;
    }

    /**
     * Get cityName
     *
     * @return string
     */
    public function getCityName()
    {
        return $this->city_name;
    }

    /**
     * Add api
     *
     * @param \AppBundle\Entity\Api $api
     *
     * @return Category
     */
    public function addApi(\AppBundle\Entity\Api $api)
    {
        $this->api[] = $api;

        return $this;
    }

    /**
     * Remove api
     *
     * @param \AppBundle\Entity\Api $api
     */
    public function removeApi(\AppBundle\Entity\Api $api)
    {
        $this->api->removeElement($api);
    }

    /**
     * Get api
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Set api
     *
     * @param \AppBundle\Entity\Api $api
     *
     * @return Category
     */
    public function setApi(\AppBundle\Entity\Api $api = null)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * Add point
     *
     * @param \AppBundle\Entity\Point $point
     *
     * @return Category
     */
    public function addPoint(\AppBundle\Entity\Point $point)
    {
        $this->points[] = $point;

        return $this;
    }

    /**
     * Remove point
     *
     * @param \AppBundle\Entity\Point $point
     */
    public function removePoint(\AppBundle\Entity\Point $point)
    {
        $this->points->removeElement($point);
    }

    /**
     * Get points
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPoints()
    {
        return $this->points;
    }
}
