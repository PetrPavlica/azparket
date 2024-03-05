<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\OfferPartTemplateRepository")
 * @ORM\Table(name="`offer_part_template`")
 * @ORM\HasLifecycleCallbacks
 */
class OfferPartTemplate extends AbstractEntity
{
    use TId;

    /**
     * @ORM\Column(type="string", nullable=false)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title='Cena'
     * FORM attribute-class="form-control"
     * FORM attribute-placeholder="Text v ceníku"
     *
     * GRID type='text'
     * GRID title="Cena"
     * GRID visible='true'
     * GRID sortable='true'
     * GRID filter='single'
     */
    protected $price;

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
     * @ORM\Column(type="text", nullable=true)
     * FORM type='editor'
     * FORM title=""
     * FORM attribute-class="form-control"
     * FORM attribute-style="height:350px"
     *
     * GRID type='text'
     * GRID title="Obsah"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $content;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="V číselníku"
     * FORM data-own=['1' > 'Nabídka modulů'|'2' > 'Popis produktů'|'3' > 'Reference']
     * FORM prompt="Nevybráno"
     * FORM attribute-class="selectpicker"
     *
     * GRID type='text'
     * GRID title="V číselníku"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'1' > 'Nabídka modulů'|'2' > 'Popis produktů'|'3' > 'Reference']
     * GRID replacement=['' > 'Nevybráno'|'1' > 'Nabídka modulů'|'2' > 'Popis produktů'|'3' > 'Reference']
     */
    protected $type;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * FORM type='checkbox'
     * FORM title="Vložit za ceník"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='bool'
     * GRID title="Vložit za ceník"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $isAfterPricing;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isDefault;

    /**
     * @ORM\OneToMany(targetEntity="OfferPart", mappedBy="template")
     */
    protected $parts;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->isModule = 1;
        $this->isReference = 0;
    }

}