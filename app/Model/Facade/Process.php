<?php

namespace App\Model\Facade;

use App\Model\Database\Entity\Customer;
use App\Model\Database\Entity\ItemInProcess;
use App\Model\Database\Entity\ItemTypeInItem;
use App\Model\Database\Entity\ProcessState;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\SQLHelper;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class Process
{
    /** @var EntityManager */
    private EntityManager $em;

    /** @var SQLHelper */
    private $SQLHelper;

    public function __construct(EntityManager $em, SQLHelper $sql)
    {
        $this->em = $em;
        $this->SQLHelper = $sql;
    }

    public function getStateCount()
    {
        $qb = $this->em->getConnection()->prepare('
            SELECT process_state_id, COUNT(*) as count
            FROM process
            GROUP BY process_state_id
        ');

        $qb->execute();
        $result = $qb->fetchAllKeyValue();

        return $result;
    }

    /**
     * @param $id
     * @param $step integer +1 / -1
     */
    public function swapProcessState($id, $step, $userId)
    {
        $process = $this->em->getProcessRepository()->find($id);
        if ($process) {
            $z = $step == 1 ? '>' : '<';
            $y = $step == 1 ? 'ASC' : 'DESC';

            $rsm = new ResultSetMappingBuilder(
                $this->em, ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT
            );
            $rsm->addRootEntityFromClassMetadata(ProcessState::class, 'ps');
            $query = $this->em->createNativeQuery("
                SELECT " . $rsm->generateSelectClause() . "
                FROM process_state ps
                WHERE ps.order $z :stateOrder AND ps.active = 1
                order by ps.order $y
                LIMIT 1
                ", $rsm);
            $query->setparameters([
                'stateOrder' => $process->processState->order,
            ]);
            $newState = $query->getOneOrNullResult();

            if ($newState == null) {
                return false;
            }

            if ($this->manageSwapProcessState($newState, $process, $userId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Managuje přepínání stavů procesů s tím, že na to navěsí akce a nastaví datumy
     * @param $newState
     * @param $process
     */
    public function manageSwapProcessState($newState, $process, $userId, $oldState = null)
    {
        if (is_numeric($newState)) {
            $newState = $this->em->getProcessStateRepository()->find($newState);
        }
        if (is_numeric($process)) {
            $process = $this->em->getProcessRepository()->find($process);
        }

        if (!$process) {
            return false;
        }

        if ($oldState == null) {
            $oldState = $process->processState;
        }

        $process->setInStateDate(new DateTime());
        $process->setNoticeDate(null);
        $process->setProcessState($newState);

        $this->em->flush($process);
        return true;
    }

    /**
     * Get specific customer for autocomplete
     * @param $customer
     * @return string customer
     */
    public function getSpecificCustomer($item)
    {
        if (!$item)
            return NULL;
        $res = "";

        if ($item->company && $item->company != '')
            $res = $item->company . ", ";
        $res .= $item->name . ' ' . $item->surname . ', ' . $item->email;

        return $res;
    }

    /**
     * Prepare empty product in process for basket
     * @return array
     */
    public function getEmptyArrayFormProces($arr = NULL, $index = 0, $resetProduct = false) {
        if (!$arr) {
            $arr = [];
        } else if (count($arr) && $resetProduct === true) {
            unset($arr['idProduct']);
            unset($arr['itemIdentification']);
            unset($arr['itemDescription']);
        }
        $arr['idProduct'][] = '';
        $arr['itemIdentification'][] = '';
        $arr['itemDescription'][] = '';
        $arr['index'][] = $index;

        return $arr;
    }

    /**
     * Get data for autocomplete whit customers
     * @param string $term search items
     * @return array results
     */
    public function getDataAutocompleteCustomers($term, $where = null)
    {
        $columns = ['company', 'name', 'email', 'idNo', 'city', 'street'];
        $alias = 'p';

        $qb = $this->em->getRepository(Customer::class)
            ->createQueryBuilder($alias)
            ->setMaxResults('20');
        if ($where) {
            $qb->where($alias . '.' . $where);
        }
        $qb = $this->SQLHelper->termToLikeQB($qb, $term, $alias, $columns);
        $result = $qb->getQuery()->getResult();

        $arr = [];
        if ($result) {
            foreach ($result as $item) {
                $company = "";
                if ($item->company && $item->company != '')
                    $company = $item->company . ", ";
                $arr[$item->id] = $company . $item->name . ' ' . $item->surname . ', ' . $item->email;
            }
        }
        return $arr;
    }

    public function generateNumberBp($process) {
        // Prepare next process number:
        $nEntity = $this->em->getSettingRepository()->findOneBy(['code' => 'process_number']);
        $number = explode('{', $nEntity->value);
        $number[1] = substr($number[1], 0, -1);
        $number[2] = strlen($number[1]);
        $number[1] = intval($number[1]) + 1;
        $number[2] = $number[2] - strlen($number[1]);

        $numberString = "";
        for ($i = 0; $i < $number[2]; $i++) {
            $numberString .= "0";
        }

        // Update order number - settings and new order
        $nEntity->setValue($number[0] . "{" . $numberString . $number[1] . "}");
        $process->setBpNumber($number[0] . $numberString . $number[1]);
        $this->em->flush($nEntity);
        $this->em->flush($process);

        return $process;
    }

    public function saveItems(\App\Model\Database\Entity\Process $process, $data)
    {
        if (isset($data['items'])) {
            $itemsNotDel = [];
            foreach ($data['items'] as $k => $item) {
                $itemEnt = null;
                if (isset($item['id']) && $item['id']) {
                    $itemEnt = $this->em->getitemInProcessRepository()->find($item['id']);
                }
                if (!$itemEnt) {
                    $itemEnt = new ItemInProcess();
                    $this->em->persist($itemEnt);
                    $itemEnt->setProcess($process);
                }
                $itemEnt->setName($item['name']);
                $itemEnt->setDescription($item['description']);
                $this->em->flush($itemEnt);
                $itemsNotDel[] = $itemEnt->id;

                if (isset($item['itemTypes'])) {
                    $typesNotDel = [];
                    foreach ($item['itemTypes'] as $itemType) {
                        $itemTypeEnt = $this->em->getItemTypeInItemRepository()->findOneBy(['item' => $itemEnt, 'type' => $itemType]);
                        if (!$itemTypeEnt) {
                            $itemTypeEnt = new ItemTypeInItem();
                            $this->em->persist($itemTypeEnt);
                            $itemTypeEnt->setItem($itemEnt);
                            $itemTypeEnt->setType($this->em->getItemTypeRepository()->find($itemType));
                            $this->em->flush($itemTypeEnt);
                        }
                        $typesNotDel[] = $itemTypeEnt->id;
                    }
                    $parameters = [
                        'id' => $itemEnt->id
                    ];
                    if (count($typesNotDel)) {
                        $qb = $this->em->createQuery('DELETE ' . ItemTypeInItem::class . ' s WHERE s.item = :id and s.id not in(:ids)');
                        $parameters['ids'] = $typesNotDel;
                    } else {
                        $qb = $this->em->createQuery('DELETE ' . ItemTypeInItem::class . ' s WHERE s.item = :id');
                    }
                    $qb->execute($parameters);
                }

            }

            $parameters = [
                'id' => $process->id
            ];
            if (count($itemsNotDel)) {
                $qb = $this->em->createQuery('DELETE ' . ItemInProcess::class . ' s WHERE s.process = :id and s.id not in(:ids)');
                $parameters['ids'] = $itemsNotDel;
            } else {
                $qb = $this->em->createQuery('DELETE ' . ItemInProcess::class . ' s WHERE s.process = :id');
            }
            $qb->execute($parameters);
        }
    }

    public function saveFromFrontData($data, $customerId)
    {
        try {
            $this->em->beginTransaction();
            $customer = $this->em->getCustomerRepository()->find($customerId);

            $process = null;
            if (isset($data['id']) && $data['id']) {
                $process = $this->em->getProcessRepository()->find($data['id']);
            }

            if (!$process) {
                $process = new \App\Model\Database\Entity\Process();
                $this->em->persist($process);
                $process->setProcessState($this->em->getProcessStateRepository()->find(1));
                $process->setInStateDate(new DateTime());
            }
            $process->setCustomer($customer);
            $this->em->flush($process);

            $this->saveItems($process, $data);

            $this->em->commit();

            return $process;
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return null;
    }

    /**
     * Return array of products in basked in process
     */
    public function getItems($process)
    {
        $items = $this->em->getItemInProcessRepository()->findBy(['process' => $process->id]);

        $arr = [];
        foreach ($items as $k => $s) {
            $arr[$k] = [
                'id' => $s->id,
                'name' => $s->name,
                'itemTypes' => [],
                'description' => $s->description
            ];
            foreach ($s->types as $type) {
                $arr[$k]['itemTypes'][] = $type->type->id;
            }
        }

        return $arr;
    }

    public function getDataFromProcess(\App\Model\Database\Entity\Process $process)
    {
        $arr = [
            'id' => $process->id,
            'items' => $this->getItems($process)
        ];

        return $arr;
    }

    public function createOrder(\App\Model\Database\Entity\Process $process)
    {
        try {
            $this->generateNumberBp($process);
            $process->setProcessState($this->em->getProcessStateRepository()->find(2));
            $process->setInStateDate(new DateTime());
            $process->setFoundedDate(new DateTime());
            $this->em->flush($process);

            return true;
        } catch (\Exception $ex) {
            Debugger::log($ex);
        }

        return false;
    }
}