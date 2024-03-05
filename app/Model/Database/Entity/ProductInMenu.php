<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;
use App\Model\Database\Entity\Attributes\TId;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ProductInMenu extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Menu")
     */
    protected $menu;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="menu")
     */
    protected $product;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}

?>