<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ConfiguratorInputRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ConfiguratorInput extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Configurator", inversedBy="inputs")
     * @ORM\JoinColumn(onDelete="CASCADE")
     
     * FORM type='select'
     * FORM title="Konfigurátor"
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=Configurator[id]
     * FORM attribute-placeholder='Zařazení'
     * FORM attribute-data-live-search='true'
     */
    protected $configurator;

    /**
     * @var string
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
     * @ORM\Column(type="string", length=255, nullable="true")
     */
    protected $webName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    
    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Pořadí polí"
     * FORM attribute-placeholder='Pořadí'
     * FORM required="Toto je je povinné pole!"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     */
    protected $orderInput;

    /**
     * @ORM\OneToMany(targetEntity="ConfiguratorNode", mappedBy="input")
     */
    protected $nodes;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->orderInput = 0;
    }
}