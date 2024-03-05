<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ReservationPlanRepository")
 * @ORM\Table(name="`reservation_plan`")
 */
class ReservationPlan extends AbstractEntity
{
    use TId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="ProductionPlan", mappedBy="reservation")
     */
    protected $plans;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}