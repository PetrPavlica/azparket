<?php

namespace App\Components\UblabooTable\Model;

use Ublaboo\DataGrid\Column\ColumnText;
use Ublaboo\DataGrid\DataGrid;

class ColumnTextTranslate extends ColumnText
{

    public function __construct(DataGrid $grid, $key, $column, $name)
    {
        parent::__construct($grid, $key, $column, $name);

        $this->setTemplate(__DIR__ . '/../templates/column_textTranlate.latte');
    }

    /**
     * Column can have variables that will be passed to custom template scope
     * @return array
     */
    public function getTemplateVariables(): array
    {
        return array_merge($this->templateVariables, [
            'column' => $this->getColumn(),
            'textColumn' => $this
        ]);
    }

}