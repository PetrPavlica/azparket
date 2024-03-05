<?php

namespace App\Components\ACLHtml;

use Nette\Application\UI;
use App\Model\ACLMapper;
use App\Model\Database\Entity\PermissionRule;

class ACLHtmlControl extends UI\Control
{

    /** @var ACLMapper */
    protected $mapper;
    protected $startName = 'App_Intra_Presenters_';

    public function __construct(ACLMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function render($html, $caption, $mapName, $presenterName = NULL, $type = NULL)
    {
        if ($type == 'global-element') {
            $presenterName = 'global';
        } else if ($presenterName == NULL)
            $presenterName = get_class($this->parent);
        else
            $presenterName = $presenterName . 'Presenter';

        $permision = $this->mapper->mapHtmlControl($this->parent->user, $presenterName, $mapName, $caption, $type);
        // if item have permissionRule
        if ($permision == PermissionRule::ACTION_SHOW) {
            $template = $this->template;
            $template->setFile(__DIR__ . '/templates/element.latte');

            $template->html = $html;

            // render template
            $template->render();
        }
    }

}