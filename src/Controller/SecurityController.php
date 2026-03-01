<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('inicio');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/registro', name: 'app_registro', methods: ['GET', 'POST'])]
    public function registro(
        Request $request,
        UsuarioRepository $usuarioRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        if ($this->getUser()) {
            return $this->redirectToRoute('inicio');
        }

        $error = null;

        if ($request->isMethod('POST')) {

            $username = trim($request->request->get('username'));
            $nombre = trim($request->request->get('nombre'));
            $email = trim($request->request->get('email'));
            $password = $request->request->get('password');

            if (!$username || !$nombre || !$email || !$password) {
                $error = "Todos los campos son obligatorios.";
            } elseif ($usuarioRepository->findOneBy(['username' => $username])) {
                $error = "El username ya existe.";
            } elseif ($usuarioRepository->findOneBy(['email' => $email])) {
                $error = "El email ya está registrado.";
            } else {

                $usuario = new Usuario();
                $usuario->setUsername($username);
                $usuario->setNombre($nombre);
                $usuario->setEmail($email);
                $usuario->setRoles(['ROLE_USER']);

                $hashedPassword = $passwordHasher->hashPassword($usuario, $password);
                $usuario->setPassword($hashedPassword);

                $entityManager->persist($usuario);
                $entityManager->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/registro.html.twig', [
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepted by firewall.');
    }

    #[Route('/perfil/{username}', name: 'app_perfil', methods: ['GET', 'POST'])]
public function perfil(
    string $username,
    Request $request,
    UsuarioRepository $usuarioRepository,
    EntityManagerInterface $entityManager
): Response {

    $this->denyAccessUnlessGranted('ROLE_USER');

    $usuario = $usuarioRepository->findOneBy(['username' => $username]);

    if (!$usuario) {
        throw $this->createNotFoundException('Usuario no encontrado');
    }

    $currentUser = $this->getUser();

    if (
        $currentUser->getUserIdentifier() !== $username
        && !in_array('ROLE_ADMIN', $currentUser->getRoles())
    ) {
        throw $this->createNotFoundException();
    }

    if ($request->isMethod('POST')) {

        $nombre = trim($request->request->get('nombre'));
        $email = trim($request->request->get('email'));

        if ($nombre && $email) {
            $usuario->setNombre($nombre);
            $usuario->setEmail($email);

            $entityManager->flush();

            return $this->redirectToRoute('inicio');
        }
    }

    return $this->render('security/perfil.html.twig', [
        'usuario' => $usuario
    ]);
}

#[Route('/changepassword/{username}', name: 'app_changepassword', methods: ['GET', 'POST'])]
public function changePassword(
    string $username,
    Request $request,
    UsuarioRepository $usuarioRepository,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
): Response {

    $this->denyAccessUnlessGranted('ROLE_USER');

    $usuario = $usuarioRepository->findOneBy(['username' => $username]);

    if (!$usuario) {
        throw $this->createNotFoundException('Usuario no encontrado');
    }

    $currentUser = $this->getUser();

    if (
        $currentUser->getUserIdentifier() !== $username
        && !in_array('ROLE_ADMIN', $currentUser->getRoles())
    ) {
        throw $this->createNotFoundException();
    }

    $error = null;

    if ($request->isMethod('POST')) {

        $password = $request->request->get('password');

        if (!$password) {
            $error = "La contraseña no puede estar vacía.";
        } else {

            $hashedPassword = $passwordHasher->hashPassword($usuario, $password);
            $usuario->setPassword($hashedPassword);

            $entityManager->flush();

            return $this->redirectToRoute('inicio');
        }
    }

    return $this->render('security/changepassword.html.twig', [
        'usuario' => $usuario,
        'error' => $error
    ]);
}


}