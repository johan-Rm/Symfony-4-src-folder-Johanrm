<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


trait ThingTrait
{
    /**
     * @var string|null the name of the item
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/name")
     *
     * @Groups({"thing"})
     */
    private $name;

    /**
     * @var string|null a description of the item
     *
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/description")
     * @Groups({"thing"})
     */
    private $description;

    /**
     * @var string|null API URL of the item
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/url")
     * @Groups("thing")
     */
    private $url;

    /**
     * @var CreativeWork|null Indicates a page (or other CreativeWork) for which this thing is the main entity being described. See \[background notes\](/docs/datamodel.html#mainEntityBackground) for details.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ ORM\ManyToOne(targetEntity="App\Entity\CreativeWork")
     * @ApiProperty(iri="http://schema.org/mainEntityOfPage")
     * @Groups("thing")
     */
    private $mainEntityOfPage;

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setMainEntityOfPage(?string $mainEntityOfPage): void
    {
        $this->mainEntityOfPage = $mainEntityOfPage;
    }

    public function getMainEntityOfPage(): ?string
    {
        return $this->mainEntityOfPage;
    }
}
