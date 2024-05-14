<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $categoria = new Categoria();
        $formulario = $this->createForm(CrearCategoriaType::class, $categoria);
        $formulario->handleRequest($request);

        if($formulario->isSubmitted() && $formulario->isValid()){
            $entityManager->persist($categoria);
            $entityManager->flush();

            return $this->redirectToRoute('app_producto');
        }

        return $this->render('categoria/crearCategoria.html.twig', [
            'formulario' => $formulario,
        ]);
    }
}
