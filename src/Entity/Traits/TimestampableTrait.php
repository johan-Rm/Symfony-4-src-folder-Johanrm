<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;


trait TimestampableTrait
{

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Groups("timestamp")
     *
     */
    protected $dateCreated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modified", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * @Groups("timestamp")
     *
     */
    protected $dateModified;

    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * @param \DateTime $dateModified
     */
    public function setDateModified(\DateTime $dateModified)
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    /**
     * @ORM\PreFlush()
     */
    public function preUpload()
    {
        $this->dateModified = new \DateTime();
    }

    /**
     * @ORM\PrePersist()
     *
     */
    public function prePersist()
    {
        $this->dateCreated = new \DateTime();
        $this->dateModified = new \DateTime();
    }
}
