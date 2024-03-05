<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ApproveNormRepository")
 * @ORM\Table(name="`approve_norm`")
 * @ORM\HasLifecycleCallbacks
 */
class ApproveNorm extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Název normy"
     * FORM attribute-class="form-control"
     * FORM required="Název je povinné pole"
     *
     * GRID type='text'
     * GRID title="Název normy"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Norma"
     * FORM attribute-class="form-control"
     * FORM attribute-placeholder="(zadejte odkaz na dokument)"
     *
     * GRID type='text'
     * GRID title="Norma"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $norm;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka"
     * FORM attribute-placeholder='volitelná poznámka'
     * FORM attribute-class='form-control input-md'
     * FORM attribute-rows='3'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}