<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ApproveRepository")
 * @ORM\Table(name="`approve`")
 * @ORM\HasLifecycleCallbacks
 */
class Approve extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Číslo nabídky"
     * FORM attribute-class="form-control"
     * FORnM attribute-placeholder="(bude vyplněno automaticky)"
     *
     * GRID type='text'
     * GRID title="Číslo nabídky"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="ApproveState")
     * FORM type='select'
     * FORM attribute-class='form-control'
     * FORM title="Stav"
     * FORM data-entity=ApproveState[name]
     * FORM data-entity-values=ApproveState[$name$][]['order' > 'ASC']
     *
     * GRID type='text'
     * GRID title="Stav"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='ApproveState'
     * GRID entity-alias='arss'
     * GRID filter=single-entity #['name']
     */
    protected $approveState;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Zákazník"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Zákazník"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $customerShort;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Poptávka"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Poptávka"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $request;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Ze dne"
     * FORM attribute-class="form-control"
     *
     * GRID type='date'
     * GRID title="Ze dne"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $startDate;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Termín"
     * FORM attribute-class="form-control"
     *
     * GRID type='date'
     * GRID title="Termín"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $deadlineDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka"
     * FORM attribute-class='autosized'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="ApproveTime")
     * FORM type='select'
     * FORM attribute-class='form-control'
     * FORM title="Doba na posouzení"
     * FORM data-entity=ApproveTime[name]
     * FORM data-entity-values=ApproveTime[$name$][]['id' > 'ASC']
     *
     * GRID type='text'
     * GRID title="Doba na posouzení"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='ApproveTime'
     * GRID entity-alias='apt'
     * GRID filter=single-entity #['name']
     */
    protected $approveTime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Typ"
     * FORM data-own=['Projekt' > 'Projekt'|'Výroba' > 'Výroba'|'TPV' > 'TPV']
     * FORM prompt="- nevybráno"
     *
     * GRID type='text'
     * GRID title="Typ"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'Projekt' > 'Projekt'|'Výroba' > 'Výroba'|'TPV' > 'TPV']
     */
    protected $approveType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Zkrácené"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='bool'
     * GRID title="Zkrácené"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $short;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $sendFinish;

    /**
     * @ORM\OneToMany(targetEntity="ApprovePart", mappedBy="approve")
     */
    protected $parts;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}