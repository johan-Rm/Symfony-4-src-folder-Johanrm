<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;


/**
 * An order is a confirmation of a transaction (a receipt), which can contain multiple line items, each represented by an Offer that has been accepted by the customer.
 *
 * @see http://schema.org/Order Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Order",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 */
class Order
{
    use IdentifiableTrait
    ;

    /**
     * @var string|null a number that confirms the given order or payment has been received
     *
     * @ORM\Column(type="text", nullable=true)
     * @ApiProperty(iri="http://schema.org/confirmationNumber")
     */
    private $confirmationNumber;

    public function setConfirmationNumber(?string $confirmationNumber): void
    {
        $this->confirmationNumber = $confirmationNumber;
    }

    public function getConfirmationNumber(): ?string
    {
        return $this->confirmationNumber;
    }
}
