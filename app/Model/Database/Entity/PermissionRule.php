<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\PermissionRuleRepository")
 */
class PermissionRule extends AbstractEntity
{

    const ACTION_WRITE = 'write';
    const ACTION_READ = 'read';
    const ACTION_SHOW = 'show';
    const ACTION_ALL = 'all';
    const ACTION_DENY = 'deny';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="PermissionGroup", inversedBy="rule")
     */
    protected $group;

    /**
     * @ORM\Column(type="string")
     */
    protected $item;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $action;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}