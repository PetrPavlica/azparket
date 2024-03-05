<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ProductRepository")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="product", uniqueConstraints={@ORM\UniqueConstraint(name="unique_klic_polozky", columns={"klic_polozky"})})
 */
class Product extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\OneToMany(targetEntity="ProductInMenu", mappedBy="product")
     * FORM type='multiselect'
     * FORM title="Zařazení"
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=Menu[id]
     * FORM multiselect-entity=ProductInMenu[product][menu]
     * FORM attribute-placeholder='Zařazení'
     * FORM attribute-multiple='true'
     * FORM attribute-data-live-search='true'
     */
    protected $menu;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-placeholder='Pořadí'
     * FORM required="Toto je je povinné pole!"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRIsD type='integer'
     * GRIsD title="Pořadí"
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='true'
     */
    protected $orderProduct;

    /**
     * @ORM\OneToMany(targetEntity="ProductImage", mappedBy="product")
     */
    protected $images;

    /**
     * @ORM\OneToMany(targetEntity="ProductFile", mappedBy="product")
     */
    protected $files;

    /**
     * @ORM\OneToMany(targetEntity="InquiryProduct", mappedBy="product")
     */
    protected $inquiryProducts;

    /**
     * @ORM\Column(type="boolean", options={"default" : 1})
     */
    protected $active;
    
    /**
     * @ORM\Column(type="boolean", nullable="true", options={"default" : 0})
     */
    protected $isImported;



    // atributes from TPV
    //
    
    /**
     * @ORM\Column(type="integer", name="klic_polozky", nullable="true")
     * FORM type='integer'
     * FORM title="Klíč položky"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     * 
     * GRID type='text'
     * GRID title="Klíč položky"
     * GRID sortable='true'
     * GRID filter='single'
     */
    protected $klic_polozky;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název položky"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Název položky"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $nazev_polozky;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Alternativní název"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Alternativní název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $alter_nazev;

    /**
     * [1 => komunální nářadí, A => služba, D => dílec, F => finál, H => hutní materiál, K => kumulace, N => nakupovaná položka, S => sestava]
     * @ORM\Column(type="string", length="1", nullable=true)
     */
    protected $klic_postaveni;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Hmotnost na měrnou jednotku [kg]"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Hmotnost na m.j."
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $hmotnost_mj;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Cena [Kč]"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $evid_cena_pol;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Délka [mm]"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Délka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $atr_rozmer_1;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Šířka [mm]"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Šířka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $atr_rozmer_2;
    
    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Výška [mm]"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Výška"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $atr_rozmer_3;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Skladové množství"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Skladové množství"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $sklad_mnozstvi;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Objem [m³]"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Objem"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $objem;

    /**
     * @ORM\Column(type="string", length="255", nullable=true)
     * FORM type='text'
     * FORM title="Měrná jednotka"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Měrná jednotka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $zkratka_mj;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Cena montáže [Kč]"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Cena montáže"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $priceInstall;

    /**
     * @ORM\Column(type="string", length="255", nullable=true)
     * FORM type='text'
     * FORM title="Atribut 2"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-disabled=''
     * FORM attribute-readonly=''
     *
     * GRID type='text'
     * GRID title="Atribut 2"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $atribut2;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->active = 1;
        $this->isImported = 0;
        $this->orderProduct = 0;
    }
}