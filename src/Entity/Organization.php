<?php

declare(strict_types=1);

namespace App\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\AdministrableTrait;


/**
 * An organization such as a school, NGO, corporation, club, etc.
 *
 * @see http://schema.org/Organization Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Organization",
 *    attributes={
 *      "normalization_context" = {
 *          "groups"= {
 *               "thing"
 *               , "identifier"
 *               , "media"
 *               , "organization"
 *               , "addresse"
 *      
 *         },"datetime_format"="Y-m-d"
 *      },
 *    },
 *    collectionOperations={ 
 *         "get"={ 
 *             "method"="GET"    
 *         },
 *         "post"={ 
 *             "access_control"="has_role('ROLE_SUPER_ADMIN')",
 *             "security_message": "Only admins can add organizations."
 *         } 
 *    },
 *    itemOperations={ 
 *        "get"={ 
 *            "method"="GET"
 *         },
 *        "put"={
 *            "method"="PUT",
 *            "access_control"="has_role('ROLE_ADMIN')",
 *            "security_post_denormalize_message": "Sorry, but you are not the actual organization owner." 
 *        }
 *    }
 *  )
 * @ORM\HasLifecycleCallbacks()
 * @ApiFilter(SearchFilter::class, properties={ "type.slug": "exact" })
 */
class Organization
{
    use IdentifiableTrait
        , ThingTrait
        , TimestampableTrait
        , AdministrableTrait
    ;

    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(length=128)
     * @Groups("organization")
     */
    private $slug;

     /**
      * @var string
      *
      * @ORM\Column(name="name", type="string", length=255)
      *
      * @Groups("organization")
      */
     private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="legal_name", type="string", length=255, nullable=true)
     *
     * @Groups("organization")
     */
    private $legalName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, options={"comment":"Phone"}, nullable=true)
     *
     * @ Assert\NotBlank(
     *  message="Please enter your phone number"
     * )
     * @ Assert\Regex(
     *  pattern="/^(0)[0-9]{9}$/",
     *  match=true,
     *  message="Your phone number is invalid"
     * )
     *
     * @Groups("organization")
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, options={"comment":"Email"}, nullable=true)
     * @ Assert\NotBlank(
     *      message="Please enter an email"
     * )
     * @ Assert\Email(
     *      message = "Your email is invalid"
     * )
     *
     * @Groups("organization")
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="foundingDate", type="date", nullable=true)
     *
     * @Groups("organization")
     */
    private $foundingDate;

    /**
    * @ORM\ManyToMany(targetEntity="Address", inversedBy="organizations", cascade= { "persist"})
    * @ORM\JoinTable(
    *  name="organizations_addresses",
    *  joinColumns={
    *      @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
    *  },
    *  inverseJoinColumns={
    *      @ORM\JoinColumn(name="address_id", referencedColumnName="id")
    *  }
    *
    * )
    * @Groups("organization")
    **/
    private $addresses;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_of_employees", type="integer", nullable=true)
     *
     * @Groups("organization")
     */
    private $numberOfEmployees;

    /**
    * @ORM\ManyToOne(targetEntity="\App\Entity\OrganizationType")
    * @ORM\JoinColumn(name="organization_type_id", referencedColumnName="id")
    *
    * @Groups("organization")
    * @ApiSubresource
    */
    private $type;

    /**
     * @var MediaObject|null indicates the main image on the page
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     * @ApiProperty(iri="http://schema.org/primaryImage")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups("organization")
     * @ApiSubresource
     */
    private $primaryImage;

    /**
     * @var MediaObject|null indicates the main image on the page
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject")
     * @ApiProperty(iri="http://schema.org/primaryImage")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups("organization")
     * @ApiSubresource
     */
    private $secondaryImage;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @Groups("organization")
     */
    private $numberOfProjects;
    
    
    public function getSlug()
    {
        return $this->slug;
    }
    
    public function setPrimaryImage(?\App\Entity\MediaObject $primaryImage): void
    {
        $this->primaryImage = $primaryImage;
    }

    public function getPrimaryImage(): ?\App\Entity\MediaObject
    {
        return $this->primaryImage;
    }

    public function setSecondaryImage(?\App\Entity\MediaObject $secondaryImage): void
    {
        $this->secondaryImage = $secondaryImage;
    }

    public function getSecondaryImage(): ?\App\Entity\MediaObject
    {
        return $this->secondaryImage;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
      $this->addresses = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
     {
         return $this->getName();
     }

     /**
      * Set name
      *
      * @param string $name
      *
      * @return Company
      */
     public function setName($name)
     {
         $this->name = $name;

         return $this;
     }

     /**
      * Get name
      *
      * @return string
      */
     public function getName()
     {
         return $this->name;
     }

    /**
     * @return string
     */
    public function getLegalName()
    {
        return $this->legalName;
    }

    /**
     * @param string $legalName
     */
    public function setLegalName($legalName)
    {
        $this->legalName = $legalName;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Add address
     *
     * @param \AppBundle\Entity\Address $address
     *
     * @return Person
     */
    public function addAddress($address)
    {
        if ($this->addresses->contains($address)) {
            return;
        }

        $this->addresses->add($address);
    }

    /**
     * Remove address
     *
     * @param \AppBundle\Entity\Address $address
     */
    public function removeAddress($address)
    {
        if (!$this->addresses->contains($address)) {
            return;
        }

        $this->addresses->removeElement($address);
    }

    /**
     * Get addresses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @return int
     */
    public function getNumberOfEmployees()
    {
        return $this->numberOfEmployees;
    }

    /**
     * @param int $numberOfEmployees
     */
    public function setNumberOfEmployees($numberOfEmployees)
    {
        $this->numberOfEmployees = $numberOfEmployees;
    }

    /**
     * Set the value of Type
     *
     * @param array type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of Type
     *
     * @return array
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \DateTime
     */
    public function getFoundingDate()
    {
        return $this->foundingDate;
    }

    /**
     * @param \DateTime $foundingDate
     */
    public function setFoundingDate($foundingDate)
    {
        $this->foundingDate = $foundingDate;
    }

    public function getNumberOfProjects(): ?int
    {
        return $this->numberOfProjects;
    }

    public function setNumberOfProjects(?int $numberOfProjects): self
    {
        $this->numberOfProjects = $numberOfProjects;

        return $this;
    }
}
