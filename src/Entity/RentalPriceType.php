<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\SluggableNameTrait;
use App\Entity\Traits\AdministrableTrait;
use App\Entity\Accommodation;


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
 * @ ORM\Entity(repositoryClass="App\Repository\RentalPriceTypeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class RentalPriceType
{
    use IdentifiableTrait
    , TimestampableTrait
    , SluggableNameTrait
    , AdministrableTrait
    ;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Accommodation", mappedBy="rentalPriceType")
     */
    private $accommodations;

 

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->accommodations = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return Collection|Accommodation[]
     */
    public function getAccommodations(): Collection
    {
        return $this->accommodations;
    }

    public function addAccommodation(Accommodation $accommodation): self
    {
        if (!$this->accommodations->contains($accommodation)) {
            $this->accommodations[] = $accommodation;
            $accommodation->setRentalPriceType($this);
        }

        return $this;
    }

    public function removeAccommodation(Accommodation $accommodation): self
    {
        if ($this->accommodations->contains($accommodation)) {
            $this->accommodations->removeElement($accommodation);
            // set the owning side to null (unless already changed)
            if ($accommodation->getRentalPriceType() === $this) {
                $accommodation->setRentalPriceType(null);
            }
        }

        return $this;
    }
}
