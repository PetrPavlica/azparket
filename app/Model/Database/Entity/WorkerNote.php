<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerNoteRepository")
 * @ORM\Table(name="`worker_note`")
 * @ORM\HasLifecycleCallbacks
 * 
 * FORM-SECTION default=1
 */
class WorkerNote extends AbstractEntity
{
    use TId;
    use TCreatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class="form-control"
     * FORM required="Název je povinné pole"
     * FORM section=default
     *
     * GRID type='text'
     * GRID title="Název poznámky"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=false)
     * FORM type='textarea'
     * FORM title="Poznámka"
     * FORM required="Neponechávejte pole prázdné"
     * FORM attribute-class='md-textarea form-control'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $note;

    /**
     * @ORM\ManyToOne(targetEntity="Worker", inversedBy="notes")
     * FORM type='select'
     * FORM title='Zaměstnanec'
     * FORM prompt='Nic není vybráno'
     * FORM required='Je nutné přiřadit zaměstnance'
     * FORM data-entity-values=Worker[$surname$ $name$ ($personalId$)][]['surname' => 'ASC']
     * FORM attribute-class="form-control selectpicker"
     * FORM attribute-data-live-search='true'
     *
     * GRID type='text'
     * GRID title="Zaměstnanec"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Worker'
     * GRID entity-alias='nw'
     * GRID filter=single-entity #['name']
     */
    protected $worker;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}