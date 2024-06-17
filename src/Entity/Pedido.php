<?php

namespace App\Entity;

use App\Repository\PedidoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PedidoRepository::class)]
class Pedido
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\ManyToOne(inversedBy: 'pedidos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    /**
     * @var Collection<int, LineasVenta>
     */
    #[ORM\OneToMany(targetEntity: LineasVenta::class, mappedBy: 'pedido', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $lineasVentas;

    public function __construct()
    {
        $this->lineasVentas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * @return Collection<int, LineasVenta>
     */
    public function getLineasVentas(): Collection
    {
        return $this->lineasVentas;
    }

    public function addLineasVenta(LineasVenta $lineasVenta): static
    {
        if (!$this->lineasVentas->contains($lineasVenta)) {
            $this->lineasVentas->add($lineasVenta);
            $lineasVenta->setPedido($this);
        }

        return $this;
    }

    public function removeLineasVenta(LineasVenta $lineasVenta): static
    {
        if ($this->lineasVentas->removeElement($lineasVenta)) {
            // set the owning side to null (unless already changed)
            if ($lineasVenta->getPedido() === $this) {
                $lineasVenta->setPedido(null);
            }
        }

        return $this;
    }
}
