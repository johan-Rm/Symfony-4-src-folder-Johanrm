<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;


trait AccommodationTrait
{
    /**
     * @var string|null PDF URL of the item
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/url")
     * @Assert\Url
     *
     * @Groups("accommodation")
    */
    private $pdfUrl;

    /**
     * @var string|null API URL of the item
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(
     *     iri="http://schema.org/url")
     *     attributes={
     *         "jsonld_context"={
     *             "@id"="http://yourcustomid.com",
     *             "@type"="http://www.w3.org/2001/XMLSchema#string",
     *             "someProperty"={
     *                 "a"="textA",
     *                 "b"="textB"
     *             }
     *         }
     *     }
     * @Assert\Url
     *
     * @Groups("accommodation")
     */
    private $websiteUrl;


    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=15, nullable=true)
     *
     * @Groups({"accommodation", "collection:accommodation"})
     */
    private $reference;

    /**
     * @ var float[]|null The number of rooms (excluding bathrooms and closets) of the acccommodation or lodging business. Typical unit code(s): ROM for room or C62 for no unit. The type of room can be put in the unitText property of the QuantitativeValue.
     *
     * @ ORM\Column(type="simple_array", nullable=true)
     * @ApiProperty(iri="http://schema.org/numberOfRooms")
     *
     * @var integer
     *
     * @ORM\Column(name="numberOfPieces", type="smallint", nullable=true)
     * @ Assert\NotNull
     * @Groups("accommodation")
    */
    private $numberOfPieces;

    /**
     * @ var float[]|null The number of rooms (excluding bathrooms and closets) of the acccommodation or lodging business. Typical unit code(s): ROM for room or C62 for no unit. The type of room can be put in the unitText property of the QuantitativeValue.
     *
     * @ ORM\Column(type="simple_array", nullable=true)
     * @ApiProperty(iri="http://schema.org/numberOfRooms")
     *
     * @var integer
     *
     * @ORM\Column(name="numberOfRooms", type="smallint")
     * @Groups("accommodation")
     *
     * @Assert\NotBlank(message="Enter the number of rooms")
    */
    private $numberOfRooms;

    /**
     * @ORM\Column(name="numberOfBathrooms", type="smallint", nullable=true)
     * @ Assert\NotNull
     * @Groups("accommodation")
    */
    private $numberOfBathrooms;

    /**
     * @ORM\Column(name="maximumOccupants", type="smallint", nullable=true)
     * @Groups("accommodation")
     *
     * @ Assert\NotBlank(message="Enter the number of occupants")
    */
    private $maximumOccupants;


    /**
     * @var MediaObject|null indicates the main image on the page
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject", inversedBy="imagesAccommodations")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @ApiProperty(iri="http://schema.org/primaryImage")
     *
     * @Groups({"accommodation", "collection:accommodation"})
     * @ApiSubresource
     *
     * @Assert\NotBlank(message="Select the main image of the property")
     */
    private $primaryImage;

    /**
     * @var MediaObject|null indicates the main image on the page
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     * @ApiProperty(iri="http://schema.org/primaryImage")
     *
     * @Groups("accommodation")
     * @ApiSubresource
     */
    private $secondaryImage;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", length=6)
     * @Groups({"accommodation", "collection:accommodation"})
     *
     * @Assert\NotBlank(message="Enter the price of the property")
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="labelPrice", type="string", length=255, nullable=true)
     *
     * @Groups("accommodation")
     */
    private $labelPrice;

    /**
     *
     * @ORM\Column(name="floorSize", type="smallint")
     * @Groups({"accommodation", "collection:accommodation"})
     *
     * @Assert\NotBlank(message="Enter the living space")
     */
    private $floorSize;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * = Land area = Surface de terrain
     *
     * @Groups("accommodation")
     */
    private $areaSize;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * = Land area = Surface de terrain
     *
     * @Groups("accommodation")
     */
    private $areaTerrace;

    /**
    * @ORM\ManyToOne(targetEntity="\App\Entity\AccommodationPlace")
    * @ORM\JoinColumn(name="place_id", referencedColumnName="id")
    * choice(campagne, golf-mogador, médina, nouvelle-ville)
    *
    * @Groups({"accommodation", "collection:accommodation"})
    * @ ApiSubresource
    *
    * @Assert\NotBlank(message="Enter the place of the property")
    */
    private $place;

     /**
      * @ ORM\ManyToMany(targetEntity="\App\Entity\MediaObject", inversedBy="imagesAccommodations", cascade={"remove"})
      * @ ORM\JoinTable(name="accommodations_images")
      */
    //private $images; 

    /**
     * Many Videos
     * @ORM\ManyToMany(targetEntity="\App\Entity\MediaObject", inversedBy="videosAccommodations", cascade={"persist"})
     * @ORM\JoinTable(name="accommodations_videos")
     *
     * @Groups("accommodation")
     * @ApiSubresource
     */
    private $videos;

    /**
     * @ORM\ManyToMany(targetEntity="\App\Entity\AccommodationAmenity", inversedBy="accommodations")
     * @ORM\JoinTable(name="accommodations_amenities")
     *
     * @Groups("accommodation")
     * @ApiSubresource
     */
    private $amenities;

    /**
     * @ORM\ManyToMany(targetEntity="\App\Entity\Tag", inversedBy="accommodations")
     * @ORM\JoinTable(name="accommodations_tags")
     *
     * @Groups("accommodation")
     * @ApiSubresource
     */
    private $tags;


    /**
    * @ORM\ManyToOne(targetEntity="\App\Entity\AccommodationLabel")
    * @ORM\JoinColumn(name="label_id", referencedColumnName="id")
    *
    * @Groups({"accommodation", "collection:accommodation"})
    * @ApiSubresource
    */
    private $label;

    /**
    * @ORM\ManyToOne(targetEntity="\App\Entity\AccommodationNature")
    * @ORM\JoinColumn(name="nature_id", referencedColumnName="id")
    * choice(location, achat ... etc)
    *
    * @Groups({ "accommodation", "collection:accommodation" })
    * @ApiSubresource
    *
    * @Assert\NotBlank(message="Select the nature of the property")
    */
    private $nature;

     /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\AccommodationType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     * choice(maison, appart ... etc)
     *
     * @Groups({"accommodation", "collection:accommodation"})
     * @ApiSubresource
     *
     * @Assert\NotBlank(message="Select the type of property")
     */
    private $type;

     /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\AccommodationLocation")
     * @ORM\JoinColumn(name="duration_id", referencedColumnName="id")
     *
     * @Groups("accommodation")
     *
     * @Assert\Expression(
     *     "(this.getDuration() && this.getNature() == 'Location') || (this.getNature() == 'Vente')",
     *     message="Please, enter a duration"
     * )
     *
     * @ApiSubresource
     */
    private $duration;

    /**
     * Many Pdfs
     * @ORM\ManyToMany(targetEntity="\App\Entity\DocumentObject", inversedBy="pdfsAccommodations")
     * @ORM\JoinTable(name="accommodations_pdfs")
     *
     * @Groups("accommodation")
     * @ApiSubresource
     */
    private $pdfs;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\Gallery")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups("accommodation")
     * @ApiSubresource
     */
    private $gallery;


    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * Set the value of Price
     *
     * @param float price
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get the value of Price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set the value of LabelPrice
     *
     * @param float labelPrice
     *
     * @return self
     */
    public function setLabelPrice($labelPrice)
    {
        $this->labelPrice = $labelPrice;

        return $this;
    }

    /**
     * Get the value of LabelPrice
     *
     * @return string
     */
    public function getLabelPrice()
    {
        return $this->labelPrice;
    }

    /**
     * Set the value of Floor Size
     *
     * @param integer floorSize
     *
     * @return self
     */
    public function setFloorSize($floorSize)
    {
        $this->floorSize = $floorSize;

        return $this;
    }

    /**
     * Get the value of Floor Size
     *
     * @return integer
     */
    public function getFloorSize()
    {
        return $this->floorSize;
    }

    public function getPlace(): ?\App\Entity\AccommodationPlace
    {
        return $this->place;
    }

    public function setPlace(?\App\Entity\AccommodationPlace $place): self
    {
        $this->place = $place;

        return $this;
    }
    


    /**
     * Set the value of duration
     *
     * @param duration
     *
     * @return self
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get the value of duration
     *
     * @return array
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Get the value of Amenities
     *
     * @return Collection|Amenity[]
     */
    public function getAmenities(): Collection
    {
        return $this->amenities;
    }

    /**
     * Set the value of Amenities
     *
     * @param mixed amenities
     *
     * @return self
     */
    public function setAmenities($amenities)
    {
        $this->amenities = $amenities;

        return $this;
    }



    /**
     * Add amenity
     *
     * @param \App\Entity\AccommodationAmenity $amenity
     *
     * @return Amenity
     */
    public function addAmenity(\App\Entity\AccommodationAmenity $amenity): self
    {
         // Bidirectional Ownership
        $amenity->addAccommodation($this);

        $this->amenities[] = $amenity;

        return $this;
    }

    /**
     * Remove amenity
     *
     * @param \App\Entity\AccommodationAmenity $amenity
     */
    public function removeAmenity(\App\Entity\AccommodationAmenity $amenity)
    {
        $this->amenities->removeElement($amenity);
    }

    /**
     * Set the value of Tags
     *
     * @param mixed tags
     *
     * @return self
     */
    public function setTags($amenities)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(\App\Entity\Tag $tag)
    {
        if ($this->tags->contains($tag)) {
            return;
        }
        $this->tags->add($tag);
        $tag->addAccommodation($this);

        return $this;
    }

    public function removeTag(\App\Entity\Tag $tag): self
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Set the value of Nature
     *
     * @param array nature
     *
     * @return self
     */
    public function setNature($nature)
    {
        $this->nature = $nature;

        return $this;
    }

    /**
     * Get the value of Nature
     *
     * @return array
     */
    public function getNature()
    {
        return $this->nature;
    }

    /**
     * Set the value of Type
     *
     * @param array type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of Type
     *
     * @return array
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get videos
     *
     * @return Collection|Accommodation[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    /**
     * Set the value of Videos
     *
     * @param mixed videos
     *
     * @return self
     */
    public function setVideos($videos)
    {
        $this->videos = $videos;

        return $this;
    }

    /**
     * Add video
     *
     * @param \App\Entity\MediaObject $video
     *
     * @return Accommodation
     */
    public function addVideo(\App\Entity\MediaObject $video)
    {
        if ($this->videos->contains($video)) {
            return;
        }
        $this->videos->add($video);
        $video->addVideosAccommodation($this);

        return $this;
    }

    /**
     * Remove video
     *
     * @param \AppBundle\Entity\MediaObject $video
     *
     * @return Accommodation
     */
    public function removeVideo(\App\Entity\MediaObject $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
        }

        return $this;
    }

    /**
     * Get images
     *
     * @return Collection|Accommodation[]
     */
    // public function getImages(): Collection
    // {
    //     return $this->images;
    // }

    /**
     * Set the value of Images
     *
     * @param mixed pdfs
     *
     * @return self
     */
    // public function setImages($images)
    // {
    //     $this->images = $images;

    //     return $this;
    // }

    /**
     * Add image
     *
     * @param \App\Entity\MediaObject $pdf
     *
     * @return Accommodation
     */
    // public function addImage(\App\Entity\MediaObject $image)
    // {
    //     if ($this->images->contains($image)) {
    //         return;
    //     }
    //     $this->images->add($image);
    //     $image->addImagesAccommodation($this);

    //     return $this;
    // }

    /**
     * Remove image
     *
     * @param \AppBundle\Entity\MediaObject $image
     *
     * @return Accommodation
     */
    // public function removeImage(\App\Entity\MediaObject $image): self
    // {
    //     if ($this->images->contains($image)) {
    //         $this->images->removeElement($image);
    //     }

    //     return $this;
    // }

    /**
     * Get pdfs
     *
     * @return Collection|Accommodation[]
     */
    public function getPdfs(): Collection
    {
        return $this->pdfs;
    }

    /**
     * Set the value of Pdfs
     *
     * @param mixed pdfs
     *
     * @return self
     */
    public function setPdfs($pdfs)
    {
        $this->pdfs = $pdfs;

        return $this;
    }

    /**
     * Add pdf
     *
     * @param \App\Entity\DocumentObject $pdf
     *
     * @return Accommodation
     */
    public function addPdf(\App\Entity\DocumentObject $pdf)
    {
        if ($this->pdfs->contains($pdf)) {
            return;
        }
        $this->pdfs->add($pdf);
        $pdf->addPdfsAccommodation($this);

        return $this;
    }

    /**
     * Remove pdf
     *
     * @param \AppBundle\Entity\DocumentObject $pdf
     *
     * @return Accommodation
     */
    public function removePdf(\App\Entity\DocumentObject $pdf): self
    {
        if ($this->pdfs->contains($pdf)) {
            $this->pdfs->removeElement($pdf);
        }

        return $this;
    }

    /**
     * Get the value of MaximumOccupants
     *
     * @return integer
     */
    public function getMaximumOccupants()
    {
        return $this->maximumOccupants;
    }

    /**
     * Set the value of MaximumOccupants
     *
     * @param integer MaximumOccupants
     *
     * @return self
     */
    public function setMaximumOccupants($maximumOccupants)
    {
        $this->maximumOccupants = $maximumOccupants;

        return $this;
    }

    /**
     * Get the value of Number Of Bathrooms
     *
     * @return integer
     */
    public function getNumberOfBathrooms()
    {
        return $this->numberOfBathrooms;
    }

    /**
     * Set the value of Number Of Bathrooms
     *
     * @param integer numberOfBathrooms
     *
     * @return self
     */
    public function setNumberOfBathrooms($numberOfBathrooms)
    {
        $this->numberOfBathrooms = $numberOfBathrooms;

        return $this;
    }


    /**
     * Get the value of Number Of Rooms
     *
     * @return integer
     */
    public function getNumberOfRooms()
    {
        return $this->numberOfRooms;
    }

    /**
     * Set the value of Number Of Rooms
     *
     * @param integer numberOfRooms
     *
     * @return self
     */
    public function setNumberOfRooms($numberOfRooms)
    {
        $this->numberOfRooms = $numberOfRooms;

        return $this;
    }

    /**
     * Get the value of Number Of Pieces
     *
     * @return integer
     */
    public function getNumberOfPieces()
    {
        return $this->numberOfPieces;
    }

    /**
     * Set the value of Number Of Pieces
     *
     * @param integer numberOfPieces
     *
     * @return self
     */
    public function setNumberOfPieces($numberOfPieces)
    {
        $this->numberOfPieces = $numberOfPieces;

        return $this;
    }

    /**
     * Set the value of Pdf Url
     *
     * @param string|null PDF URL of the item pdfUrl
     *
     * @return self
     */
    public function setPdfUrl(string $pdfUrl)
    {
        $this->pdfUrl = $pdfUrl;

        return $this;
    }

    /**
     * Get the value of Pdf Url
     *
     * @return string|null PDF URL of the item
     */
    public function getPdfUrl()
    {
        return $this->pdfUrl;
    }

    /**
     * Set the value of Website Url
     *
     * @param string|null API URL of the item websiteUrl
     *
     * @return self
     */
    public function setWebsiteUrl(string $websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    /**
     * Get the value of Website Url
     *
     * @return string|null API URL of the item
     */
    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    public function getAreaTerrace(): ?int
    {
        return $this->areaTerrace;
    }

    public function setAreaTerrace(?int $areaTerrace): self
    {
        $this->areaTerrace = $areaTerrace;

        return $this;
    }

    public function getAreaSize(): ?int
    {
        return $this->areaSize;
    }

    public function setAreaSize(?int $areaSize): self
    {
        $this->areaSize = $areaSize;

        return $this;
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


    public function setPrimaryImage(?\App\Entity\MediaObject $primaryImage): void
    {
        $this->primaryImage = $primaryImage;
    }

    public function getPrimaryImage(): ?\App\Entity\MediaObject
    {
        return $this->primaryImage;
    }

    public function setSecondaryImage(?\App\Entity\MediaObject $secondaryImage): void
    {
        $this->secondaryImage = $secondaryImage;
    }

    public function getSecondaryImage(): ?\App\Entity\MediaObject
    {
        return $this->secondaryImage;
    }

    public function setGallery(?\App\Entity\Gallery $gallery): void
    {
        $this->gallery = $gallery;
    }

    public function getGallery(): ?\App\Entity\Gallery
    {
        return $this->gallery;
    }


}
