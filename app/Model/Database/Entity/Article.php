<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ArticleRepository")
 * @ORM\Table(name="`article`")
 * @ORM\HasLifecycleCallbacks
 */
class Article extends AbstractEntity
{

    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\OneToMany(targetEntity="ArticleInMenu", mappedBy="article")
     * FORM type='multiselect'
     * FORM title="Zařazení"
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=Menu[id]['hideInSelect' => 0][]
     * FORM multiselect-entity=ArticleInMenu[article][menu]
     * FORM attribute-placeholder='Zařazení'
     * FORM attribute-multiple='true'
     * FORM attribute-data-live-search='true'
     */
    protected $menu;

    //* FORM rule-integer='Prosím zadávejte pouze čísla'
    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-placeholder='Pořadí'
     * FORM required="Toto je je povinné pole!"
     * FORM attribute-class='form-control input-md'
     * FORM default-value=0
     *
     * GRIsD type='integer'
     * GRIsD title="Pořadí"
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='true'
     */
    protected $orderArticle;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='datetime'
     * FORM title="Datum zveřejnění"
     * FORM attribute-class='form-control input-md flatPick'
     * FORM attribute-placeholder='Datum zveřejnění'
     * FORM required='Toto pole je povinné'
     *
     * GRIsD type='datetime'
     * GRIsD title="Datum zveřejnění"
     * GRIsD sortable='true'
     * GRIsD filter='date-range'
     * GRIsD visible='false'
     */
    protected $publish;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='hidden'
     *
     * GRID type='text'
     * GRID title="Typ článku"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'default' > 'Základní'|'new' > 'Aktualita'|'template' > 'Reference'|'event' > 'Akce']
     * GRID replacement=select #['default' > 'Základní'|'new' > 'Aktualita'|'template' > 'Reference'|'event' > 'Akce']
     * GRID visible='true'
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="ArticleImage", mappedBy="article")
     */
    protected $images;

    /**
     * @ORM\OneToMany(targetEntity="ArticleFile", mappedBy="article")
     */
    protected $files;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}

?>