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
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\MaterialOnVisitRepository")
 * @ORM\Table(name="`material_on_visit`")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialOnVisit extends AbstractEntity
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
     * @ORM\Column(type="text", nullable=true)
     * FORM type='text'
     * FORM title="Název materiálu"
     *
     * GRID type='text'
     * GRID title="Název materiálu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='text'
     * FORM title="Sklad"
     *
     * GRID type='text'
     * GRID title="Sklad"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
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
     * FORM title="Množství"
     * FORM rule-float ='Prosím zadávejte pouze čísla'
     * FORM required='0'
     *
     * GRID type='text'
     * GRID title="Množství"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $count;

    /**
     * @ORM\ManyToOne(targetEntity="Visit")
     *
     * GRID type='translate-text'
     * GRID title="Výjezd"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Visit'
     * GRID entity-alias='vis'
     * GRID filter='single'
     */
    protected $visit;

    /**
     * @ORM\ManyToOne(targetEntity="Material")
     * FORM type='autocomplete'
     * FORM title='Materiál'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='Material'
     *
     * GRID type='link'
     * GRID title="Materiál"
     * GRID link-target="Material:edit"
     * GRID link-params=['id' > 'material.id']
     * GRID visible='true'
     * GRID filter=single-entity #['name']
     * GRID entity-alias='mat'
     * GRID entity-link=name
     * GRID entity='Material'
     */
    protected $material;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}