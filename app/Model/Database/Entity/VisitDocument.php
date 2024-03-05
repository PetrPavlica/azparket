<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\VisitDocumentRepository")
 * @ORM\Table(name="`visit_document`")
 * @ORM\HasLifecycleCallbacks
 */
class VisitDocument extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Vlastní název"
     * FORM attribute-placeholder='Vlastní název'
     *
     * GRID type='text'
     * GRID title="Vlastní název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

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
     * @ORM\ManyToOne(targetEntity="Visit")
     *
     * GRID type='translate-text'
     * GRID title="Výjezd"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Visit'
     * GRID entity-alias='vis'
     * GRID filter='single'
     */
    protected $visit;

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
     * FORM dir='_data/visit_documents'
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