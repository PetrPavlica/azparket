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
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\MaterialRepository")
 * @ORM\Table(name="`material`")
 * @ORM\HasLifecycleCallbacks
 */
class Material extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='text'
     * FORM title="Číslo materiálu"
     *
     * GRID type='text'
     * GRID title="Číslo materiálu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $number;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-placeholder='Název'
     * FORM required="Název je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="MaterialStock")
     * FORM type='select'
     * FORM title="Sklad"
     * FORM prompt="Nevybráno"
     * FORM attribute-data-live-search='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=MaterialStock[name][][]
     * FORM data-entity-values=MaterialStock[$number$ - $name$][]['id' > 'ASC']
     *
     * GRID type='translate-text'
     * GRID title="Sklad"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='MaterialStock'
     * GRID entity-alias='ms'
     * GRID filter='single'
     * GRID filter=multicolumnname-multiselect-entity #[$number$ $name$]['id' > 'ASC']

     */
    protected $stock;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='text'
     * FORM title="Jednotka"
     *
     * GRID type='text'
     * GRID title="Jednotka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $unit;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Prodejní cena"
     * FORM rule-float ='Prosím zadávejte pouze čísla'
     * FORM required='0'
     *
     * GRID type='text'
     * GRID title="Prodejní cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $priceSale;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='text'
     * FORM title="Původní název"
     * FORM attribute-placeholder='Původní název'
     *
     * GRID type='text'
     * GRID title="Původní název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $origName;

    /**
     * @ORM\ManyToOne(targetEntity="MaterialGroup")
     * FORM type='select'
     * FORM title="Skupina"
     * FORM prompt="Nevybráno"
     * FORM attribute-data-live-search='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=MaterialGroup[name][][]
     *
     * GRID type='translate-text'
     * GRID title="Skupina"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='MaterialGroup'
     * GRID entity-alias='mg'
     * GRID filter='single'
     */
    protected $group;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní"
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
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="GWP"
     * FORM attribute-placeholder='GWP'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     *
     * GRID type='integer'
     * GRID title="GWP"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $qwp;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title='Poznámka'
     * FORM attribute-style="height: 200px"
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title='Odkaz'
     *
     * GRID type='text'
     * GRID title="Odkaz"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $link;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    public function getStockName()
    {
        if ($this->stock) {
            return sprintf("%s - %s", $this->stock->number, $this->stock->name);
        } else {
            return "";
        }
    }

}