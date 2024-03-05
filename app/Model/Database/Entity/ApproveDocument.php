<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ApproveDocumentRepository")
 * @ORM\Table(name="`approve_document`")
 * @ORM\HasLifecycleCallbacks
 */
class ApproveDocument extends AbstractEntity
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
     * @ORM\ManyToOne(targetEntity="Approve")
     *
     * GRID type='translate-text'
     * GRID title="Schvalování"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Approve'
     * GRID entity-alias='ap'
     * GRID filter='single'
     */
    protected $approve;

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
     * FORM dir='_data/approve_documents'
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