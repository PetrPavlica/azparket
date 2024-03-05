<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Model\Database\Entity\Attributes\TId;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ArticleInMenuRepository")
 * @ORM\Table(name="`article_in_menu`")
 */
class ArticleInMenu extends AbstractEntity
{

    use TId;
    
    /**
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="menu")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $article;

    /**
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="article")
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $menu;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}