<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerOnVisitRepository")
 * @ORM\Table(name="`worker_on_visit`")
 * @ORM\HasLifecycleCallbacks
 */
class WorkerOnVisit extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Visit")
     */
    protected $visit;

    /**
     * @ORM\ManyToOne(targetEntity="Worker")
     */
    protected $worker;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}