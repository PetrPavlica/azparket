<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\InquiryProduct;

/**
 * @method InquiryProduct|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method InquiryProduct|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method InquiryProduct[] findAll()
 * @method InquiryProduct[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<InquiryProduct>
 */
class InquiryProductRepository extends AbstractRepository
{

}