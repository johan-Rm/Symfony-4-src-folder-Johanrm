<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
// use App\Entity\Traits\AdministrableTrait;

/**
 * InvoiceElement
 *
 * @ORM\Table(name="invoice_element")
 * @ORM\Entity
 * @ ORM\Entity(repositoryClass="App\Repository\InvoiceElementRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class InvoiceElement
{
    use IdentifiableTrait,
        TimestampableTrait
//        AdministrableTrait
        ;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", length=3)
     */
    private $unit;

    /**
     * @var float
     *
     * @ORM\Column(name="unitPrice", type="float")
     */
    private $unitPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="totalWithoutTaxes", type="float", nullable=true)
     */
    private $totalWithoutTaxes;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="elements")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id")
     */
    private $invoice;

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    public function __toString()
    {
        return $this->getDescription() . ' - ' . $this->getQuantity() . ' x ' . $this->getUnitPrice() . ' €';
    }

    /**
     * Set Invoice
     *
     * @param \App\Entity\Invoice $invoice
     *
     * @return Invoice
     */
    public function setInvoice(\App\Entity\Invoice $invoice = null)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get Invoice
     *
     * @return \App\Entity\Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Add Invoice
     *
     * @param \App\Entity\Invoice $invoice
     *
     * @return Invoice
     */
    public function addInvoice(\App\Entity\Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Remove Invoice
     *
     * @param \App\Entity\Invoice $invoice
     */
    public function removeInvoice(\App\Entity\Invoice $invoice)
    {
        $this->invoice = null;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param float $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * @return string
     */
    public function getTotalWithoutTaxes()
    {
        return $this->totalWithoutTaxes;
    }

    /**
     * @param string $totalWithoutTaxes
     */
    public function setTotalWithoutTaxes($totalWithoutTaxes)
    {
        $this->totalWithoutTaxes = $totalWithoutTaxes;
    }

    /**
     * @ORM\PreFlush()
     */
    // public function preUpload()
    // {
    //     $this->totalWithoutTaxes = $this->unitPrice * $this->unit;
    // }

    /**
     * @ORM\PrePersist()
     *
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        // dump($this->unitPrice);
        // dump($this->quantity);
        // dump($this->unitPrice * $this->quantity);die();
        $this->totalWithoutTaxes = $this->unitPrice * $this->quantity;
    }
}
