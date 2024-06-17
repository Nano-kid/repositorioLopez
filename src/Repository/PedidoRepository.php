<?php

namespace App\Repository;

use App\Entity\Pedido;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pedido>
 */
class PedidoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pedido::class);
    }

    public function findAllOrderedByDateDesc() {
    return $this->createQueryBuilder('p')
        ->orderBy('p.fecha', 'DESC')
        ->getQuery()
        ->getResult();
}

    public function findByUsuarioId($usuarioId) {
        return $this->createQueryBuilder('p')
            ->andWhere('p.usuario = :usuarioId')
            ->setParameter('usuarioId', $usuarioId)
            ->orderBy('p.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function findByEstado($estado) {
        return $this->createQueryBuilder('p')
            ->andWhere('p.estado = :estado')
            ->setParameter('estado', $estado)
            ->orderBy('p.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function findByUsuarioIdAndEstado($usuarioId, $estado = null) {
        $queryBuilder = $this->createQueryBuilder('p')
            ->andWhere('p.usuario = :usuarioId')
            ->setParameter('usuarioId', $usuarioId);
    
        if ($estado !== null) {
            $queryBuilder->andWhere('p.estado = :estado')
                ->setParameter('estado', $estado);
        }
    
        return $queryBuilder->orderBy('p.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Pedido[] Returns an array of Pedido objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Pedido
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
