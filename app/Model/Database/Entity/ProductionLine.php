<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ProductionLineRepository")
 * @ORM\Table(name="`production_line`")
 * @ORM\HasLifecycleCallbacks
 */
class ProductionLine extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Název linky"
     * FORM attribute-class="form-control"
     * FORM required="Název linky je povinné pole"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Plánovat"
     * FORM default-value='1'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     *
     * GRID type='bool'
     * GRID title="Plánovat"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    /**
     * @ORM\OneToMany(targetEntity="Worker", mappedBy="productionLine")
     */
    protected $workers;

    /**
     * @ORM\OneToMany(targetEntity="Worker", mappedBy="productionLineChange")
     */
    protected $workersChange;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}