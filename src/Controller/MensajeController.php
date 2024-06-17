<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\AgregarMensajeType;
use App\Entity\Mensaje;
use App\Entity\Usuario;

class MensajeController extends AbstractController
{
    #[Route('/borrarMensaje/{id}', name: 'app_borrarMensaje')]
    public function borrarMensaje(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id): Response
    {   

        $mensaje = $entityManager->getRepository(Mensaje::class)->find($id);
        $idProducto = $mensaje->getProducto()->getId();
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || ($usuario->getRol() != "Cliente" && $usuario->getRol() != "Administrador")) {
            return $this->redirectToRoute('app_producto');
        }

        if ($usuario->getRol() == "Cliente" && $mensaje->getUsuario()->getId() != $usuario->getId()) {
            return $this->redirectToRoute('app_producto');
        }

        if($mensaje){
            $entityManager->remove($mensaje);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_verProducto', ['id' => $idProducto]);
    }

    #[Route('/modificarMensaje/{id}', name: 'app_modificarMensaje')]
    public function modificarMensaje(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id): Response
    {
        $mensaje = $entityManager->getRepository(Mensaje::class)->find($id);
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || ($usuario->getRol() != "Cliente" && $usuario->getRol() != "Administrador")) {
            return $this->redirectToRoute('app_producto');
        }

        if ($usuario->getRol() == "Cliente" && $mensaje->getUsuario()->getId() != $usuario->getId()) {
            return $this->redirectToRoute('app_producto');
        }


        $formulario = $this->createForm(AgregarMensajeType::class, $mensaje);
        $formulario->handleRequest($request);

        if($formulario->isSubmitted() && $formulario->isValid()){
            $entityManager->persist($mensaje);
            $entityManager->flush();

            return $this->redirectToRoute('app_verProducto', ['id' => $mensaje->getProducto()->getId()]);
        }

        return new JsonResponse([
            'id' => $mensaje->getId(),
            'contenido' => $mensaje->getContenido(),
        ]);
    }
}
