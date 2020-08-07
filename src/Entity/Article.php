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
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\CreativeWorkTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\AdministrableTrait;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * An article, such as a news article or piece of investigative report. Newspapers and magazines have articles of many different types and this is intended to cover them all.\\n\\nSee also \[blog post\](http://blog.schema.org/2014/09/schemaorg-support-for-bibliographic\_2.html).
 *
 * @see http://schema.org/Article Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Article",
 *  itemOperations={
 *      "get"={
 *          "method"="GET",
 *          "normalization_context" = {
 *              "groups"= {
 *                  "article"
 *                  , "thing"
 *                  , "identifier"
 *                  , "creative"
 *                  , "tag"
 *                  , "gallery"
 *                  , "media"
 *                  , "admin"
 *                  , "timestamp"
 *                  , "component"
 *              },"datetime_format"="Y-m-d"
 *          }
 *      }
 *  },
 *  collectionOperations={
 *      "get"={
 *          "method"="GET",   
 *          "path"="/articles",
 *          "normalization_context"={
 *              "groups"={
 *                  "collection:article",
 *                  "identifier",
 *                  "media",
 *                  "name",
 *                  "thing",
 *                  "creative",
 *                  "gallery",
 *                  "tag",
 *                  "admin"
 *              }
 *          }
 *      },
 *      "menu_get"={
 *          "method"="GET",
 *          "path"="/menu_articles",
 *          "normalization_context"={   
 *              "groups"={
 *                  "menu"
 *              }
 *          }
 *       },
  *     "list_full_get"={
 *          "method"="GET",
 *          "path"="/full_articles",
 *          "normalization_context"={
 *              "groups"={
 *                  "article"
 *                  , "thing"
 *                  , "identifier"
 *                  , "creative"
 *                  , "tag"
 *                  , "gallery"
 *                  , "media"
 *                  , "admin"
 *                  , "timestamp"
 *                  , "component"
 *              }
 *          }
 *      }
 *    }
 * )
 * @ApiFilter(BooleanFilter::class, properties={"isActive"})
 * @ApiFilter(OrderFilter::class, properties={"id", "datePublished", "lastReviewed"},
 *  arguments={
 *      "orderParameterName"="order"
 *  }
 * )
 * @ApiFilter(SearchFilter::class, properties={ "category.slug": "exact", "slug": "exact", "tags.slug": "exact" })
 * @ORM\HasLifecycleCallbacks()
 */
class Article
{
    use ThingTrait
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
     * @var string|null the actual body of the article
     *
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/articleBody")
     *
     * @Groups({"article", "collection:article", "page:article"})
     */
    private $articleBody;

    /**
     * @var string|null the actual body of the article
     *
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/articleBody")
     * 
     * @Groups({"article", "page:article", "collection:article"})
     */
    private $articleResume;

    /**
     * @var \DateTimeInterface|null date on which the content on this web page was last reviewed for accuracy and/or completeness
     *
     * @ORM\Column(type="date", nullable=true)
     * @ApiProperty(iri="http://schema.org/lastReviewed")
     *
     * @ Assert\Date
     *
     * @Groups("article")
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $lastReviewed;

    /**
     * @var MediaObject|null indicates the main image on the page
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     * @ApiProperty(iri="http://schema.org/primaryImage")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups({"article", "collection:article"})
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
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups({"article", "collection:article"})
     * @ApiSubresource
     */
    private $secondaryImage;

    /**
     * @var Comment|null comments, typically from users
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="article")
     * @ApiProperty(iri="http://schema.org/comment")
     *
     * @Groups("article")
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *
     * @Groups({ "article", "collection:article", "page:article", "menu" })
     * @ApiSubresource
     */
    private $category;

    /**
    * @ORM\ManyToMany(targetEntity="App\Entity\Tag", inversedBy="articles", cascade={"persist"})
    * @ORM\JoinTable(name="articles_tags")
    *
    * @Groups("article")
    * @ApiSubresource
    */
    private $tags;

    /**
    * @ORM\OneToMany(targetEntity="App\Entity\WebPage", mappedBy="article")
    * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
    *
    * @Groups("article")
    */
    private $webPages;

    /**
    * @Gedmo\Slug(fields={"headline"}, prefix="")
    * @ORM\Column(type="string", length=128, unique=true)
    *
    * @ApiProperty(identifier=true)
    *
    * @Groups({ "article", "collection:article", "page:article", "menu" })
    */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gallery")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups({"article", "collection:article"})
     * @ApiSubresource
     */
    private $gallery;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gallery")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups({"article", "collection:article"})
     * @ApiSubresource
     */
    private $galleryVertical;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("article")
     */
    private $blockquote;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("article")
     */
    private $blockquoteTitle;

    /**
     * Many Videos
     * @ORM\ManyToMany(targetEntity="\App\Entity\MediaObject", inversedBy="videosArticles")
     * @ORM\JoinTable(name="articles_videos")
     *
     * @Groups("article")
     * @ApiSubresource
     */
    private $videos;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Component", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinTable(name="articles_components")
     * @Groups("article")
     */
    private $components;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     * @Groups("article")
     */
    private $isActive = true;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject", inversedBy="articles")
     * @Groups("article")
     */
    private $media;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("article")
     */
    private $hideStamp = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $regenerateFormat = false;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->webPages = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->components = new ArrayCollection();
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

    public function setArticleBody(?string $articleBody): void
    {
        $this->articleBody = $articleBody;
    }

    public function getArticleResume(): ?string
    {
        return $this->articleResume;
    }

    public function setArticleResume(?string $articleResume): void
    {
        $this->articleResume = $articleResume;
    }

    public function getArticleBody(): ?string
    {
        return $this->articleBody;
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
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

        return $this;
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

    /**
     * Add webPage
     *
     * @param \AppBundle\Entity\WebPage $webPage
     */
    public function addWebPage($webPage)
    {
        if ($this->webPages->contains($webPage)) {
            return;
        }

        $this->webPages->add($webPage);
    }

    /**
     * Remove webPage
     *
     * @param \AppBundle\Entity\WebPage $webPage
     */
    public function removeWebPage($webPage)
    {
        if (!$this->webPages->contains($webPage)) {
            return;
        }

        $this->webPages->removeElement($webPage);
    }

    /**
     * Get webPages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWebPages()
    {
        return $this->webPages;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setGallery(?Gallery $gallery): void
    {
        $this->gallery = $gallery;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    public function setGalleryVertical(?Gallery $galleryVertical): void
    {
        $this->galleryVertical = $galleryVertical;
    }

    public function getGalleryVertical(): ?Gallery
    {
        return $this->galleryVertical;
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

    /**
     * @return Collection|MediaObject[]
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(MediaObject $video): self
    {
        if ($this->videos->contains($video)) {
            return $this;
        }

        $this->videos->add($video);

        return $this;
    }

    public function removeVideo(MediaObject $video): self
    {
        if ($this->videos->contains($video)) {
            $this->videos->removeElement($video);
            // set the owning side to null (unless already changed)
            
        }

        return $this;
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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getMedia(): ?MediaObject
    {
        return $this->media;
    }

    public function setMedia(?MediaObject $media): self
    {
        $this->media = $media;

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
