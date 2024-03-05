<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerOnTrafficRepository")
 * @ORM\Table(name="`worker_on_traffic`")
 * @ORM\HasLifecycleCallbacks
 */
class WorkerOnTraffic extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Traffic")
     */
    protected $traffic;

    /**
     * @ORM\ManyToOne(targetEntity="Worker")
     */
    protected $worker;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}