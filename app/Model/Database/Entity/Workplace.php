<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkplaceRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Workplace extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-placeholder='Název'
     * FORM required="Název je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis"
     *
     * GRID type='text'
     * GRID title="Popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="UserInWorkplace", mappedBy="workplace")
     * FORM type='multiselect'
     * FORM title="Vedoucí pracoviště"
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-multiple='true'
     * FORM attr-data-live-search='true'
     * FORM data-entity-values=User[$name$]['isMaster' > 1][]
     * FORM multiselect-entity=UserInWorkplace[workplace][master]
     *
     * GRID type='multi-text'
     * GRID title="Vedoucí"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='User'
     * GRID entity-alias='wm'
     * GRID entity-join-column='master'
     * GRID filter=multiselect-entity #[name]['name' > 'ASC']
     */
    protected $masters;

    /**
     * @ORM\OneToMany(targetEntity="WorkerPositionInWorkplace", mappedBy="workplace")
     * FORM type='multiselect'
     * FORM title="Pracovní pozice na pracovišti"
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-multiple='true'
     * FORM attr-data-live-search='true'
     * FORM data-entity-values=WorkerPosition[$name$][]['name' => 'ASC']
     * FORM multiselect-entity=WorkerPositionInWorkplace[workplace][position]
     *
     * GRID type='multi-text'
     * GRID title="Pracovní pozice na pracovišti"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='WorkerPosition'
     * GRID entity-join-column='position'
     * GRID entity-alias='wpos'
     * GRID filter=single-entity #['name']['name' > 'ASC']
     */
    protected $workerPositions;

    /**
     * @ORM\OneToMany(targetEntity="WorkplaceSuperiority", mappedBy="subordinateWorkplace")
     * FORM type='multiselect'
     * FORM title="Přímé nadřazené pracoviště"
     * FORM attribute-multiple='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search='true'
     * FORM data-entity=Workplace[name][]['name' => 'ASC']
     * FORM multiselect-entity=WorkplaceSuperiority[subordinateWorkplace][superiorWorkplace]
     * 
     * GRID type='multi-text'
     * GRID title="Přímé nadřazené pracoviště"
     * GRID visible='true'
     * GRID filter='single'
     * GRID entity-join-column=superiorWorkplace
     * GRID entity-link=name
     */
    protected $superiorWorkplaces;

    /**
     * @ORM\OneToMany(targetEntity="WorkplaceSuperiority", mappedBy="superiorWorkplace")
     * FORM type='multiselect'
     * FORM title="Přímé podřazené pracoviště"
     * FORM attribute-multiple='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search='true'
     * FORM data-entity=Workplace[name][]['name' => 'ASC']
     * FORM multiselect-entity=WorkplaceSuperiority[superiorWorkplace][subordinateWorkplace]
     * 
     * GRID type='multi-text'
     * GRID title="Přímé podřazené pracoviště"
     * GRID visible='true'
     * GRID filter='single'
     * GRID entity-join-column=subordinateWorkplace
     * GRID entity-link=name
     */
    protected $subordinateWorkplaces;


    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}