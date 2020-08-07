<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\AdministrableTrait;


/**
 * A rating is an evaluation on a numeric scale, such as 1 to 5 stars.
 *
 * @see http://schema.org/Rating Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Rating",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\HasLifecycleCallbacks()
 */
class Rating
{
    use IdentifiableTrait
        , ThingTrait
        , TimestampableTrait
        , AdministrableTrait
    ;

    /**
     * @var Organization|null The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Organization")
     * @ApiProperty(iri="http://schema.org/author")
     */
    private $author;

    /**
     * @var float|null The highest value allowed in this rating system. If bestRating is omitted, 5 is assumed.
     *
     * @ORM\Column(type="float", nullable=true)
     * @ApiProperty(iri="http://schema.org/bestRating")
     */
    private $bestRating;

    /**
     * @var string|null the rating for the content
     *
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/ratingValue")
     */
    private $ratingValue;

    /**
     * @var float|null The lowest value allowed in this rating system. If worstRating is omitted, 1 is assumed.
     *
     * @ORM\Column(type="float", nullable=true)
     * @ApiProperty(iri="http://schema.org/worstRating")
     */
    private $worstRating;

    public function setAuthor(?Organization $author): void
    {
        $this->author = $author;
    }

    public function getAuthor(): ?Organization
    {
        return $this->author;
    }

    /**
     * @param float|null $bestRating
     */
    public function setBestRating($bestRating): void
    {
        $this->bestRating = $bestRating;
    }

    /**
     * @return float|null
     */
    public function getBestRating()
    {
        return $this->bestRating;
    }

    public function setRatingValue(?string $ratingValue): void
    {
        $this->ratingValue = $ratingValue;
    }

    public function getRatingValue(): ?string
    {
        return $this->ratingValue;
    }

    /**
     * @param float|null $worstRating
     */
    public function setWorstRating($worstRating): void
    {
        $this->worstRating = $worstRating;
    }

    /**
     * @return float|null
     */
    public function getWorstRating()
    {
        return $this->worstRating;
    }
}
