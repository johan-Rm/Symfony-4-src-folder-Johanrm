<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends AbstractFixtures
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $person = $manager->getRepository(Person::class)->findOneById(6);

        // php bin/console fos:user:change-password testuser iep@ssword
        $user = new User();
        $user->setUsername(sprintf('johan.remy'));
        $user->setEmail(sprintf('johan.remy@graines-digitales.online'));
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            sprintf('iep@ssword')
        ));
        $user->setRoles(array('ROLE_SUPER_ADMIN'));
        $user->setEnabled(true);
        $user->setPerson($person);
        $manager->persist($user);
        $manager->flush();

        $person = $manager->getRepository(Person::class)->findOneById(7);
        $user = new User();
        $user->setUsername(sprintf('mohamed.hanini'));
        $user->setEmail(sprintf('mohamed.hanini95@gmail.com'));
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            sprintf('iep@ssword')
        ));
        $user->setPerson($person);
        $user->setRoles(array('ROLE_ADMIN'));
        $user->setEnabled(true);
        $manager->persist($user);
        $manager->flush();
        $person = $manager->getRepository(Person::class)->findOneById(4);
        $user = new User();
        $user->setUsername(sprintf('greg.bourgeaux'));
        $user->setEmail(sprintf('greg4777@gmail.com'));
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            sprintf('iep@ssword')
        ));
        $user->setPerson($person);
        $user->setRoles(array('ROLE_ADMIN'));
        $user->setEnabled(true);
        $manager->persist($user);
        $manager->flush();

        $person = $manager->getRepository(Person::class)->findOneById(2);
        $user = new User();
        $user->setUsername(sprintf('stephane.laurent'));
        $user->setEmail(sprintf('stephane.laurent@immobiliere-essaouira.com'));
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            sprintf('iep@ssword')
        ));
        $user->setPerson($person);
        $user->setRoles(array('ROLE_SUPER_ADMIN'));
        $user->setEnabled(true);
        $manager->persist($user);
        $manager->flush();

        $person = $manager->getRepository(Person::class)->findOneById(1);
        $user = new User();
        $user->setUsername(sprintf('natacha.laurent'));
        $user->setEmail(sprintf('natastefessaouira@gmail.com'));
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            sprintf('iep@ssword')
        ));
        $user->setPerson($person);
        $user->setRoles(array('ROLE_SUPER_ADMIN'));
        $user->setEnabled(true);
        $manager->persist($user);
        $manager->flush();

        $person = $manager->getRepository(Person::class)->findOneById(3);
        $user = new User();
        $user->setUsername(sprintf('irina.tibari'));
        $user->setEmail(sprintf('irina.essaouira@gmail.com'));
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            sprintf('iep@ssword')
        ));
        $user->setPerson($person);
        $user->setRoles(array('ROLE_SUPER_ADMIN'));
        $user->setEnabled(true);
        $manager->persist($user);
        $manager->flush();

        $person = $manager->getRepository(Person::class)->findOneById(5);
        $user = new User();
        $user->setUsername(sprintf('selma.hafiane'));
        $user->setEmail(sprintf('selma.essaouira@gmail.com'));
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            sprintf('iep@ssword')
        ));
        $user->setPerson($person);
        $user->setRoles(array('ROLE_SUPER_ADMIN'));
        $user->setEnabled(true);
        $manager->persist($user);
        $manager->flush();

    }
}
