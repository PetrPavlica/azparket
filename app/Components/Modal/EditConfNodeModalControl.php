<?php

namespace App\Components\Modals;

use App\Model\ACLForm;
use App\Components\FormRenderer\IFormRendererFactory;
use App\Model\DoctrineFormGenerator;
use App\Model\Facade\Configurator as ConfFac;
use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Control;
use App\Model\Database\EntityManager;
use App\Model\Database\Entity\ConfiguratorNode;
use App\Model\Database\Utils\EntityData;

class EditConfNodeModalControl extends Control
{

    /** @var DoctrineFormGenerator */
    public $formGenerator;

    /** @var IFormRendererFactory */
    public $formRenderFactory;

    /** @var EntityManager @inject */
    public $em;

    /** @var EntityData @inject */
    public $ed;

    /** @var ConfFac @inject */
    public $confFac;

    private $parentsItemsSet = false;
    private $inputItemsSet = false;

    public function __construct(
        DoctrineFormGenerator $formGenerator,
        IFormRendererFactory $formRenderer,
        EntityManager $em,
        EntityData $ed,
        ConfFac $confFac
    ) {
        $this->formGenerator = $formGenerator;
        $this->formRenderFactory = $formRenderer;
        $this->em = $em;
        $this->ed = $ed;
        $this->confFac = $confFac;
    }

    /**
     * Render component for rendering form in specific style
     */
    public function createComponentRenderer()
    {
        return $this->formRenderFactory->create();
    }

    public function render()
    {
        $t = $this->template;
        $t->setFile(__DIR__ . '/templates/editConfNode.latte');
        $t->form = $this['confNodeForm'];
        $t->render();
    }

    /**
     * ACL name='Formulář pro přidání uzlu'
     */
    public function createComponentConfNodeForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ConfiguratorNode::class, $this->presenter->user, $this, __FUNCTION__);
        $form->setMessages(['Uzel se podařilo uložit', 'success'], ['Uzel se nepodařilo uložit!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'successForm'];

        if (!$this->parentsItemsSet) {
            $form->getComponent('parents')->setItems($this->getParentsItems());
            $this->parentsItemsSet = true;
        }
        
        if (!$this->inputItemsSet) {
            $form->getComponent('input')->setItems($this->getInputItems());
            $this->inputItemsSet = true;
        }
        
        return $form;
    }

    public function successForm($form, $values)
    {
        $values2 = $this->presenter->request->getPost();
        
        $configurator = $this->em->getConfiguratorRepository()->find($this->presenter->getParameter('id'));
        if (!$configurator) {
            $this->presenter->flashMessage('Konfigurátor nenalezen. Obnovte kartu.', 'error');
            return;
        }
        
        if (isset($values2['nodeRemove'])) {
            // remove node
            if ($configurator->startNode && $values->id == $configurator->startNode->id) {
                $this->flashMessage('Kořenový uzel nemůžete odstranit, začíná zde proces konfigurátoru', 'error');
                return;
            }
            $node = $this->em->getConfiguratorNodeRepository()->find($values->id);
            if (!$node) {
                $this->presenter->flashMessage('Uzel nenalezen. Obnovte kartu.', 'error');
                return;
            }
            $this->em->remove($node);
            $this->em->flush();
            $this->presenter->flashMessage('Uzel byl odstraněn', 'success');
        } else {
            // classic process form
            $values->configurator = $configurator;
            $node = $this->formGenerator->processForm($form, $values, true);
        }

        if ($node) {
            // copy node after save
            if (isset($values2['nodeCopy'])) {
                $formVals = $this->ed->get($node);
                $formVals['id'] = '';
                $formVals['nodeNo'] = $this->confFac->getNextNodeNo($configurator);
                $this['confNodeForm']->setValues($formVals);
                $this->presenter->flashMessage('Uzel duplikován');

            } else if (isset($values2['nodeCopyBranch'])) {
                // copy whole branch
                $node = $this->confFac->copyBranch($node);
                
                $this->template->node = $node;
                $this['confNodeForm']->setValues(['id' => $node->id]);
                $this->presenter->flashMessage('Větev duplikována');

            } else {
                $this->template->node = $node;
                $this['confNodeForm']->setValues(['id' => $node->id]);
            }

        } else {

        }

        
        
        if ($this->presenter->isAjax()) {
            $this->redrawNodeFormAndGraph();
        } else {
            $this->presenter->redirect('this');
        }
    }

    public function handleEditConfNode()
    {
        $values = $this->presenter->request->getPost();
        $t = $this->template;
        if ($values[ 'id' ]) {
            preg_match_all('!\d+!', $values[ 'id' ], $matches);
            if (isset($matches[0][0])) {
                $nodeId = intval($matches[0][0]);
            } else {
                return;
            }

            $node = $this->em->getConfiguratorNodeRepository()->find($nodeId);
            $t->node = $node;
            
            // parents select items
            $this['confNodeForm']->getComponent('parents')->setItems($this->getParentsItems($nodeId));
            $this->parentsItemsSet = true;

            // input select items
            $inputCmp = $this['confNodeForm']->getComponent('input');
            $inputCmp->setItems($this->getInputItems());
            $this->inputItemsSet = true;
            
            $defArr = $this->ed->get($node);
            if ($defArr['input'] !== null && !in_array($defArr['input'], array_keys($inputCmp->getItems()))) {
                $defArr['input'] = null;
            }


            $this['confNodeForm']->setDefaults($defArr);
            //$this['confNodeForm']->setDefaults(['original' => $nodeId, 'parents' => $val]);
        } else {
            $configurator = $this->em->getConfiguratorRepository()->find($this->presenter->getParameter('id'));
            if (!$configurator) {
                $this->presenter->flashMessage('Nastala chyba', 'error');
                return;
            }
            $nodeNo = $this->confFac->getNextNodeNo($configurator);
            
            $this['confNodeForm']->setDefaults(['nodeNo' => $nodeNo, 'configurator' => $configurator->id]);
        }
        $this->redrawNodeForm();
    }

    /*public function updateConfInputItems($configuratorId) {
        $inputs = $this->em->getConfiguratorInputRepository()->findBy(['configurator' => $configuratorId]);
        $inputItems = [];
        foreach ($inputs as $in) {
            $inputItems[$in->id] = $in->name;
        }
        $this['form']->getComponent('input')->setItems($inputItems);

        $this->redrawNodeFormAndGraph();
    }*/

    public function handleAddProduct()
    {
        $presenter = $this->getPresenter();
        $values = $presenter->getRequest()->getPost();
        if (!$values['nodeId'] || !$values['productId'] || !$values['count']) {
            return;
        }

        if ($nodeProduct = $this->confFac->addNodeProduct($values['nodeId'], $values['productId'], $values['count'])) {
            if ($presenter->isAjax()) {
                $this->template->node = $nodeProduct->node;
                $this->redrawControl('editConfNodeSnipp');
                $this->redrawControl('node-body-2');
                $presenter->redrawGraph();
            }
        } else {
            $this->flashMessage('Produkt se nepodařilo přidat', 'error');
        }
        
    }

    public function handleRemoveProduct($nodeProductId, $nodeId) {
        $presenter = $this->getPresenter();
        if (!isset($nodeProductId)) {
            return;
        }
        if ($this->confFac->removeNodeProduct($nodeProductId)) {
            if ($presenter->isAjax()) {
                $node = $this->em->getConfiguratorNodeREpository()->find($nodeId);
                $this->template->node = $node;
                $this->redrawControl('editConfNodeSnipp');
                $this->redrawControl('node-body-2');
                $presenter->redrawGraph();
            }
        } else {
            $this->presenter->flashMessage('Produkt se nepodařilo ostranit', 'error');
        }
        
    }

    /**
     * Get products for autocomplete
     * @param string $term
     */
    public function handleGetProducts($term)
    {
        if (!$term) {
            $term = $_GET['term'];
        }
        $result = $this->em->getProductRepository()->getDataAutocompleteProducts($term);
        $this->getPresenter()->payload->autoComplete = json_encode($result);
        $this->getPresenter()->sendPayload();
    }

    public function redrawNodeForm() {
        $this->redrawControl('editConfNodeSnipp');
        $this->redrawControl('node-head');
        $this->redrawControl('node-body-1');
        $this->redrawControl('node-body-2');
        $this->redrawControl('node-footer');
    }

    public function redrawNodeFormAndGraph() {
        $this->redrawNodeForm();
        $this->getPresenter()->redrawGraph();
    }

    public function getParentsItems($excludeNodeId = null) {
        $qbParams = [];
        if ($this->getPresenter()->getParameter('id')) {
            $qb = $this->em->getConfiguratorNodeRepository()->createQueryBuilder('n')
            ->where('n.configurator = :confId');
            $qbParams = ['confId' => $this->getPresenter()->getParameter('id')];
        } else {
            return [];
        }
        if ($excludeNodeId) {
            $qb->andWhere('n.id != :excludeNodeId');
            $qbParams['excludeNodeId'] = $excludeNodeId;
        }
        $parents = $qb->setParameters($qbParams)->getQuery()->getResult();
        $parentsArr = [];
        foreach ($parents as $n) {
            $parentsArr[$n->id] = $n->nodeNo . '.' . ($n->name ? ' ' . $n->name : '') . ($n->input ? ' VP:' . $n->input->name : '')  . ($n->value ? ' H:' . $n->value : '');
        }
        
        return $parentsArr;
    }

    public function getInputItems() {
        $qbParams = [];
        if ($this->getPresenter()->getParameter('id')) {
            $inputs = $this->em->getConfiguratorInputRepository()->findBy(['configurator' => $this->getPresenter()->getParameter('id')]);
        } else {
            return [];
        }
        $inputArr = [];
        foreach ($inputs as $input) {
            $inputArr[$input->id] = $input->name;
        }
        
        return $inputArr;
    }
}