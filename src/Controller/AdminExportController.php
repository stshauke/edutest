<?php

namespace App\Controller;

use App\Entity\Exam;
use League\Csv\Writer;
use Mpdf\Mpdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour l’exportation des examens (CSV / PDF)
 * Accessible aux enseignants ET aux administrateurs
 */
#[Route('/admin')]
class AdminExportController extends AbstractController
{
    /**
     * 🧾 Export des résultats au format CSV
     */
    #[Route('/exam/{id}/export/csv', name: 'admin_exam_export_csv')]
    public function exportCsv(Exam $exam): StreamedResponse
    {
        // 🔒 Autorisation : enseignant OU admin
        if (!$this->isGranted('ROLE_TEACHER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        return new StreamedResponse(function() use ($exam) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            $csv->insertOne(['Étudiant', 'Email', 'Note (/20)', 'Soumis le']);

            // Parcours des affectations liées à l’examen
            foreach ($exam->getAssignments() as $assignment) {
                $student = $assignment->getStudent();
                $csv->insertOne([
                    $student?->getFullName() ?? '',
                    $student?->getEmail() ?? '',
                    $assignment->getFinalGrade() ?? '',
                    $assignment->getSubmittedAt()?->format('Y-m-d H:i') ?? '',
                ]);
            }

            echo (string) $csv;
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="exam_'.$exam->getId().'.csv"',
        ]);
    }

    /**
     * 📄 Export des résultats au format PDF
     */
    #[Route('/exam/{id}/export/pdf', name: 'admin_exam_export_pdf')]
    public function exportPdf(Exam $exam): Response
    {
        // 🔒 Autorisation : enseignant OU admin
        if (!$this->isGranted('ROLE_TEACHER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        // Génération du HTML avec Twig
        $html = $this->renderView('admin/export_pdf.html.twig', [
            'exam' => $exam,
            'assignments' => $exam->getAssignments(),
        ]);

        // Création du PDF avec mPDF
        $mpdf = new Mpdf([
            'default_font_size' => 10,
            'default_font' => 'dejavusans',
        ]);

        $mpdf->WriteHTML($html);

        // Sortie du PDF dans le navigateur
        return new Response($mpdf->Output("exam_{$exam->getId()}.pdf", 'I'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
