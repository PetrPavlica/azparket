<?php

namespace App\ApiModule\Presenters;

use Nette;
use App\Model\Database\EntityManager;

/**
 * Base presenter for all intra Presenter
 */
abstract class BaseApiPresenter extends \App\IntraModule\Presenters\BasePresenter
{
    public function startup()
    {
        parent::startup();
    }

    public function beforeRender()
    {
        parent::beforeRender();
    }

    
    public function getPost()
    {
        $rest_json = file_get_contents("php://input");
        return json_decode($rest_json, true);
    }

    public function sendSuccess($data)
    {
        $this->sendJson(['success' => $data]);
    }

    public function sendError($error)
    {
        $this->sendJson(['error' => $error]);
    }
    
}
