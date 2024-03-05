<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\SkillRepository")
 * @ORM\Table(name="`skill`")
 * @ORM\HasLifecycleCallbacks
 */
class Skill extends AbstractEntity
{
    use TId;
    use TCreatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class="form-control"
     * FORM required="Název dovednosti je povinné pole"
     *
     * GRID type='text'
     * GRID title="Název dovednosti"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;
    
    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Priorita (pořadí)"
     * FORM rule-integer='Prosím zadávejte pouze celá čísla'
     * FORM attribute-class="form-control"
     * FORM default-value=0
     *
     * GRID type='integer'
     * GRID title="Priorita"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderN;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis"
     * FORM attribute-placeholder='Popis dovednosti'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Je školitelný"
     * FORM attribute-class="m-0 mr-2 mt-2"
     *
     * GRID type='bool'
     * GRID title="Je školitelný"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neškolitelný'|'1' > 'Školitelný']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $isTenderable;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Interně"
     * FORM attribute-class="ml-4 mr-2 mt-2"
     *
     * GRID type='bool'
     * GRID title="Interně školitelný"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Interně neškolitelný'|'1' > 'Interně školitelný']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $tenderableIntern;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Externě"
     * FORM attribute-class="ml-4 mr-2 mt-2"
     *
     * GRID type='bool'
     * GRID title="Externě školitelný"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Externě neškolitelný'|'1' > 'Externě školitelný']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $tenderableExtern;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Jak často školit"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Jak často školit"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $tenderableTime;

    /**
     * @ORM\ManyToOne(targetEntity="SkillType", inversedBy="skills")
     * FORM type='select'
     * FORM title="Typ dovednosti"
     * FORM prompt="Nevybráno"
     * FORM attribute-data-live-search='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=SkillType[name][]['name' => 'ASC']
     * 
     * GRID type='text'
     * GRID title="Typ dovednosti"
     * GRID visible='true'
     * GRID filter=single-entity #['name']
     * GRID entity-alias='st'
     * GRID entity-link=name
     * GRID entity='SkillType'
     */
    protected $type;

    /**
     * @ORM\OneToMany(targetEntity="SkillInWorker", mappedBy="skill")
     */
    protected $workers;

    /**
     * @ORM\OneToMany(targetEntity="SkillInWorkerPosition", mappedBy="skill")
     */
    protected $positions;

    /**
     * @ORM\OneToMany(targetEntity="SkillInWorkerTender", mappedBy="skill")
     */
    protected $tenders;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}