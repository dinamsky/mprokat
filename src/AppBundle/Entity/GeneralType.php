<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * GeneralType
 *
 * @ORM\Table(name="general_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GeneralTypeRepository")
 */
class GeneralType
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
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @var string
     *
     * @ORM\Column(name="header", type="string", length=255)
     */
    private $header;

    /**
     * @var string
     *
     * @ORM\Column(name="car_type_ids", type="string", length=255)
     */
    private $carTypeIds;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * One Category has Many Categories.
     * @ORM\OneToMany(targetEntity="GeneralType", mappedBy="parent")
     */
    private $children;

    /**
     * Many Categories have One Category.
     * @ORM\ManyToOne(targetEntity="GeneralType", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Card", mappedBy="generalType", cascade={"remove"}, orphanRemoval=true)
     */
    private $cards;

    public function __construct() {
        $this->children = new ArrayCollection();
        $this->cards = new ArrayCollection();
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
     * Set parentId
     *
     * @param integer $parentId
     *
     * @return GeneralType
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set header
     *
     * @param string $header
     *
     * @return GeneralType
     */
    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get header
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\GeneralType $child
     *
     * @return GeneralType
     */
    public function addChild(\AppBundle\Entity\GeneralType $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\GeneralType $child
     */
    public function removeChild(\AppBundle\Entity\GeneralType $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\GeneralType $parent
     *
     * @return GeneralType
     */
    public function setParent(\AppBundle\Entity\GeneralType $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\GeneralType
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return GeneralType
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Add card
     *
     * @param \AppBundle\Entity\Card $card
     *
     * @return GeneralType
     */
    public function addCard(\AppBundle\Entity\Card $card)
    {
        $this->cards[] = $card;

        return $this;
    }

    /**
     * Remove card
     *
     * @param \AppBundle\Entity\Card $card
     */
    public function removeCard(\AppBundle\Entity\Card $card)
    {
        $this->cards->removeElement($card);
    }

    /**
     * Get cards
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Set carTypeIds
     *
     * @param string $carTypeIds
     *
     * @return GeneralType
     */
    public function setCarTypeIds($carTypeIds)
    {
        $this->carTypeIds = $carTypeIds;

        return $this;
    }

    /**
     * Get carTypeIds
     *
     * @return string
     */
    public function getCarTypeIds()
    {
        return $this->carTypeIds;
    }
}
