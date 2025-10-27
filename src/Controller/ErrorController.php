<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class ErrorController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/error/{code}', name: 'app_error', requirements: ['code' => '\d+'])]
    public function show(FlattenException $exception): Response
    {
        $statusCode = $exception->getStatusCode();
        $message = $exception->getMessage() ?: 'Une erreur est survenue.';

        // Log de l’erreur
        $this->logger->error(sprintf('Erreur %d : %s', $statusCode, $message));

        // Sélection du template selon le code
        return match ($statusCode) {
            404 => $this->render('bundles/TwigBundle/Exception/error404.html.twig', [
                'message' => $message,
                'status_code' => $statusCode,
            ]),
            500 => $this->render('bundles/TwigBundle/Exception/error500.html.twig', [
                'message' => $message,
                'status_code' => $statusCode,
            ]),
            default => $this->render('bundles/TwigBundle/Exception/error.html.twig', [
                'message' => $message,
                'status_code' => $statusCode,
            ]),
        };
    }

    // ⚠️ Gestion automatique de toutes les erreurs Symfony (sans route explicite)
    public function __invoke(FlattenException $exception): Response
    {
        $statusCode = $exception->getStatusCode();

        // Redirige vers une page selon le rôle utilisateur
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();
            if (in_array('ROLE_TEACHER', $roles)) {
                $redirectRoute = 'teacher_dashboard';
            } elseif (in_array('ROLE_STUDENT', $roles)) {
                $redirectRoute = 'student_exams';
            } else {
                $redirectRoute = 'home';
            }

            // Exemple : redirection douce sur 404
            if ($statusCode === 404) {
                return $this->redirectToRoute($redirectRoute);
            }
        }

        // Sinon affiche les templates classiques
        return $this->show($exception);
    }
}
