<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\UserRepository")
 * @ORM\Table(
 *     name="`user`",
 *     indexes={
 *      @ORM\Index(name="username", columns={"username"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks
 *
 * FORM-SECTION default=1
 *
 * FORM-SECTION rights=2
 */
class User extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false, unique=true)
     * FORM type='text'
     * FORM title="Přihlašovací jméno (login)"
     * FORM attribute-data-lpignore="true"
     *
     * GRID type='text'
     * GRID title="Uživatelské jméno"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=60, nullable=false)
     * FORM type='password'
     * FORM title="Heslo"
     * FORM rule-min_length='Minimální délka hesla je %d' #[5]
     * FORM required='0'
     * FORM attribute-data-lpignore="true"
     */
    protected $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Jméno a příjmení"
     * FORM required="Jméno a příjmení je povinné pole"
     *
     * GRID type='text'
     * GRID title="Jméno a příjmení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * FORM type='text'
     * FORM title="Telefon"
     * FORM default-value='+420 '
     *
     * GRID type='text'
     * GRID title="Telefon"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * FORM type='text'
     * FORM title="Mobil"
     * FORM default-value='+420 '
     *
     * GRID type='text'
     * GRID title="Mobil"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $mobile;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * FORM type='text'
     * FORM title="Fax"
     * FORM default-value='+420 '
     *
     * GRID type='text'
     * GRID title="Fax"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $fax;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='email'
     * FORM title="E-mail"
     * FORM default-value='@'
     * FORM required="E-mail je povinné pole"
     *
     * GRID type='text'
     * GRID title="E-mail"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $email;

    /**
     * @ORM\ManyToOne(targetEntity="PermissionGroup", inversedBy="user")
     * FORM type='select'
     * FORM prompt='--vyberte'
     * FORM title='Oprávnění'
     * FORM required='Pozice uživatele je povinné pole!'
     * FORM data-entity=PermissionGroup[name]
     *
     * GRID type='translate-text'
     * GRID title="Oprávnění"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='PermissionGroup'
     * GRID entity-alias='pg'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $group;

    /**
     * @ORM\ManyToOne(targetEntity="Department")
     * FOsRM type='select'
     * FOsRM prompt='--vyberte'
     * FOsRM title='Oddělení'
     * FOsRM data-entity=Department[name]
     *
     * GRsID type='translate-text'
     * GRsID title="Oddělení"
     * GRsID entity-link='name'
     * GRsID visible='true'
     * GRsID entity='Department'
     * GRsID entity-alias='d'
     * GRsID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $department;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title='Administrátor'
     * FORM section=rights
     *
     * GRsID type='bool'
     * GRsID title="Administrátor"
     * GRsID sortable='true'
     * GRsID filter=select(['' => 'Vše', '0' => 'Ne', '1' => 'Ano'])
     * GRsID visible='true'
     * GRsID align='center'
     */
    protected $isAdmin;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title='Obchodník'
     * FORM section=rights
     *
     * GRsID type='bool'
     * GRsID title="Obchodník"
     * GRsID sortable='true'
     * GRsID filter=select(['' => 'Vše', '0' => 'Ne', '1' => 'Ano'])
     * GRsID visible='true'
     * GRsID align='center'
     */
    protected $isSalesman;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title='Zablokován'
     * FORM section=rights
     *
     * GRID type='bool'
     * GRID title="Zablokován"
     * GRID sortable='true'
     * GRID filter=select(['' => 'Vše', '0' => 'Ne', '1' => 'Ano'])
     * GRID visible='true'
     * GRID align='center'
     */
    protected $isBlocked;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title='Vedoucí'
     * FORM section=rights
     *
     * GRID type='bool'
     * GRID title="Vedoucí"
     * GRID sortable='true'
     * GRID filter=select(['' => 'Vše', '0' => 'Ne', '1' => 'Ano'])
     * GRID visible='true'
     * GRID align='center'
     */
    protected $isMaster;

    /**
     * @ORM\OneToMany(targetEntity="WorkerInUser", mappedBy="master")
     */
    protected $workers;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FOsRM type='checkbox'
     * FOsRM title='Přístup kvalifikace'
     * FOsRM section=rights
     *
     * GRsID type='bool'
     * GRsID title="Přístup kvalifikace"
     * GRsID sortable='true'
     * GRsID filter=select(['' => 'Vše', '0' => 'Ne', '1' => 'Ano'])
     * GRsID visible='true'
     * GRsID align='center'
     */
    protected $qualificationAllow;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FOsRM type='checkbox'
     * FOsRM title='Úprava všech kvalifikací'
     * FOsRM section=rights
     *
     * GRsID type='bool'
     * GRsID title="Úprava všech kvalifikací"
     * GRsID sortable='true'
     * GRsID filter=select(['' => 'Vše', '0' => 'Ne', '1' => 'Ano'])
     * GRsID visible='true'
     * GRsID align='center'
     */
    protected $qualificationEdit;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FOsRM type='checkbox'
     * FOsRM title='Zobrazení efektivnosti kvalifikace'
     * FOsRM section=rights
     *
     * GRsID type='bool'
     * GRsID title="Zobrazení efektivnosti kvalifikace"
     * GRsID sortable='true'
     * GRsID filter=select(['' => 'Vše', '0' => 'Ne', '1' => 'Ano'])
     * GRsID visible='true'
     * GRsID align='center'
     */
    protected $qualificationViewEffective;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FOsRM type='checkbox'
     * FOsRM title='Přístup dokumenty'
     * FOsRM section=rights
     *
     * GRsID type='bool'
     * GRsID title="Přístup dokumenty"
     * GRsID sortable='true'
     * GRsID filter=select(['' => 'Vše', '0' => 'Ne', '1' => 'Ano'])
     * GRsID visible='true'
     * GRsID align='center'
     */
    protected $documentsAllow;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isHidden;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Naposledy přihlášen"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $lastLoggedAt;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * FORM type='upload'
     * FORM title='Podpis'
     * FORM dir='_data/users/sign'
     *
     * GRID type='image'
     * GRID title='Podpis'
     * GRID visible='false'
     */
    protected $signature;

    /**
     * @ORM\OneToMany(targetEntity="Worker", mappedBy="user")
     */
    protected $workersUsr;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $menu;

    public function __construct($data = null)
    {
        $this->isHidden = false;
        $this->isAdmin = false;
        $this->isBlocked = false;
        $this->isMaster = false;
        $this->qualificationAllow = false;
        $this->qualificationEdit = false;
        $this->qualificationViewEffective = false;
        $this->documentsAllow = false;
        parent::__construct($data);
    }

    public function changeLoggedAt(): void
    {
        $this->lastLoggedAt = new \DateTime();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function changeUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getLastLoggedAt(): ?\DateTime
    {
        return $this->lastLoggedAt;
    }

    public function getPasswordHash(): string
    {
        return $this->password;
    }

    public function changePasswordHash(string $password): void
    {
        $this->password = $password;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPermissions(): array
    {
        $permissionArr = [];

        if ($this->group) {
            $permissionArr = $this->group->getRules();
        }

        return $permissionArr;
    }
}