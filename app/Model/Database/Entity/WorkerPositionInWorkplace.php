<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerPositionInWorkplaceRepository")
 * @ORM\Table(name="`worker_position_in_workplace`")
 */
class WorkerPositionInWorkplace extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Workplace", inversedBy="workerPositions")
     */
    protected $workplace;

    /**
     * @ORM\ManyToOne(targetEntity="WorkerPosition", inversedBy="workplaces")
     */
    protected $position;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}