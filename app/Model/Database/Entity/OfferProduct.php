<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\OfferProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class OfferProduct extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Offer", inversedBy="products")
     */
    protected $offer;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="products")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="SET NULL")
     * 
     * FORM type='autocomplete'
     * FORM title='Produkt'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM attribute-placeholder="Hledejte klíč nebo název produkt..."
     * FORM autocomplete-entity='Product'
     *
     * GRID type='text'
     * GRID title="Produkt"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Product'
     * GRID entity-alias='oprod'
     * GRID value-mask=#[$klic_polozky$: $name$]
     * GRID filter=single-entity #['name']
     */
    protected $product;
    
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
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Cena/mj [Kč]"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Cena/mj"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $price;

    /**
     * @ORM\Column(type="integer")
     * FORM type="integer"
     * FORM title="Počet"
     * FORM default-value="1"
     * FORM required="Toto pole je nutné vyplnit"
     * FORM rule-min='Zadejte číslo větší jak 0'#[1]
     */
    protected $count;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->count = 1;
    }
}