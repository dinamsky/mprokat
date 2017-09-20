<?php

namespace MarkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarMark
 *
 * @ORM\Table(name="car_mark")
 * @ORM\Entity(repositoryClass="MarkBundle\Repository\CarMarkRepository")
 */
class CarMark
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
     * @ORM\Column(name="car_type_id", type="integer")
     */
    private $carTypeId;

    /**
     * @var string
     *
     * @ORM\Column(name="header", type="string", length=255)
     */
    private $header;


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
     * Set carTypeId
     *
     * @param integer $carTypeId
     *
     * @return CarMark
     */
    public function setCarTypeId($carTypeId)
    {
        $this->carTypeId = $carTypeId;

        return $this;
    }

    /**
     * Get carTypeId
     *
     * @return int
     */
    public function getCarTypeId()
    {
        return $this->carTypeId;
    }

    /**
     * Set header
     *
     * @param string $header
     *
     * @return CarMark
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
}

