<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MensajeController extends AbstractController
{
    #[Route('/agregarMensaje', name: 'app_agregarMensaje')]
    public function agregarMensaje(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categoria = new Mensaje();
        $formulario = $this->createForm(AgregarMensajeType::class, $categoria);
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
