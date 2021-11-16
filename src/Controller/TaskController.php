<?php

namespace App\Controller;

use DateTime;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    /**
     * @var TaskRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(TaskRepository $repository, EntityManagerInterface $manager)
    {
        $this->repository = $repository;
        $this->manager = $manager;
    }

    /**
     * @Route("/task/listing", name="task_listing")
     */
    public function index(): Response
    {

        // On va chercher avec Doctrine
        $repository = $this->getDoctrine()->getRepository(Task::class);

        // Dans ce repository nous récupérons toutes les données
        $tasks = $repository->findAll();

        // Affichage des données

        // var_dump($tasks);
        // die;

        //dd($tasks);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route ("/task/create", name="task_create")
     */
    public function createTask(Request $request)
    {
        $task = new Task;

        $task->setCreatedAt(new \DateTime());

        $form = $this->createForm(TaskType::class, $task, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($task);
            $this->manager->flush();

            return $this->redirectToRoute(('task_listing'));
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/task/update/{id}", name="task_update", requirements={"id"="\d+"})
     */
    public function updateTask(int $id, Request $request): Response
    {
        $task = $this->repository->find($id);

        $form = $this->createForm(TaskType::class, $task, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $this->manager->persist($task);
            $this->manager->flush();

            return $this->redirectToRoute(('task_update'));
        }
        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /**
     * @Route ("/task/delete/{id}", name="delete", requirements={"id"="\d+"})
     */
    public function deleteTask(int $id, Request $request)
    {
        $task = $this->getDoctrine()->getRepository(Task::class)->find($id);

        $form = $this->createForm(TaskType::class, $task, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($task);
            $manager->flush();

            return $this->redirectToRoute(('task_delete'));
        }
        return $this->render('task/delete.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
