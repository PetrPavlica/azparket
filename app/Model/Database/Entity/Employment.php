<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\EmploymentRepository")
 * @ORM\Table(name="`employment`")
 * @ORM\HasLifecycleCallbacks
 */
class Employment extends AbstractEntity
{
    use TId;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class="form-control"
     * FORM required="Název je povinné pole"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="CZ-ICSE"
     * FORM attribute-class='form-control'
     *
     * GRID type='text'
     * GRID title="CZ-ICSE"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $czicse;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}