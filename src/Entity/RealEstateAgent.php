<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\AdministrableTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


/**
 * A real-estate agent.
 *
 * @see http://schema.org/RealEstateAgent Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/RealEstateAgent",
 *  attributes={
 *      "normalization_context"={
 *          "groups"={
 *           "thing"
 *           , "identifier"
 *           , "media"
 *           , "real-estate-agent"
 *           , "person"
 *          }
 *      }
 *  },
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\HasLifecycleCallbacks()
 */
class RealEstateAgent
{
    use IdentifiableTrait
        , ThingTrait
        , TimestampableTrait
        , AdministrableTrait
    ;

    /**
     * @var string|null the price range of the business, for example ```$$$```
     *
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/priceRange")
     * @Groups("real-estate-agent")
     */
    private $priceRange;

     /**
     * @ORM\OneToMany(targetEntity="App\Entity\Accommodation", mappedBy="realEstateAgent")
     * @ Groups("real-estate-agent")
     * @ ApiSubresource()
     */
    private $accommodations;


    /**
     * @var Person|null The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Person")
     * @Groups("real-estate-agent")
     * @ ApiSubresource
     */
    private $person;
    
    /**
     * @var MediaObject|null indicates the main image on the page
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     * @ApiProperty(iri="http://schema.org/primaryImage")
     * @Groups("real-estate-agent")
     * @ApiSubresource
     */
    private $primaryImage;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, options={"comment":"Phone"}, nullable=true)
     *
     * @ Assert\NotBlank(
     *  message="Please enter your phone number"
     * )
     * @ Assert\Regex(
     *  pattern="/^(0)[0-9]{9}$/",
     *  match=true,
     *  message="Your phone number is invalid"
     * )
     *
     * @Groups("real-estate-agent")
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, options={"comment":"Email"}, nullable=true)
     *
     * @ Assert\Expression(
     *     "this.getEmail() == null && !this.getPhone() == null",
     *     message="Please, enter email or phone."
     * )
     *
     * @ Assert\Email(
     *  message = "This email is invalid"
     * )
     * @Groups("real-estate-agent")
     */
    private $email;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->accommodations = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getPerson()->getLastname() . ' ' .$this->getPerson()->getFirstname();
    }

    public function getName()
    {
        return ucfirst($this->getPerson()->getFirstname()) . ' ' . strtoupper($this->getPerson()->getLastname());
    }

    public function setPriceRange(?string $priceRange): void
    {
        $this->priceRange = $priceRange;
    }

    public function getPriceRange(): ?string
    {
        return $this->priceRange;
    }

    public function setPerson(?Person $person): void
    {
        $this->person = $person;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPrimaryImage(?MediaObject $primaryImage): void
    {
        $this->primaryImage = $primaryImage;
    }

    public function getPrimaryImage(): ?MediaObject
    {
        return $this->primaryImage;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

        /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
