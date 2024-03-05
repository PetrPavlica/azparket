<?php

declare(strict_types=1);

namespace App\Components\UblabooTable\Model;

use Nette\Forms\Container;
use Ublaboo\DataGrid\Filter\IFilterDate;

class FilterDateRange extends \Ublaboo\DataGrid\Filter\FilterDateRange
{

    /**
     * @var string
     */
    protected $template = 'datagrid_filter_daterange.latte';

    /**
     * @var array
     */
    protected $format = ['j. n. Y', 'l'];

    /**
     * @var string
     */
    protected $type = 'date-range';

    /**
     * Adds select box to filter form
     */
    public function addToFormContainer(Container $container): void
    {
        //$container = $container->addContainer($this->key);

        $from = $container->addText($this->key, $this->name);

        $from->setAttribute('data-toggle', 'daterangepicker');

        $this->addAttributes($from);

        if ($this->grid->hasAutoSubmit()) {
            $from->setAttribute('data-autosubmit-change', true);
        }

        $placeholders = $this->getPlaceholders();

        if ($placeholders !== []) {
            $textFrom = reset($placeholders);

            if ($textFrom) {
                $from->setAttribute('placeholder', $textFrom);
            }
        }
    }


    /**
     * Set format for datepicker etc
     */
    public function setFormat(string $phpFormat, string $jsFormat): IFilterDate
    {
        $this->format = [$phpFormat, $jsFormat];

        return $this;
    }


    /**
     * Get php format for datapicker
     */
    public function getPhpFormat(): string
    {
        return $this->format[0];
    }


    /**
     * Get js format for datepicker
     */
    public function getJsFormat(): string
    {
        return $this->format[1];
    }

    /**
     * Get filter condition
     */
    public function getCondition(): array
    {
        $value = $this->getValue();

        list($from, $to) = array_pad(preg_split('/-/', $value), 2, null);

        return [
            $this->column => [
                'from' => $from ? trim($from) : '',
                'to' => $to ? trim($to) : '',
            ],
        ];
    }
}
