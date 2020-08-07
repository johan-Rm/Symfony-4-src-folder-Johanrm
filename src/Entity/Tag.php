<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\CreativeWorkTrait;
use App\Entity\Traits\SluggableNameTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\AdministrableTrait;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * The most generic type of item.
 *
 * @see http://schema.org/Thing Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Thing",
    attributes={
 *      "normalization_context"={"groups"={"tag"}}
 *  },
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\HasLifecycleCallbacks()
 */
class Tag
{
    use IdentifiableTrait
        // , ThingTrait
        // , SluggableNameTrait
        , TimestampableTrait
        , AdministrableTrait
    ;

    /**
     * @ORM\ManyToMany(targetEntity="WebPage", mappedBy="tags")
     */
	private $webPages;

    /**
     * @ORM\ManyToMany(targetEntity="Article", mappedBy="tags")
     */
    private $articles;

    /**
    * @ORM\ManyToMany(targetEntity="Accommodation", mappedBy="tags")
    */
    private $accommodations;

    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(length=128)
     * @Groups("tag")
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Groups("tag")
     * @Assert\NotNull
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     * @Groups("tag")
     */
    private $isActive = true;

    /*
     * Constructor
     */
    public function __construct()
    {
        $this->webPages = new ArrayCollection();
        $this->accommodations = new ArrayCollection();
        $this->articles = new ArrayCollection();
    }

    public function __toString()
    {
      return $this->getName();
    }

    /**
     * Add WebPage
     *
     * @param App\Entity\WebPage $webPage
     *
     * @return WebPage
     */
    public function addWebPage(WebPage $webPage): void
    {
        if ($this->webPages->contains($webPage)) {
            return;
        }

        $this->webPages->add($webPage);

        // Bidirectional Ownership
        $webPage->addTag($this);
    }

    /**
     * Remove WebPage
     *
     * @param App\Entity\WebPage $webPage
     */
    public function removeWebPage(WebPage $webPage): void
    {
        // If the category does not exist in the collection, then we don't need to do anything
        if (!$this->webPages->contains($webPage)) {
            return;
        }

        // Remove category from the collection
        $this->webPages->removeElement($webPage);
        // Also remove this from the blog post collection of the category
        $webPage->removeTag($this);
    }

    /**
    * Get Galleries
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getWebPages()
    {
       return $this->webPages;
    }

    /**
     * Add Accommodation
     *
     * @param App\Entity\Accommodation $accommodation
     *
     * @return Accommodation
     */
    public function addAccommodation(Accommodation $accommodation): void
    {
        if ($this->accommodations->contains($accommodation)) {
            return;
        }

        $this->accommodations->add($accommodation);

        // Bidirectional Ownership
        $accommodation->addTag($this);
    }

    /**
     * Remove Accommodation
     *
     * @param App\Entity\Accommodation $acommodation
     */
    public function removeAccommodation(Accommodation $accommodation): void
    {
        if (!$this->accommodations->contains($accommodation)) {
            return;
        }

        $this->accommodations->removeElement($accommodation);

        $accommodation->removeTag($this);
    }

    /**
    * Get Accommodations
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getAccommodations()
    {
       return $this->accommodations;
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
            $article->addTag($this);
        }
        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            $article->removeTag($this);
        }
        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return string
    */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
}
