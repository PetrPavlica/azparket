<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ArticleGalleryRepository")
 * @ORM\Table(name="`article_gallery`")
 */
class ArticleGallery extends AbstractEntity
{

    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Article")
     */
    protected $article;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název článku"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název článku'
     * FORM required="Název článku je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název článku"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Článek"
     * FORM attribute-class='ckeditor'
     * FORM attribute-placeholder='Článek'
     *
     * GRID type='text'
     * GRID title="Článek"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $content;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Zobrazit"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Zobrazit"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Zobrazit název"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Zobrazit název"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $showName;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Rozevírací"
     *
     * GRID type='bool'
     * GRID title="Rozevírací"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $dropdown;

    /**
     * @ORM\ManyToOne(targetEntity="Language")
     */
    protected $lang;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}