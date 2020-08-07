<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
// use App\Entity\Traits\AdministrableTrait;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * InvoiceTracking
 *
 * @ORM\Table(name="invoice_tracking")
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/InvoiceTracking",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ ORM\Entity(repositoryClass="App\Repository\InvoiceTrackingRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class InvoiceTracking
{
    use IdentifiableTrait,
        TimestampableTrait
//        AdministrableTrait
        ;


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="invoiceDate", type="datetime")
     * @ Assert\NotBlank(message="Choose a date invoice please")
     */
    private $invoiceDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paymentDate", type="datetime", nullable=true)
     */
    private $paymentDate;

    /**
     * @ORM\ManyToOne(targetEntity="PaymentMethod", inversedBy="invoices")
     * @ORM\JoinColumn(name="method_payment_id", referencedColumnName="id", nullable=true)
     *
     * @ Assert\NotBlank(message="Choose a method please")
     */
    private $paymentMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="float", nullable=true)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="trackings")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;


    /**
     * Constructor
     */
    public function __construct()
    {

    }

    public function __toString()
    {

        return $this->getName() . ' - '
            . $this->getPaymentDate()->format('Y-m-d')
            . ' => ' . $this->getAmount()
        ;
    }

    /**
     * Set Event
     *
     * @param \App\Entity\Event $event
     *
     * @return Event
     */
    public function setEvent(\App\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get Event
     *
     * @return \App\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Add Event
     *
     * @param \App\Entity\Event $event
     *
     * @return Event
     */
    public function addEvent(\App\Entity\Event $event)
    {
        $this->event = $event;
    }

    /**
     * Remove Event
     *
     * @param \App\Entity\Event $event
     */
    public function removeEvent(\App\Entity\Event $event)
    {
        $this->event = null;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }


    /**
     * Set the value of Invoice Date
     *
     * @param \DateTime invoiceDate
     *
     * @return self
     */
    public function setInvoiceDate(\DateTime $invoiceDate)
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    /**
     * Get the value of Invoice Date
     *
     * @return \DateTime
     */
    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    /**
     * Set the value of Payment Date
     *
     * @param \DateTime paymentDate
     *
     * @return self
     */
    public function setPaymentDate(\DateTime $paymentDate)
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    /**
     * Get the value of Payment Date
     *
     * @return \DateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * Set the value of Payment Method
     *
     * @param mixed paymentMethod
     *
     * @return self
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get the value of Payment Method
     *
     * @return mixed
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }


    /**
     * Set the value of Amount
     *
     * @param string amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of Amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }


    /**
     * Set the value of Name
     *
     * @param string name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
