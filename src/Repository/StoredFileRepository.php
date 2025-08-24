<?php

namespace App\Repository;

use App\Entity\StoredFile;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StoredFile>
 *
 * @method StoredFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method StoredFile[]    findAll()
 * @method StoredFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method StoredFile|null findOneBy(array $criteria, array $orderBy = null)
 */
class StoredFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StoredFile::class);
    }

    public function save(StoredFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StoredFile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUser(User $user, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->andWhere('f.uploadedBy = :user')
            ->setParameter('user', $user)
            ->orderBy('f.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByContentType(string $contentType, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->andWhere('f.contentType LIKE :contentType')
            ->setParameter('contentType', $contentType . '%')
            ->orderBy('f.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findImagesByUser(User $user, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('f')
            ->andWhere('f.uploadedBy = :user')
            ->andWhere('f.contentType LIKE :contentType')
            ->setParameter('user', $user)
            ->setParameter('contentType', 'image/%')
            ->orderBy('f.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByBlobName(string $blobName): ?StoredFile
    {
        return $this->findOneBy(['blobName' => $blobName]);
    }

    public function findRecentFiles(int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
