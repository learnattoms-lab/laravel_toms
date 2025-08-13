<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find users by role
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%')
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count users by role
     */
    public function countByRole(string $role): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%' . $role . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count active users
     */
    public function countActiveUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find teachers with their statistics
     */
    public function findTeachersWithStats(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isTeacher = :isTeacher')
            ->setParameter('isTeacher', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find students with their progress
     */
    public function findStudentsWithProgress(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isTeacher = :isTeacher')
            ->setParameter('isTeacher', false)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find users by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Find users by Google ID
     */
    public function findByGoogleId(string $googleId): ?User
    {
        return $this->findOneBy(['googleId' => $googleId]);
    }

    /**
     * Find users by Apple ID
     */
    public function findByAppleId(string $appleId): ?User
    {
        return $this->findOneBy(['appleId' => $appleId]);
    }

    /**
     * Find users by Facebook ID
     */
    public function findByFacebookId(string $facebookId): ?User
    {
        return $this->findOneBy(['facebookId' => $facebookId]);
    }

    /**
     * Get recent users
     */
    public function findRecentUsers(int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get top performing teachers
     */
    public function findTopTeachers(int $limit = 5): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isTeacher = :isTeacher')
            ->andWhere('u.rating IS NOT NULL')
            ->setParameter('isTeacher', true)
            ->orderBy('u.rating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get users with highest XP
     */
    public function findTopStudentsByXP(int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isTeacher = :isTeacher')
            ->setParameter('isTeacher', false)
            ->orderBy('u.experiencePoints', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search users
     */
    public function searchUsers(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.firstName LIKE :query OR u.lastName LIKE :query OR u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get user statistics for admin dashboard
     */
    public function getUserStatistics(): array
    {
        $qb = $this->createQueryBuilder('u');
        
        $totalUsers = $qb->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();
        
        $activeUsers = $this->countActiveUsers();
        $teachers = $this->countByRole('ROLE_TEACHER');
        $students = $this->countByRole('ROLE_USER') - $teachers;
        
        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'teachers' => $teachers,
            'students' => $students,
            'inactive_users' => $totalUsers - $activeUsers
        ];
    }
}
