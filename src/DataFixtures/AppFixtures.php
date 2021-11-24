<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordHasherInterface
     */
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $faker = Factory::create("fr_FR");

        for ($u = 0; $u < 5; $u++) {

            //Création d'un nouvel objet User
            $user = new User;

            // Hashage de notre mot de passe avec les paramètres de sécurité de notre $user
            // dans /config/packages/security.yaml

            $hash = $this->encoder->hashPassword($user, "password");
            $user->setPassword($hash);

            // Si premier utilisateur crée on lui donne le rôle d'admin

            if ($u === 0) {
                $user->setRoles(["ROLE_ADMIN"])
                    ->setEmail("admin@admin.fr");
            } else {
                $user->setEmail($faker->safeEmail());
            }

            // On fait persister les données

            $manager->persist($user);
        }

        $manager->flush();

        // Création de 5 catégories
        for ($c = 0; $c < 5; $c++) {
            // Création d'un nouvel objet tag
            $tag = new Tag;
            $tag->setName($faker->colorName());

            // On fait persister les données
            $manager->persist($tag);
        }
        // On push les catégories en BDD
        $manager->flush();
        $tags = $manager->getRepository(Tag::class)->findAll();

        $listeUsers = $manager->getRepository(User::class)->findAll();
        // Création entre 15 et 30 tâches aléatoirement 
        for ($t = 0; $t < mt_rand(12, 30); $t++) {
            // Création d'un nouvel objet task 
            $task = new Task;

            // On nourrit l'objet Task
            $task->setName($faker->sentence(6))
                ->setDescription($faker->paragraph(3))
                ->setCreatedAt(new \DateTime()) // Attention les dates
                ->setDueAt($faker->dateTimeBetween("now", "6 months"))
                ->setTag($faker->randomElement($tags))
                ->setUser($faker->randomElement($listeUsers));

            // On fait persister les données
            $manager->persist($task);
        }
        $manager->flush();
    }
}
