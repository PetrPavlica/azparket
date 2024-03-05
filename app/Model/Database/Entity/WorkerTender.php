<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerTenderRepository")
 * @ORM\Table(name="`worker_tender`")
 * @ORM\HasLifecycleCallbacks
 * 
 */
class WorkerTender extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string")
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID visible='true'
     * GRID filter='single'
     */
    protected $name;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum konání"
     *
     * GRID type='date'
     * GRID title="Datum konání"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $tenderDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Maximální kapacita"
     * FORM rule-min='Zadejte číslo větší jak 0'#[1]
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Maximální kapacita"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $maxCapacity;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Začátek (např. 8:15)"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Začátek"
     * GRID visible='true'
     * GRID filter='single'
     */
    protected $timeStart;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Konec (např. 12:45)"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Konec"
     * GRID visible='true'
     * GRID filter='single'
     */
    protected $timeEnd;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Kmenový"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='bool'
     * GRID title="Kmenový"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='left'
     */
    protected $tribal;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Osnova školení"
     * FORM attribute-class='form-control'
     * FORM attribute-rows='3'
     *
     * GRID type='text'
     * GRID title="Osnova školení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Opakovat"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='bool'
     * GRID title="Opakovat"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='left'
     */
    protected $repeatTender;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Typ"
     * FORM data-own=['Pravidelné' > 'Pravidelné'|'Vstupní' > 'Vstupní']
     * FORM prompt="- nevybráno"
     *
     * GRID type='text'
     * GRID title="Typ"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'Pravidelné' > 'Pravidelné'|'Vstupní' > 'Vstupní']
     */
    protected $tenderType;
    
    /**
     * @ORM\OneToMany(targetEntity="WorkerInWorkerTender", mappedBy="tender")
     * 
     * GRID type='multi-text'
     * GRID title="Zaměstnanci v tomto řízení"
     * GRID visible='true'
     * GRID entity='Worker'
     * GRID entity-alias='ws'
     * GRID entity-join-column=worker
     * GRID entity-link=name
     * GRID filter=single-entity #['name']
     */
    protected $workers;

    /**
     * @ORM\OneToMany(targetEntity="SkillInWorkerTender", mappedBy="tender")
     * FORM type='multiselect'
     * FORM title="Učené dovednosti"
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-multiple='true'
     * FORM attr-data-live-search='true'
     * FORM data-entity-values=Skill[$name$][]['name' => 'ASC']
     * FORM multiselect-entity=SkillInWorkerTender[tender][skill]
     *
     * GRID type='multi-text'
     * GRID title="Učené dovednosti"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Skill'
     * GRID entity-join-column='skill'
     * GRID entity-alias='siwt'
     * GRID filter=single-entity #['name']['name' > 'ASC']
     */
    protected $skills;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}