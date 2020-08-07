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
 * A review of an item - for example, of a restaurant, movie, or store.
 *
 * @see http://schema.org/Review Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Review",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\HasLifecycleCallbacks()
 */
class Review
{
    use IdentifiableTrait
        , ThingTrait
        , TimestampableTrait
    ;

    /**
     * @var Thing|null the item that is being reviewed/rated
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Thing")
     * @ApiProperty(iri="http://schema.org/itemReviewed")
     */
    private $itemReviewed;

    /**
     * @var string|null the actual body of the review
     *
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/reviewBody")
     */
    private $reviewBody;

    /**
     * @var Rating|null The rating given in this review. Note that reviews can themselves be rated. The ```reviewRating``` applies to rating given by the review. The \[\[aggregateRating\]\] property applies to the review itself, as a creative work.
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Rating")
     * @ApiProperty(iri="http://schema.org/reviewRating")
     */
    private $reviewRating;

    public function setItemReviewed(?Thing $itemReviewed): void
    {
        $this->itemReviewed = $itemReviewed;
    }

    public function getItemReviewed(): ?Thing
    {
        return $this->itemReviewed;
    }

    public function setReviewBody(?string $reviewBody): void
    {
        $this->reviewBody = $reviewBody;
    }

    public function getReviewBody(): ?string
    {
        return $this->reviewBody;
    }

    public function setReviewRating(?Rating $reviewRating): void
    {
        $this->reviewRating = $reviewRating;
    }

    public function getReviewRating(): ?Rating
    {
        return $this->reviewRating;
    }
}
