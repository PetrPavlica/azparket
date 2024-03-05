<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ManagedChangeRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ManagedChange extends AbstractEntity
{
    use TId;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='radio'
     * FORM title="Typ změny"
     * FORM data-own=['1' > 'Interní'|'2' > 'Externí'|'3' > 'Organizační']
     * FORM default-value='1'
     *
     * GRID type='translate-text'
     * GRID title="Typ změny"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'1' > 'Interní'|'2' > 'Externí'|'3' > 'Organizační']
     * GRID replacement=#['1' > 'Interní'|'2' > 'Externí'|'3' > 'Organizační']
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="ManagedChange", inversedBy="childChange")
     * FORM type='autocomplete'
     * FORM title="Důsledek změny č. (TZZ)"
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='ManagedChange'
     *
     * GRID type='text'
     * GRID title="Důsledek změny č. (TZZ)"
     * GRID entity-link='id'
     * GRID entity='ManagedChange'
     * GRID entity-alias='pchange'
     * GRID sortable='true'
     * GRID filter=single-entity #['id']
     * GRID visible='true'
     * 
     * DETAIL title="Důsledek změny č."
     * DETAIL entity-value='$id$'
     */
    protected $parentChange;

    /**
     * @ORM\OneToMany(targetEntity="ManagedChange", mappedBy="parentChange")
     */
    protected $childChange;    

    // EXTERNÍ
    //

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="managedChanges")
     * FORM type='autocomplete'
     * FORM title='Zákazník'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='Customer'
     *
     * GRID type='text'
     * GRID title="Zákazník"
     * GRID entity-link='fullname'
     * GRID visible='true'
     * GRID entity='Customer'
     * GRID entity-alias='cus'
     * GRID sortable='true'
     * GRID filter=single-entity #['name']
     *
     * DETAIL title="Zákazník"
     * DETAIL entity-value='$company$, $name$ $surname$'
     */
    protected $customer;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Číslo změny zákazníka"
     * FORM attribute-placeholder='identifikace změny na straně zákazníka'
     *
     * GRID type='text'
     * GRID title="Číslo změny zákazníka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $customerChangeNo;

    // INTERNÍ
    //

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Interní stručné zdůvodnění"
     *
     * GRID type='text'
     * GRID title="Interní stručné zdůvodnění"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $internShortReason;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='select'
     * FORM title="Navrhnul"
     * FORM prompt='-- zvolte navrhovatele'
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search="true"
     * FORM data-entity-values=User[$name$][][]
     * 
     * GRID type='text'
     * GRID title="Navrhnul"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='u'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $originator;

    // SPOLEČNÉ
    //

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='select'
     * FORM title="Rozhodnul/a"
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search="true"
     * FORM prompt='-- zvolte uživatele, jenž přijal či zamítl změnu'
     * FORM data-entity-values=User[$name$][][]
     * 
     * GRID type='text'
     * GRID title="Rozhodnul/a"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='resultedby'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $resultedBy;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Výsledné rozhodnutí"
     * FORM data-own=['1' > 'Přijato'|'2' > 'Zamítnuto']
     * FORM prompt='V řešení'
     *
     * GRID type='translate-text'
     * GRID title="Výsledné rozhodnutí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'1' > 'Přijato'|'2' > 'Zamítnuto']
     * GRID replacement=#['' > 'V řešení'|'1' > 'Zamítnuto'|'2' > 'Přijato']
     */
    protected $result;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Obsah"
     * FORM attribute-class='md-textarea form-control'
     * FORM section=item
     *
     * GRID type='text'
     * GRID title="Obsah"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $text;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM section=item
     *
     * GRID type='text'
     * GRID title="Název dokumentu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $docName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Označení"
     * FORM section=item
     *
     * GRID type='text'
     * GRID title="Označení dokumentu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $docMark;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Index změny"
     * FORM section=item
     *
     * GRID type='text'
     * GRID title="Index změny"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $changeIndex;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Obsah"
     * FORM attribute-class='md-textarea form-control'
     * FORM attribute-style="height: 100px"
     * FORM section=item
     *
     * GRID type='text'
     * GRID title="Obsah dokumentu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $docText;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum navrhnutí"
     *
     * GRID type='date'
     * GRID title="Datum navrhnutí"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateCreatedAt;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum požadovaného ukončení"
     *
     * GRID type='date'
     * GRID title="Datum požadovaného ukončení"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateRequiredEnd;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum skutečného ukončení"
     *
     * GRID type='date'
     * GRID title="Datum skutečného ukončení"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateRealEnd;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Důvod změny"
     * FORM attribute-class='md-textarea form-control'
     *
     * GRID type='text'
     * GRID title="Důvod změny"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $reason;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Výsledek přezkoumání"
     * FORM attribute-class='md-textarea form-control'
     *
     * GRID type='text'
     * GRID title="Výsledek přezkoumání"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $resultOfExamination;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Současný stav"
     *
     * GRID type='text'
     * GRID title="Současný stav"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $actualState;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Požadovaný stav"
     *
     * GRID type='text'
     * GRID title="Požadovaný stav"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $requiredState;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * GRID type='translate-text'
     * GRID title="Schválil"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='utk'
     * GRID filter=single-entity #['name']
     */
    protected $approveUser;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     *
     * GRID type='date'
     * GRID title="Schváleno dne"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='false'
     */
    protected $approveDate;

    /**
     * @ORM\OneToMany(targetEntity="ManagedChangeStep", mappedBy="managedChange")
     */
    protected $steps;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->dateCreatedAt = new \DateTime();
    }
}