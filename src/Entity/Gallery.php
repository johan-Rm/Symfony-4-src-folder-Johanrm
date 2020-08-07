<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\ThingTrait;
// use App\Entity\Traits\CreativeWorkTrait;
use App\Entity\Traits\AdministrableTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ApiResource(iri="http://schema.org/ImageGallery",
   *  attributes={
 *      "normalization_context"={"groups"={"thing", "media", "gallery"}}
 *  },
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 */
class Gallery
{
    use IdentifiableTrait
        , ThingTrait
        // , CreativeWorkTrait
        , TimestampableTrait
        , AdministrableTrait
    ;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     * @Groups("gallery")
     *
     * @Assert\NotBlank(message="Enter a name")
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="MediaObject", inversedBy="galleries", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="images_galleries")
     *
     * @ Groups("gallery")
     *
     * @Assert\Count(
     *   min = "1",
     *   minMessage = "You have to select at least 1 item"
     * )
     */
    private $images;

    /**
     * @var int|null the width of the item
     *
     * @ORM\Column(type="integer", nullable=true)
     * @ApiProperty(iri="http://schema.org/width")
     * @Groups("gallery")
     */
    private $width;

    /**
     * @var int|null the height of the item
     *
     * @ORM\Column(type="integer", nullable=true)
     * @ApiProperty(iri="http://schema.org/height")
     * @Groups("gallery")
     */
    private $height;

    /**
    * @Gedmo\Slug(fields={"name"}, prefix="")
    * @ORM\Column(type="string", length=128, unique=true)
    * @Groups("component")
    */
    private $slug;

    /**
     * @ Gedmo\SortableGroup
     * @ORM\OneToMany(targetEntity="App\Entity\ImageGallery", mappedBy="gallery", fetch="EXTRA_LAZY")
     * 
     * @Groups("gallery")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $imageGalleries;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->imageGalleries = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->getName() . ' - ID : ' . $this->getId();
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * Add image
     *
     * @param App\Entity\MediaObject $image
     *
     * @return MediaObject
     */
    public function addImage(MediaObject $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
        }
        
        return $this;
    }

    /**
     * Remove image
     *
     * @param App\Entity\MediaObject $image
     */
    public function removeImage(MediaObject $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
        }

        return $this;
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return Collection|ImageGallery[]
     */
    public function getImageGalleries(): Collection
    {
        return $this->imageGalleries;
    }

    public function addImageGallery(ImageGallery $imageGallery): self
    {
        if (!$this->imageGalleries->contains($imageGallery)) {
            $this->imageGalleries[] = $imageGallery;
            $imageGallery->setGallery($this);
        }

        return $this;
    }

    public function removeImageGallery(ImageGallery $imageGallery): self
    {
        if ($this->imageGalleries->contains($imageGallery)) {
            $this->imageGalleries->removeElement($imageGallery);
            // set the owning side to null (unless already changed)
            if ($imageGallery->getGallery() === $this) {
                $imageGallery->setGallery(null);
            }
        }

        return $this;
    }
}
