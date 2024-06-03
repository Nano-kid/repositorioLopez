<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $response = $this->render('carrito/verCarrito.html.twig', [
            'usuario' => $usuario,
            'carrito' => $carritoPaginado,
            'numPaginas' => $numPaginas,
            'paginaActual' => $paginaActual,
            'total' => $precioTotal,
        ]);

        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    #[Route('/agregarAlCarrito/{productoId}', name: 'app_agregarAlCarrito')]
    public function agregarAlCarrito($productoId, Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        //Creamos el fomrulario para crear una linea venta al carrito.
        $lineaVenta = new LineasVenta();
        $formulario = $this->createForm(AgregarLineaVentaType::class, $lineaVenta);
        $formulario->handleRequest($request);

        if($formulario->isSubmitted() && $formulario->isValid()){
            //Obtenemos el producto que ira asociado a esa linea de venta
            $producto = $entityManager->getRepository(Producto::class)->find($productoId);
            $fotos = $entityManager->getRepository(Foto::class)->findBy(['producto' => $producto->getId()]);
            foreach ($fotos as $foto) {
                $producto->addFoto($foto);
            }
            $lineaVenta->setProducto($producto);

            //Calculamos el precio total de esa linea de venta
            $cantidad = $formulario->get('cantidad')->getData();
            $precio = $producto->getPrecio() * $cantidad;
            if($producto->getDescuento() != 0 || $producto->getDescuento() != null){
                $descuento = $precio * ($producto->getDescuento()/100);
                $precio = $precio - $descuento;
            }
            $lineaVenta->setTotal($precio);

            $carrito = $session->get('carrito', []);
            $carrito[] = $lineaVenta;
            $session->set('carrito', $carrito);
        }

        //return $this->redirectToRoute('app_carrito');
        return $this->redirectToRoute('app_verProducto', ['id' => $producto->getId()]);
    }

    #[Route('/carrito/quitar/{indexVenta}/{paginaActual}', name: 'app_quitarDelCarrito')]
    public function quitarDelCarrito($indexVenta, $paginaActual, SessionInterface $session): Response
    {
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

    #[Route('/confirmarPedido', name: 'app_confirmarPedido',  methods: ['POST'])]
    public function confirmarPedido(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, TokenStorageInterface $tokenStorage): Response
    {
        $pedido = new Pedido();
        $token = $tokenStorage->getToken();

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $pedido->setUsuario($token->getUser());
        }
        
        $carrito = $session->get('carrito', []);
        $precioTotal = 0;
        /*$precioTotal = 0;
        for ($i = 0; $i < count($carrito); $i++) {
            $pedido->addLineasVenta($carrito[$i]);
            $precioTotal = $precioTotal + $carrito[$i]->getTotal();
        }*/

        foreach ($carrito as $lineaVenta) {
            if ($lineaVenta instanceof LineasVenta) {
                $producto = $lineaVenta->getProducto();
                $productoExistente = $entityManager->getRepository(Producto::class)->find($producto->getId());
                $lineaVenta->setProducto($productoExistente);
                $lineaVenta->setPedido($pedido);
                $pedido->addLineasVenta($lineaVenta);
                $precioTotal += $lineaVenta->getTotal();
            }
        }

        $pedido->setTotal($precioTotal);
        $session->set('carrito', []);

        $entityManager->persist($pedido);
        $entityManager->flush();

        return $this->redirectToRoute('app_carrito');
    }
}
