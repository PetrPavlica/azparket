<?php

namespace App\Components\UblabooTable\Model;

use App\Model\Database\EntityManager;
use Contributte\Translation\Translator;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\UI;
use App\Model\Database\Utils\AnnotationParser;
use App\Model\ACLMapper;
use Nette\Security\User;
use Nette\SmartObject;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\DataGrid;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class DoctrineGridGenerator
{
    use SmartObject;

    /** @var User */
    private $user;

    /** @var ACLGrid */
    private $grid;

    /** @var array summary collumns for grid */
    private $summary = [];

    /** @var ACLMapper */
    protected $mapper;

    /** @var EntityManager */
    protected $em;

    /** @var string  */
    public string $aliasQB = 'a';

    /** @var QueryBuilder */
    private $datasource;

    /** @var array */
    protected $afterSuccess = [];

    /** @var AnnotationParser */
    private AnnotationParser $parser;

    private Translator $translator;

    private MultiAction $multiAction;

    public function __construct(
        ACLMapper $mapper,
        EntityManager $em,
        AnnotationParser $annotationParser,
        Translator $translator,
        User $user
    ) {
        $this->mapper = $mapper;
        $this->em = $em;
        $this->parser = $annotationParser;
        $this->translator = $translator;
        $this->user = $user;
    }

    /**
     * @param $grid
     * @return ACLGrid
     */
    public function setScope($grid)
    {
        $grid->setColumnsHideable();
        //$grid->addExportCsvFiltered('Csv export (dle filtru)', 'examples.csv');
        $grid->setTranslator($this->translator);
        $grid->setRefreshUrl(false);
        $grid->setStrictSessionFilterValues(false);
        $grid->setAutoSubmit(true);
        return $grid;
    }

    /**
     * @param $entity
     * @param $presenter
     * @param $function
     * @param null $findBy
     * @param null $orderBy
     * @return \App\Components\UblabooTable\Model\ACLGrid
     * @throws \Exception
     */
    public function generateGridByAnnotation($entity, $presenter, $function, $keyGrid = 'default', $findBy = null, $orderBy = null)
    {
        $this->grid = new ACLGrid($this->user, $presenter, $function, $this->mapper);
        $this->grid->setEntity($entity);

        $this->grid = $this->setScope($this->grid);
        $this->datasource = $this->createQueryBuilder($entity, $findBy, $orderBy);

        $this->multiAction = $this->grid->addMultiAction('multiAction', '');

        // prepare key for cash
        $ent = explode("\\", $entity);
        $pres = explode("\\", $presenter);
        $key = end($ent) . '-' . end($pres) . '-grid';

        $this->grid->setEconomicalTemplateGrid();

        $classAnnotations = $this->parser->getClassAnnotationsEntity($entity);
        $this->grid->annotations = $classAnnotations;

        if (isset($classAnnotations['after-success'])) {
            $this->afterSuccess[] = $classAnnotations['after-success'];
        }

        $annotations = $classAnnotations['properties'];

        foreach ($annotations as $name => $annotation) {
            // if property dont have annotation - dont create column
            if (count($annotation['grids']) == 0) {
                continue;
            }

            $defaultAnnotation = $annotation['grids']['default'];
            if (array_key_exists($keyGrid, $annotation['grids'])) {
                $defaultAnnotation = array_merge($defaultAnnotation, $annotation['grids'][$keyGrid]);
            }

            $defaultAnnotation['doctrine'] = $annotation;

            $this->createAnnotationColumn($name, $defaultAnnotation);
        }

        $this->grid->setDataSource($this->datasource);

        if (count($this->summary)) {
            $this->grid->setColumnsSummary($this->summary);
        }

        if ($this->grid->inlineSettings) {
            $this->addBigInlineEdit($this->grid);
        }

        $this->grid->setItemsPerPageList([20, 40, 60, 80, 100, 200], false);
        $this->grid->setDefaultPerPage(40);

        if (isset($annotations['createdAt'])) {
            $this->grid->setDefaultSort(['createdAt' => 'DESC']);
        }

        return $this->grid;
    }

    /**
     * Create column to grid by entity annotations
     * @param string $name of column
     * @param array $annotation
     */
    public function createAnnotationColumn($name, array $columnInfo)
    {
        $column = null;

        if (isset($columnInfo['entity-alias'])) {
            $this->datasource->addSelect($columnInfo['entity-alias'])
                ->leftJoin($this->aliasQB.'.'.$name, $columnInfo['entity-alias']);
        }
        $title = isset($columnInfo[ 'title' ]) ? $columnInfo[ 'title' ] : $name;
        $entityLink = isset($columnInfo[ 'entity-link' ]) ? $columnInfo[ 'entity-link' ] : null;
        if ($entityLink) {
            $entityLink = $name . '.' . $entityLink;
        }
        // create form component by doctrine annotation specification
        switch ($columnInfo[ 'type' ]) {
            case 'number':
            case 'integer':
                $column = $this->grid->addColumnNumber($name, $title, $entityLink);
                break;

            case 'float':
                $column = $this->grid->addColumnNumber($name, $title, $entityLink);
                if ($column) {
                    $column->setFormat(2, ',', ' ');
                }
                break;

            case 'multi-text':
                if (!isset($columnInfo['entity-join-column']) || !isset($columnInfo[ 'entity-link' ])) {
                    throw new \Exception('Error in DoctrineGrid annotation. For multi-text you ned set entity-join-column and entity-link. Name: '.$name);
                }
                $column = $this->grid->addColumnText($name, $title, $entityLink);
                $entLink = $columnInfo[ 'entity-link' ];
                $column->setRenderer(function($item) use ($columnInfo, $entLink, $name) {
                    $arr = [];

                    $col = $columnInfo['entity-join-column'];
                    if ($item->$name) {
                        foreach ($item->$name as $ii) {
                            if (!$ii->$col) {
                                continue;
                            }

                            $arr[] = $ii->$col->$entLink;
                        }
                    }

                    return implode(', ', $arr);
                });
                break;

            case 'text':
            case 'string':
                $column = $this->grid->addColumnText($name, $title, $entityLink);
                break;

            case 'translate-text':
                $column = $this->grid->addColumnTranslateText($name, $title, $entityLink);
                break;

            case 'time':
            case 'date':
            case 'datetime':
                $column = $this->grid->addColumnDateTime($name, $title, $entityLink);
                if ($column && isset($columnInfo['doctrine']['type'])) {
                    switch ($columnInfo['doctrine']['type']) {
                        case 'datetime':
                            $column->setFormat('j. n. Y H:i');
                            break;
                        case 'date':
                            $column->setFormat('j. n. Y');
                            break;
                        case 'time':
                            $column->setFormat('H:i');
                            break;
                    }
                }
                break;

            case 'link':
                if (!isset($columnInfo[ 'link-target' ])) {
                    throw new \Exception('Error in DoctrineGrid annotation. For type Link you need set link-target. Name: ' . $name);
                }
                $params = null;
                if (isset($columnInfo[ 'link-params' ])) {
                    $params = $this->parser->parseArray($columnInfo[ 'link-params' ]);
                }

                $column = $this->grid->addColumnLink($name, $title, $columnInfo[ 'link-target' ], $entityLink, $params);
                if (isset($columnInfo[ 'link-new-tab' ]) && $column) {
                    if ($columnInfo[ 'link-new-tab' ] == 'true') {
                        $column->setOpenInNewTab();
                    }
                }
                break;

            case 'status':
                $column = $this->grid->addColumnStatus($name, $title, $entityLink);
                /* @TODO dodělat tlačítka do tabulky */
                break;

            case 'boolean':
            case 'bool':
                $column = $this->grid->addColumnBoolean($name, $title, $entityLink);
                if ($column) {
                    $column->setCaret(false)
                        ->addOption(true, '')
                        ->setIcon('check')
                        ->setClass('badge badge-success')
                        ->endOption()
                        ->addOption(false, '')
                        ->setIcon('times')
                        ->setClass('badge badge-danger')
                        ->endOption();
                }
                break;

            case 'text-abstract':
                if (isset($columnInfo[ 'abstract' ]) && isset($columnInfo[ 'entity-link' ])) {
                    $entityLink = $columnInfo[ 'abstract' ] . '.' . $columnInfo[ 'entity-link' ];
                } else {
                    throw new \Exception('Error in DoctrineGrid annotation - for text-abstract column you need annotation "GRID abstract" whit name of property for foreign key and annotation "entity-link"! Column: ' . $name);
                }
                $column = $this->grid->addColumnText($name, $title, $entityLink);
                break;
            case 'file':
                $column = $this->grid->addColumnText($name, $title, $entityLink);
                $column->setTemplate(__DIR__ . '/../templates/column_file.latte', ['column' => $name]);
                break;
            case 'image':
                $column = $this->grid->addColumnText($name, $title, $entityLink);
                $column->setTemplate(__DIR__ . '/../templates/column_image.latte', ['column' => $name]);
                break;
            default:
                throw new \Exception('Unknow DoctrineGrid annotation - type of column: ' . $columnInfo[ 'type' ]);
        }
        if ($column) {
            $column->setAlign('left');
        }
        $this->addOtherProperties($column, $columnInfo, $columnInfo[ 'type' ], $name, $entityLink);
        return $column;
    }

    /**
     * Add other properties to column
     * @param Column|null $column column for defination
     * @param array $columnInfo annotation array
     * @param String $type of column
     * @param String $nameColumn
     */
    protected function addOtherProperties(?Column $column, $columnInfo, $type, $nameColumn, $entityLink)
    {
        if (!$column instanceof Column) {
            return;
        }

        foreach ($columnInfo as $name => $value) {
            switch ($name) {
                // type, and title are already prepared = do nothing
                case 'type':
                case 'entity':
                case 'title':
                case 'link-target':
                case 'link-params':
                case 'link-new-tab':
                case 'entity-link':
                case 'entity-alias':
                case 'entity-join-column':
                case 'doctrine':
                    break;

                case 'format-number':
                    if ($type != 'number') {
                        throw new \Exception('Error in DoctrineGrid annotation. Cannot set ' . $name . ' = ' . $value . ' on type: "' . $type);
                    }
                    $column->setFormat(0, ',', $value == '' ? ' ' : $value);
                    break;
                case 'format-float':
                    if ($type != 'float')
                        throw new \Exception('Error in DoctrineGrid annotation. Cannot set ' . $name . ' = ' . $value . ' on type: "' . $type);
                    $column->setFormat($value, ',', '.');
                    break;
                case 'sum':
                    if ($type != 'number') {
                        throw new \Exception('Error in DoctrineGrid annotation. Cannot set ' . $name . ' = ' . $value . ' on type: "' . $type);
                    }
                    if ($value == 'true') {
                        $this->summary[] = $nameColumn;
                    }
                    break;
                case 'format-time':
                    if ($type != 'datetime') {
                        throw new \Exception('Error in DoctrineGrid annotation. Cannot set ' . $name . ' = ' . $value . ' on type: "' . $type);
                    }
                    $column->setFormat($value);
                    break;
                case 'sortable':
                    if ($value == 'true') {
                        if (isset($columnInfo['entity-alias']) && isset($columnInfo['entity-link'])) {
                            $column->setSortable($columnInfo['entity-alias'] . '.' . $columnInfo['entity-link']);
                        } else {
                            $column->setSortable();
                        }
                    }
                    break;
                case 'visible':
                    if ($value == 'false') {
                        $column->setDefaultHide();
                    }
                    break;
                case 'align':
                    $column->setAlign($value);
                    break;
                case 'filter':
                    if (!array_key_exists('type', $value)) {
                        continue 2;
                    }
                    if ($value['type'] == 'range') {
                        $column->setFilterRange();
                    } elseif ($value['type'] == 'date') {
                        $column->setFilterDate();
                    } else if ($value['type'] == 'date-range') {
                        $this->grid->addFilterDateRange($nameColumn, $columnInfo['title'], $nameColumn);
                        //$this->grid->addFilterDateRange($nameColumn, $columnInfo['title'], $entityLink);
                    } else if ($value['type'] == 'single') {
                        $findBy = isset($columnInfo['entity-alias']) && isset($columnInfo['entity-link']) ? $columnInfo['entity-alias'] . '.' . $columnInfo['entity-link'] : $entityLink;
                        $this->grid->addFilterText($nameColumn, $columnInfo['title'], $findBy)
                                ->setSplitWordsSearch(false);
                    } else if ($value['type'] === 'single-entity') {
                            if (!isset($columnInfo['entity'])) {
                                throw new \Exception('Missing DoctrineGrid annotation "entity" - its require for annotation "filter=single-entity"');
                            }
                            if (!isset($columnInfo['entity-alias'])) {
                                throw new \Exception('Missing DoctrineGrid annotation "entity-alias" - its require for annotation "filter=single-entity"');
                            }

                            $this->grid->addFilterText($nameColumn, $columnInfo['title'], $columnInfo['entity-alias'] . '.' . $columnInfo['entity-link'])
                                ->setSplitWordsSearch(false);
                    } else if ($value['type'] === 'select-entity' && array_key_exists('column', $value)) {
                            $orderBy = [];
                            if (array_key_exists('order', $value) && $value['order']) {
                                $orderBy = $value['order'];
                            }
                            if (!class_exists($columnInfo['entity'])) {
                                $columnInfo['entity'] = 'App\\Model\\Database\\Entity\\'.$columnInfo['entity'];
                            }
                            $items = $this->em->getRepository($columnInfo['entity'])->findBy([], $orderBy);
                            $arr = ['' => 'Vše'];
                            foreach ($items as $item) {
                                if (isset($item->isHidden) && $item->isHidden == 1) {
                                    continue;
                                }
                                $idx = $value['column'];
                                $arr[$item->id] = $item->$idx;
                            }
                            $column->setFilterSelect($arr)
                                ->setPrompt('Vše')
                                ->setTranslateOptions();
                                /*->setCondition(function ($qb, $value) use (&$columnInfo, &$nameColumn) {
                                    $alias = $columnInfo['entity-alias'];
                                    $qb->andWhere($alias . '.id = ' . $value);
                                });*/
                    } else if ($value['type'] === 'select' && array_key_exists('values', $value) && is_array($value['values'])) {
                            $column->setFilterSelect($value['values'])
                                ->setPrompt('Vše')
                                ->setTranslateOptions();
                    } else if ($value['type'] === 'multiselect-entity' && array_key_exists('column', $value)) {
                        $orderBy = [];
                        if (array_key_exists('order', $value) && $value['order']) {
                            $orderBy = $value['order'];
                        }
                        if (!class_exists($columnInfo['entity'])) {
                            $columnInfo['entity'] = 'App\\Model\\Database\\Entity\\'.$columnInfo['entity'];
                        }
                        $items = $this->em->getRepository($columnInfo['entity'])->findBy([], $orderBy);
                        $arr = [];
                        foreach ($items as $item) {
                            if (isset($item->isHidden) && $item->isHidden == 1) {
                                continue;
                            }
                            $idx = $value['column'];
                            $arr[$item->id] = $item->$idx;
                        }
                            $column->setFilterMultiSelect($arr)
                                ->setAttribute('data-live-search', 'true')
                                ->setTranslateOptions()
                                ->setCondition(function ($qb, $value) use (&$columnInfo, &$nameColumn) {
                                    $alias = $columnInfo['entity-alias'];
                                    $value = array_filter((array)$value, function($val) {
                                        return $val !== '';
                                    });
                                    $qb->andWhere($alias . '.id IN ('.implode(',', $value).')');
                                });
                    } else if ($value['type'] === 'multicolumnname-multiselect-entity' && array_key_exists('column', $value)) {
                        $orderBy = [];
                        if (array_key_exists('order', $value) && $value['order']) {
                            $orderBy = $value['order'];
                        }
                        if (!class_exists($columnInfo['entity'])) {
                            $columnInfo['entity'] = 'App\\Model\\Database\\Entity\\'.$columnInfo['entity'];
                        }
                        $items = $this->em->getRepository($columnInfo['entity'])->findBy([], $orderBy);
                        $arr = [];
                        foreach ($items as $item) {
                            if (isset($item->isHidden) && $item->isHidden == 1) {
                                continue;
                            }
                            $idx = $value['column'];
                            $properties = AnnotationParser::getPropertiesOfClass(new $columnInfo['entity']);
                            foreach ($properties as $prop) {
                                if (strpos($idx, "$" . $prop . "$") !== false) {
                                    /*if (!isset($item->$prop)) {
                                        throw new \Exception('Error in doctrine annotation - GRID entity=' . $columnInfo['entity'] . ' - error in unknow entity property: ' . $prop);
                                        return;
                                    }
                                    if (is_array($item->$prop)) {
                                        throw new \Exception('Error in doctrine annotation - GRID entity=' . $columnInfo['entity'] . ' - entity property: ' . $prop . ' is foreign key - you cannot use foreign key to this annotation');
                                        return;
                                    }*/
                                    $idx = str_replace("$" . $prop . "$", $item->$prop, $idx);
                                }
                            }
                            $arr[$item->id] = $idx;
                        }
                        $column->setFilterMultiSelect($arr)
                            ->setAttribute('data-live-search', 'true')
                            ->setTranslateOptions()
                            ->setCondition(function ($qb, $value) use (&$columnInfo, &$nameColumn) {
                                $alias = $columnInfo['entity-alias'];
                                $value = array_filter((array)$value, function($val) {
                                    return $val !== '';
                                });
                                $qb->andWhere($alias . '.id IN ('.implode(',', $value).')');
                            });
                    } else {
                        throw new \Exception('Unknow DoctrineGrid annotation for filter: ' . $name . ' = ' . $value);
                    }

                    break;
                case 'abstract': //for abstract grid value - use foreign key other property
                    break;
                case 'value-mask':
                    $mask = $this->parser->parseArray(substr($value, strpos($value, '#[') + 1));
                    $entity = false;
                    if (isset($columnInfo[ 'entity' ])) {
                        $entity = true;
                    }
                    if ($column) {
                        $column->setRenderer(function($item) use (&$mask, &$entity, &$nameColumn) {
                            $item2 = $item;
                            if ($entity) {
                                $item2 = $item->$nameColumn;
                            }
                            if ($item2 == NULL)
                                return '';

                            $value = $mask[ 0 ];
                            $properties = AnnotationParser::getPropertiesOfClass($item2);
                            foreach ($properties as $prop) {
                                if (strpos($value, $prop)) {
                                    /*if (!isset($item2->$prop)) {
                                        throw new \Exception('Error in doctrine annotation - GRID value-mask=' . $value . ' - error in unknow entity property: ' . $prop);
                                    }
                                    if (is_array($item2->$prop)) {
                                        throw new \Exception('Error in doctrine annotation - GRID value-mask=' . $value . ' - entity property: ' . $prop . ' is foreign key - you cannot use foreign key to this annotation');
                                    }*/
                                    $value = str_replace("$" . $prop . "$", $item2->$prop, $value);
                                }
                            }
                            return $value;
                        });
                    }
                    break;
                case 'inline-type':
                    $this->grid->inlineSettings[ $nameColumn ] = $value;
                    break;
                case strpos($name, 'inline-data'):
                    if (!isset($columnInfo[ 'inline-type' ])) {
                        throw new \Exception('Missing DoctrineGrid annotation "inline-type" - its require for annotation "inline-data-<type>"');
                    }
                    $n = str_replace("inline-data-", "", $name);
                    if ($n == 'entity') {
                        $this->grid->arrayForeignEntity[ $nameColumn ] = $value;
                    } elseif ($n == 'own') {
                        $this->grid->arrayCustomEntity[ $nameColumn ] = $value;
                    }
                    break;
                case 'inline-entity-values':
                    if (!isset($columnInfo[ 'inline-type' ])) {
                        throw new \Exception('Missing DoctrineGrid annotation "inline-type" - its require for annotation "inline-data-<type>"');
                    }
                    $this->grid->arrayForeignEntity[ 'entity-values' ][ $nameColumn ] = $value;
                    break;
                case 'inline-prompt':
                    $this->grid->arrayForeignEntity[ 'prompt-default' ][ $nameColumn ] = $value;
                    break;
                case 'inline-multiselect-entity':
                    $n = explode('[', $value);
                    $n[ 1 ] = str_replace("]", "", $n[ 1 ]);
                    $n[ 2 ] = str_replace("]", "", $n[ 2 ]);
                    $this->grid->arrayNNForeignEntity[ $nameColumn ] = ['entity' => $n[ 0 ], 'this' => $n[ 1 ], 'foreign' => $n[ 2 ]];
                    break;
                case 'replacement':
                    $column->setReplacement($value);
                    break;
                default:
                    throw new \Exception('Unknow DoctrineGrid annotation: ' . $name . ' = ' . $value);
            }
        }
    }

    /**
     * Appent button delete to grid
     * @param string $key
     * @param string $name
     * @param string $href
     * @param array $params
     */
    public function addButtonDelete($key = 'delete', $name = '', $href = 'deleteDatagrid!', $params = null)
    {
        $delete = $this->grid->addAction($key, $name, $href, $params);
        if ($delete) {
            $delete->setIcon('times')
                ->setTitle('Smazat')
                ->setClass('red-text confirmLink');
        }
        return $delete;
    }

    public function addButtonDeleteCallback($key = 'delete', $name = 'Smazat')
    {
        $action = $this->multiAction->addActionCallback($key, $name, function($itemId) {
            $this->deleteRow($itemId);
        });
        $action->setIcon('times')
            ->setTitle('Smazat')
            ->setConfirmation(new StringConfirmation('Opravdu chcete tento záznam smazat?'))
            ->setClass('text-danger dropdown-item');
    }

    /**
     * Appent button edit to grid
     * @param string $key
     * @param string $name
     * @param string $href
     * @param array $params
     */
    public function addEditAction($key = 'edit', $name = 'Upravit', $href = '', $params = null)
    {
        $multiAction = $this->multiAction->addAction($key, $name, $href, $params);
        $action = $multiAction->getAction('edit');
        $action->setIcon('pencil-alt')
            ->setTitle('Úprava')
            ->setClass('text-link dropdown-item');
    }

    /**
     * Add inline edit to grid
     */
    public function addBigInlineEdit($grid)
    {
        $t = $this;
        $item = $grid->addInlineEdit()
            ->onControlAdd[] = function ($container) use ($t) {

            if(is_array($t->grid->inlineSettings)) {
                foreach ($t->grid->inlineSettings as $name => $type) {
                    switch ($type) {
                        case 'id':
                            break;
                        case 'text':
                            //$container->addText($name, '');
                            $container->addTextArea($name, '')
                                ->setAttribute('rows', 2);
                            break;
                        case 'date':
                            $container->addText($name, '')
                                ->setAttribute('data-provide', 'datepicker')
                                ->setAttribute('data-date-orientation', 'bottom')
                                ->setAttribute('data-date-format', 'd. m. yyyy')
                                ->setAttribute('data-date-today-highlight', 'true')
                                ->setAttribute('data-date-autoclose', 'true')
                                ->setAttribute('autocomplete', 'off')
                                ->setRequired(false)
                                ->addRule(UI\Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})');
                            // :o:  DatePicker
                            break;
                        case 'email':
                            $container->addEmail($name, '');
                            break;
                        case 'integer':
                            $container->addText($name, '')
                                ->setRequired(false)
                                ->addRule(UI\Form::INTEGER);
                            break;
                        case 'number':
                            $container->addText($name, '')
                                ->setRequired(false)
                                ->addRule(UI\Form::NUMERIC);
                            break;
                        case 'float':
                            $container->addText($name, '')
                                ->setRequired(false)
                                ->addRule(UI\Form::FLOAT);
                            break;
                        case 'checkbox':
                            $container->addCheckbox($name, '')
                                ->setAttribute('style', 'width: 20px;margin-top: 8px;margin-bottom: -8px');
                            break;
                        case 'select':
                            $for = $t->grid->arrayForeignEntity;
                            $cus = $t->grid->arrayCustomEntity;
                            if (!count($for) || !isset($for[ $name ])) {
                                if(!count($cus) || !isset($cus[ $name ])) {
                                    throw new \Exception('Missing anotation inline-data-<type> on type: ' . $type . ' and name: ' . $name);
                                } else {
                                    $arr = $this->parser->parseArray($cus[ $name ]);
                                }
                            } else {
                                if (isset($for[ 'entity-values' ][ $name ])) {
                                    $n = str_replace("]", "", $for[ 'entity-values' ][ $name ]);
                                    $n = explode('[', $n);
                                    $findBy = [];
                                    if (isset($n[ 2 ]) && $n[ 2 ] && $n[ 2 ] != "") {
                                        $findBy = $this->parser->parseArray($n[ 2 ]);
                                    }
                                    $orderBy = [];
                                    if (isset($n[ 3 ]) && $n[ 3 ] && $n[ 3 ] != "") {
                                        $orderBy = $this->parser->parseArray($n[ 3 ]);
                                    }
                                    foreach ($findBy as $colKey => $cond) {
                                        if(strpos($cond, 'insert-date-') !== false) {
                                            /*$tm = str_replace('insert-date-', '', $cond);
                                            $findBy[$colKey . ' >='] = new \DateTime($tm);*/
                                            unset($findBy[$colKey]);
                                        }
                                    }
                                    $items = $this->em->getRepository($n[ 0 ])->findBy($findBy, $orderBy);
                                    $properties = AnnotationParser::getPropertiesOfClass(new $n[ 0 ]);
                                    $arr = [];
                                    $idVal = 'id';
                                    foreach ($items as $item) {
                                        if (isset($item->isHidden) && $item->isHidden == 1)
                                            continue;
                                        $resVal = $n[ 1 ];
                                        foreach ($properties as $prop) {
                                            if($prop == 'parent') {
                                                // nothing
                                            } elseif (strpos($resVal, $prop)) {

                                                if($item->$prop instanceof \DateTime) {
                                                    $resVal = str_replace("$" . $prop . "$", $item->$prop->format('j.n.Y'), $resVal);
                                                } else {
                                                    $resVal = str_replace("$" . $prop . "$", $item->$prop, $resVal);
                                                }
                                            }
                                        }
                                        $arr[ $item->$idVal ] = $resVal;
                                    }
                                } else {
                                    $n = explode('[', $for[ $name ]);
                                    $n[ 1 ] = str_replace("]", "", $n[ 1 ]);
                                    $items = $t->em->getRepository($n[ 0 ])->findAll();
                                    $arr = [];

                                    foreach ($items as $item) {
                                        if (isset($item->isHidden) && $item->isHidden == 1) {
                                            continue;
                                        }
                                        $idx = $n[ 1 ];
                                        $arr[ $item->id ] = $item->$idx;
                                    }
                                }
                            }
                            $s = $container->addSelect($name, '', $arr)
                                ->setAttribute('style', 'height: 25px;')
                                ->setAttribute('class', 'normal-font-size');
                            if (isset($for[ 'prompt-default' ][ $name ])) {
                                $s->setPrompt($for[ 'prompt-default' ][ $name ]);
                            }
                            break;
                        case 'multiselect':
                            $for = $t->grid->arrayForeignEntity;
                            $cus = $t->grid->arrayCustomEntity;
                            if (!count($for) || !isset($for[ $name ])) {
                                if(!count($cus) || !isset($cus[ $name ])) {
                                    throw new \Exception('Missing anotation inline-data-<type> on type: ' . $type . ' and name: ' . $name);
                                } else {
                                    $arr = $this->parser->parseArray($cus[ $name ]);
                                }
                            } else {
                                if (isset($for[ 'entity-values' ][ $name ])) {
                                    $n = str_replace("]", "", $for[ 'entity-values' ][ $name ]);
                                    $n = explode('[', $n);
                                    $findBy = [];
                                    if (isset($n[ 2 ]) && $n[ 2 ] && $n[ 2 ] != "") {
                                        $findBy = $this->parser->parseArray($n[ 2 ]);
                                    }
                                    $orderBy = [];
                                    if (isset($n[ 3 ]) && $n[ 3 ] && $n[ 3 ] != "") {
                                        $orderBy = $this->parser->parseArray($n[ 3 ]);
                                    }
                                    foreach ($findBy as $colKey => $cond) {
                                        if(strpos($cond, 'insert-date-') !== false) {
                                            $tm = str_replace('insert-date-', '', $cond);
                                            $findBy[$colKey . ' >='] = new \DateTime($tm);
                                            unset($findBy[$colKey]);
                                        }
                                    }
                                    $items = $this->em->getRepository($n[ 0 ])->findBy($findBy, $orderBy);
                                    $properties = AnnotationParser::getPropertiesOfClass(new $n[ 0 ]);
                                    $arr = [];
                                    $idVal = 'id';
                                    foreach ($items as $item) {
                                        if (isset($item->isHidden) && $item->isHidden == 1)
                                            continue;
                                        $resVal = $n[ 1 ];
                                        foreach ($properties as $prop) {
                                            if($prop == 'parent') {
                                                // nothing
                                            } elseif (strpos($resVal, $prop)) {

                                                if($item->$prop instanceof \DateTime) {
                                                    $resVal = str_replace("$" . $prop . "$", $item->$prop->format('j.n.Y'), $resVal);
                                                } else {
                                                    $resVal = str_replace("$" . $prop . "$", $item->$prop, $resVal);
                                                }
                                            }
                                        }
                                        $arr[ $item->$idVal ] = $resVal;
                                    }
                                } else {
                                    $n = explode('[', $for[ $name ]);
                                    $n[ 1 ] = str_replace("]", "", $n[ 1 ]);
                                    $items = $t->em->getRepository($n[ 0 ])->findAll();
                                    $arr = [];

                                    foreach ($items as $item) {
                                        if (isset($item->isHidden) && $item->isHidden == 1) {
                                            continue;
                                        }
                                        $idx = $n[ 1 ];
                                        $arr[ $item->id ] = $item->$idx;
                                    }
                                }
                            }

                            $s = $container->addMultiSelect('masters', '', $arr)
                                ->setAttribute('style', 'height: 25px;')
                                ->setAttribute('class', 'normal-font-size selectpicker input-sm')
                                ->setAttribute('data-selected-text-format', 'count')
                                ->setAttribute('data-selected-icon-check', 'fa fa-check')
                                ->setAttribute('data-i18n-selected', '{0} vybráno')
                                ->setAttribute('title', 'Vyberte');
                            break;
                        default:
                            throw new \Exception('Unknow inline type: ' . $type . ' on name: ' . $name);
                    }
                }
            }
        };

        $grid->getInlineEdit()->onSetDefaults[] = function ($container, $item) use ($t, $grid) {
            $for = $t->grid->arrayForeignEntity;
            $forNN = $t->grid->arrayNNForeignEntity;
            $arr = [];
            if (is_array($t->grid->inlineSettings)) {
                foreach ($t->grid->inlineSettings as $name => $type) {
                    if ($grid->entity == 'Intra\Model\Database\Entity\Worker' && $name == 'madamWrite') {
                        if (isset($item->$name) && $item->$name) {
                            $comp = $container->getComponent($name);
                            if ($comp) {
                                $comp->setDisabled(true);
                            }
                        }
                    }
                    if (isset($for[$name]) && isset($item->$name->id)) {
                        $arr[$name] = $item->$name->id;

                        // Remove values to selection
                        $items = $container->getComponent($name)->items;
                        if (isset($for[ 'entity-values' ][ $name ])) {
                            $n = str_replace("]", "", $for['entity-values'][$name]);
                            $n = explode('[', $n);
                            $findBy = [];
                            if (isset($n[2]) && $n[2] && $n[2] != "") {
                                $findBy = $this->parser->parseArray($n[2]);
                            }
                            $orderBy = [];
                            if (isset($n[3]) && $n[3] && $n[3] != "") {
                                $orderBy = $this->parser->parseArray($n[3]);
                            }
                            foreach ($findBy as $colKey => $cond) {
                                if (strpos($cond, 'insert-date-') !== false) {
                                    $tm = str_replace('insert-date-', '', $cond);
                                    $findBy[$colKey . ' >='] = new \DateTime($tm);
                                    unset($findBy[$colKey]);
                                }
                            }
                            $itemsS = $this->em->getRepository($n[0])->findBy($findBy, $orderBy);
                            $itemIds = [];
                            if ($itemsS) {
                                foreach ($itemsS as $its) {
                                    $itemIds[] = $its->id;
                                }
                            }
                            foreach ($items as $ki => $kv) {
                                if ($item->$name->id != $ki && !in_array($ki, $itemIds)) {
                                    unset($items[$ki]);
                                }
                            }
                            $container->getComponent($name)->items = $items;
                        }

                        continue;
                    }
                    if (isset($forNN[$name])) {
                        if (!isset($arr[$name])) {
                            $arr[$name] = [];
                        }
                        if ($item->$name) {
                            foreach ($item->$name as $in) {
                                if (!$in->{$forNN[$name]['foreign']}) {
                                    continue;
                                }
                                $arr[$name][] = $in->{$forNN[$name]['foreign']}->id;
                            }
                        }
                        continue;
                    }
                    if ($type == 'date') {
                        if ($item->$name) {
                            $arr[$name] = $item->$name->format('j. n. Y');
                        }
                        continue;
                    }
                    $arr[$name] = $item->$name;
                }
            }
            $container->setDefaults($arr);
        };

        $generator = $this;
        $grid->getInlineEdit()->onSubmit[] = function ($id, $values) use ($grid, $generator) {
            if ($grid->entity == 'Intra\Model\Database\Entity\Worker') {
                $oldEntity = $generator->em->getRepository($grid->entity)->find($id);
                if($oldEntity) {
                    if ($oldEntity->workerStep) {
                        $oldStepId = $oldEntity->workerStep->id;
                    }
                    if ($oldEntity->madamWrite) {
                        unset($values['madamWrite']);
                    }
                }
            }

            $entity = $generator->saveEntity($grid, $values, $id);

            if(isset($oldStepId) && $grid->getNamePresenter() == 'App\Presenters\WorkerPresenter' && $entity->workerStep && $entity->workerStep->id != $oldStepId) {
                $grid->getParent()->getParent()->redirect('this');
            }
        };

        $grid->getInlineEdit()->setShowNonEditingColumns();
        $grid->getInlineEdit()->setTitle('Upravit zde');
        $grid->getInlineEdit()->setClass('indigo-text ajax');
        $grid->getInlineEdit()->setIcon('cog');

        /*$grid->addInlineAdd()
        ->setPositionTop()
        ->onControlAdd[] = function ($container) use ($t) {
        foreach ($t->grid->inlineSettings as $name => $type) {
            switch ($type) {
                case 'id':
                    break;
                case 'text':
                    $container->addText($name, '');
                    break;
                case 'integer':
                    $container->addText($name, '')
                        ->setRequired(false)
                        ->addRule(UI\Form::INTEGER);
                    break;
                case 'number':
                    $container->addText($name, '')
                        ->setRequired(false)
                        ->addRule(UI\Form::NUMERIC);
                    break;
                case 'float':
                    $container->addText($name, '')
                        ->setRequired(false)
                        ->addRule(UI\Form::FLOAT);
                    break;
                case 'checkbox':
                    $container->addCheckbox($name, '');
                    break;
                case 'select':
                    $for = $t->grid->arrayForeignEntity;
                    if (!count($for) && !isset($for[ $name ])) {
                        throw new \Exception('Missing anotation inline-data-<type> on type: ' . $type . ' and name: ' . $name);
                    }
                    $n = explode('[', $for[ $name ]);
                    $n[ 1 ] = str_replace("]", "", $n[ 1 ]);
                    $items = $t->em->getRepository($n[ 0 ])->findAll();
                    $arr = [];
                    foreach ($items as $item) {
                        if (isset($item->isHidden) && $item->isHidden == 1) {
                            continue;
                        }
                        $item = $item->toArray();
                        $arr[ $item[ 'id' ] ] = $item[ $n[ 1 ] ];
                    }
                    $s = $container->addSelect($name, '', $arr);
                    if (isset($for[ 'prompt-default' ][ $name ])) {
                        $s->setPrompt($for[ 'prompt-default' ][ $name ]);
                    }
                    break;
                default:
                    throw new \Exception('Unknow inline type: ' . $type . ' on name: ' . $name);
            }
        }
    };

    $grid->getInlineAdd()->onSubmit[] = function ($values) use ($grid, $generator) {
        $generator->saveEntity($grid, $values);
        if ($grid->presenter) {
            $grid->presenter->redirect('this');
        }
    };*/

        return $grid;
    }

    /**
     * Add inline edit to grid for agency
     */
    public function addBigInlineEditAgency($grid, $sourcesArr)
    {
        $t = $this;
        $item = $grid->addInlineEdit()
            ->onControlAdd[] = function ($container) use ($t, $sourcesArr) {
            if(is_array($t->grid->inlineSettings)) {
                foreach ($t->grid->inlineSettings as $name => $type) {
                    switch ($type) {
                        case 'id':
                            break;
                        case 'text':
                            $container->addText($name, '');
                            break;
                        case 'date':
                            $container->addText($name, '')
                                ->setAttribute('data-provide', 'datepicker')
                                ->setAttribute('data-date-orientation', 'bottom')
                                ->setAttribute('data-date-format', 'd. m. yyyy')
                                ->setAttribute('data-date-today-highlight', 'true')
                                ->setAttribute('data-date-autoclose', 'true')
                                ->setAttribute('autocomplete', 'off')
                                ->setRequired(false)
                                ->addRule(UI\Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})');
                            // :o:  DatePicker
                            break;
                        case 'email':
                            $container->addEmail($name, '');
                            break;
                        case 'integer':
                            $container->addText($name, '')
                                ->setRequired(false)
                                ->addRule(UI\Form::INTEGER);
                            break;
                        case 'number':
                            $container->addText($name, '')
                                ->setRequired(false)
                                ->addRule(UI\Form::NUMERIC);
                            break;
                        case 'float':
                            $container->addText($name, '')
                                ->setRequired(false)
                                ->addRule(UI\Form::FLOAT);
                            break;
                        case 'checkbox':
                            $container->addCheckbox($name, '')
                                ->setAttribute('style', 'width: 20px;margin-top: 8px;margin-bottom: -8px');
                            break;
                        case 'select':
                            $for = $t->grid->arrayForeignEntity;
                            $cus = $t->grid->arrayCustomEntity;
                            if (!count($for) || !isset($for[ $name ])) {
                                if(!count($cus) || !isset($cus[ $name ])) {
                                    throw new \Exception('Missing anotation inline-data-<type> on type: ' . $type . ' and name: ' . $name);
                                } else {
                                    $arr = $this->parser->parseArray($cus[ $name ]);
                                }
                            } else {
                                if (isset($for[ 'entity-values' ][ $name ])) {
                                    $n = str_replace("]", "", $for[ 'entity-values' ][ $name ]);
                                    $n = explode('[', $n);
                                    $findBy = [];
                                    if (isset($n[ 2 ]) && $n[ 2 ] && $n[ 2 ] != "") {
                                        $findBy = $this->parser->parseArray($n[ 2 ]);
                                    }
                                    $orderBy = [];
                                    if (isset($n[ 3 ]) && $n[ 3 ] && $n[ 3 ] != "") {
                                        $orderBy = $this->parser->parseArray($n[ 3 ]);
                                    }
                                    foreach ($findBy as $colKey => $cond) {
                                        if(strpos($cond, 'insert-date-') !== false) {
                                            $tm = str_replace('insert-date-', '', $cond);
                                            $findBy[$colKey . ' >='] = new \DateTime($tm);
                                            unset($findBy[$colKey]);
                                        }
                                    }
                                    if($name == 'workerStep') {
                                        $findBy['forAgency'] = '1';
                                    }

                                    $items = $this->em->getRepository($n[ 0 ])->findBy($findBy, $orderBy);
                                    $properties = AnnotationParser::getPropertiesOfClass(new $n[ 0 ]);
                                    $arr = [];
                                    $idVal = 'id';
                                    foreach ($items as $item) {
                                        if (isset($item->isHidden) && $item->isHidden == 1)
                                            continue;
                                        $resVal = $n[ 1 ];

                                        if($name == 'workerTender' || $name == 'workerTraining') {
                                            $allowed = 0;
                                            foreach ($item->sources as $conn) {
                                                if(in_array($conn->source->id, $sourcesArr)) {
                                                    $allowed = 1;
                                                    break;
                                                }
                                            }
                                            if(!$allowed) {
                                                continue;
                                            }
                                        } elseif($name == 'workerSource') {
                                            if(!in_array($item->id, $sourcesArr)) {
                                                continue;
                                            }
                                        }

                                        foreach ($properties as $prop) {
                                            if($prop == 'parent') {
                                                // nothing
                                            } elseif (strpos($resVal, $prop)) {

                                                if($item->$prop instanceof \DateTime) {
                                                    $resVal = str_replace("$" . $prop . "$", $item->$prop->format('j.n.Y'), $resVal);
                                                } else {
                                                    if($name == 'workerStep' && $prop == 'name') {
                                                        $resVal = str_replace("$" . 'name' . "$", $item->nameAg, $resVal);
                                                    } else {
                                                        $resVal = str_replace("$" . $prop . "$", $item->$prop, $resVal);
                                                    }
                                                }
                                            }
                                        }
                                        $arr[ $item->$idVal ] = $resVal;
                                    }
                                } else {
                                    $n = explode('[', $for[ $name ]);
                                    $n[ 1 ] = str_replace("]", "", $n[ 1 ]);
                                    $items = $t->em->getRepository($n[ 0 ])->findAll();
                                    $arr = [];

                                    foreach ($items as $item) {
                                        if (isset($item->isHidden) && $item->isHidden == 1) {
                                            continue;
                                        }

                                        if($name == 'workerTender' || $name == 'workerTraining') {
                                            $allowed = 0;
                                            foreach ($item->sources as $conn) {
                                                if(in_array($conn->source->id, $sourcesArr)) {
                                                    $allowed = 1;
                                                    break;
                                                }
                                            }
                                            if(!$allowed) {
                                                continue;
                                            }
                                        } elseif($name == 'workerSource') {
                                            if(!in_array($item->id, $sourcesArr)) {
                                                continue;
                                            }
                                        }

                                        $idx = $n[ 1 ];
                                        if($name == 'workerStep') {
                                            $idx = 'nameAg';
                                        }
                                        $arr[ $item->id ] = $item->$idx;
                                    }
                                }
                            }
                            $s = $container->addSelect($name, '', $arr)
                                ->setAttribute('style', 'height: 25px;');
                            if (isset($for[ 'prompt-default' ][ $name ])) {
                                $s->setPrompt($for[ 'prompt-default' ][ $name ]);
                            }
                            break;
                        default:
                            throw new \Exception('Unknow inline type: ' . $type . ' on name: ' . $name);
                    }
                }
            }
        };

        $generator = $this;
        $grid->getInlineEdit()->onSetDefaults[] = function ($container, $item) use ($t, $generator) {
            $for = $t->grid->arrayForeignEntity;
            $arr = [];
            if (is_array($t->grid->inlineSettings)) {
                foreach ($t->grid->inlineSettings as $name => $type) {
                    if (isset($for[$name]) && isset($item->$name->id)) {
                        $arr[$name] = $item->$name->id;

                        // Add set value to selection
                        $items = $container->getComponent($name)->items;
                        if(!isset($items[$item->$name->id])) {
                            if (isset($for[ 'entity-values' ][ $name ])) {
                                $n = str_replace("]", "", $for[ 'entity-values' ][ $name ]);
                                $n = explode('[', $n);
                                $properties = AnnotationParser::getPropertiesOfClass(new $n[ 0 ]);
                                $idVal = 'id';
                                $resVal = $n[ 1 ];
                                foreach ($properties as $prop) {
                                    if($prop == 'parent') {
                                        // nothing
                                    } elseif (strpos($resVal, $prop)) {
                                        if($item->$name->$prop instanceof \DateTime) {
                                            $resVal = str_replace("$" . $prop . "$", $item->$name->$prop->format('j.n.Y'), $resVal);
                                        } else {
                                            if($name == 'workerStep' && $prop == 'name') {
                                                $resVal = str_replace("$" . 'name' . "$", $item->$name->nameAg, $resVal);
                                            } else {
                                                $resVal = str_replace("$" . $prop . "$", $item->$name->$prop, $resVal);
                                            }
                                        }
                                    }
                                }
                                $items[ $item->$name->$idVal ] = $resVal;
                            } else {
                                $n = explode('[', $for[ $name ]);
                                $n[ 1 ] = str_replace("]", "", $n[ 1 ]);
                                $idx = $n[ 1 ];
                                if($name == 'workerStep') {
                                    $idx = 'nameAg';
                                }
                                $items[ $item->$name->id ] = $item->$name->$idx;
                            }
                            $container->getComponent($name)->items = $items;
                        }

                        continue;
                    }
                    if ($type == 'date') {
                        if ($item->$name) {
                            $arr[$name] = $item->$name->format('j. n. Y');
                        }
                        continue;
                    }
                    $arr[$name] = $item->$name;
                }
            }
            $container->setDefaults($arr);
        };

        $generator = $this;
        $grid->getInlineEdit()->onSubmit[] = function ($id, $values) use ($grid, $generator) {
            $entity = $generator->saveEntity($grid, $values, $id, true);
        };

        $grid->getInlineEdit()->setShowNonEditingColumns();
        $grid->getInlineEdit()->setTitle('Upravit zde');
        $grid->getInlineEdit()->setClass('indigo-text ajax');
        $grid->getInlineEdit()->setIcon('cog');

        $grid->allowRowsInlineEdit(function($item) {
            return !(!$item->onlyAgency || $item->workerTender || $item->workerTraining || ($item->workerStep && ($item->workerStep->id == 3 || $item->workerStep->id == 4)));
        });

        return $grid;
    }

    /**
     * Create specific queryBuilder by condition findBy
     * @param string $entity
     * @param array $findBy
     * @param array $orderBy
     * @return QueryBuilder
     */
    public function createQueryBuilder($entity, $findBy = null, array $orderBy = null)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select($this->aliasQB)
            ->from($entity, $this->aliasQB);
        if ($findBy) {
            if (is_array($findBy)) {
                foreach ($findBy as $key => $val) {
                    if (is_array($val)) {
                        if (stripos($key, 'NOT') !== false) {
                            $qb->andWhere($this->aliasQB . '.' . $key . " IN ('" . implode("','", $val) . "') or ".$this->aliasQB . '.' . trim(str_ireplace('NOT', '', $key))." is null");
                        } else {
                            $qb->andWhere($this->aliasQB . '.' . $key . " IN ('" . implode("','", $val) . "')");
                        }
                    } elseif ($val === null) {
                        if (stripos($key, 'NOT') !== false) {
                            $qb->andWhere($this->aliasQB . '.' . trim(str_ireplace('NOT', '', $key)) . " is not null");
                        } else {
                            $qb->andWhere($this->aliasQB . '.' . $key . " is null");
                        }
                    } else {
                        $qb->andWhere($this->aliasQB . '.' . $key . "='$val'");
                    }
                }
            } else {
                $qb->andWhere($findBy);
            }
        }
        if ($orderBy) {
            $qb->orderBy($this->aliasQB . '.' . $orderBy[ 0 ], $orderBy[ 1 ]);
        }

        return $qb;
    }

    public function findBy($qb, $findBy)
    {
        $i = 1;
        $arr = [];
        $where = '';
        if (is_array($findBy)) {
            foreach ($findBy as $name => $value) {
                $where .= $this->aliasQB . '.' . $name . ' = ?' . $i . ' AND ';
                $arr[ $i ] = $value;
                $i++;
            }
            $where = substr($where, 0, -4);
        } else {
            $where = $findBy;
        }
        $qb->where($where);
        $qb->setParameters($arr);
        return $qb;
    }

    /**
     * Function for handler onSuccess grid in inline save mode
     * @param ACLGrid $grid
     * @param array $values
     */
    public function saveEntity($grid, $values, $id = "", $onlyAgency = false)
    {
        try {
            //if $id=="" - new item, else update existing
            $entity = null;
            if ($id == "") {
                $entity = new $grid->entity();
            } else {
                $entity = $this->em->getRepository($grid->entity)->find($id);
            }

            //Save foreign entity - need find it
            if (isset($grid->arrayForeignEntity)) {
                foreach ($grid->arrayForeignEntity as $name => $value) {
                    if ($name === 'prompt-default' || $name === 'entity-values') {
                        continue;
                    }
                    if (isset($grid->arrayNNForeignEntity[ $name ])) {
                        $grid->arrayNNForeignEntity[ $name ][ 'value' ] = $values[ $name ];
                        $grid->arrayNNForeignEntity[ $name ][ 'foreign-entity' ] = $value;
                        unset($values[ $name ]);
                        continue;
                    }
                    if (isset($values[ $name ])) {
                        if($values[ $name ] == -1) {
                            $values[ $name ] = null;
                        } else {
                            $n = explode('[', $value);
                            $a = $this->em->getRepository($n[ 0 ])->find($values[ $name ]);
                            // Check if exist foreign entity - if not, dont save.
                            if ($a) {
                                $values[ $name ] = $a;
                            } else {
                                unset($values[ $name ]);
                            }
                        }
                    } else { //If value not set, set entity cell to NULL
                        $values[ $name ] = null;
                    }
                }
            }

            if (isset($grid->arrayNNForeignEntity)) {
                foreach ($grid->arrayNNForeignEntity as $name => $value) {
                    // Delete old values
                    $query = $this->em->createQuery("DELETE " . $value[ 'entity' ] . " c WHERE c." . $value[ 'this' ] . " = " . $entity->id);
                    $query->execute();

                    if ($value[ 'value' ]) {
                        // Save new
                        foreach ($value[ 'value' ] as $item) {

                            $entityForeign = new $value[ 'entity' ];
                            $foreignEntity = $this->em->getRepository($value[ 'foreign-entity' ])->find($item);
                            $data = [
                                $value[ 'this' ] => $entity,
                                $value[ 'foreign' ] => $foreignEntity
                            ];
                            $entityForeign->data($data);
                            $this->em->persist($entityForeign);
                        }
                        $this->em->flush();
                    }
                }
            }

            foreach ($grid->getSessionData('_grid_hidden_columns') as $hidden) {
                unset($values[$hidden]);
            }

            $entity->data($values);
            if ($id == "") {
                $this->em->persist($entity);
            }

            if(get_class($entity) == 'Intra\Model\Database\Entity\Worker' && isset($values['pin']) && !$entity->birthDate && strlen($values['pin']) >= 6) {
                $year = substr($values['pin'], 0, 2);
                $century = '19';
                if(intval($year) < 30) {
                    $century = '20';
                }
                $month = substr($values['pin'], 2, 2);
                if(intval($month) > 12) {
                    if(intval($month) < 33) {
                        $month = intval($month) - 20;
                    } else if(intval($month) < 63) {
                        $month = intval($month) - 50;
                    } else if(intval($month) < 83) {
                        $month = intval($month) - 70;
                    }
                }
                $day = substr($values['pin'], 4, 2);
                if(intval($day) >= 1 && intval($day) <= 31 && intval($month) >= 1 && intval($month) <= 12) {
                    $date = new \DateTime($century . $year . '-' . $month . '-' . $day);
                    $entity->setBirthDate($date);
                }
            }

            if(get_class($entity) == 'Intra\Model\Database\Entity\Worker' && $onlyAgency) {
                if($entity->workerTraining) {
                    $entity->setWorkerStep($this->em->getRepository('Intra\Model\Database\Entity\WorkerStep')->find(3));
                    $entity->setOnlyAgency(0);
                } elseif ($entity->workerTender) {
                    $entity->setWorkerStep($this->em->getRepository('Intra\Model\Database\Entity\WorkerStep')->find(2));
                    $entity->setOnlyAgency(0);
                }
            }

            $this->em->flush();

            if ($this->afterSuccess) {
                foreach ($this->afterSuccess as $as) {
                    call_user_func_array([$grid->presenter, $as], [$entity]);
                }
            }

            return $entity;
        } catch (\Exception $e) {
            // Check Integrity constraint viloadin - duplicate entry
            if (strpos($e, 'SQLSTATE[23000]')) {
                $n = explode("'", $e->getMessage());
                if (isset($grid->presenter)) {
                    $grid->presenter->flashMessage('Hodnoty se nepodařilo uložit - hodnota "' . $n[ 3 ] . '" není jedinečná - jiný záznam již má tuto hodnotu!',
                        'warning');
                }
                return;
            }
            \Tracy\Debugger::log($e);
            if (isset($grid->messageEr)) {
                $grid->flashMessage($grid->messageEr[ 0 ], $grid->messageEr[ 1 ]);
            } else {
                throw $e;
            }
            return;
        }
        if (isset($grid->messageOk)) {
            $grid->presenter->flashMessage($grid->messageOk[ 0 ], $grid->messageOk[ 1 ]);
        }
    }

    /**
     * Set rows in grid clicable - on click href to target whit attr
     * @param ACLGrid $grid
     * @param UI\Presenter $presenter
     * @param String $target for generate link - example: (":Homepage:default")
     * @param String $attr name attr from grid to link - default 'id'
     * @return ACLGrid
     */
    public function setClicableRows($grid, $presenter, $target, $attr = 'id', $moreAttr = [], callable $callback = null)
    {
        foreach ($grid->getColumns() as $col) {
            if (get_class($col) == 'Ublaboo\DataGrid\Column\ColumnLink' || get_class($col) == 'Ublaboo\DataGrid\Column\ColumnStatus' || get_class($col) == 'Ublaboo\DataGrid\Column\ColumnCallback') {
                continue;
            }
            // Add class clikable for enable click to column.
            if ($col != null && strpos($col->getTemplate(), 'column_file.latte') === false) {
                $col = $col->addCellAttributes(['class' => 'clickable']);
            }
        }
        if ($target) {
            if(!$moreAttr) {
                $grid->setRowCallback(function ($item, $tr) use ($presenter, $target, $attr, $callback) {
                    $res = true;
                    if ($callback !== null) {
                        $res = ($callback)($item);
                    }
                    if ($res) {
                        $tr->addAttribute('data-click-to', $presenter->link($target, [$attr => $item->$attr]));
                    }
                });
            } else {
                $grid->setRowCallback(function ($item, $tr) use ($presenter, $target, $attr, $moreAttr) {
                    if (isset($item->$attr)) {
                        $attrs[$attr] = $item->$attr;
                    } elseif (isset($item[0])) {
                        $attrs[$attr] = $item[0]->$attr;
                    } else {
                        $attrs[$attr] = $item[$attr];
                    }
                    foreach ($moreAttr as $key => $at) {
                        $attrs[$key] = $at;
                    }
                    $tr->addAttribute('data-click-to', $presenter->link($target, $attrs));
                });
            }

        }

        return $grid;
    }

    /**
     * Set rows in grid attribute
     * @param $grid
     * @param $attr
     * @param $value
     * @return mixed
     */
    public function setAttributeToRows($grid, $attr, $value)
    {
        foreach ($grid->getColumns() as $col) {
            if (get_class($col) == 'Ublaboo\DataGrid\Column\ColumnLink' || get_class($col) == 'Ublaboo\DataGrid\Column\ColumnStatus' || get_class($col) == 'Ublaboo\DataGrid\Column\ColumnCallback') {
                continue;
            }
            // Add class clikable for enable click to column.
            if ($col != null) {
                $col = $col->addAttributes([$attr => $value]);
            }
        }
        return $grid;
    }

    public function addExportToExcel(DataGrid $grid, UI\Presenter $presenter, $filename = null, $name = 'Export do excelu (xlsx)', $filtered = true)
    {
        $grid->addExportCallback($name, function ($data_source, DataGrid $grid) use ($presenter, $filename) {
            if (!$filename) {
                $filename = $grid->getName();
            }
            $spreadsheet = new Spreadsheet();
            $spreadsheet->setActiveSheetIndex(0);
            $spreadsheet->getActiveSheet()->getStyle('1:1')->getFont()->setBold(true);
            $col = 1;
            foreach ($grid->getReorderedColumns($grid->getColumns()) as $k => $column) {
                $row = 1;
                if (strpos(get_class($column), 'ColumnButtonCallback') !== false) {
                    continue;
                }
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $column->getName());
                $row++;
                foreach ($data_source as $item) {
                    $key = $column->getColumn();

                    $value = "";
                    if($grid->entity == 'App\Model\Database\Entity\Vacation' && $key == 'worker.surname' && $item->worker) {
                        $value = $item->worker->surname . ' ' . $item->worker->name;
                    } elseif (strpos($key, '.') !== false) {
                        $x = $item;
                        $key = explode('.', $key);

                        try {
                            foreach ($key as $v) {
                                if (!$x) {
                                    $x = '';
                                    break;
                                }
                                $x = $x->$v;
                            }
                        } catch (\Exception $ex) {
                            $x = '';
                        }
                        $value = $x;
                    } else {
                        $value = $item->$key;
                        if ((is_scalar($value) || $value === null) && isset($this->grid->annotations['properties'][$k]['grids']['default']['replacement'][$value])) {
                            $value = $this->grid->annotations['properties'][$k]['grids']['default']['replacement'][$value];
                        }
                    }
                    if (is_bool($value)) {
                        if ($value) {
                            $value = 'Ano';
                        } else {
                            $value = 'Ne';
                        }
                    } else if (isset($this->grid->annotations['properties'][$k]['grids']['default']['type']) && $this->grid->annotations['properties'][$k]['grids']['default']['type'] == 'file') {
                        $value = basename($value);
                    }
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value);
                    $row++;
                }
                $col++;
            }
            $presenter->getHttpResponse()->setContentType('application/force-download');
            $presenter->getHttpResponse()->setHeader('Content-Disposition', 'attachment;filename='.$filename.'.xlsx');
            $presenter->getHttpResponse()->setHeader('Content-Transfer-Encoding', 'binary');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            die;
        }, $filtered);
    }

    public function addExportToCSV(DataGrid $grid, UI\Presenter $presenter, $filename = null, $name = 'Export do CSV', $glue = ';', $filtered = true)
    {
        $grid->addExportCallback($name, function ($data_source, DataGrid $grid) use ($presenter, $filename, $glue) {
            if (!$filename) {
                $filename = $grid->getName();
            }
            $spreadsheet = new Spreadsheet();
            $spreadsheet->setActiveSheetIndex(0);
            $spreadsheet->getActiveSheet()->getStyle('1:1')->getFont()->setBold(true);
            $col = 1;
            foreach ($grid->getReorderedColumns($grid->getColumns()) as $k => $column) {
                $row = 1;
                if (strpos(get_class($column), 'ColumnButtonCallback') !== false) {
                    continue;
                }
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $column->getName());
                $row++;
                foreach ($data_source as $item) {
                    $key = $column->getColumn();

                    $value = "";
                    if($grid->entity == 'App\Model\Database\Entity\Vacation' && $key == 'worker.surname' && $item->worker) {
                        $value = $item->worker->surname . ' ' . $item->worker->name;
                    } elseif (strpos($key, '.') !== false) {
                        $x = $item;
                        $key = explode('.', $key);

                        try {
                            foreach ($key as $v) {
                                if (!$x) {
                                    $x = '';
                                    break;
                                }
                                $x = $x->$v;
                            }
                        } catch (\Exception $ex) {
                            $x = '';
                        }
                        $value = $x;
                    } else {
                        $value = $item->$key;
                        if ((is_scalar($value) || $value === null) && isset($this->grid->annotations['properties'][$k]['grids']['default']['replacement'][$value])) {
                            $value = $this->grid->annotations['properties'][$k]['grids']['default']['replacement'][$value];
                        }
                    }
                    if (is_bool($value)) {
                        if ($value) {
                            $value = 'Ano';
                        } else {
                            $value = 'Ne';
                        }
                    } elseif ($value instanceof \DateTime) {
                        $value = $value->format('d. m. Y H:i:s');
                    } else if (isset($this->grid->annotations['properties'][$k]['grids']['default']['type']) && $this->grid->annotations['properties'][$k]['grids']['default']['type'] == 'file') {
                        $value = basename($value);
                    }
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value);
                    $row++;
                }
                $col++;
            }
            $presenter->getHttpResponse()->setContentType('application/force-download');
            $presenter->getHttpResponse()->setHeader('Content-Disposition', 'attachment;filename='.$filename.'.csv');
            $presenter->getHttpResponse()->setHeader('Content-Transfer-Encoding', 'binary');
            $writer = new Csv($spreadsheet);
            $writer->setExcelCompatibility(true);
            $writer->setUseBOM(false);
            $writer->setDelimiter($glue);
            $writer->setIncludeSeparatorLine(false);
            $writer->save('php://output');
            die;
        }, $filtered);
    }

    public function deleteForeignKeys($entity, $id)
    {
        $data = $this->parser->getClassAnnotationsEntity($entity);
        if (isset($data['properties'])) {
            foreach ($data['properties'] as $k => $values) {
                if (!isset($data['properties'][$k]['oneToMany'])) {
                    continue;
                }

                $entities = $this->em->getRepository('App\Model\Database\Entity\\' . $data['properties'][$k]['oneToMany']['targetEntity'])->findBy([$data['properties'][$k]['oneToMany']['mappedBy'] => $id]);
                if ($entities) {
                    foreach ($entities as $e) {
                        $this->deleteForeignKeys(get_class($e), $e->id);
                        $this->em->remove($e);
                    }
                    $this->em->flush();
                }
            }
        }
    }

    public function deleteRow($id)
    {
        $action = $this->grid->getMapper()->mapInput(
            $this->grid->getUser(), $this->grid->getNamePresenter(), $this->grid->getNameGrid(), 'delete', '');

        if ($action == 'write' || $action == 'read') {
            // TODO: Delete files
            $this->deleteForeignKeys($this->grid->getEntity(), $id);
            try {
                $entity = $this->em->getRepository($this->grid->getEntity())->find($id);
                $this->em->remove($entity);
                $this->em->flush();
            } catch (\Exception $e) {
                $this->grid->getPresenter()->flashMessage('Záznam nelze smazat, protože se využívá!', 'error');
                $this->grid->getPresenter()->redirect('this');
            }
            $this->grid->getPresenter()->flashMessage('Záznam se podařilo úspěšně smazat', 'success');
            $this->grid->getPresenter()->redirect('this');
        } else {
            $this->grid->getPresenter()->flashMessage('Pro tuto akci nemáte oprávnění', 'warning');
            $this->grid->getPresenter()->redirect('this');
        }
    }

    /**
     * @return QueryBuilder
     */
    public function getDataSource()
    {
        return $this->datasource;
    }
}
