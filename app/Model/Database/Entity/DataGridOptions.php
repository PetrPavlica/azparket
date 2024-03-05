<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\DataGridOptionsRepository")
 * @ORM\Table(
 *     name="`datagrid_options`",
 *     indexes={
 *      @ORM\Index(name="key_idx", columns={"key_name"})
 *     }
 * )
 */
class DataGridOptions extends AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Database\Entity\User")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $keyName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $valueType;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $customer;
}