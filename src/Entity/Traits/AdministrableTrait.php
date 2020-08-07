<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;



trait AdministrableTrait
{
    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\User")
     * @ORM\JoinColumn(name="user_created_id", referencedColumnName="id")
     * @Groups("admin")
     * @ ApiSubresource
     */
    private $userCreated;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\User")
     * @ORM\JoinColumn(name="user_last_modified_id", referencedColumnName="id")
     */
    private $userLastModified;


    /**
     * Get user
     *
     * @return \App\Entity\User
     */
    public function getUserCreated()
    {
        return $this->userCreated;
    }

    /**
     * Set userCreated
     *
     * @param \App\Entity\User $userCreated
     */
    public function setUserCreated(\App\Entity\User $userCreated, $force = false)
    {
        if(null === $this->userCreated || true === $force) {
            $this->userCreated = $userCreated;
        }
    }

    /**
     * Get user
     *
     * @return \App\Entity\User
     */
    public function getUserLastModified()
    {
        return $this->userLastModified;
    }

    /**
     * Set userLastModified
     *
     * @param \App\Entity\User $userLastModified
     */
    public function setUserLastModified(\App\Entity\User $userLastModified)
    {
        $this->userLastModified = $userLastModified;
    }


}
