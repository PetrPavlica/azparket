<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\SkillInWorkerTenderRepository")
 * @ORM\Table(name="`skill_in_worker_tender`")
 * @ORM\HasLifecycleCallbacks
 */
class SkillInWorkerTender extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Skill", inversedBy="tenders")
     */
    protected $skill;

    /**
     * @ORM\ManyToOne(targetEntity="WorkerTender", inversedBy="skills")
     */
    protected $tender;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}