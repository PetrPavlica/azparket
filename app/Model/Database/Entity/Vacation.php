<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\VacationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Vacation extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Worker")
     * FORM type='select'
     * FORM attribute-class="form-control selectpicker"
     * FORM title="Zaměstnanec"
     * FORM prompt="Nic není vybráno"
     * FORM data-entity-values=Worker[$surname$ $name$][]['surname' => 'ASC']
     * FORM required="Zaměstnanec je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Zaměstnanec"
     * GRID entity-link='surname'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Worker'
     * GRID entity-alias='wrrk'
     * GRID value-mask=#[$surname$ $name$]
     * GRID filter=single-entity #['name', 'surname']
     */
    protected $worker;

    /**
     * @ORM\ManyToOne(targetEntity="VacationType")
     * FORM type='select'
     * FORM attribute-class="form-control selectpicker"
     * FORM title="Důvod absence"
     * FORM prompt="Nic není vybráno"
     * FORM data-entity=VacationType[name][]['name' => 'ASC']
     *
     * GRID type='text'
     * GRID title="Důvod absence"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='VacationType'
     * GRID entity-alias='vct'
     * GRID filter=single-entity #['name']
     */
    protected $vacationType;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Začátek"
     *
     * GRID type='date'
     * GRID title="Začátek"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateStart;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Konec"
     *
     * GRID type='date'
     * GRID title="Konec"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateEnd;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Čerpaná dovolená (hod)"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Čerpaná dovolená (hod)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $hours;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Čerpat dovolenou"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='bool'
     * GRID title="Čerpat dovolenou"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $countHours;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Poznámka"
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}