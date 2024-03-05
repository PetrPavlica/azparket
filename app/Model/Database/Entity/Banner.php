<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\BannerRepository")
 * @ORM\Table(name="`banner`")
 * @ORM\HasLifecycleCallbacks
 */
class Banner extends AbstractEntity
{
    
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-placeholder='Pořadí'
     * FORM required='Pořadí je povinné pole!'
     * FORM attribute-class='form-control input-md'
     *
     * GRIsD type='number'
     * GRIsD title="Pořadí"
     * GRIsD sortable='true'
     * GRIsD filter='single'
     * GRIsD visible='true'
     */
    protected $orderBanner;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $image;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='select'
     * FORM prompt='-- vyberte'
     * FORM title='Typ'
     * FORM attribute-class='form-control selectpicker'
     * FORM data-own=['main' > 'Úvod' | 'submain ' > 'Podstránka'  | 'main_banner' > 'Úvodní banner']
     * FORM required='false'
     */
    protected $type;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}
