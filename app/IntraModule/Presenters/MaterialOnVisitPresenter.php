<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;

class MaterialOnVisitPresenter extends BasePresenter
{
    /**
     * ACL name='Správa spotřebovaný materiál ve výjezdech'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Tabulka s přehledem spotřebovaného materiálu ve výjezdech'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\MaterialOnVisit::class, get_class(), __FUNCTION__);
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
            $visitLog = $this->em->getMaterialOnVisitRepository()->find($itemId);
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

        $multiAction->addActionCallback('viewMaterial', 'Materiál', function ($itemId) {
            $entity = $this->em->getMaterialOnVisitRepository()->find($itemId);
            if ($entity && $entity->material) {
                $this->redirect('Material:edit', ['id' => $entity->material->id]);
            }
        });
        $action = $multiAction->getAction('viewMaterial');
        if ($action)
            $action->setIcon('cube')
                ->setTitle('Materiál')
                ->setClass('dropdown-item datagrid-multiaction-dropdown-item text-info');
        $grid->allowRowsMultiAction('multiAction', 'viewMaterial', function($item) {
            if ($item->material) {
                return true;
            } else {
                return false;
            }
        });

        return $grid;
    }

}
