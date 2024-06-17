<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CrearCategoriaType;
use App\Entity\Categoria;
use App\Entity\Usuario;


class CategoriaController extends AbstractController
{
    #[Route('/agregarCategoria', name: 'app_agregarCategoria')]
    public function agregarCategoria(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Administrador"){
            return $this->redirectToRoute('app_producto');
        }

        $categorias = $entityManager->getRepository(Categoria::class)->findAll();

        $categoria = new Categoria();
        $formulario = $this->createForm(CrearCategoriaType::class, $categoria);
        $formulario->handleRequest($request);

        $formularioModificar = $this->createForm(CrearCategoriaType::class, $categoria);

        if($formulario->isSubmitted() && $formulario->isValid()){
            $entityManager->persist($categoria);
            $entityManager->flush();

            return $this->redirectToRoute('app_producto');
        }

        $response = $this->render('categoria/crearCategoria.html.twig', [
            'formularioModificar' => $formularioModificar,
            'formulario' => $formulario,
            'categorias' => $categorias
        ]);

        // Añadir cabeceras para evitar caché
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    #[Route('/modificarCategoria/{id}', name: 'app_modificarCategoria', methods: ['GET', 'POST'])]
    public function modificarCategoria(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id): Response
    {
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Administrador"){
            return $this->redirectToRoute('app_producto');
        }
        
        $categoria = $entityManager->getRepository(Categoria::class)->find($id);

        if (!$categoria) {
            return new JsonResponse(['error' => 'Categoria no encontrada'], 404);
        }

        $formularioModificar = $this->createForm(CrearCategoriaType::class, $categoria);
        $formularioModificar->handleRequest($request);

        if ($formularioModificar->isSubmitted() && $formularioModificar->isValid()) {
            $entityManager->persist($categoria);
            $entityManager->flush();

            return $this->redirectToRoute('app_agregarCategoria');
        }

        return new JsonResponse([
            'id' => $categoria->getId(),
            'nombre' => $categoria->getNombre(),
        ]);
    }

    #[Route('/eliminarCategoria/{id}', name: 'app_eliminarCategoria')]
    public function eliminarCategoria(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id): Response
    {
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Administrador"){
            return $this->redirectToRoute('app_producto');
        }

        $categoria = $entityManager->getRepository(Categoria::class)->find($id);

        if($categoria){
            $entityManager->remove($categoria);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_agregarCategoria');
    }

}
