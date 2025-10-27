<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * ✅ Gère la redirection automatique après connexion
 * selon le rôle de l'utilisateur connecté.
 */
class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        $roles = $user->getRoles();

        // 🎯 Redirection selon le rôle
        if (in_array('ROLE_ADMIN', $roles, true)) {
            $url = $this->urlGenerator->generate('admin_dashboard');
        } elseif (in_array('ROLE_TEACHER', $roles, true)) {
            $url = $this->urlGenerator->generate('teacher_dashboard');
        } elseif (in_array('ROLE_STUDENT', $roles, true)) {
            $url = $this->urlGenerator->generate('student_exams');
        } else {
            $url = $this->urlGenerator->generate('home');
        }

        return new RedirectResponse($url);
    }
}
