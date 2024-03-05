<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use App\Model\Database\Entity\Attributes\TId;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class ProductFileInLanguage extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="ProductFile", inversedBy="langs")
     */
    protected $file;

    /**
     * @ORM\ManyToOne(targetEntity="Language")
     */
    protected $lang;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}