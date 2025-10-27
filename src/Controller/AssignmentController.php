<?php

namespace App\Controller;

use App\Entity\Assignment;
use App\Entity\Exam;
use App\Entity\User;
use App\Form\AssignmentType;
use App\Repository\AssignmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
#[Route('/teacher/assignment')]
final class AssignmentController extends AbstractController
{
    #[Route('/', name: 'teacher_assignment_index', methods: ['GET'])]
    public function index(AssignmentRepository $assignmentRepository): Response
    {
        return $this->render('assignment/index.html.twig', [
            'assignments' => $assignmentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'teacher_assignment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $assignment = new Assignment();

        $form = $this->createForm(AssignmentType::class, $assignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assignment->setStatus('ASSIGNED');
            $assignment->setAssignedAt(new \DateTimeImmutable());

            $em->persist($assignment);
            $em->flush();

            $this->addFlash('success', '✅ Examen affecté à l’étudiant avec succès.');

            return $this->redirectToRoute('teacher_assignment_index');
        }

        return $this->render('assignment/new.html.twig', [
            'assignment' => $assignment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'teacher_assignment_show', methods: ['GET'])]
    public function show(Assignment $assignment): Response
    {
        return $this->render('assignment/show.html.twig', [
            'assignment' => $assignment,
        ]);
    }

    #[Route('/{id}/delete', name: 'teacher_assignment_delete', methods: ['POST'])]
    public function delete(Request $request, Assignment $assignment, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $assignment->getId(), $request->request->get('_token'))) {
            $em->remove($assignment);
            $em->flush();
            $this->addFlash('info', '🗑️ Affectation supprimée avec succès.');
        }

        return $this->redirectToRoute('teacher_assignment_index');
    }
}
