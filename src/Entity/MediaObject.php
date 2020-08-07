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
use ApiPlatform\Core\Annotation\ApiSubresource;


/**
 * A media object, such as an image, video, or audio object embedded in a web page or a downloadable dataset i.e. DataDownload. Note that a creative work may have many media objects associated with it on the same web page. For example, a page about a single song (MusicRecording) may have a music video (VideoObject), and a high and low bandwidth audio stream (2 AudioObject's).
 *
 * @see http://schema.org/MediaObject Documentation on Schema.org
 *
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\MediaObjectRepository")
 * @ApiResource(iri="http://schema.org/MediaObject",
 *  attributes={
 *      "normalization_context"={"groups"={"thing", "media"}}
 *  },
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\HasLifecycleCallbacks()
 * @Vich\Uploadable
 */
class MediaObject
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
     * @Groups("media")
     */
    private $contentSize;

    /**
     * @var string|null mp3, mpeg4, etc
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/encodingFormat")
     * @Groups("media")
     */
    private $encodingFormat;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/encodingFormat")
     * @Groups("media")
     */
    private $caption;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("media")
     */
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ Assert\NotNull
     * @Groups("media")
     */
    private $originalFilename;

    /**
     * @var string
     *
     * @ORM\Column(type="simple_array", nullable=true)
     * @ Assert\NotNull
     * @Groups("media")
     */
    private $dimensions;

    /**
     * @var File
     *
     * @Assert\File(
     *     maxSize = "15G",
     *     mimeTypes = {
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg",
     *          "image/gif"
     *      },
     *     mimeTypesMessage = "Formats autorisés : png, jpeg, jpg, gif"
     * )
     * @Vich\UploadableField(
     *  mapping="uploads_media_files"
     *  , fileNameProperty="filename"
     *  , size="contentSize"
     *  , mimeType="encodingFormat"
     *  , originalName="originalFilename"
     *  , dimensions="dimensions"
     * )
     * @Groups("media")
     *
     * @ Assert\Expression(
     *     "!this.getFilename() || value == null",
     *     message="Select an image"
     * )
     * @ Assert\NotBlank(message="Select an image")
     */
    private $file;

    /**
    * @var string
    *
     * @ORM\Column(type="string", length=255, nullable=true)
    */
    private $tmpFile;

    /**
     * @ORM\ManyToMany(targetEntity="Gallery", mappedBy="images")
     */
	private $galleries;

    /**
     * @ORM\OneToMany(targetEntity="Accommodation", mappedBy="primaryImage")
     */
	private $imagesAccommodations;

    /**
     * @ORM\ManyToMany(targetEntity="Accommodation", mappedBy="videos")
     */
    private $videosAccommodations;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Article", mappedBy="videos")
     */
    private $videosArticles;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\WebPage", mappedBy="videos")
     */
    private $videosWebPages;

    /**
     * @ORM\Column(type="boolean", options={ "default": false })
     *
     * @Groups("media")
     */
    private $isConform = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Article", mappedBy="media")
     */
    private $articles;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups("media")
     */
    private $link;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     * @Groups("media")
     */
    private $isActive = true;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ImageGallery", mappedBy="image", cascade={"remove"})
     */
    private $imageGalleries;

    /**
     * @ORM\Column(type="boolean")
     */
    private $toStamp = true;

    /**
     * Constructor
     */
    public function __construct()
    {
      $this->galleries = new ArrayCollection();
      $this->imagesAccommodations = new ArrayCollection();
      $this->videosAccommodations = new ArrayCollection();
      $this->videosWebPages = new ArrayCollection();
      $this->videosArticles = new ArrayCollection();
      $this->articles = new ArrayCollection();
      $this->imageGalleries = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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
     * Add Gallery
     *
     * @param App\Entity\Gallery $gallery
     *
     * @return Gallery
     */
    public function addGallery(Gallery $gallery): void
    {
        if ($this->galleries->contains($gallery)) {
            return;
        }

        $this->galleries->add($gallery);

        // Bidirectional Ownership
        $gallery->addImage($this);
    }

    /**
     * Remove Gallery
     *
     * @param App\Entity\Gallery $gallery
     */
    public function removeGallery(Gallery $gallery): void
    {
        // If the category does not exist in the collection, then we don't need to do anything
        if (!$this->galleries->contains($gallery)) {
            return;
        }

        // Remove category from the collection
        $this->galleries->removeElement($gallery);
        // Also remove this from the blog post collection of the category
        $gallery->removeImage($this);
    }

    /**
    * Get Galleries
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getGalleries()
    {
       return $this->galleries;
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

    /**
     * Set the value of Many Images have one (the same) Gallery
     *
     * @param mixed galleries
     *
     * @return self
     */
    public function setGalleries($galleries)
    {
        $this->galleries = $galleries;

        return $this;
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
     * @param App\Entity\Accommodation $imagesAccommodations
     *
     * @return Accommodation
     */
    public function addImagesAccommodation(Accommodation $accommodation): void
    {
        if ($this->imagesAccommodations->contains($accommodation)) {
            return;
        }

        $this->imagesAccommodations->add($accommodation);

        // Bidirectional Ownership
        $accommodation->addImage($this);
    }

    /**
     * Remove Accommodation
     *
     * @param App\Entity\Accommodation $accommodation
     */
    public function removeImagesAccommodation(Accommodation $accommodation): void
    {
        // If the category does not exist in the collection, then we don't need to do anything
        if (!$this->imagesAccommodations->contains($accommodation)) {
            return;
        }

        // Remove category from the collection
        $this->imagesAccommodations->removeElement($accommodation);
        // Also remove this from the blog post collection of the category
        $accommodation->removeImage($this);
    }

    /**
    * Get Accommodations
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getImagesAccommodations()
    {
       return $this->imagesAccommodations;
    }

    /**
     * Add Accommodation
     *
     * @param App\Entity\Accommodation $videosAccommodations
     *
     * @return Accommodation
     */
    public function addVideosAccommodation(Accommodation $accommodation): void
    {
        if ($this->videosAccommodations->contains($accommodation)) {
            return;
        }

        $this->videosAccommodations->add($accommodation);

        // Bidirectional Ownership
        $accommodation->addVideo($this);
    }

    /**
     * Remove Accommodation
     *
     * @param App\Entity\Accommodation $accommodation
     */
    public function removeVideosAccommodation(Accommodation $accommodation): void
    {
        // If the category does not exist in the collection, then we don't need to do anything
        if (!$this->videosAccommodations->contains($accommodation)) {
            return;
        }

        // Remove category from the collection
        $this->videosAccommodations->removeElement($accommodation);
        // Also remove this from the blog post collection of the category
        $accommodation->removeVideo($this);
    }

    /**
    * Get Accommodations
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getVideosAccommodations()
    {
       return $this->videosAccommodations;
    }

    public function getVideosArticles(): ?Article
    {
        return $this->videosArticles;
    }

    /**
     * Add Article
     *
     * @param App\Entity\Article $article
     *
     * @return Article
     */
    public function addVideosArticle(Article $article): void
    {
        if ($this->videosArticles->contains($article)) {
            return;
        }

        $this->videosArticles->add($article);

        // Bidirectional Ownership
        $article->addVideo($this);
    }

    /**
     * Remove Article
     *
     * @param App\Entity\Article $article
     */
    public function removeVideosArticle(Article $article): void
    {
        // If the category does not exist in the collection, then we don't need to do anything
        if (!$this->videosArticles->contains($article)) {
            return;
        }

        // Remove category from the collection
        $this->videosArticles->removeElement($article);
        // Also remove this from the blog post collection of the category
        $article->removeVideo($this);
    }

    /**
     * @return Collection|WebPage[]
     */
    public function getVideosWebPages(): Collection
    {
        return $this->videosWebPages;
    }

    public function addVideosWebPage(WebPage $webPage): self
    {
        if (!$this->videosWebPages->contains($webPage)) {
            $this->videosWebPages[] = $webPage;
            $webPage->addVideo($this);
        }

        return $this;
    }

    public function removeVideosWebPage(WebPage $webPage): self
    {
        if ($this->videosWebPages->contains($webPage)) {
            $this->videosWebPages->removeElement($webPage);
            $webPage->removeVideo($this);
        }

        return $this;
    }

    public function getIsConform(): ?bool
    {
        return $this->isConform;
    }

    public function setIsConform(bool $isConform): self
    {
        $this->isConform = $isConform;

        return $this;
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setMedia($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getMedia() === $this) {
                $article->setMedia(null);
            }
        }

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
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
            $imageGallery->setImage($this);
        }

        return $this;
    }

    public function removeImageGallery(ImageGallery $imageGallery): self
    {
        if ($this->imageGalleries->contains($imageGallery)) {
            $this->imageGalleries->removeElement($imageGallery);
            // set the owning side to null (unless already changed)
            if ($imageGallery->getImage() === $this) {
                $imageGallery->setImage(null);
            }
        }

        return $this;
    }

    public function getToStamp(): ?bool
    {
        return $this->toStamp;
    }

    public function setToStamp(bool $toStamp): self
    {
        $this->toStamp = $toStamp;

        return $this;
    }
}
