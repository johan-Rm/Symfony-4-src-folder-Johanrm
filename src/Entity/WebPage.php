<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\CreativeWorkTrait;
use App\Entity\Traits\SluggableHeadlineTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\AdministrableTrait;



/**
 * A web page. Every web page is implicitly assumed to be declared to be of type WebPage, so the various properties about that webpage, such as `breadcrumb` may be used. We recommend explicit declaration if these properties are specified, but if they are found outside of an itemscope, they will be assumed to be about the page.
 *
 * @see http://schema.org/WebPage Documentation on Schema.org
 *
 * @ORM\Table(name="webpage")
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/WebPage",
 *  attributes={
 *      "normalization_context"={"groups"={"thing", "identifier", "creative", "page", "tag", "media", "gallery", "page:article", "component"}}
 *  },
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ApiFilter(OrderFilter::class, properties={"datePublished", "lastReviewed"},
 *  arguments={
 *      "orderParameterName"="order"
 *  }
 * )
 * @ApiFilter(BooleanFilter::class, properties={"isActive"})
 * @ApiFilter(SearchFilter::class, properties={ "category.slug": "exact", "slug": "exact", "tags.slug": "exact" })
 * @ORM\HasLifecycleCallbacks()
 */
class WebPage
{
    use IdentifiableTrait
        // , SluggableHeadlineTrait
        , ThingTrait
        , CreativeWorkTrait
        , TimestampableTrait
        , AdministrableTrait
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
     * @var \DateTimeInterface|null date on which the content on this web page was last reviewed for accuracy and/or completeness
     *
     * @ORM\Column(type="date", nullable=true)
     * @ApiProperty(iri="http://schema.org/lastReviewed")
     * @ Assert\Date
     *
     * @Groups("page")
     * @Gedmo\Timestampable(on="update")
     */
    private $lastReviewed;

    /**
     * @var MediaObject|null indicates the main image on the page
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @ApiProperty(iri="http://schema.org/primaryImage")
     *
     * @Groups("page")
     * @ApiSubresource
     *
     * @Assert\NotBlank(message="Select the main image")
     */
    private $primaryImage;

    /**
     * @var MediaObject|null indicates the main image on the page
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     * @ApiProperty(iri="http://schema.org/primaryImage")
     *
     * @Groups("page")
     * @ApiSubresource
     */
    private $secondaryImage;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *
     * @Groups("page")
     * @ApiSubresource
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="webPages", cascade={"persist"})
     * @ORM\JoinTable(name="webpages_tags")
     *
     * @Groups("page")
     * @ApiSubresource
     */
    private $tags;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Component", inversedBy="webPages", cascade={"persist"})
     * @ORM\JoinTable(name="webpages_components")
     * @Groups("page")
     */
    private $components;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="webPages")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups("page")
     * @ApiSubresource
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gallery")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups("page")
     * @ApiSubresource
     */
    private $gallery;

    /**
    * One template has One page.
    * @ORM\ManyToOne(targetEntity="WebPageTemplate")
    * @ORM\JoinColumn(name="webpage_template_id", referencedColumnName="id")
    *
    * @Groups("page")
    */
    private $webPageTemplate;

    /**
    * @Gedmo\Slug(fields={"headline"}, prefix="")
    * @ORM\Column(type="string", length=128, unique=true)
    *
    * @ApiProperty(identifier=true)
    *
    * @Groups("page")
    */
    private $slug;

    /**
     * @var string|null API URL of the item
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/url")
     * @Assert\Url
     *
     * @Groups("page")
     */
    private $url;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\MediaObject", inversedBy="videosWebPages")
     * @ORM\JoinTable(name="webpages_videos")
     *
     * @Groups("page")
     * @ ApiSubresource
     */
    private $videos;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("page")
     */
    private $blockquote;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("page")
     */
    private $blockquoteTitle;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     * @Groups("page")
     */
    private $isActive = true;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("page")
     */
    private $hideStamp = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $regenerateFormat = false;

    /**
     * Constructor
     */
    public function __construct()
    {
      $this->tags = new ArrayCollection();
      $this->components = new ArrayCollection();
      $this->videos = new ArrayCollection();
    }

    public function __toString()
    {
      return $this->getHeadline();
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
    
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setLastReviewed(?\DateTimeInterface $lastReviewed): void
    {
        $this->lastReviewed = $lastReviewed;
    }

    public function getLastReviewed(): ?\DateTimeInterface
    {
        return $this->lastReviewed;
    }

    public function setPrimaryImage(?MediaObject $primaryImage): void
    {
        $this->primaryImage = $primaryImage;
    }

    public function getPrimaryImage(): ?MediaObject
    {
        return $this->primaryImage;
    }

    public function setSecondaryImage(?MediaObject $secondaryImage): void
    {
        $this->secondaryImage = $secondaryImage;
    }

    public function getSecondaryImage(): ?MediaObject
    {
        return $this->secondaryImage;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    public function setGallery(?Gallery $gallery): void
    {
        $this->gallery = $gallery;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    /**
     * Add component
     *
     * @param App\Entity\Component $component
     *
     * @return Component
     */
    public function addComponent(Component $component): self
    {
        if (!$this->components->contains($component)) {
            $this->components[] = $component;
        }

        return $this;
    }

    /**
     * Remove component
     *
     * @param App\Entity\Component $component
     */
    public function removeComponent(Component $component): self
    {
        if ($this->components->contains($component)) {
            $this->components->removeElement($component);
        }

        return $this;
    }

    /**
     * Get components
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComponents(): Collection
    {
        return $this->components;
    }

    /**
     * Set the value of Tags
     *
     * @param mixed tags
     *
     * @return self
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Set the value of Components
     *
     * @param mixed components
     *
     * @return self
     */
    public function setComponents($components)
    {
        $this->components = $components;

        return $this;
    }

    /**
     * Set the value of One template has One page.
     *
     * @param mixed webPageTemplate
     *
     * @return self
     */
    public function setWebPageTemplate($webPageTemplate)
    {
        $this->webPageTemplate = $webPageTemplate;

        return $this;
    }

    /**
     * Get the value of One template has One page.
     *
     * @return mixed
     */
    public function getWebPageTemplate()
    {
        return $this->webPageTemplate;
    }

    /**
     * Set the value of Article
     *
     * @param mixed article
     *
     * @return self
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get the value of Article
     *
     * @return mixed
     */
    public function getArticle()
    {
        return $this->article;
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
     * @ORM\PreFlush()
     */
    public function preUpload()
    {
        $this->dateModified = new \DateTime();
    }

    /**
     * @ORM\PrePersist()
     *
     */
    public function prePersist()
    {
        $this->datePublished = new \DateTime();
        $this->lastReviewed = new \DateTime();
        $this->dateCreated = new \DateTime();
        $this->dateModified = new \DateTime();
    }

    public function setCategory(?Tag $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): ?Tag
    {
        return $this->category;
    }

    /**
     * @return Collection|MediaObject[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(MediaObject $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
        }

        return $this;
    }

    public function removeVideo(MediaObject $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
        }

        return $this;
    }

    public function getBlockquote(): ?string
    {
        return $this->blockquote;
    }

    public function setBlockquote(?string $blockquote): self
    {
        $this->blockquote = $blockquote;

        return $this;
    }

    public function getBlockquoteTitle(): ?string
    {
        return $this->blockquoteTitle;
    }

    public function setBlockquoteTitle(?string $blockquoteTitle): self
    {
        $this->blockquoteTitle = $blockquoteTitle;

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
