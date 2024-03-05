<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ConfiguratorNodeProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ConfiguratorNodeProduct extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="ConfiguratorNode", inversedBy="products")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $node;
    
    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="products")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $product;

    /**
     * @ORM\Column(type="integer")
     */
    protected $count;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->count = 1;
    }
}