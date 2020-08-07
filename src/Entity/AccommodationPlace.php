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
use Symfony\Component\Serializer\Annotation\Groups;



/**
 * Entities that have a somewhat fixed, physical extension.
 *
 * @see http://schema.org/Place Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Place",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 *
 * @ ORM\Entity(repositoryClass="App\Repository\AccommodationPlaceRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AccommodationPlace
{
    use IdentifiableTrait
        , TimestampableTrait
        , SluggableNameTrait
        , AdministrableTrait
    ;

    /**
     * @var string|null a description of the item
     *
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/description")
     * @Groups("place")
     */
    private $description;

    /**
     * @ ORM\OneToMany(targetEntity="App\Entity\Accommodation", mappedBy="place", fetch="EXTRA_LAZY", cascade={"persist"}, orphanRemoval=false)
     */
    // private $accommodations;

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        // $this->accommodations = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    // /**
    //  * @return Collection|Accommodation[]
    //  */
    // public function getAccommodations(): Collection
    // {
    //     return $this->accommodations;
    // }

    // public function addAccommodation(Accommodation $accommodation): self
    // {
    //     if (!$this->accommodations->contains($accommodation)) {
    //         $this->accommodations[] = $accommodation;
    //         $accommodation->setPlace($this);
    //     }

    //     return $this;
    // }

    // public function removeAccommodation(Accommodation $accommodation): self
    // {
    //     if ($this->accommodations->contains($accommodation)) {
    //         $this->accommodations->removeElement($accommodation);
    //         // set the owning side to null (unless already changed)
    //         if ($accommodation->getPlace() === $this) {
    //             $accommodation->setPlace(null);
    //         }
    //     }

    //     return $this;
    // }
}
