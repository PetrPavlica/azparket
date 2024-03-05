<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;

class VisitLogPresenter extends BasePresenter
{
    /**
     * ACL name='Správa změn výjezdů'
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
     * ACL name='Tabulka s přehledem změn výjezdů'
     */
    public function createComponentTable()
    {
        $findBy = [];
        if (!in_array($this->usrGrp, [1])) { //role admin vidí všechny změny
            $findBy = ['user' => $this->user->getId()];
        }
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\VisitLog::class, get_class(), __FUNCTION__, 'dafault', $findBy);
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');

        foreach ($grid->getColumns() as $col) {
            if (get_class($col) == 'Ublaboo\DataGrid\Column\ColumnLink' || get_class($col) == 'Ublaboo\DataGrid\Column\ColumnStatus' || get_class($col) == 'Ublaboo\DataGrid\Column\ColumnCallback') {
                continue;
            }
            // Add class clikable for enable click to column.
            if ($col != null && strpos($col->getTemplate(), 'column_file.latte') === false) {
                $col = $col->addCellAttributes(['class' => 'clickable']);
            }
        }
        $grid->setRowCallback(function ($item, $tr) {
            if ($item->visit) {
                $tr->addAttribute('data-click-to', $this->link('Visit:edit', ['id' => $item->visit->id]));
            }
        });

        $multiAction->addActionCallback('viewVisit', 'Výjezd', function ($itemId) {
            $visitLog = $this->em->getVisitLogRepository()->find($itemId);
            if ($visitLog && $visitLog->visit) {
                $this->redirect('Visit:edit', ['id' => $visitLog->visit->id]);
            }
        });
        $action = $multiAction->getAction('viewVisit');
        if ($action)
            $action->setIcon('eye')
                ->setTitle('Výjezd')
                ->setClass('dropdown-item datagrid-multiaction-dropdown-item text-primary');
        $grid->allowRowsMultiAction('multiAction', 'viewVisit', function($item) {
            if ($item->visit) {
                return true;
            } else {
                return false;
            }
        });

        $grid->setDefaultSort(['foundedDate' => 'DESC']);
        return $grid;
    }

}
