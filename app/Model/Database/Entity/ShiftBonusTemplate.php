<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ShiftBonusTemplateRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ShiftBonusTemplate extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
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
     * FORM title="Den v týdnu"
     *
     * GRID type='text'
     * GRID title="Den v týdnu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $dayOfWeek;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum"
     *
     * GRID type='date'
     * GRID title="Datum"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateEnd;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Směna"
     *
     * GRID type='text'
     * GRID title="Směna"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $shift;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Linka"
     *
     * GRID type='text'
     * GRID title="Linka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $productionLine;

    /**
     * @ORM\ManyToOne(targetEntity="ShiftBonusGroup")
     */
    protected $shiftBonusGroup;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}