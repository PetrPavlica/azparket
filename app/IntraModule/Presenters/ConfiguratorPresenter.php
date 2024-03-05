<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\ACLForm;
use App\Model\Database\Entity\Configurator;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Utils\DateTime;
use App\Components\Modals\IEditConfNodeModalControlFactory;
use App\Model\Database\Entity\ConfiguratorInput;
use App\Model\Database\Entity\ConfiguratorNode;
use Exception;
use Mpdf\Tag\B;
use Nette\Utils\Strings;
use App\Model\Facade\Configurator as ConfFac;

class ConfiguratorPresenter extends BasePresenter
{

    /** @var IEditConfNodeModalControlFactory @inject */
    public $editConfNodeFac;
    
    /** @var ConfFac @inject */
    public $confFac;

    /**
     * ACL name='Správa konfigurátorů - sekce'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id) {
        if ($id) {
            
            $entity = $this->em->getConfiguratorRepository()->find($id);

            if (!$entity) {
                $this->flashMessage('Konfigurátor se nepodařilo nalézt.', 'error');
                $this->redirect('Configurator:');
            }
            $this->template->entity = $entity;
            $this['form']->setDefaults($this->ed->get($entity));

            
            // load configurator inputs
            $this->template->inputs = $inputs = $this->em->getConfiguratorInputRepository()->findBy(['configurator' => $entity], ['orderInput' => 'ASC']);
            // load nodes - w/ parents hydrate
            $nodes = $this->em->getConfiguratorNodeRepository()->createQueryBuilder('n')
                ->select('n, np')
                ->leftJoin('n.parents', 'np')
                ->where('n.configurator = :configurator')
                ->setParameter('configurator', $entity)
                ->getQuery()->getResult();
            $this->template->nodes = $nodes;

        } else {

        }

    }

    /**
     * ACL name='Tabulka konfgurátorů'
     */
    public function createComponentTable() {
        $grid = $this->gridGen->generateGridByAnnotation(Configurator::class, get_class(), __FUNCTION__);
        $this->gridGen->setClicableRows($grid, $this, 'Configurator:edit');

        // actions
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Configurator:edit', ['id' => 'id']);
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();

        $grid->addGroupAction('Vytvořit kopii')->onSelect[] = [$this, 'copyConfigurators'];

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit konfigurátoru'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Configurator::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Konfigurátor se podařilo uložit', 'success'], ['Konfigurátor se nepodařilo uložit!', 'warning']);
        //$form->setRedirect('Configurator:default');
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formSuccess'];
        return $form;
    }

    public function formSuccess($form, $values)
    {
        //$values2 = $this->request->getPost();

        $new = !$values->id;
        
        $entity = $this->formGenerator->processForm($form, $values, true);
        
        if ($entity && ($new || !$entity->startNode)) {
            $startNode = new ConfiguratorNode();
            $startNode->setName('Kořen');
            $startNode->setNodeNo(0);
            $startNode->setConfigurator($entity);
            $this->em->persist($startNode);

            $entity->setStartNode($startNode);
            $this->em->flush();
        }

        if ($entity) {
            $this->redirect('Configurator:edit', ['id' => $entity->id]);
        }
    }
    
    protected function createComponentEditConfNodeModal()
    {
        return $this->editConfNodeFac->create();
    }

    public function copyConfigurators($ids) {
        foreach ($ids as $confId) {
            $conf = $this->em->getConfiguratorRepository()->find($confId);
            $confClone = $this->confFac->copyConfigurator($conf);
        }
        if (count($ids) == 1) {
            $this->redirect('Configurator:edit', ['id' => $confClone->id]);
        }
    }
    
    public function handleAddInput() {
        $configurator = $this->em->getConfiguratorRepository()->find($this->getParameter('id'));
        if (!$configurator) {
            $this->flashMessage('Nepodařilo se přidat pole. Uložte formulář a zkuste to znovu.', 'error');
            return;
        }
        $res = $this->em->getConfiguratorInputRepository()->createQueryBuilder('i')
            ->select('max(i.orderInput) as count')
            ->where('i.configurator = :configurator')
            ->setParameter('configurator', $configurator)
            ->getQuery()->getOneOrNullResult();
        if ($res) {
            $count = $res['count'] + 1;
        } else {
            $count = 1;
        }
        $input = new ConfiguratorInput();
        
        $input->setConfigurator($configurator);
        $input->setOrderInput($count);
        $this->em->persist($input);
        $this->em->flush();
        if ($this->isAjax()) {
            $this->redrawControl('conf-inputs');
        }
    }

    public function handleRemoveInput($inputId) {
        $input = $this->em->getConfiguratorInputRepository()->find($inputId);
        if (!$input) {
            $this->flashMessage('Nepodařilo se odstranit pole', 'error');
        } else {
            $this->em->remove($input);
            try {
                $this->em->flush();
            } catch (Exception $e) {
                $this->flashMessage('Nepodařilo se odstranit pole. Nejspíše se využívá', 'error');
            }
        }
        if ($this->isAjax()) {
            $this->redrawControl('conf-inputs');
        }
    }

    public function handleUpdateInput() {
        $values = $this->request->getPost();

        if (isset($values['inputId']) && (isset($values['name']) || isset($values['description']) || isset($values['order']))) {
            $input = $this->em->getConfiguratorInputRepository()->find($values['inputId']);
            if (!$input) {
                $this->flashMessage('Nepodařilo se aktualizovat pole', 'error');
            } else {
                if (isset($values['name'])) {
                    $input->setName($values['name']);
                    $input->setWebName(Strings::webalize($values['name']));
                } else if (isset($values['description'])) {
                    $input->setDescription($values['description']);
                } else if (isset($values['order'])) {
                    if (empty($values['order'])) {
                        $this->flashMessage('Pole řada nelze zanechat prázdné', 'error');
                        return;
                    }
                    $input->setOrderInput($values['order']);
                }
                $this->em->flush();
                $this->flashMessage('Pole aktualizováno', 'info');
            }
        } else {
            $this->flashMessage('Nepodařilo se aktualizovat pole', 'error');
        }
        if (!$this->isAjax()) {
            $this->redirect('this');
        } else {
            $this->redrawControl('conf-inputs');
        }
    }

    public function redrawGraph() {
        $this->redrawControl('graphMermaidSnipp');
        $this->redrawControl('ajax-handle');
    }
}