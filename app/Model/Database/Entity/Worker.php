<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerRepository")
 * @ORM\Table(name="`worker`")
 * @ORM\HasLifecycleCallbacks
 */
class Worker extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Jméno"
     * FORM attribute-class="form-control"
     * FORM required="Jméno je povinné pole"
     *
     * GRID type='text'
     * GRID title="Jméno"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Přijmení"
     * FORM attribute-class="form-control"
     * FORM required="Příjmení je povinné pole"
     *
     * GRID type='text'
     * GRID title="Přijmení"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $surname;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Osobní číslo"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Osobní číslo"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $personalId;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Národnost"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Národnost"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $nationality;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum narození"
     * FORM attribute-class="form-control"
     *
     * GRID type='date'
     * GRID title="Datum narození"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $birthDate;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     * FORM type='text'
     * FORM title='Telefon'
     *
     * GRID type='text'
     * GRID title="Telefon"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $phone;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     * FORM type='email'
     * FORM title='E-mail'
     * FORM required="E-mail je povinné pole"
     *
     * GRID type='text'
     * GRID title="E-mail"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     * FORM type='text'
     * FORM title='Ulice'
     *
     * GRID type='text'
     * GRID title="Ulice"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $street;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     * FORM type='text'
     * FORM title='Město'
     *
     * GRID type='text'
     * GRID title="Město"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $city;

    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=true)
     * FORM type='text'
     * FORM title='PSČ'
     *
     * GRID type='text'
     * GRID title="PSČ"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $zip;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     * FORM type='text'
     * FORM title='Stát'
     *
     * GRID type='text'
     * GRID title="Stát"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $country;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     * FORM type='text'
     * FORM title='Pojištovna'
     *
     * GRID type='text'
     * GRID title="Pojištovna"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $insurance;

    
    
    /**
     * @ORM\ManyToOne(targetEntity="WorkerPosition", inversedBy="workers")
     * FOsRM type='select'
     * FOsRM attribute-class='form-control selectpicker'
     * FOsRM title="Pracovní pozice"
     * FOsRM prompt="Nic není vybráno"
     * FOsRM attribute-data-live-search='true'
     * FOsRM data-entity=WorkerPosition[name]
     * FOsRM data-entity-values=WorkerPosition[$name$][]['id' > 'ASC']
     * 
     * GRsID type='text'
     * GRsID title="Pracovní pozice"
     * GRsID entity-link='name'
     * GRsID visible='true'
     * GRsID sortable='true'
     * GRsID entity='WorkerPosition'
     * GRsID entity-alias='wrpo'
     * GRsID filter=single-entity #['name']
     */
    protected $workerPosition;

    /**
     * @ORM\OneToMany(targetEntity="SkillInWorker", mappedBy="worker")
     * FOsRM type='multiselect'
     * FOsRM title="Pracovní dovednosti"
     * FOsRM attribute-multiple='true'
     * FOsRM attribute-data-live-search='true'
     * FOsRM attribute-class='form-control selectpicker'
     * FOsRM data-entity=Skill[name][]['orderN' => 'ASC']
     * FOsRM multiselect-entity=SkillInWorker[worker][skill]
     * 
     * GRsID type='multi-text'
     * GRsID title="Pracovní dovednosti"
     * GRsID visible='true'
     * GRsID filter='single'
     * GRsID entity-join-column=skill
     * GRsID entity-link=name
     */
    protected $workerSkills;
    
    /**
     * @ORM\ManyToOne(targetEntity="ProductionLine", inversedBy="workers")
     * FOsRM type='select'
     * FOsRM attribute-class="form-control selectpicker"
     * FOsRM title="Výrobní linka"
     * FOsRM prompt="Nic není vybráno"
     * FOsRM data-entity=ProductionLine[name][]['name' => 'ASC']
     *
     * GRsID type='text'
     * GRsID title="Výrobní linka"
     * GRsID entity-link='name'
     * GRsID visible='true'
     * GRsID sortable='true'
     * GRsID entity='ProductionLine'
     * GRsID entity-alias='prli'
     * GRsID filter=single-entity #['name']
     */
    protected $productionLine;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FOsRM type='select'
     * FOsRM title="Směna"
     * FOsRM data-own=['8' > '8 hod'|'A' > 'A'|'B' > 'B'|'C' > 'C'|'D' > 'D']
     * FOsRM prompt="- nepřiřazeno"
     *
     * GRsID type='text'
     * GRsID title="Směna"
     * GRsID sortable='true'
     * GRsID filter='single'
     * GRsID visible='true'
     * GRsID filter=select #['' > 'Vše'|'8' > '8 hod'|'A' > 'A'|'B' > 'B'|'C' > 'C'|'D' > 'D']
     */
    protected $shift;

    /**
     * GRsID type='file'
     * GRsID title="Směna navíc"
     * GRsID sortable='false'
     * GRsID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRsID visible='true'
     * GRsID align='center'
     */
    protected $shiftBonusGridOnly;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FOsRM type='date'
     * FOsRM title="Datum změny směny"
     * FOsRM attribute-class="form-control"
     *
     * GRsID type='datetime'
     * GRsID title="Datum změny směny"
     * GRsID sortable='true'
     * GRsID filter='date-range'
     * GRsID visible='false'
     */
    protected $startDateChange;

    /**
     * @ORM\ManyToOne(targetEntity="ProductionLine", inversedBy="workersChange")
     * FOsRM type='select'
     * FOsRM attribute-class="form-control selectpicker"
     * FOsRM title="Výrobní linka po změně"
     * FOsRM prompt="Nic není vybráno"
     * FOsRM data-entity=ProductionLine[name][]['name' => 'ASC']
     *
     * GRsID type='text'
     * GRsID title="Výrobní linka po změně"
     * GRsID entity-link='name'
     * GRsID visible='true'
     * GRsID sortable='true'
     * GRsID entity='ProductionLine'
     * GRsID entity-alias='prlic'
     * GRsID filter=single-entity #['name']
     */
    protected $productionLineChange;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FOsRM type='select'
     * FOsRM title="Směna po změně"
     * FOsRM data-own=['8' > '8 hod'|'A' > 'A'|'B' > 'B'|'C' > 'C'|'D' > 'D']
     * FOsRM prompt="- nepřiřazeno"
     *
     * GRsID type='text'
     * GRsID title="Směna po změně"
     * GRsID sortable='true'
     * GRsID filter='single'
     * GRsID visible='true'
     * GRsID filter=select #['' > 'Vše'|'8' > '8 hod'|'A' > 'A'|'B' > 'B'|'C' > 'C'|'D' > 'D']
     */
    protected $shiftChange;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FOsRM type='select'
     * FOsRM title="Fond pracovní doby"
     * FOsRM default-value='0'
     * FOsRM data-own=['0' > 'kmen'|'200' > '200'|'240' > '240'|'280' > '280']
     *
     * GRsID type='text'
     * GRsID title="FPD"
     * GRsID sortable='true'
     * GRsID filter='single'
     * GRsID visible='true'
     * GRsID filter=select #['' > 'Vše'|'0' > 'kmen'|'200' > '200'|'240' > '240'|'280' > '280']
     */
    protected $timeFund;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='radio'
     * FORM title="Pohlaví"
     * FORM data-own=['0' > 'Žena'|'1' > 'Muž'|'2' > 'Jiné']
     * FORM default-value='1'
     *
     * GRID type='translate-text'
     * GRID title="Pohlaví"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'0' > 'Žena'|'1' > 'Muž'|'2' > 'Jiné']
     * GRID replacement=#['0' > 'Žena'|'1' > 'Muž'|'2' > 'Jiné']
     */
    protected $male;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FOsRM type='checkbox'
     * FOsRM title="Agenturní"
     * FOsRM default-value='0'
     * FOsRM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRsID type='bool'
     * GRsID title="Agenturní"
     * GRsID sortable='true'
     * GRsID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRsID visible='false'
     * GRsID align='center'
     */
    protected $agency;

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
     * @ORM\OneToMany(targetEntity="WorkerInWorkerTender", mappedBy="worker")
     * FOsRM type='multiselect'
     * FOsRM title="Výběrová řízení"
     * FOsRM attribute-multiple='true'
     * FOsRM attribute-data-live-search='true'
     * FOsRM attribute-class='form-control selectpicker'
     * FOsRM multiselect-entity=WorkerInWorkerTender[worker][tender]
     * FOsRM data-entity-values=WorkerTender[$name$][]['tenderDate' > 'DESC']
     * 
     * GRsID type='multi-text'
     * GRsID title="Výběrová řízení"
     * GRsID visible='false'
     * GRsID sortable='true'
     * GRsID entity='WorkerTender'
     * GRsID entity-alias='wrtnd'
     * GRsID entity-join-column=worker
     * GRsID entity-link=name
     * GRsID filter=single-entity #['name']
     */
    protected $workerTenders;

    /**
     * @ORM\ManyToOne(targetEntity="Employment")
     * FOsRM type='select'
     * FOsRM prompt="Nic není vybráno"
     * FOsRM attribute-class="form-control selectpicker"
     * FOsRM title="Pracovní poměr"
     * FOsRM data-entity=Employment[name]
     * FOsRM data-entity-values=Employment[$czicse$ - $name$][]['id' > 'ASC']
     *
     * GRsID type='text'
     * GRsID title="Pracovní poměr"
     * GRsID entity-link='name'
     * GRsID visible='false'
     * GRsID sortable='true'
     * GRsID entity='Employment'
     * GRsID entity-alias='wemt'
     * GRsID filter=single-entity #['name']
     */
    protected $workerEmployment;

    /**
     * @ORM\ManyToOne(targetEntity="Worker")
     * FOsRM type='select'
     * FOsRM attribute-class="form-control selectpicker"
     * FOsRM title="Nenabízet na směnu s"
     * FOsRM prompt="-- (platí pro obě linky)"
     * FOsRM data-entity-values=Worker[$surname$ $name$][]['surname' => 'ASC']
     *
     * GRsID type='text'
     * GRsID title="Nenabízet na směnu s"
     * GRsID entity-link='surname'
     * GRsID visible='true'
     * GRsID sortable='true'
     * GRsID entity='Worker'
     * GRsID entity-alias='wrkk'
     * GRsID value-mask=#[$surname$ $name$]
     * GRsID filter=single-entity #['name', 'surname']
     */
    protected $notWorker;

    /**
     * @ORM\ManyToOne(targetEntity="Worker")
     * FOsRM type='select'
     * FOsRM attribute-class="form-control selectpicker"
     * FOsRM title="Preferuje směnu s"
     * FOsRM prompt="-- nevybráno"
     * FOsRM data-entity-values=Worker[$surname$ $name$][]['surname' => 'ASC']
     *
     * GRsID type='text'
     * GRsID title="Preferuje směnu s"
     * GRsID entity-link='surname'
     * GRsID visible='true'
     * GRsID sortable='true'
     * GRsID entity='Worker'
     * GRsID entity-alias='wrkkp'
     * GRsID value-mask=#[$surname$ $name$]
     * GRsID filter=single-entity #['name', 'surname']
     */
    protected $yesWorker;

    /**
     * @ORM\OneToMany(targetEntity="WorkerInUser", mappedBy="worker")
     * FOsRM type='multiselect'
     * FOsRM title="Vedoucí"
     * FOsRM attribute-class='form-control selectpicker'
     * FOsRM attribute-multiple='true'
     * FOsRM attr-data-live-search='true'
     * FOsRM data-entity-values=User[$name$]['isMaster' > 1][]
     * FOsRM multiselect-entity=WorkerInUser[worker][master]
     *
     * GRsID type='multi-text'
     * GRsID title="Vedoucí"
     * GRsID entity-link='name'
     * GRsID visible='false'
     * GRsID sortable='true'
     * GRsID entity='User'
     * GRsID entity-alias='wm'
     * GRsID entity-join-column='master'
     * GRsID filter=multiselect-entity #[name]['name' > 'ASC']
     */
    protected $masters;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum nástupu"
     * FORM attribute-class="form-control"
     * FORM attribute-title-icon="fa fa-file-word-o"
     *
     * GRID type='datetime'
     * GRID title="Datum nástupu"
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
     * FORM title="Pracovní poměr do"
     * FORM attribute-class="form-control"
     * FORM attribute-title-icon="fa fa-file-word-o"
     *
     * GRID type='datetime'
     * GRID title="Pracovní poměr do"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     * GRIiD inline-type='date'
     */
    protected $endDate;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Ukončení pracovního poměru"
     * FORM attribute-class="form-control"
     *
     * GRID type='datetime'
     * GRID title="Ukončení pracovního poměru"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     * GRIiD inline-type='date'
     */
    protected $endContractDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FOsRM type='integer'
     * FOsRM title="Základ dovolené (hod)"
     * FOsRM attribute-class='form-control input-md'
     *
     * GRsID type='text'
     * GRsID title="Základ dovolené (hod)"
     * GRsID sortable='true'
     * GRsID filter='single'
     * GRsID visible='false'
     */
    protected $hoursVacationBase;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Barva kalendáře"
     * FORM attribute-class='form-control input-md color-care-select'
     *
     * GRID type='text'
     * GRID title="Kalendář"
     * GRID sortable='true'
     * GRID visible='false'
     */
    protected $calendarColor;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $hoursVacation;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="WorkerNote", mappedBy="worker")
     */
    protected $notes;

    /**
     * @ORM\OneToMany(targetEntity="ShiftBonus", mappedBy="worker")
     */
    protected $shiftBonus;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}