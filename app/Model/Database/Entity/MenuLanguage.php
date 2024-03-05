<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\MenuLanguageRepository")
 * @ORM\Table(name="`menu_language`")
 */
class MenuLanguage extends AbstractEntity
{

    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Menu")
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $menu;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $nameOnFront;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $nameOnSubFront;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $link;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $visible;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $showUp;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $newWindow;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $keywords;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $showOnHomepage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $showSignpost;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $menuDescription;

    /**
     * @ORM\ManyToOne(targetEntity="Language")
     */
    protected $lang;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->name = '';
    }
}