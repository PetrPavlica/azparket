<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\SkillInWorkerPositionRepository")
 * @ORM\Table(name="`skill_in_worker_position`")
 * @ORM\HasLifecycleCallbacks
 */
class SkillInWorkerPosition extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Skill", inversedBy="positions")
     */
    protected $skill;

    /**
     * @ORM\ManyToOne(targetEntity="WorkerPosition", inversedBy="skills")
     */
    protected $position;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}