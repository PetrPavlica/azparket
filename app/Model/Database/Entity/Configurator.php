<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ConfiguratorRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Configurator extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    // /**
    //  * @ORM\OneToMany(targetEntity="ProductInMenu", mappedBy="product")
    //  * FORM type='multiselect'
    //  * FORM title="Zařazení"
    //  * FORM attribute-class='form-control selectpicker'
    //  * FORM data-entity=Menu[id]
    //  * FORM multiselect-entity=ProductInMenu[product][menu]
    //  * FORM attribute-placeholder='Zařazení'
    //  * FORM attribute-multiple='true'
    //  * FORM attribute-data-live-search='true'
    //  */
    //protected $menu;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class="form-control"
     * FORM required="Název je povinné pole"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-placeholder='Pořadí'
     * FORM required="Toto je je povinné pole!"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRIsD type='integer'
     * GRIsD title="Pořadí"
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='true'
     */
    protected $orderConfigurator;

    /**
     * @ORM\OneToMany(targetEntity="ConfiguratorInput", mappedBy="configurator")
     */
    protected $inputs;

    /**
     * @ORM\OneToMany(targetEntity="ConfiguratorNode", mappedBy="configurator")
     */
    protected $nodes;
    
    /**
     * @ORM\OneToOne(targetEntity="ConfiguratorNode")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $startNode;

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

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}