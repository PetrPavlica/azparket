<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ArticleFileInLanguageRepository")
 * @ORM\Table(name="`article_file_in_language`")
 */
class ArticleFileInLanguage extends AbstractEntity
{

    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="ArticleFile", inversedBy="langs")
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