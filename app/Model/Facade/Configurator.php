<?php

namespace App\Model\Facade;

use App\Model\Database\Entity\Configurator as ConfiguratorEnt;
use App\Model\Database\Entity\ConfiguratorNode;
use App\Model\Database\Entity\ConfiguratorInput;
use App\Model\Database\Entity\ConfiguratorNodeProduct;
use App\Model\Database\Entity\ConfiguratorNodeRelation;
use App\Model\Database\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Database\Explorer;
use Nette\Http\Session;
use Nette\Http\SessionSection; 

class Configurator
{

    /** @var EntityManager */
    private EntityManager $em;

    /** @var Explorer */
    protected $db;

    /** @var Session */
    public $session;

    /** @var SessionSection */
    public $sess;

    private $pathNodes = [];

    /**
     * Construct
     * @param EntityManager $em
     * @param Explorer $db
     */
    public function __construct(EntityManager $em, Explorer $db, Session $session)
    {
        $this->em = $em;
        $this->db = $db;
        $this->session = $session;
        $this->sess = $this->session->getSection('front');
    }

    /**
     * Prepares array for configurator and saves to sess
     *
     * @param ConfiguratorEnt $conf
     * @return void
     */
    public function prepareConfData($conf)
    {
        if (!isset($this->sess->confData)) {

            $this->sess->set('confData',
                [
                    'message' => '',
                    'name' => '',
                    'surname' => '',
                    'email' => '',
                    'phone' => '',
                    'company' => '',
                    'ico' => '',
                    'installCity' => '',
                    'installZip' => '',
                    'confCount' => 0,
                    'familyHouse' => 0
                ],
                '3 hours'  
            );
        }

        if (!isset($this->sess->confData[$conf->id])) {
            $this->sess->confData['confCount']++;
        }

        /*$startNodes = $this->em->getConfiguratorNodeRepository()->createQueryBuilder('n')
            ->leftJoin('n.parent', 'p')
            ->where('p.id IS NULL AND n.configurator = :conf')
            ->setParameter('conf', $conf)
            ->getQuery()->getResult();
        */


        $this->sess->confData[$conf->id] = [
            'inputs' => [], // [id => [items, value, active]]
            'products' => [],
            'path' => [],
            'salesman' => 0, // 1+ to create inquiry that needs salesman to react & create offer manually 
            'filled' => 0, // 1 when end of form is reached
        ];

        $this->processToNextInputNode($conf->startNode, $conf->id);
        return $this->sess->confData[$conf->id];
    }

    
    /**
     * Next node holds chosen value of parent input 
     */
    public function updateConfigurator($inputId, $nextNodeId, $confId)
    {
        // check conf and sess
        $conf = $this->em->getConfiguratorRepository()->find($confId);
        if (!$this->sess->confData || !$conf) {
            return null;
        }

        // find last input node by id, nextNode should be an option value of the input, also it's child
        $lastInputNode = $this->findLastInputNodeInPath($inputId, $confId);
        // if last input node is not last node in path do reverse process
        if ($lastInputNode->id != end($this->sess->confData[$confId]['path'])) {
            $lastInputNode = $this->reverseToNode($lastInputNode, $confId);
        }

        // if empty value on imput has been selected -> do nothing until change
        if ($nextNodeId === '') {
            $this->sess->confData[$confId]['inputs'][$inputId]['value'] = '';
            return null;
        }
        $nextNode = $this->em->getConfiguratorNodeRepository()->find($nextNodeId);
        if (!$nextNode) {
            // tree already finished before
            return null;
        }
        // save last inputs value and process to next input
        $this->sess->confData[$confId]['inputs'][$inputId]['value'] = $nextNode->id;
        $nextNode = $this->processToNextInputNode($nextNode, $confId);

        if (!$nextNode) {
            // tree just finished
            $this->sess->confData[$confId]['filled'] = 1;
            return null;
        }

        return $nextNode;
    }

    /**
     * Process to next input node
     * 
     * conf process
     *    -> draw inputs
     *    -> go to first node -> add products
     *    -> while no end or idk value to salesma
     *        -> while next non input node
     *            -> next node -> add products
     *        -> allow input field AND scan child nodes and set valuea as options (add idk option?)
     *        -> detect onchange and ajax send input and value
     *        -> based on change value save selected node product
     *
     *    - add option to add not selected to inputs meaning allow all fields but always send to salesman      
     *
     * @param ConfigutatorNode
     * @param integer $confId
     * @return void
     */
    public function processToNextInputNode($node, $confId)
    {
        do {
            $this->sess->confData[$confId]['path'][] = $node->id;

            // add products if any
            if ($node->products && count($node->products)) {
                foreach ($node->products as $np) {
                    $this->sess->confData[$confId]['products'][$np->product->id] = [
                        'id' => $np->product->id,
                        'count' => $np->count
                    ];
                }
            }

            // for salesman
            if ($node->forSalesman) {
                $this->sess->confData[$confId]['salesman']++;
            }

            // prepare data and return input node 
            if ($node->input) {
                $this->sess->confData[$confId]['inputs'][$node->input->id]['active'] = 1;
                if (isset($this->sess->confData[$confId]['inputs'][$node->input->id]['items'])) {
                    unset($this->sess->confData[$confId]['inputs'][$node->input->id]['items']);
                }
                $this->sess->confData[$confId]['inputs'][$node->input->id]['items'] = $this->getInputValues($node); 
                $this->sess->confData[$confId]['inputs'][$node->input->id]['value'] = '';
                return $node;
            }

        } while ($node = $this->getNextNode($node));
        return null;
    }

    public function getNextNodeNo($configurator)
    {
        $res = $this->em->getConfiguratorNodeRepository()->createQueryBuilder('n')
            ->select('max(n.nodeNo) as max')
            ->where('n.configurator = :configurator')
            ->setParameter('configurator', $configurator)
            ->getQuery()->getOneOrNullResult();
        if ($res) {
            return $res['max'] + 1;
        } else {
            return 0;
        }
    }

    public function findLastInputNodeInPath($inputId, $confId) {
        $pathNodes = $this->getPathNodes($confId);
        for ($i = count($pathNodes) - 1; $i >= 0; $i--) {
            $node = $pathNodes[$i];
            if (!$node->input || $node->input->id != $inputId) {
                continue;
            }
            return $node;
        }
        return null;
    }

    public function reverseToNode($targetNode, $confId)
    {
        $this->sess->confData[$confId]['filled'] = 0;
        bdump('', 'reversing');

        while ($lastNodeId = array_pop($this->sess->confData[$confId]['path'])) {
            
            bdump('last ' . $lastNodeId . ' x target ' . $targetNode->id);
            if ($lastNodeId === $targetNode->id) {
                bdump('returned' . $targetNode->id);
                $this->sess->confData[$confId]['path'][] = $lastNodeId;
                return $targetNode;
            }
            $node = $this->em->getConfiguratorNodeRepository()->find($lastNodeId);

            // remove salesman if set
            if ($node->forSalesman) {
                $this->sess->confData[$confId]['salesman']--;
            }

            // remove products if any
            if ($node->products && count($node->products)) {
                foreach ($node->products as $np) {

                    if (isset($this->sess->confData[$confId]['products'][$np->product->id])) {

                        if ($this->sess->confData[$confId]['products'][$np->product->id]['count'] > $np->count) {
                            $this->sess->confData[$confId]['products'][$np->product->id]['count']--;
                        } else {
                            unset($this->sess->confData[$confId]['products'][$np->product->id]);
                        }
                    }
                }
            }

            // remove input items and value
            if ($node->input) {
                
                $this->sess->confData[$confId]['inputs'][$node->input->id]['active'] = 0;
                if (isset($this->sess->confData[$confId]['inputs'][$node->input->id])) {
                    unset($this->sess->confData[$confId]['inputs'][$node->input->id]);
                }
            }
        }

        if (!$this->sess->confData[$confId]['path']) {
            return null;
        }
        return $node;
    }

    /**
     * Get next node assuming non input nodes has only one child node
     *
     * @param ConfigutatorNode $node
     * @return void
     */
    public function getNextNode($node)
    {
        if ($node->childs && count($node->childs)) {
            return $node->childs[0]->child;
        }
        return null;
    }

    /**
     * Validates if next node is actually child of the parent node
     *
     * @param ConfiguratorNode $parentNode
     * @param integer $childNodeId
     * @param integer $confId
     * @return void
     */
    public function getChildNodeByParentAndId($parentNode, $childNodeId, $confId)
    {
        if (!$parentNode->childs) {
            return null;
        }

        $rel = $this->em->getConfiguratorNodeRelationRepository()->findOneBy(['parent' => $parentNode, 'child' => $childNodeId]);

        if ($rel && $rel->child) {
            return $rel->child;
        }
        return null;
    }

    public function getNodeById($id) {
        return $this->em->getConfiguratorNodeRepository()->find($id);
    }

    /**
     * Converts confData path ids to nodes
     *
     * @param integer $confId
     * @param boolean $forceRefresh
     * @return array
     */
    public function getPathNodes($confId, $forceRefresh = false)
    {
        if ($this->pathNodes && !$forceRefresh) {
            return $this->pathNodes;
        }
        $orderByStr = 'FIELD(n.id, ' . implode(', ', $this->sess->confData[$confId]['path']) . ')';
        $this->pathNodes = $this->em->getConfiguratorNodeRepository()->createQueryBuilder('n')
            ->where('n.id IN ' . '(' . implode(', ', $this->sess->confData[$confId]['path']) . ')')
            ->orderBy($orderByStr, '')
            ->getQuery()->getResult();
        return $this->pathNodes;
    }

    /**
     * Prepare input items array by node
     *
     * @param ConfigutatorNode $node
     * @return void
     */
    public function getInputValues($node)
    {
        $items = [];
        if ($node->childs) {
            foreach($node->childs as $childRel) {
                $child = $childRel->child;
                if (!$child->value) {
                    continue;
                }
                $items[$child->id] = $child->value;
            }
            asort($items);
        }
        return $items;
    }

    public function addNodeProduct($nodeId, $productId, $count = 1)
    {
        $product = $this->em->getProductRepository()->find($productId);
        $node = $this->em->getconfiguratorNodeRepository()->find($nodeId);
        if (!$node || !$product) {
            return null;
        }

        $nodeProduct = new ConfiguratorNodeProduct();
        $nodeProduct->setNode($node);
        $nodeProduct->setProduct($product);
        $nodeProduct->setCount($count);
        $this->em->persist($nodeProduct);
        $this->em->flush();
        return $nodeProduct;
    }

    public function removeNodeProduct($nodeProductId)
    {
        $nodeProduct = $this->em->getConfiguratorNodeProductRepository()->find($nodeProductId);
        if (!$nodeProduct) {
            return false;
        }
        $this->em->remove($nodeProduct);
        $this->em->flush();
        return true;
    }

    public function copyBranch($currNode, $targetConf = null)
    {
        $processedClones = [];
        if (!$targetConf) {
            $targetConf = $currNode->configurator;;
        }
        return $this->copyBranchRecursive($currNode, $targetConf, $processedClones);
    }

    /**
     * Recursive deep cloning of conf. nodes
     * @param ConfiguratorNode $currNode
     * @param array $processedClones To evade processing already processed when multiple parent rel.
     * @return ConfiguratorNode
     */
    public function copyBranchRecursive($currNode, $targetConf, &$processedClones, &$inputClones = null)
    {
        $currClone = clone $currNode;
        $currClone->setId(null);
        if ($currNode->input && $inputClones && isset($inputClones[$currNode->input->id])) {
            $currClone->setInput($inputClones[$currNode->input->id]);
        }
        $currClone->setParents(null);
        $currClone->setChilds(null);
        $currClone->setProducts(null);
        $currClone->setConfigurator($targetConf);
        $currClone->setNodeNo($this->getNextNodeNo($targetConf));
        $this->em->persist($currClone);
        $this->em->flush();

        if ($currNode->products) {
            foreach($currNode->products as $np) {
                $npClone = clone $np;
                $npClone->setNode($currClone);
                $this->em->persist($npClone);
            }
            $this->em->flush();
        }

        if ($currNode->childs) {
            foreach($currNode->childs as $nr) {
                if (!array_key_exists($nr->child->id, $processedClones)) {
                    $childClone = $this->copyBranchRecursive($nr->child, $targetConf, $processedClones, $inputClones);
                    $processedClones[$nr->child->id] = $childClone;
                } else {
                    $childClone = $processedClones[$nr->child->id];
                }

                $nrClone = new ConfiguratorNodeRelation();
                $nrClone->setParent($currClone);
                $nrClone->setChild($childClone);
                $this->em->persist($nrClone);
            }
            $this->em->flush();
        }
     
        return $currClone;
    }

    public function copyConfigurator($conf) {
        $confClone = clone $conf;
        $confClone->setName($conf->name . ' - kopie');
        $confClone->setId(null);
        $confClone->setNodes(null);
        $confClone->setInputs(null);
        $confClone->setStartNode(null);
        $this->em->persist($confClone);
        $this->em->flush();

        $inputClones = [];
        if ($conf->inputs) {
            foreach ($conf->inputs as $in) {
                $inClone = clone $in;
                $inClone->setConfigurator($confClone);
                $inClone->setNodes(null);
                $this->em->persist($inClone);
                $inputClones[$in->id] = $inClone;
            }
            $this->em->flush();
        }

        $startNodeClone = $this->copyBranch($conf->startNode, $confClone, $inputClones);

        $confClone->setStartNode($startNodeClone);
        $this->em->flush();
        return $confClone;
    }

}