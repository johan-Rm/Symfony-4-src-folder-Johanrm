<?php

namespace App\DataFixtures;

use App\Entity\Component;
use App\Entity\Message;
use App\Entity\PropertyValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;


class ComponentFixtures extends AbstractFixtures
{
    public function __construct()
    {
        
    }

    public function load(ObjectManager $manager)
    {
        if(!$this->empty) {
            $this->loadComponents($manager);
            $this->loadComponentInformations($manager);
            $this->loadComponentGoogleAnalytics($manager);
        }

    }

     private function loadComponentGoogleAnalytics($manager)
    {
        $setting = new Component();
        $setting->setName('Google Analytics');
        $setting->setMainEntity('http://schema.org/MyEntity');

        $propertyValue = new PropertyValue();
        $propertyValue->setName('Account');
        $propertyValue->setValue('UA-XXXXXXXX-X');
        $manager->persist($propertyValue);
        $setting->addPropertyValue($propertyValue);

        $manager->persist($setting);
        $manager->flush();
    }

    private function loadComponentInformations($manager)
    {
        $setting = new Component();
        $setting->setName('Informations');
        // $setting->setMainEntity('http://schema.org/MyEntity');

        $propertyValue = new PropertyValue();
        $propertyValue->setName('Nom du site');
        $propertyValue->setValue('L\'immobilière d\'Essaouira');
        $manager->persist($propertyValue);
        $setting->addPropertyValue($propertyValue);

        $propertyValue = new PropertyValue();
        $propertyValue->setName('Slogan');
        $propertyValue->setValue('L\'histoire d\'une passion');
        $manager->persist($propertyValue);
        $setting->addPropertyValue($propertyValue);

        $propertyValue = new PropertyValue();
        $propertyValue->setName('Email');
        $propertyValue->setValue('contact@immobiliere-essaouira.com');
        $manager->persist($propertyValue);
        $setting->addPropertyValue($propertyValue);

        $manager->persist($setting);
        $manager->flush();
    }
    
    public function loadComponents($manager)
    {
        $setting = new Component();
        $setting->setName('Contact form');
        $setting->setMainEntity('http://schema.org/Messsage');
        $setting->setService('form');
        $setting->setType('contact');


        // $propertyValue = new PropertyValue();
        // $propertyValue->setName('Name of the site');
        // $propertyValue->setValue('Graines Digitales');
        // $manager->persist($propertyValue);
        // $setting->addPropertyValue($propertyValue);

        // $propertyValue = new PropertyValue();
        // $propertyValue->setName('Slogan');
        // $propertyValue->setValue('Mon joli slogan');
        // $manager->persist($propertyValue);
        // $setting->addPropertyValue($propertyValue);

        $manager->persist($setting);
        $manager->flush();

        $setting = new Component();
        $setting->setName('Newsletter form');
        $setting->setMainEntity('http://schema.org/Messsage');
        $setting->setService('form');
        $setting->setType('newsletter');

        // $propertyValue = new PropertyValue();
        // $propertyValue->setName('Account');
        // $propertyValue->setValue('UA-XXXXXXXX-X');
        // $manager->persist($propertyValue);
        // $setting->addPropertyValue($propertyValue);

        $manager->persist($setting);
        $manager->flush();

        // $messages = $manager->getRepository(Message::class)->findAll();
        // $component = $manager->getRepository(Component::class)->findOneById(2);
        // foreach($messages as $message) {

        //     $message->setComponent($component);
        //     $manager->persist($message);

        // }

        $setting = new Component();
        $setting->setName('Related articles');
        $setting->setMainEntity('null');
        $setting->setService('list');
        $setting->setType('article');
        $manager->persist($setting);
        $manager->flush();

        $setting = new Component();
        $setting->setName('Welcome');
        $setting->setMainEntity('null');
        $setting->setService('article');
        $setting->setType('lagence');
        $manager->persist($setting);
        $manager->flush();

        $setting = new Component();
        $setting->setName('WhoWeAre');
        $setting->setMainEntity('null');
        $setting->setService('article');
        $setting->setType('nos-locaux');
        $manager->persist($setting);
        $manager->flush();
    }
}
