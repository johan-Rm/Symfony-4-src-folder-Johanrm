<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\AdministrableTrait;


/**
 * A statement of the money due for goods or services; a bill.
 *
 * @see http://schema.org/Invoice Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Invoice",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 */
class Invoice
{
    use IdentifiableTrait
        , TimestampableTrait
        , AdministrableTrait
        // , ThingTrait
        // , PlaceTrait
    ;

    const TVA = 20;

    /**
     * @var Collection<Order>|null The Order(s) related to this Invoice. One or more Orders may be combined into a single Invoice.
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Order")
     * @ORM\JoinTable(inverseJoinColumns={@ORM\JoinColumn(unique=true)})
     * @ApiProperty(iri="http://schema.org/referencesOrder")
     */
    private $referencesOrders;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=15, nullable=true)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_lastname", type="string", length=255)
     *
     * @Assert\NotBlank(message="Enter a last name please")
     */
    private $customerLastname;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_firstname", type="string", length=255)
     *
     * @Assert\NotBlank(message="Enter a first name please")
     */
    private $customerFirstname;

    /**
     * @var string
     */
    private $customerFullName;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_address", type="string", length=255, nullable=true)
     */
    private $customerAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_postal_code", type="string", length=255, nullable=true)
     */
    private $customerPostalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_city", type="string", length=255, nullable=true)
     */
    private $customerCity;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_country", type="string", length=255, nullable=true)
     */
    private $customerCountry;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     * @Assert\NotBlank(message="Choose a date invoice please")
     */
    private $date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_due_date", type="datetime", nullable=true)
     */
    private $paymentDueDate;

    /**
     * @ORM\ManyToOne(targetEntity="PaymentMethod", inversedBy="invoices")
     * @ORM\JoinColumn(name="method_payment_id", referencedColumnName="id", nullable=true)
     *
     * @Assert\NotBlank(message="Choose a method please")
     */
    private $paymentMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_penalties", type="string", length=255, nullable=true)
     */
    private $paymentPenalties;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_modality", type="string", length=255, nullable=true)
     */
    private $paymentModality;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_method_id", type="string", length=255, nullable=true)
     */
    private $paymentMethodId;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_status", type="boolean")
     */
    private $paymentStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sale_date", type="datetime", nullable=true)
     */
    private $saleDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="scheduled_payment_date", type="datetime", nullable=true)
     */
    private $scheduledPaymentDate;

    /**
     * @var float
     *
     * @ORM\Column(name="total_without_taxes", type="float", nullable=true)
     */
    private $totalWithoutTaxes;

    /**
     * @var float
     *
     * @ORM\Column(name="total_payment_due", type="float", nullable=true)
     */
    private $totalPaymentDue;

    /**
     * @var text
     *
     * @ORM\Column(name="additional_info", type="text", nullable=true)
     */
    private $additionalInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name", type="string", length=255, nullable=true)
     */
    private $bankName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_code", type="string", length=255, nullable=true)
     */
    private $bankCode;

    /**
     * @var string
     *
     * @ORM\Column(name="account_number", type="string", length=255, nullable=true)
     */
    private $accountNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="iban_number", type="string", length=255, nullable=true)
     */
    private $ibanNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="swift_bic_number", type="string", length=255, nullable=true)
     */
    private $swiftBicNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="supplier_name", type="string", length=255, nullable=true)
     */
    private $supplierName;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceElement", mappedBy="invoice", cascade= { "remove" })
     */
    private $elements;

   /**
    * @ORM\ManyToOne(targetEntity="Person")
    * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true)
    */
   private $customer;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->elements = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getCustomerLastname();
    }

    /**
     * @return \DateTime
     */
    public function getPaymentDueDate()
    {
        return $this->paymentDueDate;
    }

    /**
     * @param \DateTime $paymentDueDate
     */
    public function setPaymentDueDate(\DateTime $paymentDueDate = null)
    {
        $this->paymentDueDate = $paymentDueDate;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return string
     */
    public function getPaymentMethodId()
    {
        return $this->paymentMethodId;
    }

    /**
     * @param string $paymentMethodId
     */
    public function setPaymentMethodId($paymentMethodId)
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    /**
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param string $paymentStatus
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * @return \DateTime
     */
    public function getScheduledPaymentDate()
    {
        return $this->scheduledPaymentDate;
    }

    /**
     * @param \DateTime $scheduledPaymentDate
     */
    public function setScheduledPaymentDate(\DateTime $scheduledPaymentDate = null)
    {
        $this->scheduledPaymentDate = $scheduledPaymentDate;
    }

    /**
     * @return string
     */
    public function getTotalPaymentDue()
    {
        return $this->totalPaymentDue;
    }

    /**
     * @param string $totalPaymentDue
     */
    public function setTotalPaymentDue($totalPaymentDue)
    {
        $this->totalPaymentDue = $totalPaymentDue;
    }

    /**
     * @return mixed
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param \App\Entity\InvoiceElement $element
     */
    public function addElement($element)
    {
      if ($this->elements->contains($element)) {
          return;
      }

      $element->addInvoice($this);
      $this->elements->add($element);
    }

    /**
    * @param \App\Entity\InvoiceElement $element
    */
    public function removeElement($element)
    {
     if (!$this->elements->contains($element)) {
         return;
     }

     $this->elements->removeElement($element);
     $element->removeInvoice($this);
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     *
     * @return \App\Entity\Person
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return string
     */
    public function getCustomerLastname()
    {
        return $this->customerLastname;
    }

    /**
     * @param string $customerLastname
     */
    public function setCustomerLastname($customerLastname)
    {
        $this->customerLastname = $customerLastname;
    }

    /**
     * @return string
     */
    public function getPaymentPenalties()
    {
        return $this->paymentPenalties;
    }

    /**
     * @param string $paymentPenalties
     */
    public function setPaymentPenalties($paymentPenalties)
    {
        $this->paymentPenalties = $paymentPenalties;
    }

    /**
     * @return string
     */
    public function getPaymentModality()
    {
        return $this->paymentModality;
    }

    /**
     * @param string $paymentModality
     */
    public function setPaymentModality($paymentModality)
    {
        $this->paymentModality = $paymentModality;
    }

    /**
     * @return \DateTime
     */
    public function getSaleDate()
    {
        return $this->saleDate;
    }

    /**
     * @param \DateTime $saleDate
     */
    public function setSaleDate(\DateTime $saleDate = null)
    {
        $this->saleDate = $saleDate;
    }

    /**
     * @return text
     */
    public function getAdditionalInfo()
    {
        return $this->additionalInfo;
    }

    /**
     * @param text $additionalInfo
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->additionalInfo = $additionalInfo;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * @param string $bankCode
     */
    public function setBankCode($bankCode)
    {
        $this->bankCode = $bankCode;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return string
     */
    public function getIbanNumber()
    {
        return $this->ibanNumber;
    }

    /**
     * @param string $ibanNumber
     */
    public function setIbanNumber($ibanNumber)
    {
        $this->ibanNumber = $ibanNumber;
    }

    /**
     * @return string
     */
    public function getSwiftBicNumber()
    {
        return $this->swiftBicNumber;
    }

    /**
     * @param string $swiftBicNumber
     */
    public function setSwiftBicNumber($swiftBicNumber)
    {
        $this->swiftBicNumber = $swiftBicNumber;
    }

    /**
     * @return float
     */
    public function getTotalWithoutTaxes()
    {
        return $this->totalWithoutTaxes;
    }

    /**
     * @param float $totalWithoutTaxes
     */
    public function setTotalWithoutTaxes($totalWithoutTaxes)
    {
        $this->totalWithoutTaxes = $totalWithoutTaxes;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getCustomerFirstname()
    {
        return $this->customerFirstname;
    }

    /**
     * @param string $customerFirstname
     */
    public function setCustomerFirstname($customerFirstname)
    {
        $this->customerFirstname = $customerFirstname;
    }

    /**
     * @return string
     */
    public function getCustomerAddress()
    {
        return $this->customerAddress;
    }

    /**
     * @param string $customerAddress
     */
    public function setCustomerAddress($customerAddress)
    {
        $this->customerAddress = $customerAddress;
    }

    /**
     * @return string
     */
    public function getCustomerPostalCode()
    {
        return $this->customerPostalCode;
    }

    /**
     * @param string $customerPostalCode
     */
    public function setCustomerPostalCode($customerPostalCode)
    {
        $this->customerPostalCode = $customerPostalCode;
    }

    /**
     * @return string
     */
    public function getCustomerCity()
    {
        return $this->customerCity;
    }

    /**
     * @param string $customerCity
     */
    public function setCustomerCity($customerCity)
    {
        $this->customerCity = $customerCity;
    }

    /**
     * @return string
     */
    public function getCustomerCountry()
    {
        return $this->customerCountry;
    }

    /**
     * @param string $customerCountry
     */
    public function setCustomerCountry($customerCountry)
    {
        $this->customerCountry = $customerCountry;
    }

    /**
     * @return string
     */
    public function getCustomerFullName()
    {
        return $this->customerLastname . ' ' . $this->customerFirstname;
    }

    /**
     * @return string
     */
    public function getSupplierName()
    {
        return $this->supplierName;
    }

    /**
     * @param string $supplierName
     */
    public function setSupplierName($supplierName)
    {
        $this->supplierName = $supplierName;
    }

    public function addReferencesOrder(Order $referencesOrder): void
    {
        $this->referencesOrders[] = $referencesOrder;
    }

    public function removeReferencesOrder(Order $referencesOrder): void
    {
        $this->referencesOrders->removeElement($referencesOrder);
    }

    public function getReferencesOrders(): Collection
    {
        return $this->referencesOrders;
    }
}
