<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 *
 * @method Course|null find($id, $lockMode = null, $lockVersion = null)
 * @method Course[]    findAll()
 * @method Course[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Course|null findOneBy(array $criteria, array $orderBy = null)
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    public function save(Course $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Course $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByTeacher(User $teacher): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.teacher = :teacher')
            ->setParameter('teacher', $teacher)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublished(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.publishedAt IS NOT NULL')
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByInstrument(string $instrument, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.instrument = :instrument')
            ->andWhere('c.publishedAt IS NOT NULL')
            ->setParameter('instrument', $instrument)
            ->orderBy('c.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByLevel(string $level, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.level = :level')
            ->andWhere('c.publishedAt IS NOT NULL')
            ->setParameter('level', $level)
            ->orderBy('c.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByInstrumentAndLevel(string $instrument, string $level, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.instrument = :instrument')
            ->andWhere('c.level = :level')
            ->andWhere('c.publishedAt IS NOT NULL')
            ->setParameter('instrument', $instrument)
            ->setParameter('level', $level)
            ->orderBy('c.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findRecommendedForUser(User $user, int $limit = 6): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.publishedAt IS NOT NULL')
            ->andWhere('c.teacher != :user')
            ->setParameter('user', $user);

        // If user has preferences, use them
        if ($user->getInstrument()) {
            $qb->andWhere('c.instrument = :instrument')
               ->setParameter('instrument', $user->getInstrument());
        }

        if ($user->getSkillLevel()) {
            $qb->andWhere('c.level = :level')
               ->setParameter('level', $user->getSkillLevel());
        }

        return $qb->orderBy('c.createdAt', 'DESC')
                 ->setMaxResults($limit)
                 ->getQuery()
                 ->getResult();
    }

    public function search(string $query, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.publishedAt IS NOT NULL')
            ->andWhere('c.title LIKE :query OR c.description LIKE :query OR c.instrument LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('c.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findTopCourses(int $limit = 10): array
    {
        return $this->createQueryBuilder('c')
            ->select('c, COUNT(e.id) as enrollmentCount')
            ->leftJoin('c.enrollments', 'e')
            ->andWhere('c.publishedAt IS NOT NULL')
            ->andWhere('e.status = :status')
            ->setParameter('status', 'active')
            ->groupBy('c.id')
            ->orderBy('enrollmentCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findCoursesByTag(string $tag, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.publishedAt IS NOT NULL')
            ->andWhere('JSON_CONTAINS(c.tags, :tag) = 1')
            ->setParameter('tag', '"' . $tag . '"')
            ->orderBy('c.createdAt', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
