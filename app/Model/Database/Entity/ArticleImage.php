<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ArticleImageRepository")
 * @ORM\Table(name="`article_image`")
 */
class ArticleImage extends AbstractEntity
{

    use TId;
    use TCreatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="images")
     */
    protected $article;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $alt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $path;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $orderImg;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}
