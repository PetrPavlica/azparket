<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\VacationTypeRepository")
 * @ORM\Table(name="`vacation_type`")
 * @ORM\HasLifecycleCallbacks
 */
class VacationType extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Důvod absence"
     * FORM attribute-class="form-control"
     * FORM required="Důvod absence je povinné pole"
     *
     * GRID type='text'
     * GRID title="Důvod absence"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}