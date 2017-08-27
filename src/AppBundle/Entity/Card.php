<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * Card
 *
 * @ORM\Table(name="card")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CardRepository")
 */
class Card
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="header", type="string", length=255, nullable=true)
     */
    private $header = null;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content = null;



    /**
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     */
    private $city;

    /**
     * @var int
     *
     * @ORM\Column(name="city_id", type="bigint", nullable=true)
     */
    private $cityId = null;

    /**
     * @var int
     *
     * @ORM\Column(name="prod_year", type="integer", nullable=true)
     */
    private $prodYear = null;

    /**
     * @var int
     *
     * @ORM\Column(name="general_type_id", type="integer", nullable=true)
     */
    private $generalTypeId = null;


    /**
     * @ORM\ManyToOne(targetEntity="GeneralType")
     * @ORM\JoinColumn(name="general_type_id", referencedColumnName="id")
     */
    private $generalType;

    /**
     * @var int
     *
     * @ORM\Column(name="condition_id", type="integer", nullable=true)
     */
    private $conditionId = null;

    /**
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumn(name="condition_id", referencedColumnName="id")
     */
    private $condition;

    /**
     * @var int
     *
     * @ORM\Column(name="service_type_id", type="integer", nullable=true)
     */
    private $serviceTypeId = null;


    /**
     * @ORM\ManyToOne(targetEntity="Mark")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     */
    private $markModel;

    /**
     * @var int
     *
     * @ORM\Column(name="model_id", type="bigint", nullable=true)
     */
    private $modelId = null;

    /**
     * @var int
     *
     * @ORM\Column(name="color_id", type="integer", nullable=true)
     */
    private $colorId = null;

    /**
     * @ORM\ManyToOne(targetEntity="Color")
     * @ORM\JoinColumn(name="color_id", referencedColumnName="id")
     */
    private $color;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="bigint", nullable=true)
     */
    private $userId = null;

    /**
     * @ORM\ManyToOne(targetEntity="UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=true)
     */
    private $isActive = 1;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_create", type="datetime", nullable=true)
     */
    private $dateCreate = null;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="date_update", type="datetime", nullable=true)
     */
    private $dateUpdate = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_expiry", type="datetime", nullable=true)
     */
    private $dateExpiry = null;

    /**
     * @var int
     *
     * @ORM\Column(name="views", type="integer", nullable=true)
     */
    private $views = 0;

    /**
     * @ORM\OneToMany(targetEntity="FieldInteger", mappedBy="card", cascade={"remove"}, orphanRemoval=true)
     */
    private $fieldIntegers;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CardFeature", mappedBy="card", cascade={"remove"}, orphanRemoval=true)
     */
    private $cardFeatures;

    public function __construct() {
        $this->fieldIntegers = new ArrayCollection();
        $this->features = new ArrayCollection();
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
     * Set header
     *
     * @param string $header
     *
     * @return Card
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
     * Set content
     *
     * @param string $content
     *
     * @return Card
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set prodYear
     *
     * @param integer $prodYear
     *
     * @return Card
     */
    public function setProdYear($prodYear)
    {
        $this->prodYear = $prodYear;

        return $this;
    }

    /**
     * Get prodYear
     *
     * @return int
     */
    public function getProdYear()
    {
        return $this->prodYear;
    }



    /**
     * Set conditionId
     *
     * @param integer $conditionId
     *
     * @return Card
     */
    public function setConditionId($conditionId)
    {
        $this->conditionId = $conditionId;

        return $this;
    }

    /**
     * Get conditionId
     *
     * @return int
     */
    public function getConditionId()
    {
        return $this->conditionId;
    }

    /**
     * Set serviceTypeId
     *
     * @param integer $serviceTypeId
     *
     * @return Card
     */
    public function setServiceTypeId($serviceTypeId)
    {
        $this->serviceTypeId = $serviceTypeId;

        return $this;
    }

    /**
     * Get serviceTypeId
     *
     * @return int
     */
    public function getServiceTypeId()
    {
        return $this->serviceTypeId;
    }

    /**
     * Set modelId
     *
     * @param integer $modelId
     *
     * @return Card
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;

        return $this;
    }

    /**
     * Get modelId
     *
     * @return int
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * Set colorId
     *
     * @param integer $colorId
     *
     * @return Card
     */
    public function setColorId($colorId)
    {
        $this->colorId = $colorId;

        return $this;
    }

    /**
     * Get colorId
     *
     * @return int
     */
    public function getColorId()
    {
        return $this->colorId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return Card
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Card
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set dateCreate
     *
     * @param \DateTime $dateCreate
     *
     * @return Card
     */
    public function setDateCreate($dateCreate)
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    /**
     * Get dateCreate
     *
     * @return \DateTime
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * Set dateUpdate
     *
     * @param \DateTime $dateUpdate
     *
     * @return Card
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return \DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set dateExpiry
     *
     * @param \DateTime $dateExpiry
     *
     * @return Card
     */
    public function setDateExpiry($dateExpiry)
    {
        $this->dateExpiry = $dateExpiry;

        return $this;
    }

    /**
     * Get dateExpiry
     *
     * @return \DateTime
     */
    public function getDateExpiry()
    {
        return $this->dateExpiry;
    }

    /**
     * Set views
     *
     * @param integer $views
     *
     * @return Card
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views
     *
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set generalTypeId
     *
     * @param integer $generalTypeId
     *
     * @return Card
     */
    public function setGeneralTypeId($generalTypeId)
    {
        $this->generalTypeId = $generalTypeId;

        return $this;
    }

    /**
     * Get generalTypeId
     *
     * @return integer
     */
    public function getGeneralTypeId()
    {
        return $this->generalTypeId;
    }

    /**
     * Set cityId
     *
     * @param integer $cityId
     *
     * @return Card
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId
     *
     * @return integer
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * Set condition
     *
     * @param \AppBundle\Entity\State $condition
     *
     * @return Card
     */
    public function setCondition(\AppBundle\Entity\State $condition = null)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * Get condition
     *
     * @return \AppBundle\Entity\State
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Set color
     *
     * @param \AppBundle\Entity\Color $color
     *
     * @return Card
     */
    public function setColor(\AppBundle\Entity\Color $color = null)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return \AppBundle\Entity\Color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set generalType
     *
     * @param \AppBundle\Entity\GeneralType $generalType
     *
     * @return Card
     */
    public function setGeneralType(\AppBundle\Entity\GeneralType $generalType = null)
    {
        $this->generalType = $generalType;

        return $this;
    }

    /**
     * Get generalType
     *
     * @return \AppBundle\Entity\GeneralType
     */
    public function getGeneralType()
    {
        return $this->generalType;
    }

    /**
     * Set city
     *
     * @param \AppBundle\Entity\City $city
     *
     * @return Card
     */
    public function setCity(\AppBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \AppBundle\Entity\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set markModel
     *
     * @param \AppBundle\Entity\Mark $markModel
     *
     * @return Card
     */
    public function setMarkModel(\AppBundle\Entity\Mark $markModel = null)
    {
        $this->markModel = $markModel;

        return $this;
    }

    /**
     * Get markModel
     *
     * @return \AppBundle\Entity\Mark
     */
    public function getMarkModel()
    {
        return $this->markModel;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Card
     */
    public function setUser(\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add fieldInteger
     *
     * @param \AppBundle\Entity\FieldInteger $fieldInteger
     *
     * @return Card
     */
    public function addFieldInteger(\AppBundle\Entity\FieldInteger $fieldInteger)
    {
        $this->fieldIntegers[] = $fieldInteger;

        return $this;
    }

    /**
     * Remove fieldInteger
     *
     * @param \AppBundle\Entity\FieldInteger $fieldInteger
     */
    public function removeFieldInteger(\AppBundle\Entity\FieldInteger $fieldInteger)
    {
        $this->fieldIntegers->removeElement($fieldInteger);
    }

    /**
     * Get fieldIntegers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFieldIntegers()
    {
        return $this->fieldIntegers;
    }







    /**
     * Add cardFeature
     *
     * @param \AppBundle\Entity\CardFeature $cardFeature
     *
     * @return Card
     */
    public function addCardFeature(\AppBundle\Entity\CardFeature $cardFeature)
    {
        $this->cardFeatures[] = $cardFeature;

        return $this;
    }

    /**
     * Remove cardFeature
     *
     * @param \AppBundle\Entity\CardFeature $cardFeature
     */
    public function removeCardFeature(\AppBundle\Entity\CardFeature $cardFeature)
    {
        $this->cardFeatures->removeElement($cardFeature);
    }

    /**
     * Get cardFeatures
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCardFeatures()
    {
        return $this->cardFeatures;
    }
}
