<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;

class MaterialNeedBuyPresenter extends BasePresenter
{
    /**
     * ACL name='Správa materiálů nutno objednat'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Tabulka s přehledem materiálů nutno objednat'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\MaterialNeedBuy::class, get_class(), __FUNCTION__);
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');

        $column = $grid->addColumnStatus('isBuy', 'Je objednán?');
        if ($column) {
            $column->setCaret(false)
                ->addOption(true, 'Ano')
                ->setIcon('check')
                ->setClass('btn btn-success')
                ->endOption()
                ->addOption(false, 'Ne')
                ->setIcon('times')
                ->setClass('btn btn-danger')
                ->endOption()
                ->setAlign('center')
                ->onChange[] = [$this, 'changeIsBuy'];
        }
        $column->setFilterSelect(['' => 'Vše',  0 => 'Ne', 1 => 'Ano'])
            ->setPrompt('Vše')
            ->setTranslateOptions();
        $column->setSortable();

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
            $visitLog = $this->em->getMaterialNeedBuyRepository()->find($itemId);
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
            $entity = $this->em->getMaterialNeedBuyRepository()->find($itemId);
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

    public function changeIsBuy($id, $status)
    {
        $mnb = $this->em->getMaterialNeedBuyRepository()->find($id);
        if ($mnb) {
            $mnb->setIsBuy($status);
            $this->em->flush($mnb);
        }
        $this->redirect('this');
    }

}
