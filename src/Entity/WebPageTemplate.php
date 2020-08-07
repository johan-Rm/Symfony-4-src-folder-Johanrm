<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdentifiableTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\SluggableNameTrait;
use App\Entity\Traits\AdministrableTrait;


/**
 * Entities that have a somewhat fixed, physical extension.
 *
 * @see http://schema.org/Place Documentation on Schema.org
 *
 * @ORM\Entity
 * @ApiResource(iri="http://schema.org/WebPageTemplate",
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 *  )
 * @ORM\Table(name="webpage_template")
 * @ ORM\Entity(repositoryClass="App\Repository\WebPageTemplateRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class WebPageTemplate
{
    use IdentifiableTrait
        , TimestampableTrait
        , SluggableNameTrait
        , AdministrableTrait
    ;

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
}
