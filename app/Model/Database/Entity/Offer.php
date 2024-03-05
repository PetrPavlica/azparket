<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\OfferRepository")
 * @ORM\Table(name="`offer`")
 * @ORM\HasLifecycleCallbacks
 */
class Offer extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Číslo nabídky"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Číslo"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $offerNo;

    /**
     * @ORM\ManyToOne(targetEntity="Customer")
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
     * GRID entity-link='company'
     * GRID visible='true'
     * GRID entity='Customer'
     * GRID entity-alias='cus'
     * GRID sortable='true'
     * GRID filter=single-entity #['name']
     */
    protected $customer;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Zákazník textem"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='(Nahradí jméno zákazníka)'
     *
     * GRID type='text'
     * GRID title="Zákazník textem"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $customerText;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='autocomplete'
     * FORM title='Obchodník'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='User'
     *
     * GRID type='text'
     * GRID title="Obchodník"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='slm'
     * GRID sortable='true'
     * GRID filter=single-entity #['name']
     */
    protected $salesman;
    
    /**
    * @ORM\Column(type="string", nullable=true)
    * FORM type='text'
    * FORM title="Za cenou"
    * FORM attribute-class='form-control input-md'
    * FORM attribute-placeholder="Kč (za položkami v ceníku)"
    *
    * GRID type='text'
    * GRID title="Za cenou"
    * GRID sortable='true'
    * GRID filter='single'
    * GRID visible='false'
    */
   protected $unit;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka"
     * FORM attribute-class="form-control"
     * FORM attribute-rows="2"
     * 
     * GRID type='text'
     * GRID title="Popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum odeslání"
     * FORM attribute-class="form-control disabled"
     * FORM disabled='true'
     *
     * GRID type='datetime'
     * GRID title="Datum odeslání"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $sendDate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Obsah"
     * FORM attribute-class='form-check-input'
     */
    protected $addTOC;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Ceník"
     * FORM attribute-class='form-check-input'
     */
    protected $addPricing;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Kontakt na konec"
     * FORM attribute-class='form-check-input'
     */
    protected $addFooter;

    /**
     * @ORM\ManyToOne(targetEntity="OfferPartTemplate")
     * FORM type='select'
     * FORM title="Popis produktu"
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=OfferPartTemplate[name]['type' => '2']['order' => 'ASC']
     * FORM prompt="Nevybáno"
     *
     * GRID type='text'
     * GRID title="Popis produktu"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='OfferPartTemplate'
     * GRID entity-alias='pdt'
     * GRID filter=single-entity #['name']
     */
    protected $productDescription;

    /**
     * @ORM\ManyToOne(targetEntity="OfferPartTemplate")
     * FORM type='select'
     * FORM title="Reference"
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=OfferPartTemplate[name]['type' => '3']['order' => 'ASC']
     * FORM prompt="Nevybáno"
     *
     * GRID type='text'
     * GRID title="Reference"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='OfferPartTemplate'
     * GRID entity-alias='rt'
     * GRID filter=single-entity #['name']
     */
    protected $reference;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='hidden'
     * FORM data-entity=User[name]
     *
     * GRID type='text'
     * GRID title="Vytvořil"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='User'
     * GRID entity-alias='asdf'
     * GRID filter=single-entity #['name']
     */
    protected $originator;

    /**
     * @ORM\OneToMany(targetEntity="OfferPart", mappedBy="offer")
     */
    protected $parts;
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     * FORM type='select'
     * FORM title="Stav"
     * FORM data-own=['0' > 'Rozpracováno', '1' > 'Odesláno', '2' > 'Objednáno', '3' > 'Zamítnuto']
     * FORM default-value=0
     * FORM attribute-class="form-control selectpicker"
     *
     * GRID type='text'
     * GRID title="Stav"
     * GRID visible='true'
     * GRID replacement=#['0' > 'Rozpracováno'|'1' > 'Odesláno'|'2' > 'Objednáno', '3' > 'Zamítnuto']
     * GRID filter=select #['' > 'Vše'|'0' > 'Rozpracováno'|'1' > 'Odesláno'|'2' > 'Objednáno', '3' > 'Zamítnuto']
     */
    protected $state;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * FORM type='select'
     * FORM title="Je nový"
     * FORM default-value=1
     * FORM data-own=[''>'' | '0' > 'Bylo přijato do TPV' | '1' > 'Nebylo přijato do TPV']
     * FORM attribute-class="form-control selectpicker"
     * FORM disabled=""
     *
     * GRID type='text'
     * GRID title="TPV"
     * GRID visible='true'
     * GRID replacement=#['0' > 'Bylo přijato do TPV'|'1' > 'Nebylo přijato do TPV']
     * GRID filter=select #['' > 'Vše'|'0' > 'Bylo přijato do TPV'|'1' > 'Nebylo přijato do TPV']
     */
    protected $new;

    /**
     * @ORM\ManyToOne(targetEntity="Inquiry", inversedBy="offer")
     * FORM type='autocomplete'
     * FORM title='Založená na poptávce'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='Inquiry'
     *
     * GRID type='text'
     * GRID title="Z poptávky"
     * GRID entity-link='id'
     * GRID visible='true'
     * GRID entity='Inquiry'
     * GRID entity-alias='inq'
     * GRID sortable='true'
     * GRID filter=single-entity #['id']
     */
    protected $inquiry;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='datetime'
     * FORM title="Plánovaný datum odeslání"
     * FORM attribute-class="form-control"
     * FORM attribute-placeholder="př.: 15. 10. 2011 15:20"
     *
     * GRID type='datetime'
     * GRID title="Plánovaný datum odeslání"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $plannedSendDate;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * FORM type='checkbox'
     * FORM title="Odeslat automaticky"
     * FORM attribute-class='form-check-input'
     */
    protected $autoSend;

    /**
     * @ORM\Column(type="string", length="32", nullable="true")
     */
    protected $acceptCode;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='date'
     * FORM title="Datum potvrzení"
     * FORM attribute-class="form-control disabled"
     * FORM disabled='true'
     *
     * GRID type='datetime'
     * GRID title="Datum potvrzení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $acceptDate;

    /**
     * @ORM\OneToMany(targetEntity="OfferProduct", mappedBy="offer")
     */
    protected $products;

    /**
     * @ORM\Column(type="text", nullable="true")
     * GRID type='text'
     * GRID title="Město instalace"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $installCity;
    
    /**
     * @ORM\Column(type="text", nullable="true")
     * GRID type='text'
     * GRID title="PSČ instalace"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $installZip;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Nabídnutá cena"
     * FORM rule-float='Prosím zadávejte pouze čísla'
     * FORM rule-min='Zadejte číslo větší nebo rovno 0'#[0]
     *
     * GRID type='text'
     * GRID title="Nabídnutá cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $price;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Cena dopravy"
     * FORM rule-float='Prosím zadávejte pouze čísla'
     * FORM rule-min='Zadejte číslo větší nebo rovno 0'#[0]
     *
     * GRID type='text'
     * GRID title="Cena dopravy"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $priceDelivery;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Množství dopravy"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Množství dopravy"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $transportCount;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Cena montáže"
     * FORM rule-float='Prosím zadávejte pouze čísla'
     * FORM rule-min='Zadejte číslo větší nebo rovno 0'#[0]
     *
     * GRID type='text'
     * GRID title="Cena montáže"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $priceInstall;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Cena jeřábu"
     * FORM rule-float='Prosím zadávejte pouze čísla'
     * FORM rule-min='Zadejte číslo větší nebo rovno 0'#[0]
     *
     * GRID type='text'
     * GRID title="Cena jeřábu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $priceCrane;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Čas na cestě (jednosměr.) [h]"
     * FORM rule-float='Prosím zadávejte pouze čísla'
     * FORM rule-min='Zadejte číslo větší nebo rovno 0'#[0]
     *
     * GRID type='text'
     * GRID title="Čas na cestě"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $transportTime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Počet montážníků"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Počet montážníků"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $installWorkers;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Vzdálenost instalace [km]"
     * FORM rule-float='Prosím zadávejte pouze čísla'
     * FORM rule-min='Zadejte číslo větší nebo rovno 0'#[0]
     *
     * GRID type='text'
     * GRID title="Čas montáže"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $installDistance;
    
    /**
     * @ORM\ManyToOne(targetEntity="Vat")
     * FORM type='select'
     * FORM title='DPH'
     * FORM attribute-class="form-control selectpicker"
     * FORM data-entity=Vat[name][][]
     * FORM prompt="Nevybáno"
     * 
     * GRID type='text'
     * GRID title="DPH"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='Vat'
     * GRID entity-alias='vat'
     * GRID filter=single-entity #['name']
     */
    protected $vat;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->addFooter = 1;
        $this->addPricing = 1;
        $this->addTOC = 1;
        $this->autoSend = 0;
        $this->state = 0;
        $this->new = 1;
    }

}