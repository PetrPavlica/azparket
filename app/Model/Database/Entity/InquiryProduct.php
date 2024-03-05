<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\InquiryProductRepository")
 * @ORM\HasLifecycleCallbacks
 */
class InquiryProduct extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Inquiry")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $inquiry;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="products")
     * @ORM\JoinColumn(onDelete="CASCADE")
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