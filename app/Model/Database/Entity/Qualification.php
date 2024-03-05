<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\QualificationRepository")
 * @ORM\Table(name="`qualification`")
 * @ORM\HasLifecycleCallbacks
 */
class Qualification extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=false)
     * FORM type='date'
     * FORM title="Datum od"
     * FORM required='Toto pole je povinné!'
     *
     * GRID type='date'
     * GRID title="Datum od"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateFrom;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=false)
     * FORM type='date'
     * FORM title="Datum do"
     * FORM required='Toto pole je povinné!'
     *
     * GRID type='date'
     * GRID title="Datum do"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateTo;

    /**
     * @ORM\ManyToOne(targetEntity="Department")
     * FORM type='select'
     * FORM prompt='--vyberte'
     * FORM title='Oddělení'
     * FORM data-entity=Department[name]
     * FORM required='Toto pole je povinné!'
     *
     * GRID type='translate-text'
     * GRID title="Oddělení"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Department'
     * GRID entity-alias='d'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $department;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * GRID type='translate-text'
     * GRID title="Zapsal"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='u'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Organizátor, místo"
     *
     * GRID type='text'
     * GRID title="Organizátor, místo"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $place;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * FORM type='text'
     * FORM title="Název"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Doklad (osvědčení, certifikát)"
     *
     * GRID type='text'
     * GRID title="Doklad (osvědčení, certifikát)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $certificate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='radio'
     * FORM title="Účast"
     * FORM data-own=['1' > 'aktivní (přednášková činnost)'|'2' > 'pasivní (účast na semináři)']
     * FORM default-value='2'
     *
     * GRID type='translate-text'
     * GRID title="Účast"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'1' > 'aktivní (přednášková činnost)'|'2' > 'pasivní (účast na semináři)']
     * GRID replacement=#['1' > 'aktivní (přednášková činnost)'|'2' > 'pasivní (účast na semináři)']
     */
    protected $participation;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title='Poznámka'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='radio'
     * FORM title="Typ akce"
     * FORM data-own=['1' > 'porada, konzultace a akce, kde nelze hodnotit efektivnost výcviku'|'2' > 'seminář, školení, konference apod.']
     * FORM default-value='1'
     *
     * GRID type='translate-text'
     * GRID title="Typ akce"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'1' > 'porada, konzultace a akce, kde nelze hodnotit efektivnost výcviku'|'2' > 'seminář, školení, konference apod.']
     * GRID replacement=#['1' > 'porada, konzultace a akce, kde nelze hodnotit efektivnost výcviku'|'2' > 'seminář, školení, konference apod.']
     */
    protected $typeOfAction;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum hodnocení"
     *
     * GRID type='date'
     * GRID title="Datum hodnocení"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='false'
     */
    protected $evalutionDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='radio'
     * FORM title="Odborná úroveň (výkon lektora, srozumitelnost výkladu, ap.)"
     * FORM data-own=['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5']
     * FORM attribute-class='form-check-inline'
     * FORM default-value='1'
     *
     * GRID type='translate-text'
     * GRID title="Odborná úroveň (výkon lektora, srozumitelnost výkladu, ap.)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5']
     * GRID replacement=#['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5']
     */
    protected $professionalLevel;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='radio'
     * FORM title="Organizační zajištění"
     * FORM data-own=['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5']
     * FORM attribute-class='form-check-inline'
     * FORM default-value='1'
     *
     * GRID type='translate-text'
     * GRID title="Organizační zajištění"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5']
     * GRID replacement=#['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5']
     */
    protected $organisationSupport;

    /**
     * @ORM\Column(type="integer", name="`range`", nullable=true)
     * FORM type='radio'
     * FORM title="Rozsah (délka kurzu)"
     * FORM data-own=['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5']
     * FORM attribute-class='form-check-inline'
     * FORM default-value='1'
     *
     * GRID type='translate-text'
     * GRID title="Rozsah (délka kurzu)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5']
     * GRID replacement=#['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5']
     */
    protected $range;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='radio'
     * FORM title="Zlepšení nebo možnost zavedení nových metod a postupů"
     * FORM data-own=['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     * FORM attribute-class='form-check-inline'
     *
     * GRID type='translate-text'
     * GRID title="Zlepšení nebo možnost zavedení nových metod a postupů"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     * GRID replacement=#['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     */
    protected $newMethods;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='radio'
     * FORM title="Zvýšení bezpečnosti práce"
     * FORM data-own=['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     * FORM attribute-class='form-check-inline'
     *
     * GRID type='translate-text'
     * GRID title="Zvýšení bezpečnosti práce"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     * GRID replacement=#['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     */
    protected $safety;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='radio'
     * FORM title="Úspora času při práci"
     * FORM data-own=['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     * FORM attribute-class='form-check-inline'
     *
     * GRID type='translate-text'
     * GRID title="Úspora času při práci"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     * GRID replacement=#['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     */
    protected $timeSavings;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='radio'
     * FORM title="Zlepšení kvality práce, snížení počtu neshod"
     * FORM data-own=['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     * FORM attribute-class='form-check-inline'
     *
     * GRID type='translate-text'
     * GRID title="Zlepšení kvality práce, snížení počtu neshod"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     * GRID filter=select #['' > 'Vše'|'1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     * GRID replacement=#['1' > '1'|'2' > '2'|'3' > '3'|'4' > '4'|'5' > '5'|'0' > '0 - nelze hodnotit']
     */
    protected $qualityOfWork;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title='Připomínky'
     *
     * GRID type='text'
     * GRID title="Připomínky"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $reminders;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     * GRID type='float'
     * GRID title="Efekt"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $efficiency;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}