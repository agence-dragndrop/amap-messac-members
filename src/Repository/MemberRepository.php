<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Member|null find($id, $lockMode = null, $lockVersion = null)
 * @method Member|null findOneBy(array $criteria, array $orderBy = null)
 * @method Member[]    findAll()
 * @method Member[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function findMembersByFullName(string $fullName, bool $fetchOne = true)
    {
        $fullName = explode(" ", $fullName);
        $lastName = $fullName[0];
        $firstName = $fullName[1] ?? null;
        $query = $this->createQueryBuilder("m")
            ->andWhere("m.lastName LIKE :lastName")
            ->setParameter("lastName", "%" . $lastName . "%");
        if ($firstName) {
            $query->andWhere("m.firstName LIKE :firstName")
                ->setParameter("firstName", "%" . $firstName . "%");
        }
        if ($fetchOne) {
            return $query->getQuery()
                ->getOneOrNullResult();
        } else {
            return $query->getQuery()
                ->getResult();
        }
    }

    public function searchMember(string $search)
    {
        return $this->createQueryBuilder("m")
            ->andWhere("m.firstName LIKE :firstname")
            ->orWhere("m.lastName LIKE :lastname")
            ->setParameter("firstname", "%" . $search . "%")
            ->setParameter("lastname", "%" . $search . "%")
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Member[] Returns an array of Member objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Member
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
