<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\CreativeWorkTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\AdministrableTrait;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A media object, such as an image, video, or audio object embedded in a web page or a downloadable dataset i.e. DataDownload. Note that a creative work may have many media objects associated with it on the same web page. For example, a page about a single song (MusicRecording) may have a music video (VideoObject), and a high and low bandwidth audio stream (2 AudioObject's).
 *
 * @see http://schema.org/MediaObject Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/MediaObject",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 */
class DocumentObject
{
    use IdentifiableTrait
        , ThingTrait
        // , CreativeWorkTrait
        , TimestampableTrait
        , AdministrableTrait
    ;

    /**
     * @var integer|null file size in (mega/kilo) bytes
     *
     * @ORM\Column(type="integer", nullable=true)
     * @ApiProperty(iri="http://schema.org/contentSize")
     */
    private $contentSize;

    /**
     * @var string|null mp3, mpeg4, etc
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/encodingFormat")
     */
    private $encodingFormat;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/encodingFormat")
     */
    private $caption;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ Assert\NotNull
     *
     * @Groups("accommodation")
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ Assert\NotNull
     *
     * @Groups("accommodation")
     */
    private $originalFilename;

    /**
     * @var string
     *
     * @ORM\Column(type="simple_array", nullable=true)
     * @ Assert\NotNull
     */
    private $dimensions;

    /**
     * @var File
     *
     * @Assert\File(
     *     maxSize = "100000k",
     *     mimeTypes = {
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg",
     *          "image/gif",
     *          "application/pdf",
     *          "application/x-pdf"
     *      },
     *     mimeTypesMessage = "Formats autorisés : png, jpeg, jpg, gif, pdf"
     * )
     * @Vich\UploadableField(
     *  mapping="uploads_document_files"
     *  , fileNameProperty="filename"
     *  , size="contentSize"
     *  , mimeType="encodingFormat"
     *  , originalName="originalFilename"
     *  , dimensions="dimensions"
     * )
     * @ Assert\NotNull
     */
    private $file;

    /**
    * @var string
    *
     * @ORM\Column(type="string", length=255, nullable=true)
    */
    private $tmpFile;

    /**
     * @ORM\ManyToMany(targetEntity="Accommodation", mappedBy="pdfs")
     */
	private $pdfsAccommodations;

   /**
     * @ORM\ManyToMany(targetEntity="Person", mappedBy="pdfs")
     */
    private $pdfsPersons;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DocumentObjectType")
     *
     * @Groups("accommodation")
     */
    private $type;

    /**
     * Constructor
     */
    public function __construct()
    {
      $this->pdfsAccommodations = new ArrayCollection();
      $this->pdfsPersons = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getFilename();
    }

    public function setContentSize($contentSize): void
    {
        $this->contentSize = $contentSize;
    }

    public function getContentSize()
    {
        return $this->contentSize;
    }

    public function setEncodingFormat(?string $encodingFormat): void
    {
        $this->encodingFormat = $encodingFormat;
    }

    public function getEncodingFormat(): ?string
    {
        return $this->encodingFormat;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): void
    {
        $this->filename = $filename;
    }

    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($file) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * Set the value of Original Name
     *
     * @param string originalFilename
     *
     * @return self
     */
    public function setOriginalFilename(?string $originalFilename): void
    {
        $this->originalFilename = $originalFilename;
    }

    /**
     * Get the value of Original Name
     *
     * @return string
     */
    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    /**
     * Set the value of Dimensions
     *
     * @param string dimensions
     *
     * @return self
     */
    public function setDimensions(?array $dimensions): void
    {
        $this->dimensions = $dimensions;
    }

    /**
     * Get the value of Dimensions
     *
     * @return string
     */
    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }

    /*
    * Set tmpFile
    * @return Image
    */
    public function setTmpFile($tmpFile)
    {
        $this->tmpFile = $tmpFile;
        return $this;
    }

    /*
    * Get tmpFile
    * @return string
    */
    public function getTmpFile()
    {
        return $this->tmpFile;
    }

    /**
     * Set the value of Caption
     *
     * @param string|null caption
     *
     * @return self
     */
    public function setCaption(?string $caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get the value of Caption
     *
     * @return string|null
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Add Accommodation
     *
     * @param App\Entity\Accommodation $pdfsAccommodations
     *
     * @return Accommodation
     */
    public function addPdfsAccommodation(Accommodation $accommodation): void
    {
        if ($this->pdfsAccommodations->contains($accommodation)) {
            return;
        }

        $this->pdfsAccommodations->add($accommodation);

        // Bidirectional Ownership
        $accommodation->addPdf($this);
    }

    /**
     * Remove Accommodation
     *
     * @param App\Entity\Accommodation $gallery
     */
    public function removePdfsAccommodation(Accommodation $accommodation): void
    {
        // If the category does not exist in the collection, then we don't need to do anything
        if (!$this->pdfsAccommodations->contains($accommodation)) {
            return;
        }

        // Remove category from the collection
        $this->pdfsAccommodations->removeElement($accommodation);
        // Also remove this from the blog post collection of the category
        $accommodation->removePdf($this);
    }

    /**
    * Get Accommodations
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getPdfsAccommodations()
    {
       return $this->pdfsAccommodations;
    }

    public function getType(): ?DocumentObjectType
    {
        return $this->type;
    }

    public function setType(?DocumentObjectType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Add Person
     *
     * @param App\Entity\Person $person
     *
     * @return Person
     */
    public function addPdfsPerson(Person $person): void
    {
        if ($this->pdfsPersons->contains($person)) {
            return;
        }

        $this->pdfsPersons->add($person);

        // Bidirectional Ownership
        $person->addPdf($this);
    }

    /**
     * Remove Person
     *
     * @param App\Entity\Person $person
     */
    public function removePdfsPerson(Person $person): void
    {
        // If the category does not exist in the collection, then we don't need to do anything
        if (!$this->pdfsPersons->contains($person)) {
            return;
        }

        // Remove category from the collection
        $this->pdfsPersons->removeElement($person);
        // Also remove this from the blog post collection of the category
        $person->removePdf($this);
    }

    /**
    * Get pdfsPersons
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getPdfsPersons()
    {
       return $this->pdfsPersons;
    }

}
