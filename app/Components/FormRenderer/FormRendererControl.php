<?php

namespace App\Components\FormRenderer;

use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Control;

class FormRendererControl extends UI\Control
{
    public function render($item, $type = 'bootstrap', $dataSource = '', $dataSend = "", $dataSucc = "")
    {
        if ($item === null) {
            return;
        }
        $template = $this->template;
        $template->dataSource = $dataSource;
        $template->dataSend = $dataSend;
        $template->dataSucc = $dataSucc;

        switch ($type) {
            case 'bootstrap':
                $template->setFile(__DIR__ . '/templates/bootstrap.latte');
                $template->renderForm = $item;
                break;
            case 'sidebar':
                $template->setFile(__DIR__ . '/templates/rulesidebar.latte');
                $template->renderForm = $item;
                break;
            case 'btns':
                $template->getLatte()->addProvider('formsStack', [$item]);
                $template->setFile(__DIR__ . '/templates/btns.latte');
                $template->renderForm = $item;
                $template->link = $link = $this->parent->link(str_ireplace('Intra:', '', $this->parent->getName()).':');
                break;
            default :
                if (!isset($item[ $type ])) {
                    return;
                    //throw new \Exception("Try render in form undeclared type or undefinded input. type/input: " . $type);
                }
                $template->setFile(__DIR__ . '/templates/bootstrapElement.latte');
                $template->item = $item[ $type ];
                break;
        }
        $template->render();
    }

    public function renderLow($id, $name, $type, $value = "", $class = "", $attrs = "")
    {
        $template = $this->template;
        $template->id = $id;
        $template->type = $type;
        $template->name = $name;
        $template->class = $class;
        $template->value = $value;
        $template->attrs = $attrs;
        if (!isset($type)) {
            throw new \Exception("Try render in form undeclared type or undefinded input. type/input: " . $type);
        }
        $template->setFile(__DIR__ . '/templates/bootstrapElementLow.latte');
        $template->render();
    }

    public function renderLowFloat($id, $name, $placeholder, $value = "", $class = "", $attrs = "")
    {
        $template = $this->template;
        $template->id = $id;
        $template->placeholder = $placeholder;
        $template->name = $name;
        $template->class = $class;
        $template->value = $value;
        $template->attrs = $attrs;
        $template->setFile(__DIR__ . '/templates/bootstrapElementLowFloat.latte');
        $template->render();
    }

    public function renderLowSelect($id, $name, $selected, $data, $class = "")
    {
        $template = $this->template;
        $template->data = $data;
        $tmp = str_replace('[', '', $name);
        $tmp = str_replace(']', '', $tmp);
        $template->id = $id . '_' . $tmp;
        $template->name = $name;
        $template->selected = $selected;
        $template->class = $class;

        $template->setFile(__DIR__ . '/templates/bootstrapLowSelect.latte');
        $template->render();
    }

    public function renderLowAutocomplete(
        $id,
        $name,
        $value,
        $valueCmp = "",
        $dataSource = '',
        $class = "",
        $dataSend = "",
        $dataSucc = "",
        $placeholder = ""
    ) {
        $template = $this->template;
        $template->dataSource = $dataSource;
        $template->dataSend = $dataSend;
        $template->dataSucc = $dataSucc;
        $template->placeholder = $placeholder;
        $template->id = $id;
        $template->name = $name;
        $template->value = $value;
        $template->valueCmp = $valueCmp;
        $template->class = $class;
        $template->setFile(__DIR__ . '/templates/bootstrapLowAutocomplete.latte');
        $template->render();
    }

    public function handleDataSource($dataSource)
    {
        $presenter = $this->parent;
        if (strpos(get_class($presenter), 'Presenter') == false) {
            $presenter = $this->parent->parent;
        }
        $term = $presenter->request->getParameters()[ 'term' ];
        $dataSource = 'handle' . ucfirst($dataSource);
        return $presenter->$dataSource($term);
    }

}
