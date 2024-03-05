<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;



/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\MenuRepository")
 * @ORM\Table(name="`menu`")
 * @ORM\HasLifecycleCallbacks
 */
class Menu extends AbstractEntity
{

    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="childMenu")
     * @ORM\JoinColumn(name="parent_menu_id", referencedColumnName="id", onDelete="SET NULL")
     * FORM type='select'
     * FORM title='Rodičovské menu'
     * FORM prompt='-- zvolte menu'
     * FORM data-entity=Menu[id][][]
     * FORM data-entity-values=Menu[$id$][]['orderPage' > 'ASC']
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search="true"
     *
     * GRIsD type='text'
     * GRIsD title="Rodičovské menu"
     * GRIsD entity-link='name'
     * GRIsD visible='true'
     * GRIsD entity='Menu'
     * GRIsD entity-alias='prct'
     * GRIsD filter=single-entity #['name']
     */
    protected $parentMenu;

    /**
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="parentMenu")
     */
    protected $childMenu;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Pořadí (priorita)"
     * FORM attribute-placeholder='Pořadí'
     * FORM required="Toto je povinné pole!"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRIsD type='integer'
     * GRIsD title="Pořadí"
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='true'
     */
    protected $orderPage;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $image;

    /**
     * @ORM\OneToMany(targetEntity="ArticleInMenu", mappedBy="menu")
     */
    protected $articles;

     /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type="checkbox"
     * FORM title="Skrýt v číselnících"
     * FORM attribute="form-control"
     * FORM default-value=0
     */
    protected $hideInSelect;
    
    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->hideInSelect = true;
    }

}

?>