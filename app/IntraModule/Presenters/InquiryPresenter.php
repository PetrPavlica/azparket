<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\Inquiry;
use App\Model\ACLForm;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Utils\DateTime;
use Exception;
use Nette\Utils\Strings;
use App\Model\Facade\Offer as OfferFac;
use Mpdf\Tag\B;

class InquiryPresenter extends BasePresenter
{

    /** @var OfferFac @inject */
    public $offerFac;

    private $inquiry;

    /**
     * ACL name='Správa poptávek - sekce'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id) {
        if ($id) {
            
            $entity = $this->em->getInquiryRepository()->find($id);

            if (!$entity) {
                $this->flashMessage('Poptávku se nepodařilo nalézt.', 'error');
                $this->redirect('Inquiry:');
            }
            $this->inquiry = $entity;
            $this->template->entity = $entity;
            $this['form']->setDefaults($this->ed->get($entity));

        } else {

        }

    }

    /**
     * ACL name='Tabulka poptávek'
     */
    public function createComponentTable() {
        $grid = $this->gridGen->generateGridByAnnotation(Inquiry::class, get_class(), __FUNCTION__);
        $this->gridGen->setClicableRows($grid, $this, 'Inquiry:edit');

        // actions
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Inquiry:edit', ['id' => 'id']);
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        //$this->gridGen->addButtonDeleteCallback();

        // columns
        $grid->getColumn('createdAt')->setDefaultHide(false);

        $grid->setColumnsOrder(['id', 'configurator', 'customer', 'customerAuto', 'needsSalesman', 'installCity', 'installZip', 'forFamilyHouse', 'updatedAt', 'createdAt']);
        
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit produktu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Inquiry::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Poptávku se podařilo uložit', 'success'], ['Poptávku se nepodařilo uložit!', 'warning']);
        //$form->setRedirect('Inquiry:default');

        $myComps = [];
        
        $myComps['createdAtComp'] = $form->addText('createdAt', 'Datum poptávky')
            ->setDisabled()
            ->setOmitted()
            ->setHtmlAttribute('class', 'form-control');
        
        $myComps['confComp'] = $form->addText('conf', 'Konfigurátor')
            ->setDisabled()
            ->setOmitted()
            ->setHtmlAttribute('class', 'form-control');

        if ($this->inquiry) {
            $myComps['createdAtComp']->setValue($this->inquiry->createdAt->format('j. n. Y'));
        
            if ($this->inquiry->configurator) {
                $myComps['confComp']->setValue($this->inquiry->configurator->name);
            }
        }

        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formSuccess'];
        return $form;
    }

    public function formSuccess($form, $values)
    {
        //$values2 = $this->request->getPost();

        $new = !$values->id;
        
        $entity = $this->formGenerator->processForm($form, $values, true);
        

        if (isset($values2['send'])) {
            $this->redirect('Inquiry:default');
        } elseif (isset($values2['sendSave'])) {
            $this->redirect('Inquiry:edit', ['id' => $entity->id]);
        }
    }

    public function handleCreateOffer($id)
    {
        if ($offer = $this->offerFac->createOfferFromInquiry($id, 0, $this->user->getId())) {
            $this->flashMessage('Nabídku se úspěšně podařilo založit', 'success');
            $this->redirect('Offer:edit', ['id' => $offer->id]);
        } else {
            $this->flashMessage('Při vytváření nabídky došlo k chybě', 'error');
        }
    }

}