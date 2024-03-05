<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ProductInPlanRepository")
 * @ORM\Table(name="`product_in_plan`")
 */
class ProductInPlan extends AbstractEntity
{
    use TId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $product;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $productId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $orderName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $orderId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $orderItemId;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionPlan", inversedBy="products")
     * @ORM\JoinColumn(name="plan_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $plan;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $counts;

    /**
     * @ORM\ManyToOne(targetEntity="ReservationProduct")
     */
    protected $reservation;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}