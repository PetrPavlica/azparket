<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ConfiguratorNodeRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ConfiguratorNode extends AbstractEntity
{
    use TId;
    
    /**
     * @ORM\ManyToOne(targetEntity="Configurator", inversedBy="nodes")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $configurator;

    /**
     * @ORM\ManyToOne(targetEntity="ConfiguratorInput", inversedBy="nodes")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * 
     * FORM type='select'
     * FORM title="Vstupní pole"
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity-values=ConfiguratorInput[$name$][][]
     * FORM attribute-data-live-search="true"
     * FORM prompt="Bez pole"
     */
    protected $input;

    /**
     * @ORM\Column(type="string", length="255", nullable=true)
     * FORM type="text"
     * FORM title="Označení"
     * FORM attribute-class='form-control'
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string", length="255", nullable=true)
     * FORM type="text"
     * FORM title="Hodnota uzlu"
     * FORM attribute-class='form-control'
     */
    protected $value;

    /**
     * @ORM\OneToMany(targetEntity="ConfiguratorNodeProduct", mappedBy="node")
     */
    protected $products;

    /**
     * @ORM\OneToMany(targetEntity="ConfiguratorNodeRelation", mappedBy="child")
     * 
     * FORM type='multiselect'
     * FORM title="Rodičovské uzly"
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity-values=ConfiguratorNode[$nodeNo$. $name$ ($value$)][]['nodeNo' => 'ASC']
     * FORM multiselect-entity=ConfiguratorNodeRelation[child][parent]
     * FORM attribute-multiple='true'
     * FORM attribute-data-live-search='true'
     */
    protected $parents;
    
    /**
     * @ORM\OneToMany(targetEntity="ConfiguratorNodeRelation", mappedBy="parent")
     */
    protected $childs;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='text'
     * FORM required='1'
     * FORM default-value='1'
     * FORM title="Číslo uzlu"
     * FORM rule-number='Musí být číslo'
     * FORM attribute-class="form-control"
     *
     * GRID type='number'
     * GRID title="Číslo uzlu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $nodeNo;

    /**
     * @ORM\Column(type="boolean", nullable="true")
     * FORM type='checkbox'
     * FORM title="Odkázat na obchodníka"
     */
    protected $forSalesman;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->forSalesman = 0;
    }
}