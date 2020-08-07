<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Organization;
use App\Entity\RealEstateAgent;
use App\Entity\Address;
use App\Entity\Person;
use App\Entity\MediaObject;


class SettingFixtures extends AbstractFixtures
{
    public function load(ObjectManager $manager)
    {
        $this->loadOrganization($manager);
        $this->loadRealEstateAgents($manager);
    }

    private function loadOrganization($manager)
    {
        $address = new Address();
        $address->setAddress('802-01 Lot Azlef');
        $address->setCity('Essaouira');
        $address->setPostcode('44000');
        $address->setCountry('Maroc');
        $manager->persist($address);
        $manager->flush();


        $organization = new Organization();
        $image1 = $manager->getRepository(MediaObject::class)->findOneById(2);
        $image2 = $manager->getRepository(MediaObject::class)->findOneById(3);
        $organization->setPrimaryImage($image1);
        $organization->setSecondaryImage($image2);
        $organization->setName('L\'immobilière d\'Essaouira');
        $organization->setLegalName('SARL Natastef Maroc');
        $organization->setPhone('+212(0) 524 785 823');
        $organization->setUrl('www.immobiliere-essaouira.com');
        $organization->setEmail('contact@immobiliere-essaouira.com');
        $date = new \DateTime('2004-10-04');
        $organization->setFoundingDate($date);
        $organization->setNumberOfEmployees('2');
        $organization->setNumberOfProjects('50');
        $organization->addAddress($address);
        $manager->persist($organization);
        $manager->flush();
    }

    private function loadRealEstateAgents($manager)
    {
        
        $person = new Person();
        $person->setLastname('LAURENT-SCHOPPE');
        $person->setFirstname('Natacha');
        // $person->setGender();
        // $person->setBirthday();
        // $person->setPlaceOfBirth();
        $person->setPhone('+212 (0) 673 256 389');
        $person->setEmail('contact@immobiliere-essaouira.com');
        // $person->setComment();
        // $person->addAddress();
        $person->setOrigin('employé interne');
        $manager->persist($person);
        $manager->flush();
        $photo = $manager->getRepository(MediaObject::class)->findOneById(8);
        $realEstateAgent = new RealEstateAgent();
        $realEstateAgent->setPrimaryImage($photo);
        $realEstateAgent->setPerson($person);
        $realEstateAgent->setPhone('+212673256389');
        $realEstateAgent->setEmail('natacha.schoppe@immobiliere-essaouira.com');
        $realEstateAgent->setDescription('Directrice d\'agence');
        $manager->persist($realEstateAgent);
        $manager->flush();

        $person = new Person();
        $person->setLastname('LAURENT');
        $person->setFirstname('Stéphane');
        // $person->setGender();
        // $person->setBirthday();
        // $person->setPlaceOfBirth();
        $person->setPhone('+212 (0) 673 018 201');
        $person->setEmail('contact@immobiliere-essaouira.com');
        // $person->setComment();
        // $person->addAddress();
        $person->setOrigin('employé interne');
        $manager->persist($person);
        $manager->flush();
        $photo = $manager->getRepository(MediaObject::class)->findOneById(9);
        $realEstateAgent = new RealEstateAgent();
        $realEstateAgent->setPrimaryImage($photo);
        $realEstateAgent->setPerson($person);
        $realEstateAgent->setPhone('+212673018201');
        $realEstateAgent->setEmail('stephane.laurent@immobiliere-essaouira.com');
        $realEstateAgent->setDescription('Directeur d’agence, Responsable transactions et suivis de chantiers');
        $manager->persist($realEstateAgent);
        $manager->flush();



        $person = new Person();
        $person->setLastname('TIBARI');
        $person->setFirstname('Irina');
        // $person->setGender();
        // $person->setBirthday();
        // $person->setPlaceOfBirth();
        $person->setPhone('+212 (0) 524 785 823');
        $person->setEmail('irina.essaouira@gmail.com');
        // $person->setComment();
        // $person->addAddress();
        $person->setOrigin('employé interne');
        $manager->persist($person);
        $manager->flush();
        $photo = $manager->getRepository(MediaObject::class)->findOneById(7);
        $realEstateAgent = new RealEstateAgent();
        $realEstateAgent->setPrimaryImage($photo);
        $realEstateAgent->setPerson($person);
        $realEstateAgent->setPhone('+212607855935');
        $realEstateAgent->setEmail('irina.tibari@immobiliere-essaouira.com');
        $realEstateAgent->setDescription('Directeur d\'agence, Responsable transactions et suivis de chantiers');
        $manager->persist($realEstateAgent);
        $manager->flush();


        $person = new Person();
        $person->setLastname('BOURGEAUX');
        $person->setFirstname('Grégory');
        // $person->setGender();
        // $person->setBirthday();
        // $person->setPlaceOfBirth();
        $person->setPhone('+212(0) 672 839 714 ');
        $person->setEmail('greg.essaouira@gmail.com');
        // $person->setComment();
        // $person->addAddress();
        $person->setOrigin('employé interne');
        $manager->persist($person);
        $manager->flush();
        $photo = $manager->getRepository(MediaObject::class)->findOneById(10);
        $realEstateAgent = new RealEstateAgent();
        $realEstateAgent->setPrimaryImage($photo);
        $realEstateAgent->setPerson($person);
        $realEstateAgent->setPhone('+212672839714');
        $realEstateAgent->setEmail('greg.bourgeaux@immobiliere-essaouira.com');
        $realEstateAgent->setDescription('Négociateur en locations longues durées, chargé d\'accueil clientèle saisonnière');
        $manager->persist($realEstateAgent);
        $manager->flush();

        // dump($organization);
        // die();

        $person = new Person();
        $person->setLastname('HAFIANE');
        $person->setFirstname('Selma');
        // $person->setGender();
        // $person->setBirthday();
        // $person->setPlaceOfBirth();
        $person->setPhone('');
        $person->setEmail('');
        // $person->setComment();
        // $person->addAddress();
        $person->setOrigin('');
        $manager->persist($person);
        $manager->flush();

        $person = new Person();
        $person->setLastname('REMY');
        $person->setFirstname('Johan');
        // $person->setGender();
        // $person->setBirthday();
        // $person->setPlaceOfBirth();
        $person->setPhone('');
        $person->setEmail('');
        // $person->setComment();
        // $person->addAddress();
        $person->setOrigin('');
        $manager->persist($person);
        $manager->flush();

        $person = new Person();
        $person->setLastname('HANINI');
        $person->setFirstname('Mohamed');
        // $person->setGender();
        // $person->setBirthday();
        // $person->setPlaceOfBirth();
        $person->setPhone('');
        $person->setEmail('');
        // $person->setComment();
        // $person->addAddress();
        $person->setOrigin('');
        $manager->persist($person);
        $manager->flush();
    }
}
