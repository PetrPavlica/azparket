<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\AbsenceRepository")
 * @ORM\Table(name="`absence`")
 * @ORM\HasLifecycleCallbacks
 */
class Absence extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FOsRM type='text'
     * FOsRM title="Důvod absence"
     * FOsRM attribute-class="form-control"
     *
     * GRsID type='text'
     * GRsID title="Důvod absence"
     * GRsID visible='true'
     * GRsID filter='single'
     * GRsID sortable='true'
     */
    protected $name;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum (začátek)"
     * FOsRM required='Začátek je povinný!'
     *
     * GRID type='date'
     * GRID title="Datum (začátek)"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateStart;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum (konec)"
     * FORM attribute-placeholder='Pro jednodenní nevyplňovat'
     *
     * GRID type='date'
     * GRID title="Datum (konec)"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateEnd;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='select'
     * FORM title="Zaměstnanec"
     * FORM prompt='-- zvolte užitavele'
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search="true"
     * FORM data-entity-values=User[$name$][][]
     * FORM required="Zaměstnanec je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Zaměstnanec"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='usr'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="AbsenceState")
     * FOsRM type='select'
     * FOsRM title="Stav absence"
     * FOsRM attribute-class='form-control selectpicker'
     * FOsRM attribute-data-live-search="true"
     * FOsRM data-entity-values=AbsenceState[$name$][][]
     * FOsRM required="Stav je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Stav absence"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='AbsenceState'
     * GRID entity-alias='ast'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $state;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Časové rozmezí"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Časové rozmezí"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $timeRange;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Počítat k odpracováno"
     * FORM default-value='0'
     *
     * GRID type='bool'
     * GRID title="Počítat k odpracováno"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $addWorked;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Celý den"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Celý den"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $wholeDay;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='select'
     * FORM title="Zástup"
     * FORM prompt='-- zvolte zástup'
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search="true"
     * FORM data-entity-values=User[$name$][][]
     *
     * GRID type='text'
     * GRID title="Zaměstnanec zástup"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='User'
     * GRID entity-alias='usrdel'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $userDelegate;

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
     * @ORM\ManyToOne(targetEntity="AbsenceReason")
     * FORM type='select'
     * FORM title="Důvod absence"
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search="true"
     * FORM data-entity-values=AbsenceReason[$name$][][]
     * FORM required="Důvod je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Důvod absence"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='AbsenceReason'
     * GRID entity-alias='ar'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $reason;

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

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}