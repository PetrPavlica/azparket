<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\OperationLogRepository")
 * @ORM\Table(name="`operation_log`")
 * @ORM\HasLifecycleCallbacks
 */
class OperationLog extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * GRID type='text'
     * GRID title="Směna"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $namePublic;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $dateString;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     *
     * GRID type='date'
     * GRID title="Datum"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $datePlan;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * GRID type='text'
     * GRID title="Linka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $productionLine;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Chod linky"
     * FORM data-own=['1' > 'Plynulý chod'|'2' > 'Mimo provoz do 24h'|'3' > 'Mimo provoz nad 24h']
     * FORM prompt="- nevybráno"
     *
     * GRID type='text'
     * GRID title="Chod linky"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'1' > 'Plynulý chod'|'2' > 'Mimo provoz do 24h'|'3' > 'Mimo provoz nad 24h']
     */
    protected $endRun;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endRunChanged;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='datetime'
     * FORM title="od"
     * FORM attribute-class="form-control flatPick"
     */
    protected $endRunDate;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $endRunDateChanged;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='datetime'
     * FORM title="od"
     * FORM attribute-class="form-control flatPick"
     */
    protected $releaseStartDate;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $releaseStartDateChanged;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='datetime'
     * FORM title="Čas uvolnění linky"
     * FORM attribute-class="form-control flatPick"
     */
    protected $releaseEndDate;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $releaseEndDateChanged;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     * FORM type='datetime'
     * FORM title="Čas zápisu"
     * FORM attribute-class="form-control flatPick"
     */
    protected $releaseDate;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $releaseDateChanged;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámky"
     * FORM attribute-class='autosized'
     *
     * GRID type='text'
     * GRID title="Poznámky"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $note;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Záznam o kontrole závěsové techniky"
     * FORM attribute-class='autosized'
     *
     * GRID type='text'
     * GRID title="Záznam o kontrole závěsové techniky"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $hingeTechnology;


    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check1n1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check1n1Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check1n2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check1n2Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check2n1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check2n1Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check2n2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check2n2Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check3n1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check3n1Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check3n2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check3n2Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check4n1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check4n1Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check4n2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check4n2Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check5n1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check5n1Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check5n2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check5n2Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check6n1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check6n1Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check6n2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check6n2Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check7n1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check7n1Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check7n2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check7n2Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check8n1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check8n1Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check9n1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check9n1Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check10n1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check10n1Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check10n2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check10n2Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check10n3;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check10n3Changed;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=""
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $check10n4;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $check10n4Changed;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem1Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem1Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem1NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem2Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem2Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem2NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem3;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem3Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem3Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem3NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem4;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem4Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem4Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem4NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem5;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem5Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem5Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem5NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem6;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem6Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem6Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem6NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem7;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem7Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem7Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem7NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem8;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem8Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem8Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem8NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem9;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem9Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem9Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem9NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem10;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem10Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem10Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem10NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem11;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem11Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem11Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem11NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $giveItem12;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem12Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $giveItem12Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItem12NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem1Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem1Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem1NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem2Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem2Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem2NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem3;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem3Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem3Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem3NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem4;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem4Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem4Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem4NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem5;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem5Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem5Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem5NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem6;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem6Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem6Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem6NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem7;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem7Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem7Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem7NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem8;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem8Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem8Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem8NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem9;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem9Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem9Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem9NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem10;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem10Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem10Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem10NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem11;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem11Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem11Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem11NoteChanged;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title=""
     * FORM data-own=['V pořádku' > 'V pořádku'|'Poškozeno' > 'Poškozeno'|'Chybí' > 'Chybí']
     * FORM prompt="--"
     */
    protected $takeItem12;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem12Changed;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title=""
     * FORM attribute-class="form-control"
     */
    protected $takeItem12Note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItem12NoteChanged;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Předal"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $giveItemsCheck;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $giveItemsCheckChanged;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Převzal"
     * FORM default-value='0'
     * FORM attribute-class='form-control onoffswitch-checkbox'
     */
    protected $takeItemsCheck;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $takeItemsCheckChanged;

    /**
     * @ORM\OneToMany(targetEntity="OperationLogItem", mappedBy="operationLog")
     */
    protected $logItems;

    /**
     * @ORM\OneToMany(targetEntity="OperationLogProblem", mappedBy="operationLog")
     */
    protected $logProblems;

    /**
     * @ORM\OneToMany(targetEntity="OperationLogSuggestion", mappedBy="operationLog")
     */
    protected $logSuggestions;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}