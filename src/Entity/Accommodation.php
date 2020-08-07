<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\AccommodationTrait;
use App\Entity\Traits\AdministrableTrait;
use App\Entity\Traits\CreativeWorkTrait;
use App\Entity\Traits\TimestampableTrait;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;


/**
 * An accommodation is a place that can accommodate human beings, e.g. a hotel room, a camping pitch, or a meeting room. Many accommodations are for overnight stays, but this is not a mandatory requirement. For more specific types of accommodations not defined in schema.org, one can use additionalType with external vocabularies.
 *
 * See also the [dedicated document on the use of schema.org for marking up hotels and other forms of accommodations](/docs/hotels.html).
 *
 * @see http://schema.org/Accommodation Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Accommodation",
 *  attributes={
 *      "normalization_context" = {
 *          "groups"= {
 *               "thing"
 *               , "identifier"
 *               , "creative"
 *               , "name"
 *               , "gallery"
 *               , "accommodation"
 *               , "tag"
 *               , "media"
 *               , "place"
 *               , "real-estate-agent"
 *         }
 *         ,"datetime_format"="Y-m-d"
 *      }
 *  },
 *  collectionOperations={
 *      "get"={
 *          "method"="GET",       
 *          "normalization_context"={
 *              "groups"={
 *                  "collection:accommodation",
 *                  "media",
 *                  "place",
 *                  "nature",
 *                  "name",
 *                  "thing"
 *              }
 *          }
 *      },
 *     "list_full_get"={
 *          "method"="GET",
 *          "path"="/full_accommodations",
 *          "normalization_context"={
 *              "groups"={
 *               "thing"
 *               , "identifier"
 *               , "creative"
 *               , "name"
 *               , "gallery"
 *               , "accommodation"
 *               , "tag"
 *               , "media"
 *               , "place"
 *               , "real-estate-agent"
 *              }
 *          }
 *      }
 *  },
 *  itemOperations={"get"={"method"="GET"}}
 * )
 * @ApiFilter(SearchFilter::class,
 *   properties={
 *           "slug": "exact",
 *           "reference": "exact",
 *           "tags.slug": "exact",
 *           "amenities.slug": "exact",
 *           "nature.slug": "exact",
 *           "type.slug": "exact",
 *           "label.slug": "exact",
 *           "place.slug": "exact",
 *           "duration.slug": "exact"
 *       }
 *   )
 * @ApiFilter(BooleanFilter::class, properties={"isActive"})
 * @ApiFilter(OrderFilter::class, properties={"id", "datePublished"},
 *  arguments={
 *      "orderParameterName"="order"
 *  }
 * )
 * @ApiFilter(RangeFilter::class, properties={"price", "floorSize"})
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="App\Repository\AccommodationRepository")
 */
class Accommodation
{
    use IdentifiableTrait
        , ThingTrait
        , CreativeWorkTrait
        , AccommodationTrait
        , AdministrableTrait
        , TimestampableTrait
    ;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @ApiProperty(identifier=false)
     *
     * @Groups("identifier")
     */
    private $id;

    /**
     * @var string|null headline of the article
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/headline")
     * @Groups({"accommodation", "collection:accommodation"})
     */
    private $headline;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="accommodation")
     *
     * @Groups("accommodation")
     * @ApiSubresource
     */
    private $rentals;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RentalPriceType", inversedBy="accommodations")
     *
     * @Groups({"accommodation", "collection:accommodation"})
     * @ApiSubresource
     */
    private $rentalPriceType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\RealEstateAgent", inversedBy="accommodations")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups("accommodation")
     * @ApiSubresource()
     *
     */
    private $realEstateAgent;

    /**
    * @ORM\Column(type="string", length=160, nullable=true)
    *
    * @Groups("accommodation")
    */
    private $geo;

    /**
    * @Gedmo\Slug(fields={"metaTitle"}, prefix="", updatable=true)
    * @ORM\Column(type="string", length=160, unique=true)
    *
    * @ApiProperty(identifier=true)
    *
    * @Groups({"accommodation", "collection:accommodation"})
    */
    private $slug;

   /**
    * @ORM\ManyToOne(targetEntity="Person", inversedBy="accommodations")
    * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=true)
    *
    * @Assert\NotBlank(message="Select the owner of the property")
    */
    private $person;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", options={"default": true})
     * @Groups("accommodation")
     */
    private $isActive = true;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("accommodation")
     */
    private $informations;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    *
    * @Groups("accommodation")
    */
    private $urbanTaxes;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    *
    * @Groups("accommodation")
    */
    private $oldReference;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    *
    * @Groups("accommodation")
    */
    private $unionCharges;

    /**
    * @ORM\Column(type="string", length=255, nullable=true)
    *
    * @Groups("accommodation")
    */
    private $turnover;

    /**
     * @ORM\ManyToMany(targetEntity="Person", mappedBy="teams")
     */
    private $persons;

    /**
     * @ORM\OneToMany(
     *  targetEntity="AccommodationDetail"
     *  , mappedBy="accommodation"
     *  , cascade= {"persist", "remove"}
     * )
     *
     * @Groups("accommodation")
     */
    private $details;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("accommodation")
     */
    private $ourOpinion;

    /**
     * @ORM\Column(type="boolean")
     */
    private $regenerateReference = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $regeneratePdf = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("accommodation")
     */
    private $hideStamp = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $regenerateFormat = false;



    public function __construct()
    {
        $this->amenities = new ArrayCollection();
        $this->details = new ArrayCollection();
        $this->pdfs = new ArrayCollection();
        // $this->images = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->rentals = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->persons = new \Doctrine\Common\Collections\ArrayCollection();
        $this->items = new ArrayCollection();
    }

    public function __toString()
    {        
        $string = $this->getReference();
        if(!empty($this->getName())) {
            $string.= ' - ' . $this->getName();
        }

        if(empty($string)) {
            $string = 'null';
        }

        return $string;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add Rental
     *
     * @param \App\Entity\Event $rentals
     *
     */
    public function addRental($rental)
    {
        $this->rentals[] = $rental;
    }

    /**
     * Remove $rental
     *
     * @param \App\Entity\Event $rental
     */
    public function removeRental($rental)
    {
        $this->rentals->removeElement($rental);
    }

    /**
     * Get appointments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRentals()
    {
        return $this->rentals;
    }

    public function getRealEstateAgent(): ?RealEstateAgent
    {
        return $this->realEstateAgent;
    }

    public function setRealEstateAgent(?RealEstateAgent $realEstateAgent): self
    {
        $this->realEstateAgent = $realEstateAgent;

        return $this;
    }

    public function getRentalPriceType(): ?RentalPriceType
    {
        return $this->rentalPriceType;
    }

    public function setRentalPriceType(?RentalPriceType $rentalPriceType): self
    {
        $this->rentalPriceType = $rentalPriceType;

        return $this;
    }

    public function getGeo()
    {
        return $this->geo;
    }

    /**
     * Set the value of geo
     *
     * @param mixed geo
     *
     * @return self
     */
    public function setGeo($geo)
    {
        $this->geo = $geo;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the value of Slug
     *
     * @param mixed slug
     *
     * @return self
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

   /**
    * @return mixed
    */
   public function getPerson()
   {
       return $this->person;
   }

   /**
    * @param mixed $person
    *
    * @return \App\Entity\Person
    */
   public function setPerson(\App\Entity\Person $person)
   {
       $this->person = $person;
   }

    /**
     * Set name
     *
     * @param string $isActive
     *
     * @return Beneficiary
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
    * @param Person $person
    */
   public function addPerson($person)
   {
       if ($this->persons->contains($person)) {
           return;
       }
       $this->persons->add($person);
       $person->addTeam($this);
   }
   /**
    * @param Person $person
    */
   public function removePerson($person)
   {
       if (!$this->persons->contains($person)) {
           return;
       }
       $this->persons->removeElement($person);
       $person->removeTeam($this);
   }


    /**
     * Set the value of Rentals
     *
     * @param mixed rentals
     *
     * @return self
     */
    public function setRentals($rentals)
    {
        $this->rentals = $rentals;

        return $this;
    }

    /**
     * Set the value of Informations
     *
     * @param mixed informations
     *
     * @return self
     */
    public function setInformations($informations)
    {
        $this->informations = $informations;

        return $this;
    }

    /**
     * Get the value of Informations
     *
     * @return mixed
     */
    public function getInformations()
    {
        return $this->informations;
    }

    /**
     * Set the value of Urban Taxes
     *
     * @param mixed urbanTaxes
     *
     * @return self
     */
    public function setUrbanTaxes($urbanTaxes)
    {
        $this->urbanTaxes = $urbanTaxes;

        return $this;
    }

    /**
     * Get the value of Urban Taxes
     *
     * @return mixed
     */
    public function getUrbanTaxes()
    {
        return $this->urbanTaxes;
    }

    /**
     * Set the value of oldReference
     *
     * @param mixed oldReference
     *
     * @return self
     */
    public function setOldReference($oldReference)
    {
        $this->oldReference = $oldReference;

        return $this;
    }

    /**
     * Get the value of oldReference
     *
     * @return mixed
     */
    public function getOldReference()
    {
        return $this->oldReference;
    }


    /**
     * Set the value of Union Charges
     *
     * @param mixed unionCharges
     *
     * @return self
     */
    public function setUnionCharges($unionCharges)
    {
        $this->unionCharges = $unionCharges;

        return $this;
    }

    /**
     * Get the value of Union Charges
     *
     * @return mixed
     */
    public function getUnionCharges()
    {
        return $this->unionCharges;
    }

    /**
     * Set the value of Turnover
     *
     * @param mixed turnover
     *
     * @return self
     */
    public function setTurnover($turnover)
    {
        $this->turnover = $turnover;

        return $this;
    }

    /**
     * Get the value of Turnover
     *
     * @return mixed
     */
    public function getTurnover()
    {
        return $this->turnover;
    }

    /**
     * Set the value of Persons
     *
     * @param mixed persons
     *
     * @return self
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;

        return $this;
    }

    /**
     * Get the value of Persons
     *
     * @return mixed
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param \App\Entity\AccommodationDetail $detail
     */
    public function addDetail($detail)
    {
       if (!$this->details->contains($detail)) {
            $this->details[] = $detail;
            $detail->setAccommodation($this);
        }

        return $this;
    }

    /**
    * @param \App\Entity\AccommodationDetail $detail
    */
    public function removeDetail($detail)
    {
        if ($this->details->contains($detail)) {
            $this->details->removeElement($detail);
            // set the owning side to null (unless already changed)
            if ($detail->getAccommodation() === $this) {
                $detail->setAccommodation(null);
            }
        }
    }

    public function getOurOpinion(): ?string
    {
        return $this->ourOpinion;
    }

    public function setOurOpinion(?string $ourOpinion): void
    {
        $this->ourOpinion = $ourOpinion;
    }

    public function getRegenerateReference(): ?bool
    {
        return $this->regenerateReference;
    }

    public function setRegenerateReference(bool $regenerateReference): self
    {
        $this->regenerateReference = $regenerateReference;

        return $this;
    }

    public function getRegeneratePdf(): ?bool
    {
        return $this->regeneratePdf;
    }

    public function setRegeneratePdf(bool $regeneratePdf): self
    {
        $this->regeneratePdf = $regeneratePdf;

        return $this;
    }

    public function getHideStamp(): ?bool
    {
        return $this->hideStamp;
    }

    public function setHideStamp(bool $hideStamp): self
    {
        $this->hideStamp = $hideStamp;

        return $this;
    }

    public function getRegenerateFormat(): ?bool
    {
        return $this->regenerateFormat;
    }

    public function setRegenerateFormat(bool $regenerateFormat): self
    {
        $this->regenerateFormat = $regenerateFormat;

        return $this;
    }

}
