<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use Nette\Application\UI\Form;

class DailyPlanPresenter extends BasePresenter
{
    /**
     * ACL name='Správa plán zakázek'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderDefault() {
        $this->template->scriptsForCalendar = true;
        //$this->template->hideTitleHeading = true;

        $workers = $this->em->getWorkerRepository()->findBy(['active' => 1]);
        $this->template->workers = $workers;

        if($this->sess->displayed) {
            $this['displayedForm']->setDefaults($this->sess->displayed);
        }
    }

    /**
     * ACL name='Formulář pro filtrovani preplanovani'
     */
    public function createComponentDisplayedForm()
    {
        $form = new Form();

        $workers = $this->em->getWorkerRepository()->findBy(['active' => 1]);
        foreach ($workers as $worker) {
            $form->addCheckbox('displayed'.$worker->id, $worker->name . ' ' . $worker->surname)
                ->setDefaultValue(1);
        } // :o:  close();

        $form->onSuccess[] = [$this, 'successDisplayedForm'];
        return $form;
    }

    public function successDisplayedForm($form, $values)
    {
        $displayedVal = $form->getValues(TRUE);

        $this->sess->displayed = $displayedVal;

        $this->redirect('DailyPlan:default');
        return;
    }

}
