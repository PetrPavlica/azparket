<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Database\Entity\Attributes\TId;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ArticleNewRepository")
 * @ORM\Table(name="`article_new`")
 */
class ArticleNew extends AbstractEntity
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
     * @ORM\Column(type="date", nullable=true)
     * FORM type='text'
     * FORM title="Datum aktuality"
     * FORM attribute-class='form-control input-md'
     * FORM required='Toto pole je povinné'
     * FORM attribute-placeholder='Datum aktuality'
     * 
     * GRID type='datetime'
     * GRID title="Datum aktuality"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $dateStart;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Text konání"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Konání'
     *
     * GRID type='text'
     * GRID title="Konání"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $dateText;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $link;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Perex"
     * FORM attribute-class='ckeditor'
     * FORM attribute-placeholder='Perex'
     *
     * GRID type='text'
     * GRID title="Perex"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $perex;

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
     * FORM title="Zobrazit v kalendáři"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="V kalendáři"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $showInCalendar;

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