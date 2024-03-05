<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ApproveTimeRepository")
 * @ORM\Table(name="`approve_time`")
 */
class ApproveTime extends AbstractEntity
{
    use TId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder="Název"
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
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Počet dní na schválení"
     * FORM attribute-class='form-control input-md'
     * FORM required="Počet dní je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Počet dní na schválení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $numDays;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}