<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
// use App\Entity\Traits\SluggableNameTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\AdministrableTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A property-value pair, e.g. representing a feature of a product or place. Use the 'name' property for the name of the property. If there is an additional human-readable version of the value, put that into the 'description' property.\\n\\n Always use specific schema.org properties when a) they exist and b) you can populate them. Using PropertyValue as a substitute will typically not trigger the same effect as using the original, specific property.
 *
 * @see http://schema.org/PropertyValue Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/PropertyValue",
 *  attributes={
 *      "normalization_context"={"groups"={"thing", "component"}}
 *  },
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\HasLifecycleCallbacks()
 * @ApiFilter(SearchFilter::class, properties={ "type": "exact", "slug": "exact" })
 */
class Component
{
    use IdentifiableTrait
        , ThingTrait
        // , SluggableNameTrait
        , TimestampableTrait
        , AdministrableTrait
    ;

    /**
     * @var CreativeWork|null Indicates a page (or other CreativeWork) for which this thing is the main entity being described. See \[background notes\](/docs/datamodel.html#mainEntityBackground) for details.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/Service")
     * @Groups("component")
     */
    private $service;

    /**
     * @var CreativeWork|null Indicates a page (or other CreativeWork) for which this thing is the main entity being described. See \[background notes\](/docs/datamodel.html#mainEntityBackground) for details.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/Type")
     * @Groups("component")
     */
    private $type;

    /**
     * @var CreativeWork|null Indicates a page (or other CreativeWork) for which this thing is the main entity being described. See \[background notes\](/docs/datamodel.html#mainEntityBackground) for details.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/mainEntity")
     * @Groups("component")
     */
    private $mainEntity;

    /**
    * @var Collection<PropertyValue>|null
    *
    * @ORM\OneToMany(targetEntity="App\Entity\PropertyValue", mappedBy="setting", cascade= { "remove" })
    * @Groups("component")
    */
    private $propertyValues;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\WebPage", mappedBy="components")
     */
    private $webPages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Article", mappedBy="components")
     */
    private $articles;

    /**
    * @ORM\Column(type="string", length=255)
    * @Groups("component")
    */
    private $slugPath;

    /**
    * @Gedmo\Slug(fields={"name"}, prefix="")
    * @ORM\Column(type="string", length=128, unique=true)
    * @Groups("component")
    */
    private $slug;

    public function __construct()
    {
        $this->propertyValues = new ArrayCollection();
    }

    public function __toString()
    {
      return $this->getName();
    }

    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string|null $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string|null $service
     */
    public function setService($service): void
    {
        $this->service = $service;
    }

    /**
     * @return string|null
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return mixed
     */
    public function getPropertyValues()
    {
        return $this->propertyValues;
    }

    /**
     * @param \App\Entity\PropertyValue $propertyValue
     */
    public function addPropertyValue($propertyValue)
    {
      if ($this->propertyValues->contains($propertyValue)) {
          return;
      }

      $propertyValue->addSetting($this);
      $this->propertyValues->add($propertyValue);
    }

    /**
    * @param \App\Entity\PropertyValue $propertyValue
    */
    public function removeElement($propertyValue)
    {
     if (!$this->propertyValues->contains($propertyValue)) {
         return;
     }

     $this->propertyValues->removePropertyValue($propertyValue);
     $propertyValue->removeSetting($this);
    }

    public function setMainEntity(?string $mainEntity): void
    {
        $this->mainEntity = $mainEntity;
    }

    public function getMainEntity(): ?string
    {
        return $this->mainEntity;
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
        $webPage->addComponent($this);
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
        $webPage->removeComponent($this);
    }

    /**
    * Get WebPages
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getWebPages()
    {
       return $this->webPages;
    }

    public function getSlugPath()
    {
        return $this->slugPath;
    }

    /**
     * Add Article
     *
     * @param App\Entity\Article $article
     *
     * @return Article
     */
    public function addArticle(Article $article): void
    {
        if ($this->articles->contains($article)) {
            return;
        }

        $this->articles->add($article);

        // Bidirectional Ownership
        $article->addComponent($this);
    }

    /**
     * Remove Article
     *
     * @param App\Entity\Article $article
     */
    public function removeArticle(Article $article): void
    {
        // If the category does not exist in the collection, then we don't need to do anything
        if (!$this->articles->contains($article)) {
            return;
        }

        // Remove category from the collection
        $this->articles->removeElement($article);
        // Also remove this from the blog post collection of the category
        $article->removeComponent($this);
    }

    /**
    * Get Articles
    *
    * @return \Doctrine\Common\Collections\Collection
    */
    public function getArticles()
    {
       return $this->articles;
    }

        /**
     * Set the value of Slug
     *
     * @param mixed slug
     *
     * @return self
     */
    public function setSlugPath($slugPath)
    {
        $this->slugPath = $slugPath;

        return $this;
    }

}
