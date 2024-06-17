<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductoRepository::class)]
class Producto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 1000)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255)]
    private ?string $unidadVenta = null;

    #[ORM\Column]
    private ?float $precio = null;

    #[ORM\Column(nullable: true)]
    private ?float $descuento = null;

    #[ORM\OneToMany(targetEntity: Foto::class, mappedBy: 'producto', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $fotos;

    #[ORM\ManyToOne(inversedBy: 'productos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categoria $categoria = null;

    #[ORM\OneToMany(targetEntity: LineasVenta::class, mappedBy: 'producto')]
    private Collection $lineasVentas;

    #[ORM\OneToMany(targetEntity: Mensaje::class, mappedBy: 'producto', orphanRemoval: true)]
    private Collection $mensajes;

    public function __construct()
    {
        $this->fotos = new ArrayCollection();
        $this->lineasVentas = new ArrayCollection();
        $this->mensajes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): static
    {
        $this->precio = $precio;
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

    public function getUnidadVenta(): ?string
    {
        return $this->unidadVenta;
    }

    public function setUnidadVenta(string $unidadVenta): static
    {
        $this->unidadVenta = $unidadVenta;
        return $this;
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): static
    {
        $this->categoria = $categoria;
        return $this;
    }

    public function getFotos(): Collection
    {
        return $this->fotos;
    }

    public function addFoto(Foto $foto): static
    {
        if (!$this->fotos->contains($foto)) {
            $this->fotos->add($foto);
            $foto->setProducto($this);
        }
        return $this;
    }

    public function removeFoto(Foto $foto): static
    {
        if ($this->fotos->removeElement($foto)) {
            if ($foto->getProducto() === $this) {
                $foto->setProducto(null);
            }
        }
        return $this;
    }

    public function getLineasVentas(): Collection
    {
        return $this->lineasVentas;
    }

    public function addLineasVenta(LineasVenta $lineasVenta): static
    {
        if (!$this->lineasVentas->contains($lineasVenta)) {
            $this->lineasVentas->add($lineasVenta);
            $lineasVenta->setProducto($this);
        }
        return $this;
    }

    public function removeLineasVenta(LineasVenta $lineasVenta): static
    {
        if ($this->lineasVentas->removeElement($lineasVenta)) {
            if ($lineasVenta->getProducto() === $this) {
                $lineasVenta->setProducto(null);
            }
        }
        return $this;
    }

    public function getMensajes(): Collection
    {
        return $this->mensajes;
    }

    public function addMensaje(Mensaje $mensaje): static
    {
        if (!$this->mensajes->contains($mensaje)) {
            $this->mensajes->add($mensaje);
            $mensaje->setProducto($this);
        }
        return $this;
    }

    public function removeMensaje(Mensaje $mensaje): static
    {
        if ($this->mensajes->removeElement($mensaje)) {
            if ($mensaje->getProducto() === $this) {
                $mensaje->setProducto(null);
            }
        }
        return $this;
    }
}
