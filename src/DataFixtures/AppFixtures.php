<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Task;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $faker = Factory::create("fr_FR");

        // Création entre 15 et 30 tâches aléatoirement 
        for ($t = 0; $t < mt_rand(12, 30); $t++) {
            // Création d'un nouvel objet task 
            $task = new Task;

            // On nourrit l'objet Task
            $task->setName($faker->sentence(6))
                ->setDescription($faker->paragraph(3))
                ->setCreatedAt(new \DateTime()) // Attention les dates
                ->setDueAt($faker->dateTimeBetween("now", "6 months"));

            // On fait persister les données
            $manager->persist($task);
        }

        $manager->flush();
    }
}
