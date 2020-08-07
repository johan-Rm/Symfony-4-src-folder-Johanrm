<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
// use App\Entity\Traits\AdministrableTrait;

/**
 * AccommodationDetail
 *
 * @ORM\Entity
 * @ ORM\Entity(repositoryClass="App\Repository\AccommodationDetailRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AccommodationDetail
{
    use IdentifiableTrait,
        TimestampableTrait
//        AdministrableTrait
        ;


    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    *
    * @Groups("accommodation")
    */
    private $label;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    *
    * @Groups("accommodation")
    */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Accommodation", inversedBy="details")
     * @ORM\JoinColumn(name="accommodation_id", referencedColumnName="id")
     */
    private $accommodation;


    /**
     * Constructor
     */
    public function __construct()
    {

    }

    public function __toString()
    {
        return $this->getLabel() . ' => ' . $this->getValue();
    }
    
    /**
     * Set the value of Label
     *
     * @param mixed label
     *
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the value of Label
     *
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the value of Value
     *
     * @param mixed value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of Value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getAccommodation(): ?Accommodation
    {
        return $this->accommodation;
    }

    public function setAccommodation(?Accommodation $accommodation): self
    {
        $this->accommodation = $accommodation;

        return $this;
    }

}
