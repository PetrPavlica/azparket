<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ShiftBonusGroupRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ShiftBonusGroup extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis"
     *
     * GRID type='text'
     * GRID title="Popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;


    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}