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
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerInPlanRepository")
 * @ORM\Table(name="`worker_in_plan`")
 */
class WorkerInPlan extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Worker", inversedBy="plans")
     */
    protected $worker;

    /**
     * @ORM\ManyToOne(targetEntity="ShiftPlan", inversedBy="workers")
     * @ORM\JoinColumn(name="plan_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $plan;

    /**
     * @ORM\ManyToOne(targetEntity="WorkerPosition")
     */
    protected $workerPosition;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $manual;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $plusLog;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $minusLog;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $hours;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}