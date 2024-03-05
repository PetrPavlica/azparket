<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ApprovePartRepository")
 * @ORM\Table(name="`approve_part`")
 * @ORM\HasLifecycleCallbacks
 */
class ApprovePart extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

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
     * GRID entity-alias='arsst'
     * GRID filter=single-entity #['name']
     */
    protected $approveState;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Poř. čís."
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Pořadové číslo"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $interNumber;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Název dílu"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Název dílu"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $interMark;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Zatřídění"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Zatřídění"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $interClass;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $interName;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Plocha (dm2)"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     *
     * GRID type='text'
     * GRID title="Plocha (dm2)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $interArea;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Číslo výkresu"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Číslo výkresu"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $cusNumber;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Hmotnost"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Hmotnost"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $cusName;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Tryskání"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='text'
     * GRID title="Tryskání"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $blasting;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Celková od"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     *
     * GRID type='text'
     * GRID title="Celková od"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techTotalFrom;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Celková do"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     *
     * GRID type='text'
     * GRID title="Celková do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techTotalTo;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Zn / ZnNi od"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalFrom"
     *
     * GRID type='text'
     * GRID title="Zn / ZnNi od"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techZnFrom;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Zn / ZnNi do"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalTo"
     *
     * GRID type='text'
     * GRID title="Zn / ZnNi do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techZnTo;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="KTL od"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalFrom"
     *
     * GRID type='text'
     * GRID title="KTL od"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techKtlFrom;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="KTL do"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalTo"
     *
     * GRID type='text'
     * GRID title="KTL do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techKtlTo;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Prá od"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalFrom"
     *
     * GRID type='text'
     * GRID title="Prá od"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techPraFrom;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Prá do"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalTo"
     *
     * GRID type='text'
     * GRID title="Prá do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techPraTo;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Zn / ZnNi od"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalFrom"
     *
     * GRID type='text'
     * GRID title="Zn / ZnNi od"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techZnFrom2;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Zn / ZnNi do"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalTo"
     *
     * GRID type='text'
     * GRID title="Zn / ZnNi do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techZnTo2;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="KTL od"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalFrom"
     *
     * GRID type='text'
     * GRID title="KTL od"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techKtlFrom2;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="KTL do"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalTo"
     *
     * GRID type='text'
     * GRID title="KTL do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techKtlTo2;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Prá od"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalFrom"
     *
     * GRID type='text'
     * GRID title="Prá od"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techPraFrom2;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Prá do"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalTo"
     *
     * GRID type='text'
     * GRID title="Prá do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techPraTo2;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Zn / ZnNi od"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalFrom"
     *
     * GRID type='text'
     * GRID title="Zn / ZnNi od"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techZnFrom3;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Zn / ZnNi do"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalTo"
     *
     * GRID type='text'
     * GRID title="Zn / ZnNi do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techZnTo3;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="KTL od"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalFrom"
     *
     * GRID type='text'
     * GRID title="KTL od"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techKtlFrom3;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="KTL do"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalTo"
     *
     * GRID type='text'
     * GRID title="KTL do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techKtlTo3;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Prá od"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalFrom"
     *
     * GRID type='text'
     * GRID title="Prá od"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techPraFrom3;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Prá do"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     * FORM attribute-class="countTotalTo"
     *
     * GRID type='text'
     * GRID title="Prá do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $techPraTo3;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Čas navěšování"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Čas navěšování"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $timeNorm;

    /**
     * @ORM\ManyToOne(targetEntity="ApproveNorm")
     */
    protected $norm1;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Norma"
     * FORM attribute-class="form-control"
     */
    protected $normFile1;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Další norma"
     * FORM attribute-class="form-control"
     */
    protected $normFile2;

    /**
     * @ORM\ManyToOne(targetEntity="ApproveNorm")
     */
    protected $norm2;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Krýt závit"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='text'
     * GRID title="Krýt závit"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $techDemand1;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Krýt otvor"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='text'
     * GRID title="Krýt otvor"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $techDemand2;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Krýt plochu"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='text'
     * GRID title="Krýt plochu"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $techDemand3;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Kontrolovat závit"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='text'
     * GRID title="Kontrolovat závit"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $techDemand4;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Kontrolovat rozměr"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='text'
     * GRID title="Kontrolovat rozměr"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $techDemand5;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Zvláštní znak"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Zvláštní znak"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $techDemand6;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Jiné požadavky"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Jiné požadavky"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $techDemand7;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="počet ks / závěs"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="počet ks / závěs"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $interDemand1;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="kg / buben"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="kg / buben"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $interDemand2;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Závěs"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Závěs"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $interDemand3;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Kapacitní ověření (%)"
     * FORM rule-float ='Prosím desetinné číslo'
     * FORM required='0'
     *
     * GRID type='text'
     * GRID title="Kapacitní ověření (%)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $capacity;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Poznámky"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Poznámky"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $note;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Výsledek"
     * FORM data-own=['Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     * FORM prompt="- zatím nevybráno"
     *
     * GRID type='text'
     * GRID title="Výsledek Tk"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     */
    protected $approveResultTk;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * GRID type='translate-text'
     * GRID title="Schválil Tk"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='utk'
     * GRID filter=single-entity #['name']
     */
    protected $approveUserTk;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     *
     * GRID type='date'
     * GRID title="Schváleno dne Tk"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='false'
     */
    protected $approveDateTk;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Důvod"
     * FORM attribute-style='height: 35px;'
     *
     * GRID type='text'
     * GRID title="Důvod Tk"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $approveNoteTk;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Výsledek"
     * FORM data-own=['Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     * FORM prompt="- zatím nevybráno"
     *
     * GRID type='text'
     * GRID title="Výsledek Chpú"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     */
    protected $approveResultChpu;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * GRID type='translate-text'
     * GRID title="Schválil Chpú"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='uch'
     * GRID filter=single-entity #['name']
     */
    protected $approveUserChpu;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     *
     * GRID type='date'
     * GRID title="Schváleno dne Chpú"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='false'
     */
    protected $approveDateChpu;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Důvod"
     * FORM attribute-style='height: 35px;'
     *
     * GRID type='text'
     * GRID title="Důvod Chpú"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $approveNoteChpu;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Výsledek"
     * FORM data-own=['Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     * FORM prompt="- zatím nevybráno"
     *
     * GRID type='text'
     * GRID title="Výsledek VPÚ"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     */
    protected $approveResultVpu;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * GRID type='translate-text'
     * GRID title="Schválil VPÚ"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='uvpu'
     * GRID filter=single-entity #['name']
     */
    protected $approveUserVpu;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     *
     * GRID type='date'
     * GRID title="Schváleno dne VPÚ"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='false'
     */
    protected $approveDateVpu;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Důvod"
     * FORM attribute-style='height: 35px;'
     *
     * GRID type='text'
     * GRID title="Důvod VPÚ"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $approveNoteVpu;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Výsledek"
     * FORM data-own=['Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     * FORM prompt="- zatím nevybráno"
     *
     * GRID type='text'
     * GRID title="Výsledek RefO"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     */
    protected $approveResultRefo;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * GRID type='translate-text'
     * GRID title="Schválil RefO"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='urefo'
     * GRID filter=single-entity #['name']
     */
    protected $approveUserRefo;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     *
     * GRID type='date'
     * GRID title="Schváleno dne RefO"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='false'
     */
    protected $approveDateRefo;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Důvod"
     * FORM attribute-style='height: 35px;'
     *
     * GRID type='text'
     * GRID title="Důvod RefO"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $approveNoteRefo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Výsledek"
     * FORM data-own=['Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     * FORM prompt="- zatím nevybráno"
     *
     * GRID type='text'
     * GRID title="Výsledek TPV"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     */
    protected $approveResultTpv;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * GRID type='translate-text'
     * GRID title="Schválil TPV"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='utp'
     * GRID filter=single-entity #['name']
     */
    protected $approveUserTpv;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     *
     * GRID type='date'
     * GRID title="Schváleno dne TPV"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='false'
     */
    protected $approveDateTpv;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Důvod"
     * FORM attribute-style='height: 35px;'
     *
     * GRID type='text'
     * GRID title="Důvod TPV"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $approveNoteTpv;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Výsledek"
     * FORM data-own=['Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     * FORM prompt="- zatím nevybráno"
     *
     * GRID type='text'
     * GRID title="Výsledek Kooperace"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     */
    protected $approveResultKoop;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * GRID type='translate-text'
     * GRID title="Schválil Kooperace"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='ukoop'
     * GRID filter=single-entity #['name']
     */
    protected $approveUserKoop;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     *
     * GRID type='date'
     * GRID title="Schváleno dne Kooperace"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='false'
     */
    protected $approveDateKoop;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Důvod"
     * FORM attribute-style='height: 35px;'
     *
     * GRID type='text'
     * GRID title="Důvod Kooperace"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $approveNoteKoop;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Výsledek"
     * FORM data-own=['Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     * FORM prompt="- zatím nevybráno"
     *
     * GRID type='text'
     * GRID title="Výsledek Personální"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'Schvaluji' > 'Schvaluji'|'Neschvaluji' > 'Neschvaluji'|'Schvaluji do projektu' > 'Schvaluji do projektu']
     */
    protected $approveResultPers;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * GRID type='translate-text'
     * GRID title="Schválil Personální"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='upers'
     * GRID filter=single-entity #['name']
     */
    protected $approveUserPers;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     *
     * GRID type='date'
     * GRID title="Schváleno dne Personální"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='false'
     */
    protected $approveDatePers;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Důvod"
     * FORM attribute-style='height: 35px;'
     *
     * GRID type='text'
     * GRID title="Důvod Personální"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $approveNotePers;

    /**
     * @ORM\OneToMany(targetEntity="ApprovePartDocument", mappedBy="approvePart")
     */
    protected $documents;

    /**
     * @ORM\ManyToOne(targetEntity="Approve")
     */
    protected $approve;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}