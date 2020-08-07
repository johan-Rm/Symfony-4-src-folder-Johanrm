<?php

namespace App\Listeners;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\Article;
use App\Entity\WebPage;
use App\Entity\Accommodation;
use App\Entity\Organization;
use Doctrine\Common\Util\Inflector;


/**
 * MetaBaliseListener
 */
class MetaBaliseListener
{
    private $rules;
    private $orm;
    private $organization;

    public function __construct(EntityManagerInterface $orm)
    {
        $this->orm = $orm;
        $this->rules = array(
            'accommodation' => array(
                'location' => array(),
                'vente' => array()
            )
        );
        $this->organization = $this->orm->getRepository(Organization::class)->findOneById(1);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // return false car j utilise un service maintenant
        return false;

        if ($entity instanceof Accommodation ) {

            if(null !== $entity->getNature() 
                && 'location' == $entity->getNature()->getSlug()
            ) {
                $metaTitle = substr($this->buildLocationMetaTitle($entity), 0, 70);
                $entity->setMetaTitle($metaTitle);
                $metaDescription = substr($this->buildLocationMetaDescription($entity), 0, 160);
                $entity->setMetaDescription($metaDescription);
            } else if(null !== $entity->getNature() 
                && 'vente' == $entity->getNature()->getSlug()
            ) {
                $metaTitle = substr($this->buildVenteMetaTitle($entity), 0, 70);
                $entity->setMetaTitle($metaTitle);
                $metaDescription = substr($this->buildVenteMetaDescription($entity), 0, 160);
                $entity->setMetaDescription($metaDescription);
            }

            if(empty($entity->getHeadline())) {
                $entity->setHeadline($entity->getMetaTitle());    
            }
        }

        if ($entity instanceof Article) {
            $metaTitle = substr($entity->getHeadline(), 0, 70);
            $entity->setMetaTitle($metaTitle);
            $metaDescription = substr($entity->getArticleResume(), 0, 160);
            $entity->setMetaDescription($metaDescription);
        }

        if ($entity instanceof WebPage) {
            $metaTitle = substr($entity->getHeadline(), 0, 70);
            $entity->setMetaTitle($metaTitle);
            $metaDescription = substr($entity->getAlternativeHeadline(), 0, 160);
            $entity->setMetaDescription($metaDescription);
        }
        
        return; 
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // return false car j utilise un service maintenant
        return false;
        
        if ($entity instanceof Accommodation ) {
        
            if(null !== $entity->getNature() 
                && 'location' == $entity->getNature()->getSlug()
            ) {
                $metaTitle = substr($this->buildLocationMetaTitle($entity), 0, 70);
                $entity->setMetaTitle($metaTitle);
                $metaDescription = substr($this->buildLocationMetaDescription($entity), 0, 160);
                $entity->setMetaDescription($metaDescription);
                
            } else if(null !== $entity->getNature() 
                && 'vente' == $entity->getNature()->getSlug()
            ) {
                $metaTitle = substr($this->buildVenteMetaTitle($entity), 0, 70);
                $entity->setMetaTitle($metaTitle);
                $metaDescription = substr($this->buildVenteMetaDescription($entity), 0, 160);
                $entity->setMetaDescription($metaDescription);
            }

            if(empty($entity->getHeadline())) {
                $entity->setHeadline($entity->getMetaTitle());    
            }
        }

        if ($entity instanceof Article) {
            $metaTitle = substr($entity->getHeadline(), 0, 70);
            $entity->setMetaTitle($metaTitle);
            $metaDescription = substr($entity->getPushForward(), 0, 160);
            $entity->setMetaDescription($metaDescription);
        }

        if ($entity instanceof WebPage) {
            $metaTitle = substr($entity->getHeadline(), 0, 70);
            $entity->setMetaTitle($metaTitle);
            $metaDescription = substr($entity->getPushForward(), 0, 160);
            $entity->setMetaDescription($metaDescription);
        }
        
        return;
    }

    private function buildLocationMetaTitle($entity) 
    {
        // Location appartement, 25.47 m² T-1 à Aurillac, 369 € | Orpi
        $metaTitle = ucfirst(
            $entity->getNature()->getName()
        );
        if(null !== $entity->getType()->getName()) {
            $metaTitle.= ' - ' . ucfirst(
                $entity->getType()->getName()
            );
        }
        if(null !== $entity->getFloorSize()) {
            $metaTitle.= ' - ' . $entity->getFloorSize() . ' m²';
        }
        if(null !== $entity->getPlace()->getName()) {
            $metaTitle.= ' - ' . Inflector::ucwords($entity->getPlace()->getName());
        }
        if(null !== $entity->getPrice()) {
            $metaTitle.= ' - ' . $entity->getPrice() . ' €';
        }

        return $metaTitle;
    }

    private function buildLocationMetaDescription($entity) 
    {
        // Appartement, 25.47 m² T-1 à louer à Aurillac pour 369 € avec Orpi
        $metaDescription = ucfirst(
            $entity->getType()->getName()
        );
        
        if(null !== $entity->getFloorSize()) {
            $metaDescription.= ', ' . $entity->getFloorSize() . ' m²';
        }
        $metaDescription. ' à louer';
        if(null !== $entity->getPlace()->getName()) {
            $metaDescription.= ' à ' . Inflector::ucwords(
                $entity->getPlace()->getName()
            );
        }
        if(null !== $entity->getPrice()) {
            $metaDescription.= ' pour ' . $entity->getPrice() . ' €';
        }
        if(null !== $this->organization) {
            $metaDescription.= ' avec ' . $this->organization->getName();
        }
        

        return $metaDescription;
    }

     private function buildVenteMetaTitle($entity) 
    {
        // Appartement Marseille 13 40 m² T-2 à vendre, 100 000 € | Orpi
        $metaTitle = ucfirst(
            $entity->getType()->getName()
        );
        if(null !== $entity->getPlace()->getName()) {
            $metaTitle.= ' - ' . Inflector::ucwords($entity->getPlace()->getName());
        }
        if(null !== $entity->getFloorSize()) {
            $metaTitle.= ' - ' . $entity->getFloorSize() . ' m²';
        }
        if(null !== $entity->getPrice()) {
            $metaTitle.= ' - ' . $entity->getPrice() . ' €';
        }

        return $metaTitle;
    }

    private function buildVenteMetaDescription($entity) 
    {
        // Appartement, 40 m² T-2 à acheter à Marseille 13 pour 100000 € avec Orpi
        $metaDescription = ucfirst(
            $entity->getType()->getName()
        );
        if(null !== $entity->getFloorSize()) {
            $metaDescription.= ', ' . $entity->getFloorSize() . ' m²';
        }
        $metaDescription. ' à acheter';
        if(null !== $entity->getPlace()->getName()) {
            $metaDescription.= ' à ' . Inflector::ucwords(
                $entity->getPlace()->getName()
            );
        }
        if(null !== $entity->getPrice()) {
            $metaDescription.= ' pour ' . $entity->getPrice() . ' €';
        }
        if(null !== $this->organization) {
            $metaDescription.= ' avec ' . $this->organization->getName();
        }

        return $metaDescription;
    }
}
