<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\TaskCommentRepository")
 * @ORM\Table(name="`task_comment`")
 * @ORM\HasLifecycleCallbacks
 */
class TaskComment extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title='Komentář'
     * FORM attribute-style="height: 200px"
     *
     * GRID type='text'
     * GRID title="Komentář"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Task")
     *
     * GRID type='translate-text'
     * GRID title="Úkol"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Task'
     * GRID entity-alias='ap'
     * GRID filter='single'
     */
    protected $task;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * GRID type='translate-text'
     * GRID title="Přidal"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='u'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $user;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}