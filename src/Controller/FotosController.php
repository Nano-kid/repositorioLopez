<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Foto;
use App\Entity\Producto;
use App\Entity\Usuario;

class FotosController extends AbstractController
{
    
    #[Route('/agregarFoto', name: 'app_agregarFoto',  methods: ['POST'])]
    public function agregarFoto(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response {
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Administrador"){
            return $this->redirectToRoute('app_producto');
        }

        try{ 
            $foto = $request->files->get('imagen');
            $idProducto = $request->request->get('idProducto');

            // Buscar el producto por ID
            $producto = $entityManager->getRepository(Producto::class)->find($idProducto);
            if (!$producto) {
                return new JsonResponse(['success' => false, 'message' => 'Producto no encontrado'], 404);
            }

            if ($foto && $foto->isValid()) {
                // Definir un nombre único para el archivo
                $nombreArchivo = uniqid() . '.' . $foto->guessExtension();

                // Intentar mover el archivo a la ubicación de destino
                try {
                    $foto->move(
                        $this->getParameter('kernel.project_dir') . ('/public/imagenesProductos/'), // Asegúrate de definir este parámetro en config/services.yaml
                        $nombreArchivo
                    );
                } catch (FileException $e) {
                    return new JsonResponse(['success' => false, 'message' => 'Error al mover el archivo'], 500);
                }

                // Crear una nueva entidad de Foto y asociarla con el Producto
                $foto = new Foto();
                $foto->setNombre($nombreArchivo);
                $foto->setProducto($producto);
                
                // Persistir la nueva foto en la base de datos
                $entityManager->persist($foto);
                $entityManager->flush();

                $fotoBd = $entityManager->getRepository(Foto::class)->findOneBy(['nombre' => $nombreArchivo]);

                return new JsonResponse([
                    'success' => true, 
                    'message' => 'Imagen subida exitosamente',
                    'foto' => $nombreArchivo,
                    'idFoto' => $fotoBd->getId(),
                ]);
            }
            return new JsonResponse(['success' => false, 'message' => 'Archivo inválido'], 400);

        }catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }

    #[Route('/eliminarFoto', name: 'app_eliminarFoto',  methods: ['POST'])]
    public function eliminarFoto(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response {
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Administrador"){
            return $this->redirectToRoute('app_producto');
        }

        try {
            $idFoto = $request->request->get('idFoto');
            $foto = $entityManager->getRepository(Foto::class)->find($idFoto);

            if (!$foto) {
                return new JsonResponse(['success' => false, 'message' => 'Foto no encontrada'], 404);
            } else {
                $entityManager->remove($foto);
                $entityManager->flush();
            }

            return new JsonResponse([
                'success' => true, 
                'message' => 'Imagen borrada exitosamente',
            ]);

        }catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Error interno del servidor'], 500);
        }
    }
}