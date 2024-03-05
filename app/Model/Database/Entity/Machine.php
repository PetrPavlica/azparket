<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\MachineRepository")
 * @ORM\Table(name="`machine`")
 * @ORM\HasLifecycleCallbacks
 */
class Machine extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
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
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Evidenční číslo"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Evidenční číslo"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $regId;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text"
     * FORM title="Druh"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Druh"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $type;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Váha (kg)"
     * FORM rule-float ='Prosím zadávejte pouze čísla'
     * FORM required='0'
     *
     * GRID type='text'
     * GRID title="Váha"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $weight;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Aktivní"
     * FORM default-value='1'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='bool'
     * GRID title="Aktivní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    /**
     * @ORM\OneToMany(targetEntity="MachineInExternServiceVisit", mappedBy="machine")
     * FORM type='multiselect'
     * FORM title="Návštěva externího servisu"
     * FORM attribute-multiple='true'
     * FORM attribute-data-live-search='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM multiselect-entity=MachineInExternServiceVisit[machine][externServiceVisit]
     * FORM data-entity-values=ExternServiceVisit[$name$][]['visitDate' > 'DESC']
     * 
     * GRID type='multi-text'
     * GRID title="Návštěva externího servisu"
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='ExternServiceVisit'
     * GRID entity-alias='exsrv'
     * GRID entity-join-column=machine
     * GRID entity-link=name
     * GRID filter=single-entity #['name']
     */
    protected $externServiceVisits;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum nákupu"
     * FORM attribute-class="form-control"
     * FORM attribute-title-icon="fa fa-file-word-o"
     *
     * GRID type='datetime'
     * GRID title="Datum nákupu"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     * GRIiD inline-type='date'
     */
    protected $startDate;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum prodeje"
     * FORM attribute-class="form-control"
     * FORM attribute-title-icon="fa fa-file-word-o"
     *
     * GRID type='datetime'
     * GRID title="Datum prodeje"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     * GRIiD inline-type='date'
     */
    protected $endDate;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}