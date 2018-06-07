<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * FormOrder
 *
 * @ORM\Table(name="form_order")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\FormOrderRepository")
 */
class FormOrder
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="transport", type="string", length=255)
     */
    private $transport;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_in", type="date", nullable=true)
     */
    private $dateIn = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_out", type="date", nullable=true)
     */
    private $dateOut = null;

    /**
     * @var string
     *
     * @ORM\Column(name="city_in", type="string", length=255)
     */
    private $cityIn;

    /**
     * @var string
     *
     * @ORM\Column(name="city_out", type="string", length=255)
     */
    private $cityOut;

    /**
     * @var string
     *
     * @ORM\Column(name="form_type", type="string", length=255)
     */
    private $formType;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_create", type="datetime", nullable=true)
     */
    private $dateCreate = null;

    /**
     * @var int
     *
     * @ORM\Column(name="card_id", type="integer", nullable=true)
     */
    private $cardId = null;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $userId = null;


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
     * Set name
     *
     * @param string $name
     *
     * @return FormOrder
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
     * Set email
     *
     * @param string $email
     *
     * @return FormOrder
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return FormOrder
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return FormOrder
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
     * Set transport
     *
     * @param string $transport
     *
     * @return FormOrder
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * Get transport
     *
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Set dateIn
     *

     *
     * @return FormOrder
     */
    public function setDateIn($dateIn)
    {
        $this->dateIn = $dateIn;

        return $this;
    }

    /**
     * Get dateIn
     *
     * @return \DateTime
     */
    public function getDateIn()
    {
        return $this->dateIn;
    }

    /**
     * Set dateOut
     *

     *
     * @return FormOrder
     */
    public function setDateOut($dateOut)
    {
        $this->dateOut = $dateOut;

        return $this;
    }

    /**
     * Get dateOut
     *
     * @return \DateTime
     */
    public function getDateOut()
    {
        return $this->dateOut;
    }

    /**
     * Set cityIn
     *
     * @param string $cityIn
     *
     * @return FormOrder
     */
    public function setCityIn($cityIn)
    {
        $this->cityIn = $cityIn;

        return $this;
    }

    /**
     * Get cityIn
     *
     * @return string
     */
    public function getCityIn()
    {
        return $this->cityIn;
    }

    /**
     * Set cityOut
     *
     * @param string $cityOut
     *
     * @return FormOrder
     */
    public function setCityOut($cityOut)
    {
        $this->cityOut = $cityOut;

        return $this;
    }

    /**
     * Get cityOut
     *
     * @return string
     */
    public function getCityOut()
    {
        return $this->cityOut;
    }

    /**
     * Set formType
     *
     * @param string $formType
     *
     * @return FormOrder
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;

        return $this;
    }

    /**
     * Get formType
     *
     * @return string
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * Set dateCreate
     *
     * @param string $dateCreate
     *
     * @return FormOrder
     */
    public function setDateCreate($dateCreate)
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    /**
     * Get dateCreate
     *
     * @return string
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * Set cardId
     *
     * @param integer $cardId
     *
     * @return FormOrder
     */
    public function setCardId($cardId)
    {
        $this->cardId = $cardId;

        return $this;
    }

    /**
     * Get cardId
     *
     * @return int
     */
    public function getCardId()
    {
        return $this->cardId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return FormOrder
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
}
