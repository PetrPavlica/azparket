<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ApprovePartDocumentRepository")
 * @ORM\Table(name="`approve_part_document`")
 * @ORM\HasLifecycleCallbacks
 */
class ApprovePartDocument extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title='Poznámka'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="ApprovePart", inversedBy="documents")
     * FORM type='text'
     *
     * GRID type='translate-text'
     * GRID title="Položka"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='ApprovePart'
     * GRID entity-alias='apr'
     * GRID filter='single'
     */
    protected $approvePart;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * GRID type='translate-text'
     * GRID title="Založil"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='u'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * FORM type='upload'
     * FORM title='Soubor'
     * FORM dir='_data/approve_part_documents'
     *
     * GRID type='file'
     * GRID title='Soubor'
     */
    protected $document;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}