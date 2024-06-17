<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\ModificarEstadoType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Pedido;
use App\Entity\Categoria;
use App\Entity\LineasVenta;
use App\Entity\Producto;
use App\Entity\Usuario;

class PedidosController extends AbstractController
{
    #[Route('/pedidos', name: 'app_pedidos', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        $pedidos = $entityManager->getRepository(Pedido::class)->findAllOrderedByDateDesc();
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || ($usuario->getRol() != "Cliente" && $usuario->getRol() != "Administrador")) {
            return $this->redirectToRoute('app_producto');
        }

        if ($usuario->getRol() == "Cliente") {
            $pedidos = $entityManager->getRepository(Pedido::class)->findByUsuarioId($usuario->getId());
        } 

        $estado = $request->query->get('query', '');
        if($estado == "Listo" || $estado == "Pendiente" || $estado == "Finalizado"){
            $pedidos = $entityManager->getRepository(Pedido::class)->findByEstado($estado);
            if ($usuario->getRol() == "Cliente") {
                $pedidos = $entityManager->getRepository(Pedido::class)->findByUsuarioIdAndEstado($usuario->getId(), $estado);
            } 
        }else {
            $pedidos = $entityManager->getRepository(Pedido::class)->findAllOrderedByDateDesc();
            if ($usuario->getRol() == "Cliente") {
                $pedidos = $entityManager->getRepository(Pedido::class)->findByUsuarioId($usuario->getId());
            } 
        }


        $categorias = $entityManager->getRepository(Categoria::class)->findAll();
        $formulario = $this->createForm(ModificarEstadoType::class);

        $response = $this->render('pedidos/index.html.twig', [
            'filtro' => $estado,
            'formModificarEstado' => $formulario,
            'pedidos' => $pedidos,
            'usuario' => $usuario,
            'categorias' => $categorias,
        ]);

        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    #[Route('/pedido/{id}', name: 'app_pedido')]
    public function verPedido(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id): Response
    {
        $categorias = $entityManager->getRepository(Categoria::class)->findAll();
        $pedido = $entityManager->getRepository(Pedido::class)->find($id);
        if($pedido){
            $lineasVenta = $entityManager->getRepository(LineasVenta::class)->findByPedidoId($id);
        }

        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || ($usuario->getRol() != "Cliente" && $usuario->getRol() != "Administrador")) {
            return $this->redirectToRoute('app_producto');
        }

        if ($usuario->getRol() == "Cliente" && $pedido->getUsuario()->getId() != $usuario->getId()) {
            return $this->redirectToRoute('app_producto');
        }

        foreach ($lineasVenta as $lineaVenta) {
            if ($lineaVenta instanceof LineasVenta) {
                $producto = $lineaVenta->getProducto()->getId();
                $productoExistente = $entityManager->getRepository(Producto::class)->find($producto);
                $lineaVenta->setProducto($productoExistente);
            }
        }


        $paginaActual = $request->query->getInt('paginaActual', 1);
        $productosPorPagina = 4;
        $numProductos = count($lineasVenta);
        $numPaginas= ceil($numProductos / $productosPorPagina );

        $inicio = ($paginaActual - 1) * $productosPorPagina ;
        $productosPaginados = array_slice($lineasVenta, $inicio, $productosPorPagina );

        if (empty($productosPaginados) && $paginaActual > 1) {
            return $this->redirectToRoute('app_carrito');
        }

        $response = $this->render('pedidos/verPedido.html.twig', [
            'usuario' => $usuario,
            'pedido' => $pedido,
            'categorias' => $categorias,
            'lineasVenta' => $productosPaginados,
            'numPaginas' => $numPaginas,
            'paginaActual' => $paginaActual,
        ]);

        $response->setMaxAge(0);
        $response->setSharedMaxAge(0);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    #[Route('/confirmarPedido', name: 'app_confirmarPedido',  methods: ['POST'])]
    public function confirmarPedido(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, TokenStorageInterface $tokenStorage): Response
    {
        $carrito = $session->get('carrito', []);
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Cliente") {
            return $this->redirectToRoute('app_producto');
        }

        if($usuario->getRol() == "Cliente" && count($carrito) <= 0){
            return $this->redirectToRoute('app_carrito');
        }

        $pedido = new Pedido();
        $pedido->setUsuario($usuario);
        $precioTotal = 0;
        $pedido->setEstado("Pendiente");
        $pedido->setFecha(new \DateTime());
        $pedido->setTotal(0);
        $entityManager->persist($pedido);
        $entityManager->flush();

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

        $pedido->setTotal(round($precioTotal, 2));
        $session->set('carrito', []);
        
        $entityManager->flush();

        $session->set('mensaje', 'Pedido Confirmado');
        return $this->redirectToRoute('app_carrito');
    }

    #[Route('/eliminarPedido/{id}', name: 'app_eliminarPedido')]
    public function eliminarPedido(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id): Response
    {
        $pedido = $entityManager->getRepository(Pedido::class)->find($id);
        $lineasVenta = $entityManager->getRepository(LineasVenta::class)->findByPedidoId($id);
        $token = $tokenStorage->getToken();
        $usuario = null;

        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Cliente") {
            return $this->redirectToRoute('app_producto');
        }

        if ($usuario->getRol() == "Cliente" && $pedido->getUsuario()->getId() != $usuario->getId() && $pedido->getEstado() == "Pendiente" ) {
            return $this->redirectToRoute('app_producto');
        }

        $lineasVenta = $pedido->getLineasVentas();
        if(count($lineasVenta) > 0){
            foreach ($lineasVenta as $lineaVenta) {
                $pedido->removeLineasVenta($lineaVenta);
                $entityManager->remove($lineaVenta);
            }            
        }
        $entityManager->flush();

        $entityManager->remove($pedido);
        $entityManager->flush();
            
        return $this->redirectToRoute('app_pedidos');
    }

    #[Route('/modificarEstado/{id}', name: 'app_modificarEstado', methods: ['GET', 'POST'])]
    public function modificarCantidad(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id): Response
    {
        $token = $tokenStorage->getToken();
        $usuario = null;
        
        if ($token !== null && $token->getUser() instanceof Usuario) {
            $usuario = $token->getUser();
        }

        if($usuario == null || $usuario->getRol() != "Administrador") {
            return $this->redirectToRoute('app_producto');
        }
        
        $paginaActual = $request->query->get('paginaActual');
        $pedido = $entityManager->getRepository(Pedido::class)->find($id);
        $estado = $pedido->getEstado();

        $formulario = $this->createForm(ModificarEstadoType::class);
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            $estado = $formulario->get('estado')->getData();
            $pedido->setEstado($estado);
            $entityManager->flush();

            return $this->redirectToRoute('app_pedidos');
        }

        return new JsonResponse([
            'id' => $pedido->getId(),
            'estado' => $estado,
        ]);
    }
}
