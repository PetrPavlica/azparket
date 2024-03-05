<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkplaceSuperiorityRepository")
 * @ORM\Table(name="`workplace_superiority`")
 * @ORM\HasLifecycleCallbacks
 */
class WorkplaceSuperiority extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Workplace", inversedBy="subordinateWorkplaces")
     */
    protected $superiorWorkplace;

    /**
     * @ORM\ManyToOne(targetEntity="Workplace", inversedBy="superiorWorkplaces")
     */
    protected $subordinateWorkplace;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}