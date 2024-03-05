<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ProductionPlanRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductionPlan extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $dateString;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum"
     *
     * GRID type='date'
     * GRID title="Datum"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $datePlan;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Směna"
     *
     * GRID type='text'
     * GRID title="Směna"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $shift;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Linka"
     *
     * GRID type='text'
     * GRID title="Linka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $productionLine;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $customer;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $customerId;

    /**
     * @ORM\ManyToOne(targetEntity="ReservationPlan")
     */
    protected $reservation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $rodHang;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $rodSend;

    /**
     * @ORM\OneToMany(targetEntity="ProductInPlan", mappedBy="plan")
     */
    protected $products;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}