<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;
use App\Model\Database\Entity\Attributes\TId;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ProductImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductImage extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="images")
     */
    protected $product;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $alt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $path;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isMain;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $orderImg;

    public function __construct($data = null)
    {
        $this->isMain = false;
        parent::__construct($data);
    }

}
