<?php

namespace App\Components\UblabooTable\Model;

use Ublaboo\DataGrid\Column\ColumnStatus;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Status\Option;

class ColumnButtonCallback extends ColumnStatus
{

    /**
     * @var string
     */
    public $dateFormat = 'j.n.Y';

    public function __construct(DataGrid $grid, $key, $column, $name)
    {
        parent::__construct($grid, $key, $column, $name);

        $this->setTemplate(__DIR__ . '/../templates/column_buttonCallback.latte');
    }

    /**
     * Add prompt option to status select
     * @param string $text
     * @return Option
     * @throws DataGridColumnStatusException
     */
    public function addPrompt($text)
    {
        $option = new OptionPrompt(null, $this, NULL, $text);
        $this->options[] = $option;
        return $option;
    }

    public function getCurrentOption(Row $row): ?Option
    {
        foreach ($this->getOptions() as $option) {
            if ($option->getValue() === NULL) {
                continue;
            }

            if (is_numeric($option->getValue())) {
                if (!is_numeric($row->getValue($this->getColumn())))
                    continue;
            }
            if ($option->getValue() == $row->getValue($this->getColumn())) {
                return $option;
            }
        }
        return null;
    }

    /**
     * Get prompt option to select
     * @param Row $row
     * @return Option
     */
    public function getPromptOption(Row $row)
    {
        foreach ($this->getOptions() as $option) {
            if ($option->getValue() === NULL) {
                return $option;
            }
        }
        return NULL;
    }

    /**
     * Set output DateTime format
     * @param string $format
     */
    public function setDateFormat($format)
    {
        $this->dateFormat = $format;
    }
}