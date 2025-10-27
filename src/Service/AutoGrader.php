<?php

namespace App\Service;

use App\Entity\Assignment;
use App\Entity\Answer;
use App\Entity\Choice;
use Doctrine\ORM\EntityManagerInterface;

class AutoGrader
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Corrige automatiquement toutes les réponses d’un Assignment.
     * Retourne une note finale sur 20.
     */
    public function grade(Assignment $assignment): float
    {
        $totalPoints = 0.0;
        $pointsObtained = 0.0;

        $exam = $assignment->getExam();
        $answers = $assignment->getAnswers();

        foreach ($exam->getQuestions() as $question) {
            $totalPoints += $question->getPoints();

            // 🔍 Trouve la réponse correspondante
            $answer = $this->em->getRepository(Answer::class)->findOneBy([
                'assignment' => $assignment,
                'question' => $question,
            ]);

            if (!$answer) {
                // Crée une réponse vide si elle n'existe pas
                $answer = new Answer();
                $answer->setAssignment($assignment);
                $answer->setQuestion($question);
                $answer->setAnswerText('');
                $this->em->persist($answer);
            }

            $points = 0.0;

            // ⚙️ Type de question : QCM
            if ($question->getType() === 'QCM') {
                $correctChoices = $question->getChoices()->filter(fn(Choice $c) => $c->isCorrect());
                $selectedChoices = $answer->getSelectedChoices();

                $nbCorrect = count($correctChoices);
                $nbGoodSelected = 0;

                foreach ($selectedChoices as $choice) {
                    if ($choice->isCorrect()) {
                        $nbGoodSelected++;
                    }
                }

                // ✅ Attribue les points proportionnellement
                if ($nbCorrect > 0) {
                    $points = $question->getPoints() * ($nbGoodSelected / $nbCorrect);
                }
            }

            // ⚙️ Type de question : Texte libre
            elseif ($question->getType() === 'TEXT') {
                // Pour l’instant, on ne peut pas noter automatiquement les réponses libres
                // => elles sont marquées 0 mais stockées pour correction manuelle
                $points = 0;
            }

            // 📝 Enregistre les points obtenus
            $answer->setPointsAwarded($points);
            $this->em->persist($answer);

            $pointsObtained += $points;
        }

        $this->em->flush();

        // ✅ Calcule la note sur 20
        $finalGrade = $totalPoints > 0 ? round(($pointsObtained / $totalPoints) * 20, 2) : 0.0;

        $assignment->setFinalGrade($finalGrade);
        $this->em->flush();

        return $finalGrade;
    }
}
