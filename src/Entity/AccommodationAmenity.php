<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\SluggableNameTrait;
use App\Entity\Traits\AdministrableTrait;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * Entities that have a somewhat fixed, physical extension.
 *
 * @see http://schema.org/? Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/?",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 *
 * @ ORM\Entity(repositoryClass="App\Repository\AccommodationAmenityRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AccommodationAmenity
{
    use IdentifiableTrait
        , TimestampableTrait
        , SluggableNameTrait
        , AdministrableTrait
    ;

    /**
    * @ORM\ManyToMany(targetEntity="Accommodation", mappedBy="amenities")
    */
    private $accommodations;

   /**
     * @var boolean
     *
     * @ORM\Column(name="withPicto", type="boolean", nullable=true)
     * @Groups("accommodation")
     */
    private $withPicto;

    /**
     * @var boolean
     *
     * @ORM\Column(name="slugPicto", type="string", nullable=true)
     * @Groups("accommodation")
     */
    private $slugPicto;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->accommodations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Add accommodation
     *
     * @param \App\Entity\Accommodation $accommodation
     *
     * @return Accommodation
     */
    public function addAccommodation($accommodation)
    {
        if ($this->accommodations->contains($accommodation)) {
            return;
        }

        $this->accommodations->add($accommodation);
    }

    /**
     * Remove accommodation
     *
     * @param \App\Entity\Accommodation $accommodation
     */
    public function removeAccommodation($accommodation)
    {
        if (!$this->accommodations->contains($accommodation)) {
            return;
        }

        $this->accommodations->removeElement($accommodation);
    }

    /**
     * Get accommodations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccommodations()
    {
        return $this->accommodations;
    }

    /**
     * Set withPicto
     *
     * @param string $withPicto
     *
     * @return AccommodationAmenity
     */
    public function setWithPicto($withPicto)
    {
        $this->withPicto = $withPicto;

        return $this;
    }

    /**
     * Get withPicto
     *
     * @return string
     */
    public function getWithPicto()
    {
        return $this->withPicto;
    }

    /**
     * Set slugPicto
     *
     * @param string $slugPicto
     *
     * @return AccommodationAmenity
     */
    public function setSlugPicto($slugPicto)
    {
        $this->slugPicto = $slugPicto;

        return $this;
    }

    /**
     * Get slugPicto
     *
     * @return string
     */
    public function getSlugPicto()
    {
        return $this->slugPicto;
    }
}
