<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;

class TaskLogPresenter extends BasePresenter
{
    /**
     * ACL name='Správa změn úkolů'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderDefault()
    {

    }

    /**
     * ACL name='Tabulka s přehledem změn úkolů'
     */
    public function createComponentTable()
    {
        $findBy = [];
        if (!in_array($this->usrGrp, [1])) { //role admin vidí všechny změny
            $findBy = ['user' => $this->user->getId(), 'task.assigned' => $this->user->getId()];
        }
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\TaskLog::class, get_class(), __FUNCTION__, null, $findBy);
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $grid->setDefaultSort(['foundedDate' => 'DESC']);
        return $grid;
    }

}
