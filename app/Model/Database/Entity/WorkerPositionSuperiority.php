<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerPositionSuperiorityRepository")
 * @ORM\Table(name="`worker_position_superiority`")
 * @ORM\HasLifecycleCallbacks
 */
class WorkerPositionSuperiority extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="WorkerPosition", inversedBy="subordinatePositions")
     */
    protected $superiorPosition;

    /**
     * @ORM\ManyToOne(targetEntity="WorkerPosition", inversedBy="superiorPositions")
     */
    protected $subordinatePosition;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}