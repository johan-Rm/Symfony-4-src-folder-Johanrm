<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\AdministrableTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiFilter;



/**
 * A person (alive, dead, undead, or fictional).
 *
 * @see http://schema.org/Person Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/Person",
 *  attributes={
 *      "normalization_context"={"groups"={"thing", "person"}}
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
 * @ApiFilter(SearchFilter::class, properties={ "firstname": "exact", "lastname": "exact", "email": "exact" })
 * @ORM\HasLifecycleCallbacks()
 */
class Person
{
    use IdentifiableTrait
        , ThingTrait
        , TimestampableTrait
        , AdministrableTrait
    ;


    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true, options={"comment":"Firstname"})
     *
     * @Assert\NotBlank(
     *  message="Enter a first name please"
     * )
     * @Groups("person")
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, options={"comment":"Lastname"})
     * @Assert\NotBlank(
     *  message="Enter a name please"
     * )
     * @Groups("person")
     */
    private $lastname;

    /**
     * @ORM\ManyToOne(targetEntity="Gender", inversedBy="persons")
     * @ORM\JoinColumn(name="gender_id", referencedColumnName="id", nullable=true)
     *
     * @ Assert\NotBlank(message="Enter a gender please")
     * @Groups("person")
     */
    private $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date" , options={"comment":"Birthday"}, nullable=true)
     * @Groups("person")
     */
    private $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="place_of_birth", type="string", length=255, options={"comment":"Place of birth"}, nullable=true)
     * @Groups("person")
     */
    private $placeOfBirth;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, options={"comment":"Phone"}, nullable=true)
     *
     * @Assert\Expression(
     *     "this.getEmail() || this.getPhone()",
     *     message="Please, enter email or phone"
     * )
     *
     * @ Assert\Regex(
     *  pattern="/^(0)[0-9]{9}$/",
     *  match=true,
     *  message="This phone number is invalid"
     * )
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, options={"comment":"Email"}, nullable=true)
     *
     *
     * @Assert\Email(
     *  message = "This email is invalid"
     * )
     */
    private $email;


    /**
    * @ORM\ManyToMany(targetEntity="Address", inversedBy="persons", cascade= {"persist"})
    * @ORM\JoinTable(
    *  name="persons_addresses",
    *  joinColumns={
    *      @ORM\JoinColumn(name="person_id", referencedColumnName="id")
    *  },
    *  inverseJoinColumns={
    *      @ORM\JoinColumn(name="address_id", referencedColumnName="id")
    *  }
    * )
    **/
    private $addresses;

    /**
    * @ORM\ManyToMany(targetEntity="Accommodation", inversedBy="persons", cascade= {"persist"})
    * @ORM\JoinTable(
    *  name="persons_accommodations",
    *  joinColumns={
    *      @ORM\JoinColumn(name="person_id", referencedColumnName="id")
    *  },
    *  inverseJoinColumns={
    *      @ORM\JoinColumn(name="accommodation_id", referencedColumnName="id")
    *  }
    * )
    **/
    private $teams;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="person")
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="Accommodation", mappedBy="person")
     */
    private $accommodations;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $origin;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $customer_traffic = [];

    /**
     * Many Pdfs
     * @ORM\ManyToMany(targetEntity="\App\Entity\DocumentObject", inversedBy="pdfsPersons", cascade={"persist"})
     * @ORM\JoinTable(name="persons_pdfs")
     *
     * @Groups("person")
     * @ApiSubresource
     */
    private $pdfs;

    /**
    * @ORM\ManyToOne(targetEntity="\App\Entity\PersonPosition")
    * @ORM\JoinColumn(name="position_id", referencedColumnName="id")
    *
    * @Groups("person")
    * @ ApiSubresource
    */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("person")
     */
    private $informations;


    /**
    * @ORM\ManyToOne(targetEntity="\App\Entity\PersonNature")
    * @ORM\JoinColumn(name="nature_id", referencedColumnName="id")
    *
    * @Groups("person")
    * @ApiSubresource
    */
    private $prospect;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->pdfs = new ArrayCollection();
        $this->teams = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getFullName();
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $informations
     */
    public function setInformations($informations)
    {
        $this->informations = $informations;
    }

    /**
     * @return string
     */
    public function getInformations()
    {
        return $this->informations;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function getFullName()
    {
        return trim($this->getFirstName().' '.$this->getLastName());
    }

    public function setFullName($fullName)
    {
        $names = explode(' ', $fullName);
        $firstName = array_shift($names);
        $lastName = implode(' ', $names);

        $this->setFirstName($firstName);
        $this->setLastName($lastName);
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return string
     */
    public function getPlaceOfBirth()
    {
        return $this->placeOfBirth;
    }

    /**
     * @param string $placeOfBirth
     */
    public function setPlaceOfBirth($placeOfBirth)
    {
        $this->placeOfBirth = $placeOfBirth;
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
     * Set comment
     *
     * @param string $comment
     *
     * @return Appointment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
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
     * Add accommodations
     *
     * @param \App\Entity\Accommodation $accommodation
     *
     * @return Person
     */
    public function addAccommodation($accommodation)
    {
        $this->accommodations[] = $accommodation;
    }

    /**
     * Remove accommodations
     *
     * @param \App\Entity\Accommodation $accommodation
     */
    public function removeAccommodation($accommodation)
    {
        $this->accommodations->removeElement($accommodation);
    }

    /**
     * Get accommodations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccommodations()
    {
        return $this->accommodations;
    }

    /**
     * Add Event
     *
     * @param \App\Entity\Event $event
     *
     * @return Person
     */
    public function addEvent($event)
    {
        $this->events[] = $event;
    }

    /**
     * Remove appointment
     *
     * @param \App\Entity\Event $event
     */
    public function removeEvent($event)
    {
        $this->events->removeElement($event);
    }

    /**
     * Get appointments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
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

    public function getCustomerTraffic(): ?array
    {
        return $this->customer_traffic;
    }

    public function setCustomerTraffic(?array $customer_traffic): self
    {
        $this->customer_traffic = $customer_traffic;

        return $this;
    }

     /**
     * Get pdfs
     *
     * @return Collection|Accommodation[]
     */
    public function getPdfs(): Collection
    {
        return $this->pdfs;
    }

    /**
     * Set the value of Pdfs
     *
     * @param mixed pdfs
     *
     * @return self
     */
    public function setPdfs($pdfs)
    {
        $this->pdfs = $pdfs;

        return $this;
    }

    /**
     * Add pdf
     *
     * @param \App\Entity\DocumentObject $pdf
     *
     * @return Accommodation
     */
    public function addPdf(\App\Entity\DocumentObject $pdf)
    {
        if ($this->pdfs->contains($pdf)) {
            return;
        }
        $this->pdfs->add($pdf);
        $pdf->addPdfsPerson($this);

        return $this;
    }

    /**
     * Remove pdf
     *
     * @param \AppBundle\Entity\DocumentObject $pdf
     *
     * @return Accommodation
     */
    public function removePdf(\App\Entity\DocumentObject $pdf): self
    {
        if ($this->pdfs->contains($pdf)) {
            $this->pdfs->removeElement($pdf);
        }

        return $this;
    }

    /**
     * Add accommodation
     *
     * @param \AppBundle\Entity\Accommodation $accommodation
     *
     * @return Person
     */
    public function addTeam($accommodation)
    {
        if ($this->teams->contains($accommodation)) {
            return;
        }

        $this->teams->add($accommodation);
    }

    /**
     * Remove accommodation
     *
     * @param \AppBundle\Entity\Accommodation $accommodation
     */
    public function removeTeam($accommodation)
    {
        if (!$this->teams->contains($accommodation)) {
            return;
        }

        $this->teams->removeElement($accommodation);
    }

    /**
     * Get teams
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTeams()
    {
        return $this->teams;
    }

    /**
     * Set the value of prospect
     *
     * @param mixed prospect
     *
     * @return self
     */
    public function setProspect($prospect)
    {
        $this->prospect = $prospect;

        return $this;
    }

    /**
     * Get the value of prospect
     *
     * @return mixed
     */
    public function getProspect()
    {
        return $this->prospect;
    }


    /**
     * Set the value of Addresses
     *
     * @param mixed addresses
     *
     * @return self
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * Set the value of Teams
     *
     * @param mixed teams
     *
     * @return self
     */
    public function setTeams($teams)
    {
        $this->teams = $teams;

        return $this;
    }

    /**
     * Set the value of Events
     *
     * @param mixed events
     *
     * @return self
     */
    public function setEvents($events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Set the value of Accommodations
     *
     * @param mixed accommodations
     *
     * @return self
     */
    public function setAccommodations($accommodations)
    {
        $this->accommodations = $accommodations;

        return $this;
    }

}
