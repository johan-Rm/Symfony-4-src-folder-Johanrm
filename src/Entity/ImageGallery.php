<?php

namespace App\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\ThingTrait;
// use App\Entity\Traits\CreativeWorkTrait;
use App\Entity\Traits\AdministrableTrait;


/**
 * @ApiResource(iri="http://schema.org/ImageGallery",
 *  attributes={
 *      "normalization_context"={"groups"={"thing", "media", "gallery"}}
 *  },
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\Entity(repositoryClass="App\Repository\ImageGalleryRepository")
 * @ORM\Table(name="image_gallerie")
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class ImageGallery
{
   use IdentifiableTrait
        , ThingTrait
        // , CreativeWorkTrait
        // , TimestampableTrait
        , AdministrableTrait
    ;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject", inversedBy="imageGalleries")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("gallery")
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gallery", inversedBy="imageGalleries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $gallery;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     * @Groups("gallery")
     */
    private $position;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("gallery")
     */
    private $inSlider = false;


    public function __toString()
    {
        return $this->image->getFilename();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?MediaObject
    {
        return $this->image;
    }

    public function setImage(?MediaObject $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    public function setGallery(?Gallery $gallery): self
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getInSlider(): ?bool
    {
        return $this->inSlider;
    }

    public function setInSlider(bool $inSlider): self
    {
        $this->inSlider = $inSlider;

        return $this;
    }

}
