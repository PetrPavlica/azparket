<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ManagedRiscRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ManagedRisc extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="integer")
     * FORM type='radio'
     * FORM title="Typ rizika"
     * FORM data-own=['1' > 'Interní'|'2' > 'Externí'|'3' > 'Zainteresovaných stran']
     * FORM default-value='1'
     *
     * GRID type='translate-text'
     * GRID title="Typ rizika"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'1' > 'Interní'|'2' > 'Externí'|'3' > 'Zainteresovaných stran']
     * GRID replacement=#['1' > 'Interní'|'2' > 'Externí'|'3' > 'Zainteresovaných stran']
     */
    protected $type;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Aspekt"
     *
     * GRID type='text'
     * GRID title="Aspekt"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $aspect;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis rizika"
     *
     * GRID type='text'
     * GRID title="Popis rizika"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $aspectRiscDesc;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis příležitosti"
     *
     * GRID type='text'
     * GRID title="Popis příležitosti"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $aspectOpporDesc;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Očekávání zainteresované strany"
     *
     * GRID type='text'
     * GRID title="Očekávání zainteresované strany"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $interestedPartyExpectations;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Pravděpodonost"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM default-value=0
     *
     * GRID type='integer'
     * GRID title="Pravděpodonost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $probability;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Závažnost"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM default-value=0
     *
     * GRID type='integer'
     * GRID title="Závažnost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $relevance;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Odhalitelnost"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM default-value=0
     *
     * GRID type='integer'
     * GRID title="Odhalitelnost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $detectability;
    
    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Přínos"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM default-value=0
     *
     * GRID type='integer'
     * GRID title="Přínos"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $benefit;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Realizovatelnost"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM default-value=0
     *
     * GRID type='integer'
     * GRID title="Realizovatelnost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $feasibility;

    /**
     * @ORM\Column(type="text")
     * FORM type='textarea'
     * FORM title="Opatření rizika"
     * FORM default-value=0
     *
     * GRID type='text'
     * GRID title="Opatření rizika"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $measureRisc;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Opatření příležitosti"
     *
     * GRID type='text'
     * GRID title="Opatření příležitosti"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $measureOppor;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Termín opatření risku"
     *
     * GRID type='date'
     * GRID title="Termín opatření risku"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateRisc;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Termín opatření příležitosti"
     *
     * GRID type='date'
     * GRID title="Termín opatření příležitosti"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateOppor;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Odpovědnost rizika"
     *
     * GRID type='text'
     * GRID title="Odpovědnost rizika"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $riscRespond;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Odpovědnost příležitosti"
     *
     * GRID type='text'
     * GRID title="Odpovědnost příležitosti"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $opporRespond;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Typ zainteresované strany"
     * FORM data-own=['1' > 'Vlastníci organizace'|'2' > 'Zákazníci'|'3' > 'Stát, Obec, Městský úřad, Krajský úřad'|'4' > 'Dozorové orgány'|'5' > 'Finanční instituce'|'6' > 'Zaměstnanci'|'7' > 'Dodavatelé'|'8' > 'Sousedé'|'9' > 'Nevládní organizace']
     * FORM prompt='Nespecifikováno'
     *
     * GRID type='translate-text'
     * GRID title="Typ zainteresované strany"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'1' > 'Vlastníci organizace'|'2' > 'Zákazníci'|'3' > 'Stát, Obec, Městský úřad, Krajský úřad'|'4' > 'Dozorové orgány'|'5' > 'Finanční instituce'|'6' > 'Zaměstnanci'|'7' > 'Dodavatelé'|'8' > 'Sousedé'|'9' > 'Nevládní organizace']
     * GRID replacement=#['' > 'Nespecifikováno'|'1' > 'Vlastníci organizace'|'2' > 'Zákazníci'|'3' > 'Stát, Obec, Městský úřad, Krajský úřad'|'4' > 'Dozorové orgány'|'5' > 'Finanční instituce'|'6' > 'Zaměstnanci'|'7' > 'Dodavatelé'|'8' > 'Sousedé'|'9' > 'Nevládní organizace']
     */
    protected $interestedPartyType;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název zainteresované strany"
     *
     * GRID type='text'
     * GRID title="Název zainteresované strany"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $interestedPartyName;

    /**
     * @ORM\OneToMany(targetEntity="ManagedRiscRevaluation", mappedBy="managedRisc")
     */
    protected $revaluations;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->type = 1;
    }
}