<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\SluggableNameTrait;
use App\Entity\Traits\AdministrableTrait;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;


/**
 * Entities that have a somewhat fixed, physical extension.
 *
 * @see http://schema.org/? Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/?",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ApiFilter(SearchFilter::class,
 *   properties={ 
 *          "slug": "exact"
 *       }
 *   )
 * @ApiFilter(BooleanFilter::class, properties={"isActive", "isLocation"})
 * @ ORM\Entity(repositoryClass="App\Repository\AccommodationTypeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AccommodationType
{
    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $isActive = true;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isLocation = false;

    use IdentifiableTrait
        , TimestampableTrait
        , SluggableNameTrait
        , AdministrableTrait
    ;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->getName();
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

    public function getIsLocation(): ?bool
    {
        return $this->isLocation;
    }

    public function setIsLocation(bool $isLocation): self
    {
        $this->isLocation = $isLocation;

        return $this;
    }
}
