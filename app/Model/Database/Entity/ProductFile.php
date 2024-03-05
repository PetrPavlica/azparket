<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ProductFileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductFile extends AbstractEntity
{
    use TId;
    use TCreatedAt;


    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="files")
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
     * 1 -> výkres
     * 2 -> 3D model
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $section;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $orderFile;

    /**
     * @ORM\OneToMany(targetEntity="ProductFileInLanguage", mappedBy="file")
     */
    protected $langs;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}
