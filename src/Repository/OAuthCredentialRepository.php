<?php

namespace App\Repository;

use App\Entity\OAuthCredential;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OAuthCredential>
 *
 * @method OAuthCredential|null find($id, $lockMode = null, $lockVersion = null)
 * @method OAuthCredential[]    findAll()
 * @method OAuthCredential[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method OAuthCredential|null findOneBy(array $criteria, array $orderBy = null)
 */
class OAuthCredentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OAuthCredential::class);
    }

    public function save(OAuthCredential $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OAuthCredential $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUserAndProvider(User $user, string $provider): ?OAuthCredential
    {
        return $this->findOneBy([
            'user' => $user,
            'provider' => $provider
        ]);
    }

    public function findValidCredentialsByUser(User $user, string $provider): ?OAuthCredential
    {
        $credential = $this->findByUserAndProvider($user, $provider);
        
        if (!$credential || $credential->isExpired()) {
            return null;
        }
        
        return $credential;
    }

    public function findExpiredCredentials(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.expiresAt < :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }
}
