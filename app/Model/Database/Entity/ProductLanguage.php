<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;
use App\Model\Database\Entity\Attributes\TId;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ProductLanguageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductLanguage extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Product")
     */
    protected $product;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $shortDescription;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @ORM\ManyToOne(targetEntity="Language")
     */
    protected $lang;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->active = 1;
    }
}