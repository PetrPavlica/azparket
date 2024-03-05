<?php

namespace App\IntraModule\Presenters;

use App\Model\ACLForm;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Facade\Visit;
use Doctrine\Common\Collections\Criteria;

class VisitProcessPresenter extends BasePresenter
{
    /** @var Visit @inject */
    public $visFac;

    /**
     * ACL name='Správa stavů OP výjezdů'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderDefault($slug = null)
    {
        $qb = $this[ 'table' ]->getDataSource();
        if ($slug) {
            $qb->filterOne(['state' => $slug]);
        }
        $this[ 'table' ]->setDataSource($qb);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getVisitProcessRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('VisitProcess:');
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);

            if ($entity->customer)
                $this['form']->setAutocmp('customer', $entity->customer->name);

            if ($entity->customerOrdered)
                $this['form']->setAutocmp('customerOrdered', $entity->customerOrdered->name);

            if ($entity->traffic)
                $this['form']->setAutocmp('traffic', $entity->traffic->name);
        } else {
            $dateCreated = new \DateTime();
            $this[ 'form' ]->setDefaults(['dateAcceptOrder' => $dateCreated->format('j. n. Y')]);
        }
        $this->template->openTab = ($this->getParameter('openTab') !== null) ? $this->getParameter('openTab') : null;

        if(isset($this->sess->turnoverExportVisitsDoc)){
            $this->template->canDownloadZip = true;
        }
    }

    /**
     * ACL name='Odeslání dokumentů výjezdů emailem'
     */
    public function renderSendDocs($id, $docIds = [])
    {
        if (!$id) {
            $this->flashMessage('Obchodní případ se nepodařilo najít!', 'warning');
            $this->redirect('VisitProcess:default');
        }
        $visitProcess = $this->em->getVisitProcessRepository()->find($id);
        if (!$visitProcess) {
            $this->flashMessage('Obchodní případ se nepodařilo najít!', 'warning');
            $this->redirect('VisitProcess:default');
        }
        if (!is_array($docIds) || !count($docIds)) {
            $this->flashMessage('Dokumenty se nepodařilo najít!', 'warning');
            $this->redirect('VisitProcess:edit', ['id' => $id, 'openTab' => '#docs']);
        }
        $dId = [];
        foreach ($docIds as $docId) {
            $doc = $this->em->getVisitDocumentRepository()->find($docId);
            $dId[$docId] = $docId;
        }
        $this[ 'sendDocsForm' ]->getComponent('id')->value = $id;
        $this[ 'sendDocsForm' ]->getComponent('docIds')->items = $dId;
        $this[ 'sendDocsForm' ]->getComponent('docIds')->value = $docIds;
        $this->template->visitProcess = $visitProcess;
        $this->template->docIds = $docIds;
    }

    /**
     * ACL name='Odeslání dokumentů výjezdů emailem k fakturaci'
     */
    public function renderSendInvoicing($id, $docIds = [])
    {
        if (!$id) {
            $this->flashMessage('Obchodní případ se nepodařilo najít!', 'warning');
            $this->redirect('VisitProcess:default');
        }
        $visitProcess = $this->em->getVisitProcessRepository()->find($id);
        if (!$visitProcess) {
            $this->flashMessage('Obchodní případ se nepodařilo najít!', 'warning');
            $this->redirect('VisitProcess:default');
        }
        if (!is_array($docIds) || !count($docIds)) {
            $this->flashMessage('Dokumenty se nepodařilo najít!', 'warning');
            $this->redirect('VisitProcess:edit', ['id' => $id, 'openTab' => '#docs']);
        }
        $dId = [];
        foreach ($docIds as $docId) {
            $doc = $this->em->getVisitDocumentRepository()->find($docId);
            $dId[$docId] = $docId;
        }
        $this[ 'sendInvoicingForm' ]->getComponent('id')->value = $id;
        $this[ 'sendInvoicingForm' ]->getComponent('docIds')->items = $dId;
        $this[ 'sendInvoicingForm' ]->getComponent('docIds')->value = $docIds;
        $this->template->visitProcess = $visitProcess;
        $this->template->docIds = $docIds;
    }

    /**
     * ACL name='Tabulka s přehledem OP výjezdů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\VisitProcess::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'VisitProcess:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');

        $grid->getColumn('materialNeedBuyVisit')->setRenderer(function($item) {
            $arr = [];
            if ($item->visits) {
                foreach ($item->visits as $v) {
                    $needBuy = [];
                    if ($v->materialNeedBuy) {
                        foreach ($v->materialNeedBuy as $nb) {
                            $needBuy[] = $nb->name;
                        }
                    }
                    $arr[] = implode(', ', $needBuy);
                }
            }
            return implode(', ', $arr);
        });
        $grid->getFilter('materialNeedBuyVisit')->setCondition(function($qb, $value) {
            $search = $this->SQLHelper->termToLike($value, 'matNeBu', ['name']);
            $qb->leftJoin(\App\Model\Database\Entity\Visit::class, 'vis', 'WITH', 'a.id = vis.visitProcess');
            $qb->leftJoin(\App\Model\Database\Entity\MaterialNeedBuy::class, 'matNeBu', 'WITH', 'vis.id = matNeBu.visit');
            $qb->andWhere($search);;
        });

        $multiAction->addAction('edit', 'Upravit', 'VisitProcess:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Tabulka s přehledem výjezdů'
     */
    public function createComponentVisitTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Visit::class, get_class(), __FUNCTION__, 'default', ['visitProcess' => $this->params['id']]);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Visit:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');

        $grid->getColumn('durationHours')
            ->setRenderer(function($item) {
                return sprintf('%d:%02d', $item->durationHours, $item->durationMinutes);
            });

        $grid->getColumn('materialNeedBuy')->setRenderer(function($item) {
            $arr = [];
            if ($item->materialNeedBuy) {
                foreach ($item->materialNeedBuy as $mnb) {
                    $arr[] = $mnb->name;
                }
            }
            return implode(', ', $arr);
        });
        $grid->getColumn('materialNeedBuy')->setSortableCallback(function($qb, $value) {
            $qb->leftJoin('\App\Model\Database\Entity\MaterialNeedBuy', 'mnb2', 'WITH', 'a.id = mnb2.visit');
            foreach ($value as $k => $v) {
                $qb->orderBy('mnb2.name', $v);
            }
        });
        $grid->getFilter('materialNeedBuy')->setCondition(function($qb, $value) {
            $search = $this->SQLHelper->termToLike($value, 'mnb', ['name']);
            $qb->leftJoin('\App\Model\Database\Entity\MaterialNeedBuy', 'mnb', 'WITH', 'a.id = mnb.visit');
            $qb->andWhere($search);
        });

        $grid->addGroupAction('Udělat kopii')->onSelect[] = [$this, 'makeVisitCopy'];

        $multiAction->addAction('edit', 'Upravit', 'Visit:edit', ['id' => 'id', 'backP' => 'visitProcess.id']);
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit OP výjezdu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\VisitProcess::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit obchodní případ', 'success'], ['Nepodařilo se uložit obchodní případ!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'successVisitProcessForm'];
        $form->onValidate[] = function($form) {
            $values = $form->getValues();
            if ($values->orderId != '') { // Kontrola jedinečnosti Data
                $criteriaStart = new Criteria();
                $criteriaStart->where(Criteria::expr()->notIn('id', [$values->id]));
                $criteriaStart->andWhere(Criteria::expr()->eq('orderId', $values->orderId));
                $op = $this->em->getVisitProcessRepository()->matching($criteriaStart)->getValues();
                if ($op) {
                    $form->addError('Pozor! Obchodní případ se nepodařilo uložit. Pod tímto id zakázky máte již jinou zákázku!');
                }
            }
        };
        return $form;
    }

    public function successVisitProcessForm($form, $values)
    {
        $values2 = $this->request->getPost();
        if(!$values->state) {
            $this->flashMessage('Prosím vyplňte pole Stav OP!', 'warning');
            return;
        }
        if(!$values->customer) {
            $this->flashMessage('Prosím vyplňte pole Zákazník!', 'warning');
            return;
        }
        if(!$values->traffic) {
            $this->flashMessage('Prosím vyplňte pole Provozovna!', 'warning');
            return;
        }
        if(!$values->customerOrdered) {
            $this->flashMessage('Prosím vyplňte pole Objednatel!', 'warning');
            return;
        }

        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }

        if (isset($values2['sendBack'])) { // Uložit a zpět
            $this->redirect('VisitProcess:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('VisitProcess:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('VisitProcess:edit');
        } else {
            $this->redirect('VisitProcess:edit', ['id' => $entity->id]);
        }
    }

    /**
     * ACL name='Formulář pro práci s dokumenty'
     */
    public function createComponentFormDocs()
    {
        $form = new ACLForm();
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'formDocsSuccess'];
        return $form;
    }

    public function formDocsSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        if (isset($values2[ 'downloadDocs' ]) || isset($values2[ 'sendEmailDocs' ]) || isset($values2[ 'sendInvoicingDocs' ])) {
            $ids = [];
            foreach ($values2 as $k => $f) {
                if ($f == true) {
                    $st = substr($k, 0, 4);
                    if ($st == 'doc/') {
                        $r = explode('/', $k);
                        //r[0]-dokument, r[1]-id dokumentu
                        $ids[] = $r[1];
                    }
                }
            }
            if (isset($values2[ 'downloadDocs' ])) {
                $this->downloadDocuments($ids, $values2['processIdno']);
            }
            if (isset($values2[ 'sendEmailDocs' ])) {
                $this->redirect('VisitProcess:sendDocs', ['id' => $values2['processId'], 'docIds' => $ids]);
            }
            if (isset($values2[ 'sendInvoicingDocs' ])) {
                $this->redirect('VisitProcess:sendInvoicing', ['id' => $values2['processId'], 'docIds' => $ids]);
            }
            return;
        }
    }

    /**
     * ACL name='Formulář pro odeslání dokumentů mailem'
     */
    public function createComponentSendDocsForm()
    {
        $form = new ACLForm();
        $form->addHidden('id');
        $form->addMultiSelect('docIds')->setHtmlAttribute('style', 'display: none;');
        $form->addText('email', 'Email');
        $form->addTextArea('note', 'Poznámka', 2, 5)->setHtmlAttribute('class', 'form-control');
        $form->setMessages(['Podařilo se odeslat dokumenty', 'success'], ['Nepodařilo se odeslat dokumenty!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'sendDocsFormSuccess'];
        return $form;
    }

    public function sendDocsFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        $visitProcess = $this->em->getVisitProcessRepository()->find($values2['id']);
        $res = $this->mailSender->sendVisitDocs($values2, $this->user->getId(), 'obchodnímu případu '.$visitProcess->orderId, $visitProcess->traffic->id);
        if ($res) {
            $this->flashMessage('Emaily se podařilo odeslat.', 'success');
        } else {
            $this->flashMessage('Emaily se nepodařilo odeslat.', 'warning');
        }
        $this->redirect('VisitProcess:edit', ['id' => $values2['id'], 'openTab' => '#docs']);
    }

    /**
     * ACL name='Formulář pro odeslání dokumentů mailem k fakturaci'
     */
    public function createComponentSendInvoicingForm()
    {
        $form = new ACLForm();
        $form->addHidden('id');
        $form->addMultiSelect('docIds')->setHtmlAttribute('style', 'display: none;');
        $form->addTextArea('note', 'Poznámka', 2, 5)->setHtmlAttribute('class', 'form-control');
        $form->setMessages(['Podařilo se odeslat dokumenty', 'success'], ['Nepodařilo se odeslat dokumenty!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'sendInvoicingFormSuccess'];
        return $form;
    }

    public function sendInvoicingFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        $visitProcess = $this->em->getVisitProcessRepository()->find($values2['id']);
        $res = $this->mailSender->sendVisitDocs($values2, $this->user->getId(),'obchodnímu případu '.$visitProcess->orderId, $visitProcess->traffic->id,true);
        if ($res) {
            $this->flashMessage('Emaily se podařilo odeslat.', 'success');
        } else {
            $this->flashMessage('Emaily se nepodařilo odeslat.', 'warning');
        }
        $this->redirect('VisitProcess:edit', ['id' => $values2['id'], 'openTab' => '#docs']);
    }

    public function makeVisitCopy($ids)
    {
        $this->visFac->createCopiesVisit($ids);
        $this->flashMessage('Kopírování bylo dokončeno. Kopie v tabulce.', 'success');
        $this->redirect('this');
    }

    public function downloadDocuments($ids, $baseFileName)
    {
        $archiveName = str_replace('/', '_', $baseFileName);
        $fileName = date('Ymdhis').'-'.$archiveName.".zip";
        $archive_file_name = "_data/temp-files/".$fileName;
        $zip = new \ZipArchive();
        if ($zip->open($archive_file_name, \ZipArchive::CREATE) === TRUE) {
            foreach ($ids as $docId) {
                $doc = $this->em->getVisitDocumentRepository()->find($docId);
                //$docName = str_replace('/', '_', substr($doc->document, strrpos($doc->document, '/') + 1));
                $docName = $doc->name;
                $zip->addFile($doc->document, $docName);
            }
            $zip->close();

            $this->sess->turnoverExportVisitsDoc['file'] = $archive_file_name;
            $this->sess->turnoverExportVisitsDoc['name'] = $archiveName;
        }
        $this->redirect('this');
    }

    public function handleDownloadZip()
    {
        if (isset($this->sess->turnoverExportVisitsDoc)) {
            $this->getHttpResponse()->setContentType('application/zip');
            $this->getHttpResponse()->setHeader('Content-Disposition', 'filename='.$this->sess->turnoverExportVisitsDoc['name'].'.zip');
            $this->getHttpResponse()->setHeader('Content-Length', filesize($this->sess->turnoverExportVisitsDoc['file']));
            $this->getHttpResponse()->setHeader('Content-Transfer-Encoding', 'binary');
            $this->getHttpResponse()->setHeader('Cache-Control', 'must-revalidate');
            $this->getHttpResponse()->setHeader('Pragma', 'public');
            readfile($this->sess->turnoverExportVisitsDoc['file']);
            unlink($this->sess->turnoverExportVisitsDoc['file']);
            unset($this->sess->turnoverExportVisitsDoc);
            $this->terminate();
        }
    }

    public function handleGetVisitProcessOrderId()
    {
        $processMask = 'OP';
        $query2 = $this->em->getConnection()->prepare("
                  SELECT order_id 
                  FROM visit_process 
                  WHERE order_id LIKE '".$processMask."%'
                  ORDER BY id DESC 
                  LIMIT 1
                ");
        $query2->execute();
        $data = $query2->fetchAll();
        if ($data) {
            if (substr(date("Y"),2) != substr($data[0]['order_id'],2, 2)) {
                $nextId = 1;
            } else {
                $nextId = substr($data[0]['order_id'], 4) + 1;
            }
        } else {
            $nextId = 1;
        }
        $yearMask = substr(date("Y"),2);
        $idMask = $processMask.$yearMask.sprintf("%05d", $nextId);
        $this->sendJson((object)[
            'id' => $idMask,
        ]);
    }

    public function handleDeleteVisitDocument($documentId)
    {
        $res = $this->visFac->deleteVisitDocum($documentId);
        if ($this->isAjax()) {
            $this->redrawControl('product-documents');
        } else {
            $this->redirect('this');
        }
    }

    /**
     * Get customers for autocomplete
     * @param string $term
     */
    public function handleGetCustomers($term)
    {
        $result = $this->em->getCustomerRepository()->getDataAutocompleteCustomers($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    /**
     * @param $term
     */
    public function handleGetCustomerOrdereds($term)
    {
        $result = $this->em->getCustomerOrderedRepository()->getDataAutocompleteCustomerOrdered($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleGetTraffics($term)
    {
        $result = $this->em->getTrafficRepository()->getDataAutocompleteTraffics($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

}
