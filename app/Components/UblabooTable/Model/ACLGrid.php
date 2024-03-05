<?php

namespace App\Components\UblabooTable\Model;

use App\Model\ACLMapper;
use App\Model\Database\Entity\DataGridOptions;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Forms\Form as NetteForm;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\Column\Action;
use Ublaboo\DataGrid\Column\ColumnDateTime;
use Ublaboo\DataGrid\Column\ColumnLink;
use Ublaboo\DataGrid\Column\ColumnNumber;
use Ublaboo\DataGrid\Column\ColumnStatus;
use Ublaboo\DataGrid\Column\ItemDetail;
use Ublaboo\DataGrid\DataGrid;
use Nette\ComponentModel\IContainer;
use Ublaboo\DataGrid\DataModel;
use Ublaboo\DataGrid\Exception\DataGridColumnNotFoundException;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Exception\DataGridFilterNotFoundException;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;
use Ublaboo\DataGrid\Row;

class ACLGrid extends DataGrid
{
    /** mixed entity */
    public $entity;

    /** @var mixed */
    public $presenter;

    /** messages on success form */
    public $messageOk;

    /** messages on error form */
    public $messageEr;

    /** array of foreign entity for save method */
    public $arrayForeignEntity = NULL;

    /** array of foreign entity for save method */
    public $arrayNNForeignEntity = NULL;

    /** array of custom entity for save method */
    public $arrayCustomEntity = NULL;

    /** @var array */
    public $inlineSettings;

    /** @var User */
    private $user;

    /** @var string */
    private $namePresenter;

    /** @var ACLMapper */
    private $mapper;

    /** @var string */
    private $nameGrid;

    /** @var boolean */
    protected $visibleFilters;

    /** @var bool */
    private $enableDatabaseOptions = true;

    /** @var array */
    private $columnsSelected = [];

    /** @var bool */
    private $defaultVisibleFilters = true;

    /** @var array */
    public $annotations;

    /** @var string */
    private $customerRole = 'customer';

    /**
     * Construct for ACL Grid
     * @param User $user actual user
     * @param string $presenter name of actual presenter
     * @param string $function name fo actual function
     * @param ACLMapper $mapper
     * @param IContainer $parent
     * @param string $name
     */
    public function __construct($user, $presenter, $function, $mapper, IContainer $parent = NULL, $name = NULL)
    {
        parent::__construct($parent, $name);
        $this->user = $user;
        $this->namePresenter = $presenter;
        $this->nameGrid = $function;
        $this->mapper = $mapper;
    }

    /**
     * {inheritDoc}
     */
    public function setParent(?IContainer $parent, string $name = null)
    {
        parent::setParent($parent, $name);
        $this->presenter = $parent;
        if ($this->enableDatabaseOptions) {
            $key = trim($this->getSessionSectionName());
            $qb = $this->mapper->getEm()->getRepository(DataGridOptions::class)->createQueryBuilder('a');
            $qb->select('a')->where('a.keyName LIKE :key and a.user = :user and a.customer = :customer');
            $qb->setParameters(['key' => $key . '_%', 'user' => $this->user->getId(), 'customer' => $this->user->isInRole($this->customerRole)]);
            $options = $qb->getQuery()->getResult();
            if ($options) {
                foreach ($options as $o) {
                    $this->columnsSelected[$o->keyName] = $o;
                }
            }
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    /*public function addColumnText($key, $name, $column = NULL): ColumnText
    {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

        if ($action == 'write' || $action == 'read') {
            return parent::addColumnText($key, $name, $column);
        }

        return null;
    }*/

    /**
     * @inheritDoc
     */
    public function addColumnCheckbox($key, $name, $column = null)
    {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

        if ($action == 'write' || $action == 'read') {
            $item = parent::addColumnStatus($key, $name, $column);
            $item->setTemplate(__DIR__ . '/../templates/column_checkbox.latte');

            return $item;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function addColumnTranslateText($key, $name, $column = NULL)
    {
        $action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

        if ($action == 'write' || $action == 'read') {
            //$this->addColumnCheck($key);
            $column = $column ?: $key;
            $item = $this->addColumn($key, new ColumnTextTranslate($this, $key, $column, $name));
            return $item;
        }

        return NULL;
    }

    /**
     * @inheritDoc
     */
    public function addColumnNumber($key, $name, $column = NULL): ColumnNumber
    {
        if (strtolower($key) == 'id') {
            return parent::addColumnNumber($key, $name, $column);
        }

        /*$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

        if ($action == 'write' || $action == 'read') {*/
            return parent::addColumnNumber($key, $name, $column);
        /*}

        return null;*/
    }

    /**
     * @inheritDoc
     */
    public function addColumnDateTime($key, $name, $column = NULL): ColumnDateTime
    {
        /*$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

        if ($action == 'write' || $action == 'read') {*/
            return parent::addColumnDateTime($key, $name, $column);
        /*}

        return null;*/
    }

    /**
     * @inheritDoc
     */
    public function addColumnLink($key, $name, $href = NULL, $column = NULL, array $params = NULL): ColumnLink
    {
        /*$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

        if ($action == 'write' || $action == 'read') {*/
            return parent::addColumnLink($key, $name, $href, $column, $params);
        /*}

        return null;*/
    }

    /**
     * @inheritDoc
     */
    public function addAction($key, $name, $href = NULL, array $params = NULL): Action
    {
        /*$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name == '' ? 'Akce: ' . $key : $name);

        if ($action == 'write' || $action == 'read') {*/
            return parent::addAction($key, $name, $href, $params);
        /*}

        return null;*/
    }

    /**
     * @inheritDoc
     */
    public function addColumnStatus($key, $name, $column = NULL): ColumnStatus
    {
        /*$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

        if ($action == 'write' || $action == 'read') {*/
            return parent::addColumnStatus($key, $name, $column);
        /*}
        return null;*/
    }

    /**
     * @inheritDoc
     */
    public function addColumnBoolean($key, $name, $column = NULL)
    {
        /*$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

        if ($action == 'write' || $action == 'read') {*/
            $item = parent::addColumnStatus($key, $name, $column);
            $item->setTemplate(__DIR__ . '/../templates/column_boolean.latte');

            return $item;
        /*}

        return NULL;*/
    }

    public function setEconomicalTemplateGrid()
    {
        $this->setTemplateFile(__DIR__ . '/../templates/datagrid/datagrid.latte');
    }

    public function setTreeView(callable $get_children_callback, $tree_view_has_children_column = 'has_children') :DataGrid
    {
        parent::setTreeView($get_children_callback, $tree_view_has_children_column);
        $this->setTemplateFile(__DIR__ . '/../templates/datagrid/datagrid_tree.latte');

        return $this;
    }

    /**
     * Set entity in grid
     * @param string $entity
     */
    public function setEntity($entity)
    {
        if (!class_exists($entity)) {
            throw new \Exception("Entity $entity not exist!");
        }
        $this->entity = $entity;
    }

    /**
     * Return entity in grid
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Return name of grid
     * @return string
     */
    public function getNameGrid()
    {
        return $this->nameGrid;
    }

    public function getQB()
    {
        return $this->dataModel->getDataSource();
    }

    /**
     * Return name parent presenter
     * @return string
     */
    public function getNamePresenter()
    {
        return $this->namePresenter;
    }

    /**
     * Return mapper
     * @return ACLMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * Return user
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set messages on success form - succ save and err save
     * @param array $messageOk [text, type]
     * @param array $messageEr [text, type]
     */
    public function setMessages($messageOk, $messageEr, $presenter)
    {
        $this->messageOk = $messageOk;
        $this->messageEr = $messageEr;
        $this->presenter = $presenter;
    }

    public function handleHideFilters()
    {
        $this->visibleFilters = false;
        $this->saveSessionData('_grid_visible_filters', false);

        $this->redrawControl();

        $this->onRedraw();
    }

    public function handleShowFilters()
    {
        $this->visibleFilters = true;
        $this->saveSessionData('_grid_visible_filters', true);

        $this->redrawControl();

        $this->onRedraw();
    }

    private $originalTemplateFile;

    public function getOriginalTemplateFile(): string
    {
        return $this->originalTemplateFile ?: __DIR__ . '/../templates/datagrid/datagrid.latte';
    }

    public function setOriginalTemplateFile($file)
    {
        $this->originalTemplateFile = $file;
    }

    /**
     * Find some unique session key name
     * @return string
     */
    public function getSessionSectionName(): string
    {
        $suffix = '';
        if ($this->getParent() && $this->getParent()->getParent()) {
            $suffix = $this->getParent()->getParent()->getRequest()->getParameter('slug');
            if ($suffix) {
                $suffix = '--' . $suffix;
            }
        }
        return $this->getPresenter()->getName() . ':' . $this->getUniqueId() . $suffix;
    }

    /**
     * @inheritDoc
     */
    public function handleShowAllColumns(): void
    {
        $this->deleteSessionData('_grid_hidden_columns');
        $this->saveSessionData('_grid_hidden_columns_manipulated', true);

        $this->redrawControl();

        $this->onRedraw();
    }

    /**
     * @inheritDoc
     */
    public function handleShowDefaultColumns(): void
    {
        $this->deleteSessionData('_grid_hidden_columns');
        $this->saveSessionData('_grid_hidden_columns_manipulated', false);

        $this->redrawControl();

        $this->onRedraw();
    }

    /**
     * @inheritDoc
     */
    public function handleShowColumn($column): void
    {
        $columns = $this->getSessionData('_grid_hidden_columns');

        if (!empty($columns)) {
            $pos = array_search($column, $columns, true);

            if ($pos !== false) {
                unset($columns[$pos]);
            }
        }

        $this->saveSessionData('_grid_hidden_columns', $columns);
        $this->saveSessionData('_grid_hidden_columns_manipulated', true);

        $this->redrawControl();

        $this->onRedraw();
    }

    /**
     * @inheritDoc
     */
    public function handleHideColumn($column): void
    {
        /**
         * Store info about hiding a column to session
         */
        $columns = $this->getSessionData('_grid_hidden_columns');

        if (empty($columns)) {
            $columns = [$column];
        } elseif (!in_array($column, $columns, true)) {
            array_push($columns, $column);
        }

        $this->saveSessionData('_grid_hidden_columns', $columns);
        $this->saveSessionData('_grid_hidden_columns_manipulated', true);

        $this->redrawControl();

        $this->onRedraw();
    }

    /**
     * @inheritDoc
     */
    public function getColumns(): array
    {
        $return = $this->columns;

        try {
            $this->getParentComponent();

            $columns_to_hide = [];
            if (!$this->getSessionData('_grid_hidden_columns_manipulated', false)) {
                foreach ($this->columns as $key => $column) {
                    if ($column->getDefaultHide()) {
                        $columns_to_hide[] = $key;
                    }
                }

                if (!empty($columns_to_hide)) {
                    $this->saveSessionData('_grid_hidden_columns', $columns_to_hide);
                    $this->saveSessionData('_grid_hidden_columns_manipulated', true);
                }
            }

            $hidden_columns = $this->getSessionData('_grid_hidden_columns', $columns_to_hide);

            foreach ($hidden_columns as $column) {
                if (!empty($this->columns[$column])) {
                    $this->columnsVisibility[$column] = [
                        'visible' => false,
                    ];

                    unset($return[$column]);
                }
            }

        } catch (DataGridHasToBeAttachedToPresenterComponentException $e) {
        }

        return $return;
    }

    /**
     * Items can have thair detail - toggled
     * @param mixed $detail callable|string|bool
     * @param bool|NULL $primary_where_column
     * @return ItemDetailSimple
     */
    public function setItemsDetail($detail = TRUE, $primary_where_column = NULL): ItemDetail
    {
        if ($this->isSortable()) {
            throw new DataGridException('You can not use both sortable datagrid and items detail.');
        }

        $this->itemsDetail = new ItemDetailSimple(
            $this, $primary_where_column ?: $this->primaryKey
        );

        if (is_string($detail)) {
            /**
             * Item detail will be in separate template
             */
            $this->itemsDetail->setType('template');
            $this->itemsDetail->setTemplate($detail);
        } else if (is_callable($detail)) {
            /**
             * Item detail will be rendered via custom callback renderer
             */
            $this->itemsDetail->setType('renderer');
            $this->itemsDetail->setRenderer($detail);
        } else if (TRUE === $detail) {
            /**
             * Item detail will be rendered probably via block #detail
             */
            $this->itemsDetail->setType('block');
        } else {
            throw new DataGridException(
                '::setItemsDetail() can be called either with no parameters or with parameter = template path or callable renderer.'
            );
        }

        return $this->itemsDetail;
    }

    public function render(): void
    {
        /**
         * Check whether datagrid has set some columns, initiated data source, etc
         */
        if (!($this->dataModel instanceof DataModel)) {
            throw new DataGridException('You have to set a data source first.');
        }

        if ($this->columns === []) {
            throw new DataGridException('You have to add at least one column.');
        }

        $template = $this->getTemplate();

        if (!$template instanceof Template) {
            throw new \UnexpectedValueException;
        }

        $template->setTranslator($this->getTranslator());

        /**
         * Invoke possible events
         */
        $this->onRender($this);

        /**
         * Prepare data for rendering (datagrid may render just one item)
         */
        $rows = [];

        $items = $this->redrawItem !== [] ? $this->dataModel->filterRow($this->redrawItem) : $this->dataModel->filterData(
            $this->getPaginator(),
            $this->createSorting($this->sort, $this->sortCallback),
            $this->assembleFilters()
        );

        $hasGroupActionOnRows = false;

        foreach ($items as $item) {
            $rows[] = $row = new Row($this, $item, $this->getPrimaryKey());

            if (!$hasGroupActionOnRows && $row->hasGroupAction()) {
                $hasGroupActionOnRows = true;
            }

            if ($this->rowCallback !== null) {
                ($this->rowCallback)($item, $row->getControl());
            }

            /**
             * Walkaround for item snippet - snippet is the <tr> element and its class has to be also updated
             */
            if ($this->redrawItem !== []) {
                $this->getPresenter()->payload->_datagrid_redrawItem_class = $row->getControlClass();
                $this->getPresenter()->payload->_datagrid_redrawItem_id = $row->getId();
            }
        }

        if ($hasGroupActionOnRows) {
            $hasGroupActionOnRows = $this->hasGroupActions();
        }

        if ($this->isTreeView()) {
            $template->add('treeViewHasChildrenColumn', $this->treeViewHasChildrenColumn);
        }

        $template->rows = $rows;

        $template->columns = $this->getReorderedColumns($this->getColumns());
        $template->actions = $this->actions;
        $template->exports = $this->exports;
        $template->filters = $this->filters;
        $template->toolbarButtons = $this->toolbarButtons;
        $template->aggregationFunctions = $this->getAggregationFunctions();
        $template->multipleAggregationFunction = $this->getMultipleAggregationFunction();

        $template->filter_active = $this->isFilterActive();
        $template->originalTemplate = $this->getOriginalTemplateFile();
        $template->iconPrefix = static::$iconPrefix;
        $template->iconPrefix = static::$iconPrefix;
        $template->itemsDetail = $this->itemsDetail;
        $template->columnsVisibility = $this->getColumnsVisibility();
        $template->columnsSummary = $this->columnsSummary;

        $template->inlineEdit = $this->inlineEdit;
        $template->inlineAdd = $this->inlineAdd;

        $template->hasGroupActions = $this->hasGroupActions();
        $template->hasGroupActionOnRows = $hasGroupActionOnRows;
        $template->visibleFilters = $this->visibleFilters;
        $template->allColumns = $this->getReorderedColumns($this->columns);
        $template->widthColumns = $this->getSessionData('_grid_width_columns', []);

        /**
         * Walkaround for Latte (does not know $form in snippet in {form} etc)
         */
        $template->filter = $this['filter'];

        if ($this->enableDatabaseOptions) {
            $this->mapper->getEm()->flush();
        }

        /**
         * Set template file and render it
         */
        $template->setFile($this->getTemplateFile());
        $template->render();
    }

    /**
     * Try to restore session stuff
     * @return void
     * @throws DataGridFilterNotFoundException
     */
    public function findSessionValues(): void
    {
        $this->visibleFilters = $this->getSessionData('_grid_visible_filters', $this->defaultVisibleFilters);

        if (!$this->testEmpty($this->filter) || ($this->page != 1) || !empty($this->sort)) {
            return;
        }

        if (!$this->rememberState) {
            return;
        }

        if ($page = $this->getSessionData('_grid_page')) {
            $this->page = intval($page);
        }

        if ($per_page = $this->getSessionData('_grid_perPage')) {
            $this->perPage = intval($per_page);
        }

        if ($sort = $this->getSessionData('_grid_sort')) {
            $this->sort = $sort;
        }

        foreach ($this->getSessionData() as $key => $value) {
            $other_session_keys = [
                '_grid_perPage',
                '_grid_sort',
                '_grid_page',
                '_grid_has_sorted',
                '_grid_has_filtered',
                '_grid_hidden_columns',
                '_grid_hidden_columns_manipulated',
                '_grid_visible_filters',
                '_grid_width_columns',
                '_grid_position_columns'
            ];

            if (!in_array($key, $other_session_keys, true)) {
                try {
                    $this->getFilter($key);

                    $this->filter[$key] = $value;

                } catch (DataGridException $e) {
                    if ($this->strictSessionFilterValues) {
                        throw new DataGridFilterNotFoundException("Session filter: Filter [$key] not found");
                    }
                }
            }
        }

        /**
         * When column is sorted via custom callback, apply it
         */
        if (empty($this->sortCallback) && !empty($this->sort)) {
            foreach ($this->sort as $key => $order) {
                try {
                    $column = $this->getColumn($key);

                } catch (DataGridColumnNotFoundException $e) {
                    $this->deleteSessionData('_grid_sort');
                    $this->sort = [];

                    return;
                }

                if ($column && $column->isSortable() && is_callable($column->getSortableCallback())) {
                    $this->sortCallback = $column->getSortableCallback();
                }
            }
        }
    }

    /**
     * Handler for reseting the filter
     * @return void
     */
    public function handleResetFilter(): void
    {
        /**
         * Session stuff
         */
        $this->deleteSessionData('_grid_page');

        if ($this->defaultFilterUseOnReset) {
            $this->deleteSessionData('_grid_has_filtered');
        }

        if ($this->defaultFilterUseOnReset) {
            $this->deleteSessionData('_grid_has_sorted');
        }

        foreach ($this->getSessionData() as $key => $value) {
            if (!in_array($key, [
                '_grid_perPage',
                '_grid_sort',
                '_grid_page',
                '_grid_has_filtered',
                '_grid_has_sorted',
                '_grid_hidden_columns',
                '_grid_hidden_columns_manipulated',
                '_grid_visible_filters',
                '_grid_width_columns',
                '_grid_position_columns'
            ], true)) {
                $this->deleteSessionData($key);
            }
        }

        $this->filter = [];

        $this->reloadTheWholeGrid();
    }

    /**
     * @inheritDoc
     */
    public function getSessionData($key = null, $default_value = null)
    {
        if (!$this->rememberState) {
            return $key ? $default_value : [];
        }

        if ($this->enableDatabaseOptions) {
            if ($key) {
                $keyName = trim($this->getSessionSectionName() . '_' . $key);

                $option = isset($this->columnsSelected[$keyName]) ? $this->columnsSelected[$keyName] : null;

                if ($option) {
                    switch ($option->valueType) {
                        case 'array':
                            $value = \json_decode($option->value, true);
                            break;
                            case 'bool':
                                $value = boolval($option->value);
                                break;
                            case 'arrayHash':
                                $value = ArrayHash::from(\json_decode($option->value, true));
                                break;
                            default:
                                $value = $option->value;
                                break;
                    }
                    return $value;
                }
            } else {
                $keyName = trim($this->getSessionSectionName());
                $options = null;
                if ($this->columnsSelected) {
                    $options = $this->columnsSelected;
                }
                $temp = [];
                if ($options) {
                    foreach ($options as $option) {
                        $keyN = str_replace( $keyName.'_', '', $option->keyName);
                        switch ($option->valueType) {
                            case 'array':
                                $temp[$keyN] = \json_decode($option->value, true);
                                break;
                            case 'bool':
                                $temp[$keyN] = boolval($option->value);
                                break;
                            case 'arrayHash':
                                $temp[$keyN] = ArrayHash::from(\json_decode($option->value, true));
                                break;
                            default:
                                $temp[$keyN] = $option->value;
                                break;
                        }
                    }
                }
                return $temp;
            }

            return $default_value;
        } else {
            return ($key ? $this->gridSession->{$key} : $this->gridSession) ?? $default_value;
        }
    }

    /**
     * @inheritDoc
     */
    public function saveSessionData($key, $value): void
    {
        if ($this->rememberState) {
            if ($this->enableDatabaseOptions) {
                $keyName = trim($this->getSessionSectionName() . '_' . $key);
                $option = isset($this->columnsSelected[$keyName]) ? $this->columnsSelected[$keyName] : null;
                if (!$option) {
                    $option = new DataGridOptions();
                    $option->setKeyName($keyName);
                    if ($this->user->isLoggedIn()) {
                        $option->setUser($this->mapper->getEm()->getRepository(\App\Model\Database\Entity\User::class)->find($this->user->getId()));
                        $option->setCustomer($this->user->isInRole($this->customerRole));
                        $this->mapper->getEm()->persist($option);
                    }
                }
                $valueTemp = $value;
                $type = 'string';
                if (is_array($value)) {
                    $type = 'array';
                    $valueTemp = \json_encode($valueTemp);
                } else if (is_bool($value)) {
                    $type = 'bool';
                    $valueTemp = strval(intval($valueTemp));
                } else if ($value instanceof ArrayHash) {
                    $type = 'arrayHash';
                    $valueTemp = \json_encode((array)$valueTemp);
                } else {
                    $valueTemp = strval($valueTemp);
                }
                $option->setValue($valueTemp);
                $option->setValueType($type);
                $this->columnsSelected[$keyName] = $option;
            } else {
                $this->gridSession->{$key} = $value;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteSessionData($key): void
    {
        if ($this->enableDatabaseOptions) {
            $keyName = trim($this->getSessionSectionName() . '_' . $key);
            if (isset($this->columnsSelected[$keyName])) {
                $this->mapper->getEm()->remove($this->columnsSelected[$keyName]);
                unset($this->columnsSelected[$keyName]);
            }
        } else {
            unset($this->gridSession->{$key});
        }
    }

    /**
     * @inheritDoc
     */
    public function assembleFilters(): array
    {
        foreach ($this->filter as $key => $value) {
            if (!isset($this->filters[$key])) {
                $this->deleteSessionData($key);

                continue;
            }

            if (is_array($value) || $value instanceof \Traversable) {
                if (!$this->testEmpty($value)) {
                    $this->filters[$key]->setValue($value);
                }
            } else {
                if ($value !== '' && $value !== null) {
                    $this->filters[$key]->setValue($value);
                }
            }
        }

        foreach ($this->columns as $key => $column) {
            if (isset($this->sort[$key])) {
                $column->setSort($this->sort[$key]);
            }
        }

        $this->onFiltersAssembled($this->filters);
        return $this->filters;
    }

    /**
     * Test recursively whether given array is empty
     * @param  array $array
     * @return bool
     */
    private function testEmpty($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value) || $value instanceof ArrayHash) {
                if (!$this->testEmpty($value)) {
                    return false;
                }
            } else {
                if ($value) {
                    return false;
                }

                if (in_array($value, [0, '0', false], true)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getColumnsCount(): int
    {
        $count = sizeof($this->getColumns());

        if ($this->actions !== []
            || $this->canHideColumns()
            || $this->isSortable()
            || $this->getItemsDetail() !== null
            || $this->getInlineEdit() !== null
            || $this->getInlineAdd() !== null) {
            $count++;
        }

        if ($this->hasGroupActions()) {
            $count++;
        }

        return $count;
    }

    public function createComponentSettings(): Form
    {
        $form = new Form($this, 'settings');

        $form->setMethod(static::$formMethod);

        $form->setTranslator($this->getTranslator());

        $columnVisibility = $this->getColumnsVisibility();
        $widthColumns = $this->getSessionData('_grid_width_columns', []);
        $posColumns = $this->getSessionData('_grid_position_columns', []);
        $visibleFilters = $this->getSessionData('_grid_visible_filters', $this->defaultVisibleFilters);

        $checkbox = $form->addCheckbox('visibleFilters', 'Zobrazit filtry');
        $checkbox->setDefaultValue($visibleFilters);

        $pos = 1;
        foreach ($this->columns as $k => $column) {
            $checkbox = $form->addCheckbox('visible_'.$k, $column->getName());
            $checkbox->setDefaultValue($columnVisibility[$k]['visible'] ?? false);

            $input = $form->addInteger('width_'.$k, $column->getName());
            $input->setDefaultValue($widthColumns[$k] ?? 0);
            $input->setRequired(false);

            $hidden = $form->addHidden('pos_'.$k);
            $hidden->setDefaultValue($posColumns[$k] ?? $pos);
            $pos++;
        }

        $form->onError[] = function(NetteForm $form): void {

        };

        $form->onSuccess[] = function (NetteForm $form, $values): void {
            $hiddenColumns = $this->getSessionData('_grid_hidden_columns', []);
            $widthColumns = $this->getSessionData('_grid_width_columns', []);
            $posColumns = $this->getSessionData('_grid_position_columns', []);

            foreach ($values as $key => $v) {
                if (strpos($key, 'visible_') !== false) {
                    $k = str_replace('visible_', '', $key);
                    if ($v && in_array($k, $hiddenColumns, true)) {
                        $pos = array_search($k, $hiddenColumns, true);

                        if ($pos !== false) {
                            unset($hiddenColumns[$pos]);
                        }
                    } else if (!$v && !in_array($k, $hiddenColumns, true)) {
                        array_push($hiddenColumns, $k);
                    }
                } elseif (strpos($key, 'width_') !== false) {
                    $k = str_replace('width_', '', $key);
                    $v = intval($v);
                    if (!$v && array_key_exists($k, $widthColumns)) {
                        unset($widthColumns[$k]);
                    } else if ($v) {
                        $widthColumns[$k] = $v;
                    }
                } elseif (strpos($key, 'pos_') !== false) {
                    $k = str_replace('pos_', '', $key);
                    $v = intval($v);
                    $posColumns[$k] = $v;
                }
            }

            $this->saveSessionData('_grid_hidden_columns', $hiddenColumns);
            $this->saveSessionData('_grid_hidden_columns_manipulated', true);
            $this->saveSessionData('_grid_width_columns', $widthColumns);
            $this->saveSessionData('_grid_position_columns', $posColumns);
            $this->saveSessionData('_grid_visible_filters', $values->visibleFilters);

            if ($this->enableDatabaseOptions) {
                $this->mapper->getEm()->flush();
            }

            if ($this->getPresenter()->isAjax()) {
                $this->redrawControl();

                $this->onRedraw();
            } else {
                $this->redirect('this');
            }
        };

        return $form;
    }

    public function handleResizeColumn(): void
    {
        $values = $this->getPresenter()->getHttpRequest()->getPost();
        if (!array_key_exists('column', $values) || !array_key_exists('width', $values)) {
            return;
        }

        $widthColumns = $this->getSessionData('_grid_width_columns', []);
        $widthColumns[$values['column']] = intval($values['width']);

        $this->saveSessionData('_grid_width_columns', $widthColumns);

        $this->redrawControl();

        $this->onRedraw();
    }

    public function getReorderedColumns($columns): array
    {
        $posColumns = $this->getSessionData('_grid_position_columns', []);
        asort($posColumns);
        if (count($posColumns)) {
            uksort($columns, function ($a, $b) use ($posColumns) {
                $keys = array_keys($posColumns);
                $pos_a = array_search($a, $keys);
                $pos_b = array_search($b, $keys);
                return $pos_a - $pos_b;
            });
        }

        return $columns;
    }

    public function handleDownloadFile()
    {
        $params = $this->getPresenter()->getRequest()->getParameters();
        if (file_exists($params['table-file'])) {
            $this->getPresenter()->sendResponse(new FileResponse($params['table-file']));
        }
    }

    /**
     * @throws DataGridException
     */
    public function addFilterDateRange(
        string $key,
        string $name,
        ?string $column = null,
        string $nameSecond = '-'
    ): FilterDateRange
    {
        $column = $column ?? $key;

        $this->addFilterCheck($key);

        return $this->filters[$key] = new FilterDateRange($this, $key, $name, $column, $nameSecond);
    }

    public function addMultiAction(string $key, string $name): MultiAction
    {
        $this->addActionCheck($key);

        $action = new MultiAction($this, $key, $name);

        $this->actions[$key] = $action;

        return $action;
    }

    /**
     * @return Action|MultiAction
     * @throws DataGridException
     */
    public function getAction(string $key)
    {
        $actionKey = null;
        if (strpos($key, '|') !== false) {
            list($key, $actionKey) = explode('|', $key);
        }
        if (!isset($this->actions[$key])) {
            throw new DataGridException(sprintf('There is no action at key [%s] defined.', $key));
        }
        if ($this->actions[$key] instanceof MultiAction && $actionKey) {
            return $this->actions[$key]->getAction($actionKey);
        }

        return $this->actions[$key];
    }

    /**
     * @param array|string[] $snippets
     */
    public function reload(array $snippets = []): void
    {
        if ($this->getPresenter()->isAjax()) {
            $this->redrawControl('resetFilter');
        }

        parent::reload($snippets);
    }
}