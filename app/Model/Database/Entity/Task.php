<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\TaskRepository")
 * @ORM\Table(name="`task`")
 * @ORM\HasLifecycleCallbacks
 */
class Task extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
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
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis"
     * FORM attribute-placeholder='Popis'
     * FORM attribute-class='form-control input-md'
     * FORM attribute-style="height: 200px"
     *
     * GRID type='text'
     * GRID title="Popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="TaskState")
     * FORM type='select'
     * FORM title="Stav"
     * FORM prompt="Nevybráno"
     * FORM attribute-data-live-search='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=TaskState[name][]['orderType' => 'ASC']
     * FORM required="Stav je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Stav"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='TaskState'
     * GRID entity-alias='ts'
     * GRID filter=single-entity #['name']
     */
    protected $taskState;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum založení"
     *
     * GRID type='date'
     * GRID title="Datum založení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $foundedDate;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Uzavřít do"
     * FORM required="Uzavřít do je povinné pole!"
     *
     * GRID type='date'
     * GRID title="Uzavřít do"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $closeToDate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Prioritní?"
     * FORM attribute-class="m-0 mr-2 mt-2"
     * FORM default-value='0'
     *
     * GRID type='bool'
     * GRID title="Prioritní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $priority;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='hidden'
     * FORM data-entity=User[name]
     *
     * GRsID type='translate-text'
     * GRsID title="Založil"
     * GRsID entity-link='name'
     * GRsID visible='false'
     * GRsID entity='User'
     * GRsID entity-alias='uorig'
     * GRsID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $originator;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='select'
     * FORM title="Přiřazeno"
     * FORM prompt='-- zvolte užitavele'
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search="true"
     * FORM data-entity-values=User[$name$][][]
     * FORM required="Přiřazeno je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Přiřazeno"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='uass'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $assigned;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='hidden'
     * FORM data-entity=User[name]
     *
     * GRsID type='translate-text'
     * GRsID title="Upravil"
     * GRsID entity-link='name'
     * GRsID visible='false'
     * GRsID entity='User'
     * GRsID entity-alias='uledit'
     * GRsID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $lastEdited;

    /**
     * @ORM\OneToMany(targetEntity="TaskLog", mappedBy="task")
     */
    protected $taskChangeLog;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $inStateDate;

    /**
     * @ORM\OneToMany(targetEntity="TaskDocument", mappedBy="task")
     */
    protected $documents;

    /**
     * @ORM\OneToMany(targetEntity="TaskComment", mappedBy="task")
     */
    protected $comments;

    public function __construct($data = null)
    {
        $this->foundedDate = new \DateTime();
        $this->inStateDate = new \DateTime();
        parent::__construct($data);
    }
}