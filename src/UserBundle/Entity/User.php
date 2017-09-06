<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 */
class User
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
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="temp_password", type="string", length=255)
     */
    private $tempPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="header", type="string", length=255)
     */
    private $header;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=255)
     */
    private $login;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_create", type="datetime", nullable=true)
     */
    private $dateCreate = null;

    /**
     * @var string
     *
     * @ORM\Column(name="activate_string", type="string", length=32)
     */
    private $activateString;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_activated", type="boolean")
     */
    private $isActivated = 0;


    /**
     * @ORM\OneToMany(targetEntity="UserBundle\Entity\UserInfo", mappedBy="user")
     */
    private $information;

    /**
     * @ORM\OneToMany(targetEntity="UserBundle\Entity\UserOrder", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $userOrders;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Card", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $cards;

    public function __construct() {
        $this->information = new ArrayCollection();
        $this->userOrders = new ArrayCollection();
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
     * Set email
     *
     * @param string $email
     *
     * @return User
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
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set header
     *
     * @param string $header
     *
     * @return User
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
     * Set dateCreate
     *
     * @param \DateTime $dateCreate
     *
     * @return User
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
     * Set isActivated
     *
     * @param boolean $isActivated
     *
     * @return User
     */
    public function setIsActivated($isActivated)
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    /**
     * Get isActivated
     *
     * @return boolean
     */
    public function getIsActivated()
    {
        return $this->isActivated;
    }

    /**
     * Set activateString
     *
     * @param string $activateString
     *
     * @return User
     */
    public function setActivateString($activateString)
    {
        $this->activateString = $activateString;

        return $this;
    }

    /**
     * Get activateString
     *
     * @return string
     */
    public function getActivateString()
    {
        return $this->activateString;
    }

    /**
     * Add information
     *
     * @param \UserBundle\Entity\UserInfo $information
     *
     * @return User
     */
    public function addInformation(\UserBundle\Entity\UserInfo $information)
    {
        $this->information[] = $information;

        return $this;
    }

    /**
     * Remove information
     *
     * @param \UserBundle\Entity\UserInfo $information
     */
    public function removeInformation(\UserBundle\Entity\UserInfo $information)
    {
        $this->information->removeElement($information);
    }

    /**
     * Get information
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInformation()
    {
        return $this->information;
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return User
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Add userOrder
     *
     * @param \UserBundle\Entity\UserOrder $userOrder
     *
     * @return User
     */
    public function addUserOrder(\UserBundle\Entity\UserOrder $userOrder)
    {
        $this->userOrders[] = $userOrder;

        return $this;
    }

    /**
     * Remove userOrder
     *
     * @param \UserBundle\Entity\UserOrder $userOrder
     */
    public function removeUserOrder(\UserBundle\Entity\UserOrder $userOrder)
    {
        $this->userOrders->removeElement($userOrder);
    }

    /**
     * Get userOrders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserOrders()
    {
        return $this->userOrders;
    }

    /**
     * Add card
     *
     * @param \AppBundle\Entity\Card $card
     *
     * @return User
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
     * Set tempPassword
     *
     * @param string $tempPassword
     *
     * @return User
     */
    public function setTempPassword($tempPassword)
    {
        $this->tempPassword = $tempPassword;

        return $this;
    }

    /**
     * Get tempPassword
     *
     * @return string
     */
    public function getTempPassword()
    {
        return $this->tempPassword;
    }
}
