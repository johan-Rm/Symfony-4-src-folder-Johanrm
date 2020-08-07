<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\SluggableNameTrait;
use App\Entity\Traits\AdministrableTrait;


/**
 * Entities that have a somewhat fixed, physical extension.
 *
 * @see http://schema.org/? Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/?",
 *  collectionOperations={
 *      "menu_get"={
 *          "method"="GET",
 *          "path"="/menu_accommodation_natures",
 *          "normalization_context"={   
 *              "groups"={
 *                  "menu"
 *              }
 *          }
 *       }
 *    },
 *  itemOperations={"get"={"method"="GET"}}
 * )
 *
 * @ ORM\Entity(repositoryClass="App\Repository\AccommodationNatureRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AccommodationNature
{
    use IdentifiableTrait
        , TimestampableTrait
        , SluggableNameTrait
        , AdministrableTrait
    ;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccommodationNature", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AccommodationNature", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="accommodationNature")
     */
    private $events;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getChildren(): ?Collection
    {
        return $this->children;
    }

    public function setChildren(?self $children): Collection
    {
        $this->children = $children;

        return $this;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setChildren($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getChildren() === $this) {
                $child->setChildren(null);
            }
        }

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function addParent(self $parent): self
    {
        if (!$this->parent->contains($parent)) {
            $this->parent[] = $parent;
            $parent->setParent($this);
        }

        return $this;
    }

    public function removeParent(self $parent): self
    {
        if ($this->parent->contains($parent)) {
            $this->parent->removeElement($parent);
            // set the owning side to null (unless already changed)
            if ($parent->getParent() === $this) {
                $parent->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setAccommodationNature($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getAccommodationNature() === $this) {
                $event->setAccommodationNature(null);
            }
        }

        return $this;
    }

}
