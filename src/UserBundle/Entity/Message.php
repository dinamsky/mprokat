<?php

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Message
 *
 * @ORM\Table(name="message")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\MessageRepository")
 */
class Message
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
     * @ORM\Column(name="card_id", type="bigint")
     */
    private $cardId;

    /**
     * @var int
     *
     * @ORM\Column(name="from_user_id", type="bigint")
     */
    private $fromUserId;

    /**
     * @var int
     *
     * @ORM\Column(name="to_user_id", type="bigint")
     */
    private $toUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_create", type="datetime", nullable=true)
     */
    private $dateCreate = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_attachment", type="boolean")
     */
    private $isAttachment;


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
     * Set fromUserId
     *
     * @param integer $fromUserId
     *
     * @return Message
     */
    public function setFromUserId($fromUserId)
    {
        $this->fromUserId = $fromUserId;

        return $this;
    }

    /**
     * Get fromUserId
     *
     * @return int
     */
    public function getFromUserId()
    {
        return $this->fromUserId;
    }

    /**
     * Set toUserId
     *
     * @param integer $toUserId
     *
     * @return Message
     */
    public function setToUserId($toUserId)
    {
        $this->toUserId = $toUserId;

        return $this;
    }

    /**
     * Get toUserId
     *
     * @return int
     */
    public function getToUserId()
    {
        return $this->toUserId;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set dateCreate
     *
     * @param \DateTime $dateCreate
     *
     * @return Message
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
     * Set isAttachment
     *
     * @param boolean $isAttachment
     *
     * @return Message
     */
    public function setIsAttachment($isAttachment)
    {
        $this->isAttachment = $isAttachment;

        return $this;
    }

    /**
     * Get isAttachment
     *
     * @return bool
     */
    public function getIsAttachment()
    {
        return $this->isAttachment;
    }

    /**
     * Set cardId
     *
     * @param integer $cardId
     *
     * @return Message
     */
    public function setCardId($cardId)
    {
        $this->cardId = $cardId;

        return $this;
    }

    /**
     * Get cardId
     *
     * @return integer
     */
    public function getCardId()
    {
        return $this->cardId;
    }
}
