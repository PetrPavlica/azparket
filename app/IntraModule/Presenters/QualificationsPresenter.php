<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Document;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\Qualification;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;

class QualificationsPresenter extends BasePresenter
{
    /**
     * ACL name='Správa kvalifikace'
     * ACL rejection='Nemáte přístup ke správě kvalifikace.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání nové kvalifikace'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getQualificationRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Kvalifikace nebyla nalezena.', 'error');
                $this->redirect('Qualifications:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem kvalifikace'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Qualification::class, get_class(), __FUNCTION__);

        $userData = $this->getUser()->getIdentity()->getData();
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Qualifications:edit', 'id', 0, function($item) use($userData) {
            return ($item->user && $item->user->id == $userData['id']) || $userData['qualificationEdit'];
        });
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        try {
            if (!$this->getUser()->getIdentity()->getData()['qualificationViewEffective']) {
                $grid->removeColumn('efficiency');
            }
        } catch (\Exception $ex) {}

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Qualifications:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $grid->allowRowsAction('edit', function($item) use ($userData): bool {
            return $item->user->id == $userData['id'] || $userData['qualificationEdit'];
        });

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit kvalifikace'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Qualification::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit kvalifikace', 'success'], ['Nepodařilo se uložit kvalifikaci!', 'error']);
        $form->setRedirect('Qualifications:');

        $form->onSuccess[] = function(Form $form, $values): void {
            $values2 = $this->request->getPost();

            if (empty($values->id)) {
                $values['user'] = $this->em->getUserRepository()->find($this->getUser()->getId());
            }

            $evalutionDate = null;
            if ($values->evalutionDate) {
                $evalutionDate = date_create_from_format('j. n. Y', $values->evalutionDate);
            }

            $values['efficiency'] = 0;
            if ($evalutionDate && $evalutionDate->format('Y') > 2006 && $values->typeOfAction == 2) {
                $sumRate = $sum = 0;

                if ($values->professionalLevel != 0) {
                    $sumRate++;
                    $sum += $values->professionalLevel;
                }
                if ($values->organisationSupport != 0) {
                    $sumRate++;
                    $sum += $values->organisationSupport;
                }
                if ($values->range != 0) {
                    $sumRate++;
                    $sum += $values->range;
                }
                if ($values->newMethods != 0) {
                    $sumRate++;
                    $sum += $values->newMethods;
                }
                if ($values->safety != 0) {
                    $sumRate++;
                    $sum += $values->safety;
                }
                if ($values->timeSavings != 0) {
                    $sumRate++;
                    $sum += $values->timeSavings;
                }
                if ($values->qualityOfWork != 0) {
                    $sumRate++;
                    $sum += $values->qualityOfWork;
                }

                $values['efficiency'] = round(($sum / $sumRate), 2);
            }

            $entity = $this->formGenerator->processForm($form, $values, true);

            if (isset($values2['send'])) {
                $this->redirect('Qualifications:');
            } else {
                $this->redirect('Qualifications:edit', ['id' => $entity->id]);
            }
        };

        return $form;
    }
}