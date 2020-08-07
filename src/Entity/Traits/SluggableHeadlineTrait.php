<?php

namespace App\Entity\Traits;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

trait SluggableHeadlineTrait
{
    /**
     * @Gedmo\Slug(fields={"headline"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    public function getSlug()
    {
        return $this->slug;
    }
}
