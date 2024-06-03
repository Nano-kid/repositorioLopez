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
use App\Form\AgregarLineaVentaType;
use App\Entity\Usuario;
use App\Entity\Foto;
use App\Entity\Producto;
use App\Entity\Mensaje;
use App\Entity\Categoria;
use App\Entity\LineasVenta;


class ProductoController extends AbstractController
{
    #[Route('/', name: 'app_producto')]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {   
        $productos = $entityManager->getRepository(Producto::class)->findAll();
        $categorias = $entityManager->getRepository(Categoria::class)->findAll();

        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        $response = $this->render('producto/index.html.twig', [
            'productos' => $productos,
            'categorias' => $categorias,
            'usuario' => $usuario,
        ]);

        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    #[Route('/agregarProducto', name: 'app_agregarProducto')]
    public function agregarProducto(Request $request, EntityManagerInterface $entityManager): Response
    {
        $producto = new Producto();
        $formulario = $this->createForm(CrearProductoType::class, $producto, [
            'is_image_required' => true,
        ]);
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
            $entityManager->persist($producto);
            $entityManager->flush();

            return $this->redirectToRoute('app_verProducto', ['id' => $producto->getId()]);
        }

        return $this->render('producto/crearProducto.html.twig', [
            'formulario' => $formulario,
            'productoM' => $producto,
        ]);
    }

    #[Route('/eliminarProducto/{id}', name: 'app_eliminarProducto')]
    public function eliminarProducto(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id): Response
    {
        $producto = $entityManager->getRepository(Producto::class)->find($id);

        if($producto){
            $entityManager->remove($producto);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_producto');
    }

    #[Route('/verProducto/{id}', name: 'app_verProducto', methods: ['GET'])]
    public function verProducto(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id, Request $request): Response {
        //Obtener el producto y productos relacionados de la misma categoria
        $producto = $entityManager->getRepository(Producto::class)->find($id);
        $categorias = $entityManager->getRepository(Categoria::class)->findAll();

        if (!$producto) {
            return $this->redirectToRoute('app_producto');
        }

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

        $lineaVenta = new LineasVenta();
        $formAgregarCarrito = $this->createForm(AgregarLineaVentaType::class, $lineaVenta);

        $response = $this->render('producto/verProducto.html.twig', [
            'producto' => $producto,
            'usuario' => $usuario,
            'productosRelacionados' => $productosRelacionados,
            'formAgregarCarrito' => $formAgregarCarrito,
            'formulario' => $formulario,
            'categorias' => $categorias,
        ]);
        
        // Añadir cabeceras para evitar caché
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    #[Route('/buscarProducto', name: 'app_buscarProducto', methods: ['GET'])]
    public function buscarProducto(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $categorias = $entityManager->getRepository(Categoria::class)->findAll();
        $query = $request->query->get('query', '');

        if (empty($query)) {
            // Si no hay término de búsqueda, redirigir a la página de productos o mostrar un mensaje adecuado
            return $this->redirectToRoute('app_producto');
        }

        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        // Búsqueda de productos por nombre
        $productos = $entityManager->getRepository(Producto::class)->createQueryBuilder('p')
            ->where('p.nombre LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        return $this->render('producto/resultadosBusqueda.html.twig', [
            'productos' => $productos,
            'query' => $query,
            'usuario' => $usuario,
            'categorias' => $categorias,
        ]);
    }

    #[Route('/productosPorCategoria', name: 'app_categoria', methods: ['GET'])]
    public function productosPorCategoria(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $categoriaId = $request->query->get('id', '');

        // Obtener todas las categorías
        $categorias = $entityManager->getRepository(Categoria::class)->findAll();

        // Obtener el usuario actual si está autenticado
        $token = $tokenStorage->getToken();
        $usuario = null;
        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        // Obtener la categoría seleccionada
        $categoria = $entityManager->getRepository(Categoria::class)->find($categoriaId);
        if (!$categoria) {
            throw $this->createNotFoundException('Categoría no encontrada');
        }

        // Obtener productos por categoría
        $productos = $entityManager->getRepository(Producto::class)->findBy(['categoria' => $categoria]);

        $paginaActual = $request->query->getInt('paginaActual', 1);
        $productosPorPagina = 6;
        $numProductos = count($productos);
        $numPaginas= ceil($numProductos / $productosPorPagina );

        $inicio = ($paginaActual - 1) * $productosPorPagina ;
        $productosPaginados = array_slice($productos, $inicio, $productosPorPagina );

        // Renderizar la plantilla con los datos obtenidos
        $response = $this->render('producto/produCategoria.html.twig', [
            'productos' => $productosPaginados,
            'categorias' => $categorias,
            'usuario' => $usuario,
            'numPaginas' => $numPaginas,
            'paginaActual' => $paginaActual,
            'categoria' => $categoria,
        ]);

        // Establecer las cabeceras de control de caché
        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }
}
