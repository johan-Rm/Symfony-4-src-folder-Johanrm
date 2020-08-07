<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\AdministrableTrait;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiSubresource;


/**
 * An event happening at a certain time and location, such as a concert, lecture, or festival. Ticketing information may be added via the \[\[offers\]\] property. Repeated events may be structured as separate Event objects.
 *
 * @see http://schema.org/Event Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Event",
 *  attributes={
 *      "normalization_context" = {
 *          "groups"= {
 *               "thing"
 *               , "identifier"
 *               , "name"
 *               , "person"
 *               , "event"
 *           },"datetime_format"="Y-m-d"
 *      }
 *  },
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ApiFilter(OrderFilter::class, properties={"id", "beginAt", "endAt"},
 *  arguments={
 *      "orderParameterName"="order"
 *  }
 * )
 * @ApiFilter(DateFilter::class, properties={"beginAt", "endAt"})
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 */
class Event
{
    use IdentifiableTrait
        , ThingTrait
        , TimestampableTrait
        , AdministrableTrait
    ;

    /**
    * @var string
    *
    * @ORM\Column(name="comment", type="text", nullable=true)
    * @Groups("event")
    */
   private $comment;

   /**
    * @var \DateTime
    *
    * @ORM\Column(name="begin_at", type="datetime")
    * @Groups("event")
    *
    * @Assert\NotBlank(message="Enter a rental start date")
    */
   private $beginAt;

   /**
    * @var \DateTime
    *
    * @ORM\Column(name="end_at", type="datetime")
    * @Groups("event")
    *
    * @Assert\NotBlank(message="Enter a rental end date")
    */
   private $endAt;

   /**
    * @ORM\ManyToOne(targetEntity="Person", inversedBy="events")
    * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=true)
    * @Groups("event")
    *
    * @Assert\NotBlank(message="Select a tenant")
    */
   private $person;

   /**
    * @ORM\ManyToOne(targetEntity="Accommodation", inversedBy="rentals")
    * @ORM\JoinColumn(name="accommodation_id", referencedColumnName="id", nullable=true)
    *
    * @Groups("event")
    *
    * @Assert\NotBlank(message="Select an accommodation")
    */
   private $accommodation;

   /**
   * One Event has One Location.
   * @ORM\ManyToOne(targetEntity="RentalType")
   * @ORM\JoinColumn(name="rental_type_id", referencedColumnName="id")
   * @Groups("event")
   *
   * @Assert\NotBlank(message="Select a rental type")
   */
   private $rentalType;

   /**
    * @ORM\Column(type="string", length=255, nullable=true)
    * @Groups("event")
    */
   private $depositAmount;

   /**
    * @ORM\Column(type="boolean", nullable=true)
    * @Groups("event")
    */
   private $depositStatus;

   /**
    * @ORM\ManyToOne(targetEntity="DocumentObject")
    * @Groups("event")
    */
   private $rentalAgreement;

   /**
     * @Vich\UploadableField(mapping="uploads_document_files", fileNameProperty="document")
     * 
     * @Assert\File(
     *     maxSize = "8M",
     *     mimeTypes = {"application/pdf", "application/x-pdf"},
     *     mimeTypesMessage = "Please upload a valid PDF"
     * )
     * @Groups("event")
     */
    private $documentFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     * @Groups("event")
     */
    private $document;

   /**
    * @ORM\ManyToOne(targetEntity="App\Entity\AccommodationNature", inversedBy="events")
    * @Groups("event")
    * @ApiSubresource
    */
   private $accommodationNature;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\InvoiceTracking", mappedBy="event", cascade= { "remove" })
     * @Groups("event")
     * @ApiSubresource
     */
    private $trackings;

   /**
    * Constructor
    */
   public function __construct()
   {

   }


   public function __toString()
   {
       // pour traduire
       // https://stackoverflow.com/questions/45926194/symfony-how-to-use-translation-component-in-entity-tostring

       $label = null;
       $label.= ' loué du ' . $this->getBeginAt()->format('Y/m/d');
       $label.= ' au ' . $this->getEndAt()->format('Y/m/d');
       $label.= ' à ' . $this->getPerson()->getLastname();
       if(null != $this->getRentalType()) {
           // $label.= '<span class="badge badge-primary">' . $this->getRentalType() . '</span>';
           $label.= ' - ' . $this->getRentalType();
       }


       return $label;
   }

   /**
    * Set comment
    *
    * @param string $comment
    *
    * @return event
    */
   public function setComment($comment)
   {
       $this->comment = $comment;

       return $this;
   }

   /**
    * Get comment
    *
    * @return string
    */
   public function getComment()
   {
       return $this->comment;
   }

   public function getBeginAt(): ?\DateTimeInterface
   {
       return $this->beginAt;
   }

   public function setBeginAt(?\DateTimeInterface $beginAt = null): void
   {
       $this->beginAt = $beginAt;
   }

   public function getEndAt(): ?\DateTimeInterface
   {
       return $this->endAt;
   }

   public function setEndAt(?\DateTimeInterface $endAt = null): void
   {
       $this->endAt = $endAt;
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
    * @return mixed
    */
   public function getAccommodation()
   {
       return $this->accommodation;
   }

   /**
    * @param mixed $accommodation
    *
    * @return \App\Entity\Accommodation
    */
   public function setAccommodation(\App\Entity\Accommodation $accommodation)
   {
       $this->accommodation = $accommodation;
   }

    /**
     * Set the value of One Event has One Location.
     *
     * @param mixed rentalType
     *
     * @return self
     */
    public function setRentalType($rentalType)
    {
        $this->rentalType = $rentalType;

        return $this;
    }

    /**
     * Get the value of One Event has One Location.
     *
     * @return mixed
     */
    public function getRentalType()
    {
        return $this->rentalType;
    }

    public function getDepositAmount(): ?string
    {
        return $this->depositAmount;
    }

    public function setDepositAmount(?string $depositAmount): self
    {
        $this->depositAmount = $depositAmount;

        return $this;
    }

    public function getDepositStatus(): ?bool
    {
        return $this->depositStatus;
    }

    public function setDepositStatus(?bool $depositStatus): self
    {
        $this->depositStatus = $depositStatus;

        return $this;
    }

    public function getRentalAgreement(): ?DocumentObject
    {
        return $this->rentalAgreement;
    }

    public function setRentalAgreement(?DocumentObject $rentalAgreement): void
    {
        $this->rentalAgreement = $rentalAgreement;
    }

    public function getAccommodationNature(): ?AccommodationNature
    {
        return $this->accommodationNature;
    }

    public function setAccommodationNature(?AccommodationNature $accommodationNature): self
    {
        $this->accommodationNature = $accommodationNature;

        return $this;
    }


    /**
     * Set the value of Document
     *
     * @param File document
     *
     * @return self
     */
    public function setDocument(File $document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get the value of Document
     *
     * @return File
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return File
     */
    public function getDocumentFile()
    {
        return $this->documentFile;
    }

    /**
     * @param File $documentFile
     */
    public function setDocumentFile($documentFile)
    {
        $this->documentFile = $documentFile;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($documentFile) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    /**
     * @return mixed
     */
    public function getTrackings()
    {
        return $this->trackings;
    }

    /**
     * @param \App\Entity\InvoiceTracking $tracking
     */
    public function addTracking($tracking)
    {
      if ($this->trackings->contains($tracking)) {
          return;
      }

      $tracking->addEvent($this);
      $this->trackings->add($tracking);
    }

    /**
    * @param \App\Entity\InvoiceTracking $tracking
    */
    public function removeTracking($tracking)
    {
     if (!$this->trackings->contains($tracking)) {
         return;
     }

     $this->trackings->removeElement($tracking);
     $tracking->removeEvent($this);
    }

}
