<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\CustomerRepository")
 * @ORM\Table(name="`customer`")
 * @ORM\HasLifecycleCallbacks
 */
class Customer extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Firma"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Firma'
     *
     * GRID type='text'
     * GRID title="Firma"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $company;

    /**
     * @ORM\OneToMany(targetEntity="CustomerInType", mappedBy="customer")
     * FORM type='multiselect'
     * FORM title="Typy zákazníka"
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=CustomerType[name][]['name' => 'ASC']
     * FORM multiselect-entity=CustomerInType[customer][type]
     * FORM attribute-placeholder='Typy zákazníka'
     * FORM attribute-multiple='true'
     * FORM attribute-data-live-search="true"
     *
     * GRID type='multi-text'
     * GRID title="Typy zákazníka"
     * GRID visible='true'
     * GRID filter='single'
     * GRID entity-alias=ct
     * GRID entity-join-column=type
     * GRID entity-link=name
     */
    protected $types;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORsM type='text'
     * FORsM title="Titul"
     * FORsM attribute-class='form-control input-md'
     * FORsM attribute-placeholder='Titul'
     *
     * GRIsD type='text'
     * GRIsD title="Titul"
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='true'
     */
    protected $degree;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Provozovna"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Provozovna'
     *
     * GRID type='text'
     * GRID title="Provozovna"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $workshop;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Jméno"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Jméno'
     *
     * GRID type='text'
     * GRID title="Jméno"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Příjmení"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Příjmení'
     *
     * GRID type='text'
     * GRID title="Příjmení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $surname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $fullname;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='email'
     * FORM title="Email"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Email'
     *
     * GRID type='text'
     * GRID title="Email"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='password'
     * FORM title="Heslo"
     * FORM attribute-placeholder='Heslo'
     * FORM attribute-class='form-control input-md'
     * FORM rule-min_length='Minimální délka hesla je %d' #[5]
     * FORM required='0'
     */
    protected $password;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="IČO"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='IČO'
     *
     * GRID type='text'
     * GRID title="IČO"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $idNo;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="DIČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='DIČ'
     *
     * GRID type='text'
     * GRID title="DIČ"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $vatNo;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Jméno kontaktní osoby"
     * FORM attribute-class='form-control'
     * FORM attribute-placeholder='Jméno'
     *
     * GRID type='text'
     * GRID title="Jméno kontaktní osoby"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $contactPerson;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='email'
     * FORM title="Email kontaktní osoby"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Email'
     *
     * GRID type='text'
     * GRID title="Email kontaktní os."
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $emailDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Telefon kontatní osoby"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Telefon'
     *
     * GRID type='text'
     * GRID title="Telefon kontaktní osoby"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $phoneDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Telefon/mobil"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Telefon'
     *
     * GRID type='text'
     * GRID title="Telefon/mobil"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Ulice a č. p."
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Ulice'
     *
     * GRID type='text'
     * GRID title="Ulice doručení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $streetDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Město"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Město'
     *
     * GRID type='text'
     * GRID title="Město doručení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $cityDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="PSČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='PSČ'
     *
     * GRID type='text'
     * GRID title="PSČ doručení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $zipDelivery;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Ulice a č. p."
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Ulice'
     *
     * GRID type='text'
     * GRID title="Ulice fakturační"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $street;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Město"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Město'
     *
     * GRID type='text'
     * GRID title="Město fakturační"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="PSČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='PSČ'
     *
     * GRID type='text'
     * GRID title="PSČ fakturační"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $zip;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerState")
     * FORM type='select'
     * FORM title='Stav'
     * FORM prompt='-- vyberte stav'
     * FORM data-entity=CustomerState[name]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Stav"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='CustomerState'
     * GRID entity-alias='cusSt'
     * GRID filter=single-entity #['name']
     */
    protected $customerState;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * GRID type='datetime'
     * GRID title="Datum změny stavu zákazníka"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $dateChangeState;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Číslo účtu"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Číslo účtu'
     *
     * GRID type='text'
     * GRID title="Číslo účtu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $accountNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Kód banky"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Kód banky'
     *
     * GRID type='text'
     * GRID title="Kód banky"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $bankCode;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název banky"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název banky'
     *
     * GRID type='text'
     * GRID title="Název banky"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $bankName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Internetové stránky"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Internetové stránky'
     *
     * GRID type='text'
     * GRID title="Internetové stránky"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $www;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka"
     * FORM attribute-placeholder='volitelná poznámka'
     * FORM attribute-class='form-control input-md'
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
     * FORM type='text'
     * FORM title="Splatnost (počet dní)"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Splatnost'
     *
     * GRID type='text'
     * GRID title="Splatnost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $maturity;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='text'
     * FORM title="Konstantní symbol"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Konstantní symbol'
     *
     * GRID type='text'
     * GRID title="Konstantní symbol"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $constantSymbol;

    /**
     * @ORM\OneToMany(targetEntity="Process", mappedBy="customer")
     */
    protected $processes;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=" Aktivní"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Aktivní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Vytvořen automaticky s poptávkou"
     * FORM default-value='false'
     * FORM attribute-disabled=""
     * FORM attribute-readonly=""
     *
     * GRID type='bool'
     * GRID title="Vytvořen s poptávkou"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $createdByReservation;
    
    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    protected $recoveryHash;

    /**
     * @ORM\OneToMany(targetEntity="CustomerNotification", mappedBy="customer")
     */
    protected $notifications;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $contractNotify;

    public function __construct($data = null)
    {
        $this->importedDataFromLs = false;
        parent::__construct($data);
    }

    public function getPasswordHash(): string
    {
        return $this->password;
    }

    public function changePasswordHash(string $password): void
    {
        $this->password = $password;
    }

    public function getCompanyName()
    {
        return sprintf("%s (%s)", $this->company, $this->fullname);
    }

    public function getInvoiceAddress()
    {
        return sprintf("%s, %s %s", $this->street, $this->zip, $this->city);
    }

    public function getDeliveryAddress()
    {
        return sprintf("%s, %s %s", $this->streetDelivery, $this->zipDelivery, $this->cityDelivery);
    }
}