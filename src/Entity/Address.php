<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\Person;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
use Symfony\Component\Serializer\Annotation\Groups;
// use AppBundle\Entity\Traits\AdministrableTrait;


/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity
 * @ ORM\Entity(repositoryClass="App\Repository\AddressRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Address
{
    use IdentifiableTrait,
        TimestampableTrait
//        AdministrableTrait
    ;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Groups("addresse")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     * @Groups("addresse")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=255)
     * @Groups("addresse")
     */
    private $postcode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255)
     * @Groups("addresse")
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     * @Groups("addresse")
     */
    private $country;

    /**
     * @ORM\ManyToMany(targetEntity="Person", mappedBy="addresses")
     */
    private $persons;

    /**
     * @ORM\ManyToMany(targetEntity="Organization", mappedBy="addresses")
     */
    private $organizations;

    /**
     * Constructor
     */
    public function __construct()
    {
      $this->persons = new \Doctrine\Common\Collections\ArrayCollection();
      $this->organizations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getFullAddress();
    }

    public function getFullAddress()
    {
        return trim($this->getAddress() . ' ' . $this->getPostcode() . ' ' . $this->getCity() . ' ' . $this->getCountry());
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Address
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
     * Set city
     *
     * @param string $city
     *
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
    * @param Person $person
    */
   public function addPerson($person)
   {
       if ($this->persons->contains($person)) {
           return;
       }
       $this->persons->add($person);
       $person->addAddress($this);
   }
   /**
    * @param Person $person
    */
   public function removePerson($person)
   {
       if (!$this->persons->contains($person)) {
           return;
       }
       $this->persons->removeElement($person);
       $person->removeAddress($this);
   }

   /**
   * @param Organization $organization
   */
  public function addOrganization($organization)
  {
      if ($this->organizations->contains($organization)) {
          return;
      }
      $this->organizations->add($organization);
      $organization->addOrganization($this);
  }

  /**
   * @param Organization $organization
   */
  public function removeOrganization($organization)
  {
      if (!$this->organizations->contains($organization)) {
          return;
      }
      $this->organizations->removeElement($organization);
      $organization->removeOrganization($this);
  }


    /**
     * Set the value of Persons
     *
     * @param mixed persons
     *
     * @return self
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;

        return $this;
    }

    /**
     * Get the value of Persons
     *
     * @return mixed
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * Set the value of Organizations
     *
     * @param mixed organizations
     *
     * @return self
     */
    public function setOrganizations($organizations)
    {
        $this->organizations = $organizations;

        return $this;
    }

    /**
     * Get the value of Organizations
     *
     * @return mixed
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }

}
