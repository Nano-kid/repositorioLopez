<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\AgregarLineaVentaType;
use App\Repository\LineasVentaRepository;
use App\Entity\Usuario;
use App\Entity\LineasVenta;
use App\Entity\Producto;
use App\Entity\Pedido;
use App\Entity\Categoria;
use App\Entity\Foto;

class CarritoController extends AbstractController
{
    #[Route('/carrito', name: 'app_carrito')]
    public function verCarrito(Request $request, EntityManagerInterface $entityManager,  LineasVentaRepository $lineasVentaRepository, TokenStorageInterface $tokenStorage, SessionInterface $session): Response
    {
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Cliente"){
            return $this->redirectToRoute('app_producto');
        }

        $categorias = $entityManager->getRepository(Categoria::class)->findAll();
        $formulario = $this->createForm(AgregarLineaVentaType::class);

        $carrito = $session->get('carrito', []);
        $precioTotal = 0;
        for ($i = 0; $i < count($carrito); $i++) {
            $lineasVenta = $carrito[$i];
            $precioTotal = $precioTotal + $lineasVenta->getTotal();
        }

        //Indicamos las variables necesarias para la paginacion
        $paginaActual = $request->query->getInt('paginaActual', 1);
        $productosPorPagina = 4;
        $numProductos = count($carrito);
        $numPaginas= ceil($numProductos / $productosPorPagina );

        $inicio = ($paginaActual - 1) * $productosPorPagina ;
        $carritoPaginado = array_slice($carrito, $inicio, $productosPorPagina );

        if (empty($carritoPaginado) && $paginaActual > 1) {
            // Redirige a la página anterior si la actual está vacía
            return $this->redirectToRoute('app_carrito');
        }

        $mensaje = $session->get('mensaje', null);
        $session->set('mensaje', null);

        $response = $this->render('carrito/verCarrito.html.twig', [
            'mensaje' => $mensaje,
            'usuario' => $usuario,
            'carrito' => $carritoPaginado,
            'numPaginas' => $numPaginas,
            'paginaActual' => $paginaActual,
            'total' => round($precioTotal, 2),
            'formModificarCarrito' => $formulario,
            'categorias' => $categorias,
        ]);

        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }


    #[Route('/agregarAlCarrito/{productoId}', name: 'app_agregarAlCarrito')]
    public function agregarAlCarrito($productoId, Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, SessionInterface $session): Response
    {
        $token = $tokenStorage->getToken();
        $usuario = null;
        $producto = $entityManager->getRepository(Producto::class)->find($productoId);

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Cliente"){
            if($producto){
                return $this->redirectToRoute('app_verProducto', ['id' => $producto->getId()]);
            }else{
                return $this->redirectToRoute('app_producto');
            }
        }

        $lineaVenta = new LineasVenta();
        $formulario = $this->createForm(AgregarLineaVentaType::class, $lineaVenta);
        $formulario->handleRequest($request);

        if($formulario->isSubmitted() && $formulario->isValid() && $producto !== null ){
            $fotos = $entityManager->getRepository(Foto::class)->findBy(['producto' => $producto->getId()]);
            foreach ($fotos as $foto) {
                $producto->addFoto($foto);
            }
            $lineaVenta->setProducto($producto);

            $cantidad = $formulario->get('cantidad')->getData();
            if($cantidad <= 0){
                $session->set('mensaje', 'La cantidad no puede ser ni 0 ni un numero negativo');        
                return $this->redirect($request->getUri());
            }

            if ($producto->getUnidadVenta() === "Unidad" && fmod($cantidad, 1) !== 0.0) {
                $session->set('mensaje', 'La cantidad debe ser un numero entero para las ventas por Unidad.');        
                return $this->redirect($request->getUri());
            }

            $precio = $producto->getPrecio() * $cantidad;
            if($producto->getDescuento() != 0 || $producto->getDescuento() != null){
                $lineaVenta->setDescuento($producto->getDescuento());
                $descuento = $precio * ($producto->getDescuento()/100);
                $precio = $precio - $descuento;
            }
            
            $lineaVenta->setCantidad($cantidad);
            $lineaVenta->setTotal(round($precio, 2));

            $carrito = $session->get('carrito', []);
            $carrito[] = $lineaVenta;
            $session->set('carrito', $carrito);

            $session->set('mensaje', 'Agregado al carrito');
        }

        //return $this->redirectToRoute('app_carrito');
        return $this->redirectToRoute('app_verProducto', ['id' => $producto->getId()]);
    }

    #[Route('/quitarCarrito/{indexVenta}/{paginaActual}', name: 'app_quitarDelCarrito')]
    public function quitarDelCarrito($indexVenta, $paginaActual, TokenStorageInterface $tokenStorage, SessionInterface $session): Response
    {
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Cliente"){
            return $this->redirectToRoute('app_producto');
        }

        $carrito = $session->get('carrito', []);

        if($paginaActual == 1){
            if (isset($carrito[$indexVenta])) {
                unset($carrito[$indexVenta]);
            }
        }else{
            $posicion = (($paginaActual - 1) * 4) + $indexVenta;
            if (isset($carrito[$posicion])) {
                unset($carrito[$posicion]);
            }
        }

        $carrito = array_values($carrito);
        $session->set('carrito', $carrito);

        return $this->redirectToRoute('app_carrito');
    }


    #[Route('/modificarCantidad/{index}', name: 'app_modificarCantidad', methods: ['GET', 'POST'])]
    public function modificarCantidad(Request $request, TokenStorageInterface $tokenStorage, SessionInterface $session, EntityManagerInterface $entityManager, $index): Response
    {
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Cliente"){
            return $this->redirectToRoute('app_producto');
        }

        $paginaActual = $request->query->get('paginaActual');
        $carrito = $session->get('carrito', []);

        if (!isset($carrito[$index])) {
            return new JsonResponse(['error' => 'Línea de venta no encontrada', 'index' => $index], 404);
        }

        $lineaVentaEncontrada = $carrito[$index];

        $formulario = $this->createForm(AgregarLineaVentaType::class, $lineaVentaEncontrada);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted()) {
            $nuevaCantidad = $formulario->get('cantidad')->getData();

            if(!is_numeric($nuevaCantidad) || $nuevaCantidad <= 0){
                $session->set('mensaje', 'La cantidad debe ser un numero positivo');        
                return $this->redirectToRoute('app_carrito');
            }

            $producto = $lineaVentaEncontrada->getProducto();
            $precio = $producto->getPrecio() * $nuevaCantidad;

            if ($producto->getUnidadVenta() === "Unidad" && fmod($nuevaCantidad, 1) !== 0.0) {
                $session->set('mensaje', 'La cantidad debe ser un numero entero para las ventas por Unidad.');        
                return $this->redirectToRoute('app_carrito');
            }

            if ($producto->getDescuento() != 0 || $producto->getDescuento() != null) {
                $descuento = $precio * ($producto->getDescuento() / 100);
                $precio = $precio - $descuento;
            }

            $lineaVentaEncontrada->setCantidad($nuevaCantidad);
            $precioRedondeado = round($precio, 2);
            $lineaVentaEncontrada->setTotal($precioRedondeado);

            $carrito[$index] = $lineaVentaEncontrada;
            $session->set('carrito', $carrito);

            $session->set('mensaje', 'Cantidad modificada'); 
            return $this->redirectToRoute('app_carrito');
        }

        return new JsonResponse([
            'index' => $index,
            'cantidad' => $lineaVentaEncontrada->getCantidad(),
            'unidadVenta' => $lineaVentaEncontrada->getProducto()->getUnidadVenta(),
        ]);
    }

}
