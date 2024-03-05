<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ArticleFileRepository")
 * @ORM\Table(name="`article_file`")
 */
class ArticleFile extends AbstractEntity
{

    use TId;
    use TCreatedAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="Article", inversedBy="files")
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
    protected $orderFile;

    /**
     * @ORM\OneToMany(targetEntity="ArticleFileInLanguage", mappedBy="file")
     */
    protected $langs;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}
