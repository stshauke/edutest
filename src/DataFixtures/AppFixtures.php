<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 👩‍🏫 Création de l’enseignant
        $enseignant = new User();
        $enseignant->setEmail('enseignant@demo.fr');
        $enseignant->setFullName('Professeur Démo');
        $enseignant->setRoles(['ROLE_TEACHER']);
        $enseignant->setPassword(
            $this->passwordHasher->hashPassword($enseignant, 'teacher123')
        );
        $manager->persist($enseignant);

        // 👨‍🎓 Création de l’étudiant
        $etudiant = new User();
        $etudiant->setEmail('etudiant@demo.fr');
        $etudiant->setFullName('Étudiant Démo');
        $etudiant->setRoles(['ROLE_STUDENT']);
        $etudiant->setPassword(
            $this->passwordHasher->hashPassword($etudiant, 'student123')
        );
        $manager->persist($etudiant);

        // ✅ Envoie les deux utilisateurs en base
        $manager->flush();
    }
}
