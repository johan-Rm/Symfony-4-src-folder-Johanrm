<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\CreativeWorkTrait;
use App\Entity\Traits\TimestampableTrait;
// use App\Entity\Traits\AdministrableTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiSubresource;



/**
 * A single message from a sender to one or more organizations or people.
 *
 * @see http://schema.org/Message Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Message",
 *  attributes={  
 *      "jsonld_embed_context"=true,
 *      "normalization_context"={"groups"={"message", "person", "real-estate-agent"}},
 *      "denormalization_context"={"groups"={"message", "person", "real-estate-agent"}}
 *  },
 *  collectionOperations={
 *      "get"={
 *          "method"="GET"
 *      },
 *      "post"
 *  },
 *  itemOperations={
 *      "get"={
 *          "method"="GET"
 *      }
 *  }
 *  )
 * @ORM\HasLifecycleCallbacks()
 */
class Message
{
    use IdentifiableTrait
        // , ThingTrait
        // , CreativeWorkTrait
        , TimestampableTrait
        // , AdministrableTrait
    ;

    /**
     * @var \DateTimeInterface|null the date/time at which the message was sent
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @ApiProperty(iri="http://schema.org/dateSent")
     * @ Assert\DateTime
     * @Groups("message")
     */
    private $dateSent;

    /**
     * @var CreativeWork|null a CreativeWork attached to the message
     *
     * @ ORM\ManyToOne(targetEntity="App\Entity\CreativeWork")
     * @ApiProperty(iri="http://schema.org/messageAttachment")
     * @Groups("message")
     */
    private $messageAttachment;

    /**
     * @var Organization|null A sub property of participant. The participant who is at the receiving end of the action.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\RealEstateAgent")
     * @ ORM\Column(nullable=true)
     * @ApiProperty(iri="http://schema.org/recipient")
     * @Groups("message")
     * @ApiSubresource
     */
    private $recipient;

    /**
     * @var Organization|null A sub property of participant. The participant who is at the sending end of the action.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Person")
     * @ ORM\Column(nullable=true)
     * @ApiProperty(iri="http://schema.org/sender")
     * @Groups("message")
     * @ ApiSubresource
     */
    private $sender;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups("message")
     */
    private $subject;

    /**
     * @var string|null the textual content of this CreativeWork
     *
     * @ORM\Column(type="text")
     * @ApiProperty(iri="http://schema.org/text")
     * @Groups("message")
     */
    private $text;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("message")
     */
    private $origin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("message")
     */
    private $entity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("message")
     */
    private $identifier;


    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setDateSent(?\DateTimeInterface $dateSent): void
    {
        $this->dateSent = $dateSent;
    }

    public function getDateSent(): ?\DateTimeInterface
    {
        return $this->dateSent;
    }

    public function setMessageAttachment(?CreativeWork $messageAttachment): void
    {
        $this->messageAttachment = $messageAttachment;
    }

    public function getMessageAttachment(): ?CreativeWork
    {
        return $this->messageAttachment;
    }

    public function setRecipient(?RealEstateAgent $recipient): void
    {
        $this->recipient = $recipient;
    }

    public function getRecipient(): ?RealEstateAgent
    {
        return $this->recipient;
    }

    public function setSender(?Person $sender): void
    {
        $this->sender = $sender;
    }

    public function getSender(): ?Person
    {
        return $this->sender;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(?string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }


    /**
     * Get the value of Entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of Entity
     *
     * @param mixed entity
     *
     * @return self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get the value of Identifier
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set the value of Identifier
     *
     * @param mixed identifier
     *
     * @return self
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

}
