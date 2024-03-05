<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\CustomerInTypeRepository")
 * @ORM\Table(name="`customer_in_type`")
 * @ORM\HasLifecycleCallbacks
 */
class CustomerInType extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="types")
     */
    protected $customer;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerType")
     */
    protected $type;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}