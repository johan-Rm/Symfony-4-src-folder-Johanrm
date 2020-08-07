<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\ThingTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\SluggableNameTrait;


/**
 * A property-value pair, e.g. representing a feature of a product or place. Use the 'name' property for the name of the property. If there is an additional human-readable version of the value, put that into the 'description' property.\\n\\n Always use specific schema.org properties when a) they exist and b) you can populate them. Using PropertyValue as a substitute will typically not trigger the same effect as using the original, specific property.
 *
 * @see http://schema.org/PropertyValue Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/PropertyValue",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\HasLifecycleCallbacks()
 */
class PropertyValue
{
    use IdentifiableTrait
        // , ThingTrait
        , SluggableNameTrait
        , TimestampableTrait
    ;

    /**
     * @var float|null The value of the quantitative value or property value node.\\n\\n\* For \[\[QuantitativeValue\]\] and \[\[MonetaryAmount\]\], the recommended type for values is 'Number'.\\n\* For \[\[PropertyValue\]\], it can be 'Text;', 'Number', 'Boolean', or 'StructuredValue'.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="http://schema.org/value")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Component", inversedBy="propertyValues")
     * @ORM\JoinColumn(name="component_id", referencedColumnName="id")
     */
    private $setting;

    public function __construct()
    {

    }

    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * Set the value of Value
     *
     * @return self
     */
    public function setValue(string $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of Value
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set setting
     *
     * @param \App\Entity\Setting $setting
     *
     * @return Setting
     */
    public function setSetting(\App\Entity\Component $setting = null)
    {
        $this->setting = $setting;

        return $this;
    }

    /**
     * Get Invoice
     *
     * @return \App\Entity\Setting
     */
    public function getSetting()
    {
        return $this->setting;
    }
    /**
    * Add Setting
    *
    * @param \App\Entity\Setting $setting
    *
    * @return Setting
    */
    public function addSetting(\App\Entity\Component $setting)
    {
       $this->setting = $setting;
    }

    /**
    * Remove Setting
    *
    * @param \App\Entity\Setting $setting
    */
    public function removeSetting(\App\Entity\Component $setting)
    {
       $this->setting = null;
    }

}
