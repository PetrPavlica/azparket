<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\SkillInWorkerRepository")
 * @ORM\Table(name="`skill_in_worker`")
 * @ORM\HasLifecycleCallbacks
 */
class SkillInWorker extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Skill", inversedBy="workers")
     */
    protected $skill;

    /**
     * @ORM\ManyToOne(targetEntity="Worker", inversedBy="skills")
     */
    protected $worker;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}