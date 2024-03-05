<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\InquiryValue;

/**
 * @method InquiryValue|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method InquiryValue|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method InquiryValue[] findAll()
 * @method InquiryValue[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<InquiryValue>
 */
class InquiryValueRepository extends AbstractRepository
{

}