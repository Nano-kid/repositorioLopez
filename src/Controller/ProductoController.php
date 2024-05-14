<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\CrearProductoType;
use App\Form\AgregarMensajeType;
use App\Entity\Usuario;
use App\Entity\Foto;
use App\Entity\Producto;
use App\Entity\Mensaje;


class ProductoController extends AbstractController
{
    #[Route('/', name: 'app_producto')]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {   
        $productos = $entityManager->getRepository(Producto::class)->findAll();

        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        return $this->render('producto/index.html.twig', [
            'productos' => $productos,
            'usuario' => $usuario,
        ]);
    }

    #[Route('/agregarProducto', name: 'app_agregarProducto')]
    public function agregarProducto(Request $request, EntityManagerInterface $entityManager): Response
    {
        $producto = new Producto();
        $formulario = $this->createForm(CrearProductoType::class, $producto);
        $formulario->handleRequest($request);

        if($formulario->isSubmitted() && $formulario->isValid()){
            $foto = new Foto();
            $formfoto = $formulario->get('foto')->getData();
            if ($formfoto != null) {
                $nombreArchivo = uniqid().'.'.$formfoto->guessExtension();
    
                try {
                    $formfoto->move(
                        $this->getParameter('kernel.project_dir') . '/public/imagenesProductos/',
                        $nombreArchivo
                    );
                } catch (FileException $e) {
                    
                }
    
                $foto->setNombre($nombreArchivo);
                $foto->setProducto($producto);
                $producto->addFoto($foto);
            }

            $entityManager->persist($producto);
            $entityManager->flush();

            return $this->redirectToRoute('app_producto');
        }

        return $this->render('producto/crearProducto.html.twig', [
            'formulario' => $formulario,
            'producto' => $producto,
        ]);
    }

    #[Route('/modificarProducto/{id}', name: 'app_modificarProducto')]
    public function modificarProducto(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $producto = $entityManager->getRepository(Producto::class)->find($id);

        $formulario = $this->createForm(CrearProductoType::class, $producto);
        $formulario->handleRequest($request);

        if($formulario->isSubmitted() && $formulario->isValid()){

            $foto = new Foto();
            $formfoto = $formulario->get('foto')->getData();
            if ($formfoto != null) {
                $nombreArchivo = uniqid().'.'.$formfoto->guessExtension();
    
                try {
                    $formfoto->move(
                        $this->getParameter('kernel.project_dir') . '/public/imagenesProductos/',
                        $nombreArchivo
                    );
                } catch (FileException $e) {
                    
                }
    
                $foto->setNombre($nombreArchivo);
                $foto->setProducto($producto);
                $producto->addFoto($foto);
            }
            
            $entityManager->persist($producto);
            $entityManager->flush();

            return $this->redirectToRoute('app_producto');
        }

        return $this->render('producto/crearProducto.html.twig', [
            'formulario' => $formulario,
            'productoM' => $producto,
        ]);
    }

    /*#[Route('/verProducto/{id}', name:'app_verProducto')]
    public function verProducto(EntityManagerInterface $entityManager, $id): Response {

        $producto = $entityManager->getRepository(Producto::class)->find($id);

        return $this->render('producto/verProducto.html.twig', [
            'producto' => $producto 
        ]);
    }*/

    #[Route('/verProducto/{id}', name: 'app_verProducto')]
    public function verProducto(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id, Request $request): Response {
        //Obtener el producto y productos relacionados de la misma categoria
        $producto = $entityManager->getRepository(Producto::class)->find($id);
        $categoria = $producto->getCategoria();
        $productosRelacionados = $categoria->getProductos()->filter(function($productoArray) use ($id) {
            return $productoArray->getId() != $id;
        });
        
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        $mensaje = new Mensaje();
        $formulario = $this->createForm(AgregarMensajeType::class, $mensaje);
        $formulario->handleRequest($request);
        
        if($formulario->isSubmitted() && $formulario->isValid()){
            $mensaje->setUsuario($usuario);
            $mensaje->setProducto($producto);
            $mensaje->setFecha(new \DateTime());
            $entityManager->persist($mensaje);
            $entityManager->flush();

            // Redirigir a la misma URL desde la que se envió el formulario
            return $this->redirect($request->getUri());
        }

        return $this->render('producto/verProducto.html.twig', [
            'producto' => $producto,
            'usuario' => $usuario,
            'productosRelacionados' => $productosRelacionados,
            'formulario' => $formulario
        ]);
    }
}
