<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\SluggableNameTrait;
// use App\Entity\Traits\AdministrableTrait;


/**
 * Gender
 *
 * @ORM\Table(name="gender")
 * @ORM\Entity
 * @ ORM\Entity(repositoryClass="App\Repository\GenderRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Gender
{
    use IdentifiableTrait
        // , ThingTrait
        , TimestampableTrait
        , SluggableNameTrait
        // AdministrableTrait
        ;

    /**
     * @ORM\OneToMany(targetEntity="Person", mappedBy="gender")
     */
    private $persons;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->getName();
    }


    /**
     * @return mixed
     */
    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @param mixed $persons
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;
    }

}
