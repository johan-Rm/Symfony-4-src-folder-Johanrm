<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;


/**
 * Entities that have a somewhat fixed, physical extension.
 *
 * @see http://schema.org/? Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/?",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\Entity(repositoryClass="App\Repository\TranslationRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Translation
{
    use IdentifiableTrait
        // , TimestampableTrait
    ;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5)
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $hashKey;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $fieldName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $entityName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $entityId;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $isLocked;

    /**
     * @var text
     *
     * @ORM\Column(type="text")
     */
    private $keyLeft;

    /**
     * @var text
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $valueRight;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $translatedEntityName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $translatedFieldName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isHtml = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $keySlug;

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    public function __toString()
    {
        return $this->getKeyLeft();
    }

    /**
     * Get the value of Lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Get the value of Lock
     *
     * @return boolean
     */
    public function getIsLocked()
    {
        return $this->isLocked;
    }

    /**
     * Get the value of left
     *
     * @return text
     */
    public function getKeyLeft()
    {
        return $this->keyLeft;
    }

    /**
     * Get the value of Right
     *
     * @return text
     */
    public function getValueRight()
    {
        return $this->valueRight;
    }

    /**
     * Get the value of entityName
     *
     * @return text
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Get the value of entityId
     *
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Get the value of fieldName
     *
     * @return text
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set the value of Lang
     *
     * @param string lang
     *
     * @return self
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Set the value of field
     *
     * @param string fieldName
     *
     * @return self
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * Set the value of entityName
     *
     * @param string entityName
     *
     * @return self
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Set the value of entityId
     *
     * @param string entityId
     *
     * @return self
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Set the value of isLocked
     *
     * @param boolean isLocked
     *
     * @return self
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Set the value of Left
     *
     * @param text left
     *
     * @return self
     */
    public function setKeyLeft($keyLeft)
    {
        $this->keyLeft = $keyLeft;

        return $this;
    }

    /**
     * Set the value of $right
     *
     * @param text $right
     *
     * @return self
     */
    public function setValueRight($valueRight)
    {
        $this->valueRight = $valueRight;

        return $this;
    }


    /**
     * Set the value of HashKey
     *
     * @param string hashKey
     *
     * @return self
     */
    public function setHashKey($hashKey)
    {
        $this->hashKey = $hashKey;

        return $this;
    }

    /**
     * Get the value of HashKey
     *
     * @return string
     */
    public function getHashKey()
    {
        return $this->hashKey;
    }

    public function getTranslatedEntityName(): ?string
    {
        return $this->translatedEntityName;
    }

    public function setTranslatedEntityName(?string $translatedEntityName): self
    {
        $this->translatedEntityName = $translatedEntityName;

        return $this;
    }

    public function getTranslatedFieldName(): ?string
    {
        return $this->translatedFieldName;
    }

    public function setTranslatedFieldName(?string $translatedFieldName): self
    {
        $this->translatedFieldName = $translatedFieldName;

        return $this;
    }

    public function getIsHtml(): ?bool
    {
        return $this->isHtml;
    }

    public function setIsHtml(bool $isHtml): self
    {
        $this->isHtml = $isHtml;

        return $this;
    }

    public function getKeySlug(): ?string
    {
        return $this->keySlug;
    }

    public function setKeySlug(?string $keySlug): self
    {
        $this->keySlug = $keySlug;

        return $this;
    }

}
