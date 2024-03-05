<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * (MasterInWorkplace)
 * 
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\UserInWorkplaceRepository")
 * @ORM\Table(name="`user_in_workplace`")
 */
class UserInWorkplace extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Workplace", inversedBy="masters")
     */
    protected $workplace;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="workplaces")
     */
    protected $master;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}