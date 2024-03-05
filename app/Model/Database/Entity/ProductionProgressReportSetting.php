<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ProductionProgressReportSettingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ProductionProgressReportSetting extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='hidden'
     *
     * GRID type='text'
     * GRID title="Linka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $line;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='number'
     * FORM title="Počet lidí na směnu"
     * FORM rule-float='Prosím číslo'
     * FORM required='Toto pole je povinné'
     *
     * GRID type='text'
     * GRID title="Počet lidí na směnu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $numberPeoplePerShift;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='number'
     * FORM title="Mzdové náklady"
     * FORM rule-float='Prosím číslo'
     * FORM required='Toto pole je povinné'
     *
     * GRID type='text'
     * GRID title="Mzdové náklady"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $monthlyLaborCosts;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}