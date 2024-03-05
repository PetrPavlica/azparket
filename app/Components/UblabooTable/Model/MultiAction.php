<?php


namespace App\Components\UblabooTable\Model;


use Ublaboo\DataGrid\Column\Action;
use Ublaboo\DataGrid\Column\ActionCallback;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;

class MultiAction extends \Ublaboo\DataGrid\Column\MultiAction
{
    public function __construct(DataGrid $grid, string $key, string $name)
    {
        parent::__construct($grid, $key, $name);
        $this->setTemplate(__DIR__ . '/../templates/column_multi_action.latte');
    }

    /**
     * @return \Ublaboo\DataGrid\Column\Action
     */
    public function addActionCallback(
        string $key,
        string $name,
        ?callable $callback = null
    ): ActionCallback
    {
        if (isset($this->actions[$key])) {
            throw new DataGridException(
                sprintf('There is already action at key [%s] defined for MultiAction.', $key)
            );
        }

        $params = ['__id' => $this->grid->getPrimaryKey()];

        $this->actions[$key] = $action = new ActionCallback($this->grid, $key, $this->key.'|'.$key, $name, $params);
        $action->setClass('dropdown-item datagrid-multiaction-dropdown-item');

        if ($callback !== null) {
            $action->onClick[] = $callback;
        }

        return $action;
    }
}