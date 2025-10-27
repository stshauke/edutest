<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // 🔒 Si l'utilisateur est déjà connecté, redirige selon son rôle
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();

            if (in_array('ROLE_TEACHER', $roles, true)) {
                return $this->redirectToRoute('teacher_dashboard');
            }

            if (in_array('ROLE_STUDENT', $roles, true)) {
                return $this->redirectToRoute('student_exams');
            }

            // 🔁 Fallback : vers l'accueil
            return $this->redirectToRoute('home');
        }

        // ❌ Récupère une éventuelle erreur d'authentification
        $error = $authenticationUtils->getLastAuthenticationError();
        // 📧 Dernier email saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // 🚪 Symfony gère la déconnexion automatiquement via le firewall.
        throw new \LogicException('Cette méthode peut rester vide — Symfony gère la déconnexion automatiquement.');
    }
}
