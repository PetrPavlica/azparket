<?php

namespace App\Components\UblabooTable\Model;

use Nette\Utils\Html;
use Ublaboo\DataGrid\Utils\ItemDetailForm;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Column\ItemDetail;
use Ublaboo;
use Ublaboo\DataGrid\Traits;
use Ublaboo\DataGrid\Exception\DataGridItemDetailException;

class ItemDetailSimple extends ItemDetail {

    /**
     * @param DataGrid $grid
     * @param string   $primary_where_column
     */
    public function __construct(DataGrid $grid, $primary_where_column)
    {
        parent::__construct($grid, $primary_where_column);
        $this->class = 'blue-text ajax';
        $this->title = 'Detail';

    }

}
