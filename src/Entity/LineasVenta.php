<?php

namespace App\Entity;

use App\Repository\LineasVentaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LineasVentaRepository::class)]
class LineasVenta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $cantidad = null;

    #[ORM\Column(nullable: true)]
    private ?float $descuento = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\ManyToOne(inversedBy: 'lineasVentas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Producto $producto = null;

    #[ORM\ManyToOne(inversedBy: 'lineasVentas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pedido $Pedido = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCantidad(): ?float
    {
        return $this->cantidad;
    }

    public function setCantidad(float $cantidad): static
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    public function getDescuento(): ?float
    {
        return $this->descuento;
    }

    public function setDescuento(?float $descuento): static
    {
        $this->descuento = $descuento;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

        return $this;
    }

    public function getProducto(): ?Producto
    {
        return $this->producto;
    }

    public function setProducto(?Producto $producto): static
    {
        $this->producto = $producto;

        return $this;
    }

    public function getPedido(): ?Pedido
    {
        return $this->Pedido;
    }

    public function setPedido(?Pedido $Pedido): static
    {
        $this->Pedido = $Pedido;

        return $this;
    }
}
