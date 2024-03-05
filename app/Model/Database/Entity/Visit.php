<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\VisitRepository")
 * @ORM\Table(name="`visit`")
 * @ORM\HasLifecycleCallbacks
 */
class Visit extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='text'
     * FORM title="Typ opravy"
     * FORM attribute-placeholder='Typ opravy'
     * FORM required="Typ opravy je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Typ opravy"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Interní poznámka"
     * FORM attribute-placeholder='Interní poznámka'
     * FORM attribute-class='form-control input-md'
     * FORM attribute-style="height: 200px"
     *
     * GRID type='text'
     * GRID title="Interní poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="ID Výjezdu"
     * FORM attribute-placeholder='ID Výjezdu'
     *
     * GRID type='text'
     * GRID title="ID Výjezdu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderId2;

    /**
     * @ORM\OneToMany(targetEntity="MaterialNeedBuy", mappedBy="visit")
     *
     * GRID type='text'
     * GRID title="Nutno objednat"
     * GRID visible='true'
     * GRID sortable='true'
     * GRID filter=single
     */
    protected $materialNeedBuy;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis práce"
     * FORM attribute-placeholder='Popis práce'
     * FORM attribute-class='form-control input-md'
     * FORM attribute-style="height: 200px"
     *
     * GRID type='text'
     * GRID title="Popis práce"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $workDescription;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Doprava (paušál nebo v km)"
     * FORM attribute-placeholder='Doprava'
     *
     * GRID type='text'
     * GRID title="Doprava"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $ships;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Zařízení"
     *
     * GRID type='text'
     * GRID title="Zařízení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $device;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Sériové číslo"
     *
     * GRID type='text'
     * GRID title="Sériové číslo"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $serialNumber;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Jméno zástupce zákazníka"
     *
     * GRID type='text'
     * GRID title="Jméno zástupce zákazníka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $nameCustomer;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="ID Zakázky"
     *
     * GRsID type='text'
     * GRsID title="ID Zakázky"
     * GRsID sortable='true'
     * GRsID filter='single'
     * GRsID visible='true'
     */
    protected $orderId;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum do kdy dojet"
     *
     * GRID type='date'
     * GRID title="Datum do kdy dojet"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateDeadline;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Čas do kdy dojet"
     *
     * GRID type='bool'
     * GRID title="Čas do kdy dojet"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $deadlineTimes;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum"
     * FORM required="Datum je povinné pole!"
     *
     * GRID type='date'
     * GRID title="Datum"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateStart;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FOsRM type='date'
     * FOsRM title="Konec opakování"
     *
     * GRsID type='date'
     * GRsID title="Konec opakování"
     * GRsID sortable='true'
     * GRsID filter='date-range'
     * GRsID visible='true'
     */
    protected $dateEnd;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Doba trvání hodiny"
     * FORM data-own=[ '-1' > 'N' | '0' > '0' | '1' > '1' | '2' > '2' | '3' > '3' | '4' > '4' | '5' > '5' | '6' > '6' | '7' > '7' | '8' > '8' | '9' > '9' | '10' > '10' | '11' > '11' | '12' > '12' | '13' > '13' | '14' > '14' | '15' > '15' | '16' > '16' | '17' > '17' | '18' > '18' | '19' > '19' | '20' > '20' | '21' > '21' | '22' > '22' | '23' > '23']
     *
     * GRID type='text'
     * GRID title="Doba trvání"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $durationHours;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Doba trvání"
     * FORM data-own=['0' > '00' | '30' > '30']
     */
    protected $durationMinutes;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Čas výjezdu (např. 8:00)"
     *
     * GRID type='bool'
     * GRID title="Čas výjezdu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $onceTimes;

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
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='Customer'
     * GRID entity-alias='cus'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $customer;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerOrdered")
     * FORM type='autocomplete'
     * FORM title='Objednavatel'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='CustomerOrdered'
     *
     * GRID type='text'
     * GRID title="Objednavatel"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='CustomerOrdered'
     * GRID entity-alias='cusorde'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $customerOrdered;

    /**
     * @ORM\ManyToOne(targetEntity="Traffic")
     * FORM type='autocomplete'
     * FORM title='Provozovna'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='Traffic'
     *
     * GRID type='text'
     * GRID title="Provozovna"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='Traffic'
     * GRID entity-alias='traff'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $traffic;

    /**
     * @ORM\ManyToOne(targetEntity="VisitProcess")
     * FORM type='autocomplete'
     * FORM title='Obchodní případ'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM attribute-placeholder='(nevyplněno vytvoří nový)'
     * FORM autocomplete-entity='VisitProcess'
     *
     * GRID type='text'
     * GRID title="Obchodní případ"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='VisitProcess'
     * GRID entity-alias='visproc'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $visitProcess;

    /**
     * @ORM\ManyToOne(targetEntity="VisitState")
     * FORM type='select'
     * FORM attribute-class="form-control selectpicker"
     * FORM title="Stav workflow"
     * FOsRM prompt="Nic není vybráno"
     * FORM data-entity-values=VisitState[$name$]['active' => '1']['stateOrder' => 'ASC']
     *
     * GRID type='text'
     * GRID title="Stav workflow"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='VisitState'
     * GRID entity-alias='viss'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $state;

    /**
     * @ORM\ManyToOne(targetEntity="VisitStatus")
     * FORM type='select'
     * FORM attribute-class="form-control selectpicker"
     * FORM title="Stav výjezdu"
     * FORM prompt="Nic není vybráno"
     * FORM data-entity-values=VisitStatus[$name$]['active' => '1']['name' => 'ASC']
     *
     * GRID type='text'
     * GRID title="Stav výjezdu"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='VisitStatus'
     * GRID entity-alias='visstt'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="WorkerOnVisit", mappedBy="visit")
     * FORM type='multiselect'
     * FORM title="Zaměstnanec"
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-multiple='true'
     * FORM attr-data-live-search='true'
     * FORM data-entity-values=Worker[$name$][]['name' => 'ASC']
     * FORM multiselect-entity=WorkerOnVisit[visit][worker]
     *
     * GRID type='multi-text'
     * GRID title="Zaměstnanec"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Worker'
     * GRID entity-join-column='worker'
     * GRID entity-alias='siwtvp'
     * GRID filter=single-entity #['name']['name' > 'ASC']
     */
    protected $worker;

    /**
     * @ORM\OneToMany(targetEntity="MaterialOnVisit", mappedBy="visit")
     */
    protected $material;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Minuty před výjezdem"
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Minuty před výjezdem"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $minutesBefore;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Minuty po výjezdu"
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='float'
     * GRID title="Minuty po výjezdu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $minutesAfter;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Služba"
     * FORM default-value='0'
     *
     * GRID type='bool'
     * GRID title="Služba"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $service;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Příčina poruchy"
     * FORM data-own=['0' > '--Vyberte' | '1' > 'Pravidelný servis' | '2' > 'Běžné opotřebení' | '3' > 'Poškozeno třetí osobou' | '4' > 'Poškozeno obsluhou']
     */
    protected $demadgeOther;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Zakázka dokončena"
     * FORM data-own=['0' > '--Vyberte' | '1' > 'ANO' | '2' > 'NE']
     */
    protected $orderFinish;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Zápis do evidenční knihy"
     * FORM data-own=['0' > '--Vyberte' | '1' > 'ANO' | '2' > 'Není třeba' | '3' > 'Chybí EK']
     */
    protected $writeEvidenceBook;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Evidenční kniha"
     */
    protected $evidenceBook;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Množství doplněného chladiva"
     * FORM required='0'
     * FORM rule-float="Zadaná hodnota musí být číslo!"
     */
    protected $amountCooling;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Pozice"
     */
    protected $position;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Zařízení řádně předáno"
     * FORM data-own=['0' > 'ANO' | '1' > 'NE']
     */
    protected $handover;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Bez dopravy"
     * FORM default-value='0'
     *
     * GRID type='bool'
     * GRID title="Bez dopravy"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $freeDeli;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='hidden'
     */
    protected $signature;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='hidden'
     */
    protected $customerSignImage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Hrozí nebezpečí a rizika k činnosti prováděné na pracovišti?"
     * FORM data-own=['0' > '--Vyberte' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozp;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="S elektrickou energií"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpElVoltage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Ve výšce/ nad hloubkou více jak 1,5m"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpHeight;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Nebezpečná energie"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpHazardVoltage;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Ve stísněných prostorách"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpArea;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Pod zavěšeným břemenem"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpUnderBurden;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="VZV a jiná průmyslová vozidla"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpVzv;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Uklouznutí, zakopnutí, pád"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpHurt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Ostré hrany a předměty, řezné nářadí a nástroje"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpCut;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Pájení, broušené, svařování"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpWelding;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Nakládání s chladivem"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpCooling;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Nebezpečné chemikálie"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpChemikals;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Nedostatek kyslíku/ nebezpečné výpary a plyny"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpOxygen;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Ruční manipulace s břemenem"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpBurden;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Padající předměty"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpFall;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Odletující předměty při broušení, vrtání..."
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpFlySubject;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Rotující a pohybující se části strojů a zařízení"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpRotateSubject;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Zvýšená hladina hluku"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpNoise;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Azbest"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO' | '2' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpAsbest;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Jiná než uvedená rizika"
     * FORM data-own=['0' > 'NE' | '1' > 'ANO']
     * FORM attribute-class="form-control"
     */
    protected $bozpOther;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="K dispozici pracovní oděv a OOPP"
     * FORM data-own=['0' > 'NE' | '1' > 'ANO']
     * FORM attribute-class="form-control"
     */
    protected $bozpClothes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Jsou všechny OOPP a oděvy použity"
     * FORM data-own=['0' > 'NE' | '1' > 'ANO']
     * FORM attribute-class="form-control"
     */
    protected $bozpUseClothes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Kontrola pracovních pomůcek/ platnost revize"
     * FORM data-own=['0' > 'NE' | '1' > 'ANO']
     * FORM attribute-class="form-control"
     */
    protected $bozpCheck;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Informování jiných subjektů"
     * FORM data-own=['0' > 'Neaplikovatelné' | '1' > 'ANO']
     * FORM attribute-class="form-control"
     */
    protected $bozpInformationOther;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Je možné práce bezpečně provést"
     * FORM data-own=['0' > 'ANO' | '1' > 'NE']
     * FORM attribute-class="form-control"
     */
    protected $bozpSafety;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpElVoltageText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpHeightText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpHazardVoltageText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpAreaText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpUnderBurdenText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpVzvText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpHurtText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpCutText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpWeldingText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpCoolingText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpChemikalsText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpOxygenText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpBurdenText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpFallText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpFlySubjectText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpRotateSubjectText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpNoiseText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpAsbestText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpOtherText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpClothesText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpUseClothesText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpCheckText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpInformationOtherText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Přijatá opatření"
     * FORM attribute-class="form-control"
     */
    protected $bozpSafetyText;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Výrobce zařízení"
     */
    protected $refrigerantProducer;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Rok výroby"
     */
    protected $refrigerantManufactureYear;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Použitý detektor"
     * FORM data-own=['1' > 'D-TEK' | '2' > 'Fieldpiece' | '3' > 'Testo']
     */
    protected $refrigerantDetector;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Systém detekce úniku"
     * FORM data-own=['0' > 'NE' | '1' > 'Ano']
     */
    protected $refrigerantDetectionSystem;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Zařízení je v pořádku"
     * FORM data-own=['0' > 'ANO' | '1' > 'NE']
     */
    protected $refrigerantDevicesIsOk;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Závada"
     * FORM attribute-placeholder='Závada'
     * FORM attribute-class='form-control input-md'
     * FORM attribute-style="height: 100px"
     */
    protected $refrigerantDemadge;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Typ revize"
     * FORM data-own=['0' > 'Po servisním zásahu' | '1' > 'Pravidelná']
     */
    protected $refrigerantTypeRevision;

    /**
     * @ORM\ManyToOne(targetEntity="Material")
     * FORM type='autocomplete'
     * FORM title='Chladivo'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='Material'
     *
     * GRID type='text'
     * GRID title="Chladivo"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='Material'
     * GRID entity-alias='refrig'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $refrigerant;

    /**
     * @ORM\OneToMany(targetEntity="VisitDocument", mappedBy="visit")
     */
    protected $document;

    /**
     * @ORM\OneToMany(targetEntity="VisitLog", mappedBy="visit")
     */
    protected $visitLog;

    public function __construct($data = null)
    {
        $this->freeDeli = false;
        $this->service = false;
        parent::__construct($data);
    }

}