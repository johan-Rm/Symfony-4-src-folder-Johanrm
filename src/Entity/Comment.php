<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Article;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\CreativeWorkTrait;
use App\Entity\Traits\TimestampableTrait;
// use App\Entity\Traits\AdministrableTrait;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * A comment on an item - for example, a comment on a blog post. The comment's content is expressed via the \[\[text\]\] property, and its topic via \[\[about\]\], properties shared with all CreativeWorks.
 *
 * @see http://schema.org/Comment Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Comment",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\HasLifecycleCallbacks()
 */
class Comment
{
    use IdentifiableTrait
        , ThingTrait
        , CreativeWorkTrait
        , TimestampableTrait
        // , AdministrableTrait
    ;

    /**
     * @var int|null the number of downvotes this question, answer or comment has received from the community
     *
     * @ORM\Column(type="integer", nullable=true)
     * @ApiProperty(iri="http://schema.org/downvoteCount")
     */
    private $downvoteCount;

    /**
     * @var int|null the number of upvotes this question, answer or comment has received from the community
     *
     * @ORM\Column(type="integer", nullable=true)
     * @ApiProperty(iri="http://schema.org/upvoteCount")
     */
    private $upvoteCount;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="comments")
     */
    private $article;

    public function __toString()
    {
      return $this->getText();
    }

    public function setDownvoteCount(?int $downvoteCount): void
    {
        $this->downvoteCount = $downvoteCount;
    }

    public function getDownvoteCount(): ?int
    {
        return $this->downvoteCount;
    }

    public function setUpvoteCount(?int $upvoteCount): void
    {
        $this->upvoteCount = $upvoteCount;
    }

    public function getUpvoteCount(): ?int
    {
        return $this->upvoteCount;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }
}
