<?php

namespace App\Controller;

use App\Entity\Exam;
use App\Entity\Assignment;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_TEACHER')]
#[Route('/teacher')]
class TeacherDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'teacher_dashboard')]
    public function index(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        // 🧮 Repositories
        $examRepo = $em->getRepository(Exam::class);
        $assignRepo = $em->getRepository(Assignment::class);

        // 🔹 Pagination des examens
        $query = $examRepo->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
            ->getQuery();

        $exams = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // 10 examens par page
        );

        // 📊 Récupération des affectations
        $assignments = $assignRepo->findAll();
        $totalAssignments = count($assignments);

        // 🔹 Calcul du taux de soumission
        $submitted = count(array_filter($assignments, fn($a) => $a->getStatus() === 'SUBMITTED'));
        $submissionRate = $totalAssignments > 0
            ? round(($submitted / $totalAssignments) * 100, 2)
            : 0;

        // 👩‍🎓 Nombre d’étudiants distincts ayant rendu au moins un examen
        $uniqueStudentsCount = (int) $em->createQueryBuilder()
            ->select('COUNT(DISTINCT a.student)')
            ->from(Assignment::class, 'a')
            ->where('a.status = :status')
            ->setParameter('status', 'SUBMITTED')
            ->getQuery()
            ->getSingleScalarResult();

        // 📈 Statistiques par examen
        $stats = [];
        foreach ($exams as $exam) {
            $examAssignments = array_filter(
                $assignments,
                fn($a) => $a->getExam() === $exam && $a->getFinalGrade() !== null
            );

            $grades = array_map(fn($a) => $a->getFinalGrade(), $examAssignments);

            $mean = !empty($grades) ? array_sum($grades) / count($grades) : null;
            $std = null;

            if ($mean !== null && count($grades) > 1) {
                $variance = array_sum(array_map(fn($g) => pow($g - $mean, 2), $grades)) / count($grades);
                $std = round(sqrt($variance), 2);
            }

            $stats[] = [
                'exam' => $exam,
                'count' => count($examAssignments),
                'mean' => $mean !== null ? round($mean, 2) : null,
                'std'  => $std,
            ];
        }

        // 🧾 Rendu de la vue
        return $this->render('teacher/dashboard.html.twig', [
            'exams' => $exams,
            'totalAssignments' => $totalAssignments,
            'submissionRate' => $submissionRate,
            'uniqueStudentsCount' => $uniqueStudentsCount,
            'stats' => $stats,
        ]);
    }
}
