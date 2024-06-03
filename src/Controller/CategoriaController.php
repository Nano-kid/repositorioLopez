<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CrearCategoriaType;
use App\Entity\Categoria;


class CategoriaController extends AbstractController
{
    #[Route('/categoria', name: 'app_categoria')]
    public function index(): Response
    {
        return $this->render('categoria/index.html.twig', [
            'controller_name' => 'CategoriaController',
        ]);
    }

    #[Route('/agregarCategoria', name: 'app_agregarCategoria')]
    public function agregarCategoria(Request $request, EntityManagerInterface $entityManager): Response
    {
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
    public function modificarCategoria(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
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
    public function eliminarCategoria(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $categoria = $entityManager->getRepository(Categoria::class)->find($id);

        if($categoria){
            $entityManager->remove($categoria);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_agregarCategoria');
    }

}
