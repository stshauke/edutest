<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Exam;
use App\Entity\Assignment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $userRepo = $em->getRepository(User::class);
        $examRepo = $em->getRepository(Exam::class);
        $assignRepo = $em->getRepository(Assignment::class);

        $teachers = $userRepo->findByRole('ROLE_TEACHER');
        $students = $userRepo->findByRole('ROLE_STUDENT');
        $exams = $examRepo->findAll();
        $assignments = $assignRepo->findAll();

        // 📊 Statistiques globales
        $totalTeachers = count($teachers);
        $totalStudents = count($students);
        $totalExams = count($exams);
        $submitted = count(array_filter($assignments, fn($a) => $a->getStatus() === 'SUBMITTED'));
        $submissionRate = count($assignments) > 0 ? round(($submitted / count($assignments)) * 100, 1) : 0;

        // 📈 Données pour le graphique Chart.js
        $chartData = [
            'labels' => ['Enseignants', 'Étudiants', 'Examens', 'Soumissions'],
            'values' => [$totalTeachers, $totalStudents, $totalExams, $submissionRate],
        ];

        // 🧑‍💻 Création utilisateur rapide (optionnelle)
        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email'));
            $fullName = trim($request->request->get('fullName'));
            $password = trim($request->request->get('password'));
            $role = $request->request->get('role');

            if (!$email || !$password || !$role) {
                $this->addFlash('danger', 'Veuillez remplir tous les champs.');
            } elseif ($userRepo->findOneBy(['email' => $email])) {
                $this->addFlash('warning', 'Un compte avec cet email existe déjà.');
            } else {
                $user = new User();
                $user->setEmail($email);
                $user->setFullName($fullName ?: 'Utilisateur');
                $user->setRoles([$role]);
                $user->setPassword($hasher->hashPassword($user, $password));
                $user->setIsApproved(true);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', '✅ Utilisateur créé avec succès !');
                return $this->redirectToRoute('admin_dashboard');
            }
        }

        return $this->render('admin/dashboard.html.twig', [
            'teachers' => $teachers,
            'students' => $students,
            'exams' => $exams,
            'totalTeachers' => $totalTeachers,
            'totalStudents' => $totalStudents,
            'totalExams' => $totalExams,
            'submissionRate' => $submissionRate,
            'chartData' => $chartData,
        ]);
    }

    #[Route('/approve/{id}', name: 'admin_approve_user')]
    public function approveUser(User $user, EntityManagerInterface $em): Response
    {
        if (!$user->isApproved()) {
            $user->setIsApproved(true);
            $em->flush();
            $this->addFlash('success', sprintf('✅ %s a été approuvé.', $user->getFullName()));
        } else {
            $this->addFlash('info', 'Cet utilisateur est déjà approuvé.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/ban/{id}', name: 'admin_ban_user')]
    public function banUser(User $user, EntityManagerInterface $em): Response
    {
        $user->setIsApproved(false);
        $em->flush();
        $this->addFlash('warning', sprintf('🚫 %s a été désactivé.', $user->getFullName()));

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/delete/{id}', name: 'admin_delete_user')]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $name = $user->getFullName();
        $em->remove($user);
        $em->flush();

        $this->addFlash('danger', sprintf('🗑️ L’utilisateur "%s" a été supprimé avec succès.', $name));
        return $this->redirectToRoute('admin_dashboard');
    }
}
