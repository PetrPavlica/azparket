<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ConfiguratorNodeRelationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ConfiguratorNodeRelation extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="ConfiguratorNode", inversedBy="childs")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $parent;
    
    /**
     * @ORM\ManyToOne(targetEntity="ConfiguratorNode", inversedBy="parents")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $child;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}