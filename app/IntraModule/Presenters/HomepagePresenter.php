<?php
declare(strict_types=1);

namespace App\IntraModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Security\Passwords;

class HomepagePresenter extends BasePresenter
{
    /** @var Passwords @inject */
    public Passwords $passwords;

    public function renderDefault()
    {
        /*$futureWorkerTender = $this->em->createQueryBuilder()
            ->select("wt")
            ->from('App\Model\Database\Entity\WorkerTender', "wt")
            ->leftJoin('App\Model\Database\Entity\WorkerInWorkerTender', 'wiwt', 'WITH', 'wiwt.tender = wt')
            ->leftJoin('App\Model\Database\Entity\Worker', 'w', 'WITH', 'wiwt.worker = w')
            ->leftJoin('App\Model\Database\Entity\User', 'u', 'WITH', 'u = w.user')
            ->where("wt.tenderDate >= '".date('Y-m-d')." 00:00:00'");
        if (!in_array($this->usrGrp, [1])) { //jen svoje pro skupinu zaměstnanci
            $futureWorkerTender = $futureWorkerTender->andWhere('u.id = '.$this->user->getId());
        }
        $futureWorkerTender = $futureWorkerTender->orderBy('wt.tenderDate')
            ->groupBy('wt')
            ->setMaxResults(20);
        $futureWorkerTender = $futureWorkerTender->getQuery()
            ->getResult();
        $this->template->cardFutureWorkerTender = $futureWorkerTender;*/
    }

    public function createComponentPassForm()
    {
        $that = $this;

        $form = new Form();
        $form->addPassword('oldPass', 'Současné heslo')
            ->setHtmlAttribute('class', 'form-control');
        $form->addPassword('newPass', 'Nové heslo')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::PATTERN, 'Heslo musí být minimálně 8 znaků a obsahovat písmeno a číslo', '^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$');
        $form->addPassword('newPassCheck', 'Zopakujte nové heslo')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::EQUAL, 'Hesla musí být stejná', $form['newPass']);
        $form->addSubmit('send', 'Změnit heslo')
            ->setHtmlAttribute('class', 'btn btn-primary ladda-button');
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();
            if (isset($values2['send'])) {
                $user = $that->em->getUserRepository()->find($that->user->id);
                if($that->passwords->verify($values['oldPass'], $user->getPasswordHash())) {
                    $newPass = $that->passwords->hash($values['newPass']);
                    $user->setPassword($newPass);
                    $that->em->flush();
                    $that->flashMessage('Změna hesla proběhla úspěšně!', 'success');
                    $that->redirect(':Homepage:default');
                } else {
                    $that->flashMessage('Současné heslo bylo nesprávné!', 'warning');
                    $that->redirect('this');
                }
            }
        };

        return $form;
    }

    /**
     * ACL name='Přehled potávek na dashboard'
     */
    public function createComponentInquiryTable() {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Inquiry::class, get_class(), __FUNCTION__);
        $this->gridGen->setClicableRows($grid, $this, 'Inquiry:edit');

        // datasource
        $this->gridGen->getDataSource()
            ->leftJoin('a.offer' , 'offer')
            ->andWhere('a.needsSalesman = 1 AND offer.id IS NULL');

        // actions
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Inquiry:edit', ['id' => 'id']);
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();

        // columns
        $grid->getColumn('needsSalesman')->setDefaultHide(false);

        $grid->setColumnsOrder(['id', 'configurator', 'customer', 'customerAuto', 'needsSalesman', 'installCity', 'installZip', 'forFamilyHouse', 'updatedAt', 'createdAt']);
        
        return $grid;
    }
}