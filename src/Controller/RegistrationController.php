<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Entity\Foto;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $usuario = new Usuario();
        $formulario = $this->createForm(RegistrationFormType::class, $usuario);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {

            $usuario->setPassword(
                $userPasswordHasher->hashPassword(
                    $usuario,
                    $formulario->get('password')->getData()
                )
            );

            $usuario->setRol("Cliente");

            $foto = new Foto();
            $formfoto = $formulario->get('foto')->getData();
            if ($formfoto != null) {
                $nombreArchivo = uniqid().'.'.$formfoto->guessExtension();
    
                try {
                    $formfoto->move(
                        $this->getParameter('kernel.project_dir') . '/public/imagenesUsuario/',
                        $nombreArchivo
                    );
                } catch (FileException $e) {
                    // handle the exception if file upload fails
                    // e.g. return an error message to the user
                }
    
                // Set the file name to the 'foto' property of the user
                $foto->setNombre($nombreArchivo);
                $foto->setUsuario($usuario);
                $usuario->setFoto($foto);
            }

            $entityManager->persist($usuario);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $security->login($usuario, 'form_login', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $formulario,
        ]);
    }
}
