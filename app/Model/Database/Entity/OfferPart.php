<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\OfferPartRepository")
 * @ORM\Table(name="`offer_part`")
 * @ORM\HasLifecycleCallbacks
 */
class OfferPart extends AbstractEntity
{
    use TId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Označení"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Označení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", name="`order`", nullable=false)
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-class='form-control input-md'
     * FORM required="Pořadí je povinné pole!"
     * FORM default-value='0'
     *
     * GRID type='text'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="Offer", inversedBy="parts")
     * FORM type='autocomplete'
     * FORM title='Nabídka'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='Offer'
     *
     * GRID type='text'
     * GRID title="Nabídka"
     * GRID entity-link='offerNo'
     * GRID visible='true'
     * GRID entity='Offer'
     * GRID entity-alias='off'
     * GRID sortable='true'
     * GRID filter=single-entity #['offerNo']
     */
    protected $offer;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title='Cena'
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Cena"
     * GRID visible='true'
     * GRID sortable='true'
     * GRID filter='single'
     */
    protected $price;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='text'
     * FORM title="Obsah"
     * FORM attribute-class="form-control"
     * FORM attribute-style=""
     *
     * GRID type='text'
     * GRID title="Obsah"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $content;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Kapitola"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='bool'
     * GRID title="Kapitola"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $isChapter;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Na novou stránku"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='bool'
     * GRID title="Na novou stránku"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $pageBreak;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * FORM type='checkbox'
     * FORM title="Za ceník"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='bool'
     * GRID title="Za ceník"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $isAfterPricing;

    /**
     * @ORM\ManyToOne(targetEntity="OfferPartTemplate", inversedBy="parts")
     */
    protected $template;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->isModule = 1;
        $this->order = 0;
        $this->isReference = 0;
    }

}