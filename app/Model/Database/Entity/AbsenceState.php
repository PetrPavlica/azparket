<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\AbsenceStateRepository")
 * @ORM\Table(name="`absence_state`")
 * @ORM\HasLifecycleCallbacks
 */
class AbsenceState extends AbstractEntity
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
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Pořadí (priorita)"
     * FORM attribute-placeholder='Pořadí'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     *
     * GRID type='integer'
     * GRID title="Pořadí (priorita)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $stateOrder;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Pro všechny"
     * FORM default-value='0'
     *
     * GRID type='bool'
     * GRID title="Pro všechny"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $forAll;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Editace pro zaměstnance"
     * FORM default-value='0'
     *
     * GRID type='bool'
     * GRID title="Editace zaměstnancem?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $allowEditTech;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}