<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
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
        $user = $this->getUser();
        $role = $user->getRoles();
        $id = $user->getId();
        $admin = "ROLE_ADMIN";
        $slug = $user->getIsPrefered();
        // dd($user);

        if (in_array($admin, $role)) {
            $tasks = $this->repository->findBy(array('isArchived' => '0'));
        } else {
            $tasks = $this->repository->findBy(array(
                'isArchived' => '0',
                'user' => $id
            ));
        }
        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
            'slug' => $slug,
        ]);
    }

    /**
     * @Route("/task/archives", name="task_archives")
     */
    public function indexArchives(): Response
    {
        $user = $this->getUser();
        $role = $user->getRoles();
        $id = $user->getId();
        $admin = "ROLE_ADMIN";
        // dd($user);

        if (in_array($admin, $role)) {
            $tasks = $this->repository->findBy(array('isArchived' => '1'));
        } else {
            $tasks = $this->repository->findBy(array(
                'isArchived' => '1',
                'user' => $id
            ));
        }
        return $this->render('task/archives.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route ("/task/create", name="task_create")
     * @Route ("/task/update/{id}", name="task_update", requirements={"id"="\d+"})
     */
    public function task(Task $task = null, Request $request)
    {
        if (!$task) {
            $task = new Task;

            $task->setCreatedAt(new \DateTime());

            $user = $this->getUser();
            $task->setUser($user);
        }



        $form = $this->createForm(TaskType::class, $task, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($task);
            $this->manager->flush();

            $this->addFlash(
                "success",
                "L'action a bien ??t?? effectu??e"
            );
            return $this->redirectToRoute(('task_listing'));
        }

        return $this->render('task/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route ("/task/delete/{id}", name = "task_delete", requirements = {"id"="\d+"})
     */
    public function deleteTask(Task $task): Response
    {
        $this->manager->remove($task);
        $this->manager->flush();

        $this->addFlash(
            "success",
            "La suppression a bien ??t?? effectu??e"
        );

        return $this->redirectToRoute("task_listing");
    }

    /**
     * Undocumented function
     *
     * @Route ("/task/listing/download", name="task_download")
     */
    public function downloadPdf()
    {
        $tasks = $this->repository->findAll();
        $pdfoption = new Options;
        $pdfoption->set('defaultFont', 'Arial');
        //$pdfoption->setIsRemoteEnabled(true);

        // On instancie DOMPDF
        $dompdf = new Dompdf($pdfoption);

        $html = $this->renderView('pdf/pdfdownload.html.twig', [
            'tasks' => $tasks,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fichier = 'J\'adore les pdf';
        $dompdf->stream($fichier, [
            'Attachement' => true
        ]);

        return new Response();
    }

    // V??rifie si la date ??ffective de la t??che est pass??e ou non
    public function checkDueAt(Task $task)
    {
        $flag = false;
        $dueAt = $task->getDueAt();
        $today = new \DateTime();

        if ($today > $dueAt) {
            $flag = true;
        }
        return $flag;
    }

    /**
     * @Route("task/archive/{id}", name="task_archive", requirements={"id"="\d+"})
     * @return Response
     */
    public function archiveTask(Task $task): Response
    {
        if ($this->checkDueAt($task)) {
            $task->setIsArchived(1);
            $this->manager->persist($task);
            $this->manager->flush();
            $this->addFlash(
                'success',
                'La t??che a bien ??t?? archiv??e.'
            );
        } else {
            $this->addFlash(
                'warning',
                'Impossible d\'archiver une t??che dont l\'??ch??ance n\'a pas eu lieu'
            );
        }
        return $this->redirectToRoute("task_listing");
    }

    /**
     * @Route("/calendar", name="task_calendar", methods={"GET"})
     */
    public function calendar(): Response
    {
        return $this->render('task/index.html.twig');
    }

    /**
     *@Route ("/task/archives_{slug}")
     */
    public function displayTable(String $slug)
    {
        //  R??cup??ration des infos de l'utilisateur.
        $user = $this->getUser();


        if ($slug != 'manual') {
            $tasks = $this->repository->findAll();
            $user->setIsPrefered(0);
            for ($i = 0; $i < count($tasks); $i++) {
                if ($this->checkDueAt($tasks[$i])) {
                    $this->archiveTask($tasks[$i]);
                }
            }
        } else {
            $user->setIsPrefered(1);
        }
        $this->manager->persist($user);
        $this->manager->flush();

        return $this->index();
    }
}
