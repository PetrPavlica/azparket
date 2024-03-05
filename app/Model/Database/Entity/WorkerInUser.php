<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerInUserRepository")
 * @ORM\Table(name="`worker_in_user`")
 */
class WorkerInUser extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Worker", inversedBy="masters")
     * @ORM\JoinColumn(name="worker_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $worker;

    /**
     * User is master
     * 
     * @ORM\ManyToOne(targetEntity="User", inversedBy="workers")
     */
    protected $master;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}