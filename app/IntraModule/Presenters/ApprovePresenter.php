<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Approve;
use App\Model\Database\Entity\ApproveDocument;
use App\Model\Database\Entity\ApprovePart;
use App\Model\Database\Entity\ApprovePartDocument;
use App\Model\Database\Entity\PermissionItem;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class ApprovePresenter extends BasePresenter
{
    /**
     * ACL name='Správa schvalování'
     * ACL rejection='Nemáte přístup ke správě schvalování.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);

        $slug = $this->getParameter('slug');
        $approveState = null;
        if ($slug) {
            $approveState = $this->em->getApproveStateRepository()->find($slug);
        }

        $this->sess->approveState = $approveState;
    }

    /**
     * ACL name='Zobrazení stránky schvalování'
     */
    public function renderDefault()
    {
        if(!$this->isAjax()) {
            if(file_exists("dfiles/normSelect.txt") && date('Y-m-d') == date('Y-m-d', filemtime("dfiles/normSelect.txt"))) {
                $normSelect = unserialize(file_get_contents("dfiles/normSelect.txt"));
            } else {
                $normSelect = $this->getNormForSelect();
                file_put_contents("dfiles/normSelect.txt", serialize($normSelect));
            }
        }
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním schvalování'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        $customerSelect = unserialize(file_get_contents("dfiles/customerSelect.txt"));
        $this->template->interClassName = unserialize(file_get_contents("dfiles/interClassName.txt"));
        $this->template->customerSelect = $customerSelect;

        $this->template->normSelect = unserialize(file_get_contents("dfiles/normSelect.txt"));
        $this->template->interClassSelect = unserialize(file_get_contents("dfiles/interClassSelect.txt"));
        if ($id) {
            $entity = $this->em->getApproveRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Schvalování nebylo nalezeno.', 'error');
                $this->redirect('Approve:');
            } // :o:  close();
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
            $this->template->approveParts = $this->em->getApprovePartRepository()->findBy(['approve' => $entity]);
            $this->template->approveDocs = $this->em->getApproveDocumentRepository()->findBy(['approve' => $entity]);

            $this->template->openTab = ($this->getParameter('openTab') !== null) ? $this->getParameter('openTab') : null;
        } else {
            $this->template->openTab = null;
        }
    }

    /**
     * ACL name='Tabulka s přehledem schvalování'
     */
    public function createComponentTable()
    {
        $findBy = [];
        if (isset($this->sess->approveState)) {
            $findBy = ['approveState' => $this->sess->approveState->id];
        }

        $grid = $this->gridGen->generateGridByAnnotation(Approve::class, get_class(), __FUNCTION__, 'default', $findBy);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Approve:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Approve:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');


            $this->gridGen->addButtonDeleteCallback();

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit schvalování'
     */
    public function createComponentForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(Approve::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit schvalování', 'success'], ['Nepodařilo se uložit schvalování!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            $isNew = 0;
            if($values2['id']) {
                $oldEntity = $that->em->getApproveRepository()->find($values2['id']);
            } else {
                $isNew = 1;
            }

            $entity = $that->formGenerator->processForm($form, $values, true);

            if (!$entity) {
                return;
            }

            $entity->setCustomerShort($values2['customerShort']);

            if(!$entity->startDate) {
                $entity->setStartDate(new \DateTime());
                $that->em->flush();
            }

            /*if($entity->startDate && $entity->approveTime && $entity->approveTime->numDays) {
                $deadlineDate = clone $entity->startDate;
                $deadlineDate->modify('+' . $entity->approveTime->numDays . ' day');
                $entity->setDeadlineDate($deadlineDate);
                $that->em->flush();
            }*/

            if($isNew) {
                $mailer = new SendmailMailer();
                $fromMask = 'Info Webrex demo <info@webrex.eu>';

                $email_body = '';
                $email_body .= '<table>';
                $email_body .= '<tr><td><b>Název</b></td><td>';
                $email_body .= '<a href="https://'.$_SERVER['SERVER_NAME'].$this->link('Approve:edit', ['id' => $entity->id]).'" target="_blank">'.$entity->name.'</a>';
                $email_body .= '</td></tr>';
                if($entity->description) {
                    $email_body .= '<tr><td><b>Poznámka</b></td><td>';
                    $email_body .= nl2br($entity->description);
                    $email_body .= '</td></tr>';
                }
                if($entity->customerShort) {
                    $email_body .= '<tr><td><b>Zákazník</b></td><td>';
                    $email_body .= $entity->customerShort;
                    $email_body .= '</td></tr>';
                }
                if($entity->startDate) {
                    $email_body .= '<tr><td><b>Ze dne</b></td><td>';
                    $email_body .= $entity->startDate->format('d. m. Y');
                    $email_body .= '</td></tr>';
                }
                if($entity->deadlineDate) {
                    $email_body .= '<tr><td><b>Termín</b></td><td>';
                    $email_body .= $entity->deadlineDate->format('d. m. Y');
                    $email_body .= '</td></tr>';
                }
                $email_body .= '</table><br>';


                //$subjects = $this->em->getWorkerRepository()->findBy(['active' => 1, 'workerPosition' => [18,9,26,13,19]]);
                $subjects = ['info@webrex.eu'];
                foreach ($subjects as $subject) {
                    if($subject) {
                        $mail = new Message();
                        $mail->setFrom($fromMask)
                            ->addTo($subject)
                            ->setSubject('Webrex demo - přidána nová nabídka k posouzení (' . $entity->name . ')');
                        $mail->setHtmlBody($email_body);
                        //$mailer->send($mail);
                    }
                }
            }

            if (isset($values2['sendBack'])) { // Uložit a zpět
                $this->redirect('Approve:default', ['slug' => $entity->approveState->id]);
            } else if (isset($values2['send'])) { //Uložit
                $this->redirect('Approve:edit', ['id' => $entity->id]);
            } else if (isset($values2['sendNew'])) {
                $this->redirect('Approve:edit');
            } else {
                $this->redirect('Approve:edit', ['id' => $entity->id]);
            }

        };

        return $form;
    }

    /**
     * ACL name='Formulář pro přidání/edit dokumentu schvalování'
     */
    public function createComponentDocModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(ApproveDocument::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit dokument', 'success'], ['Nepodařilo se uložit dokument!', 'error']);
        $form->isRedirect = false;

        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            if (empty($values->id)) {
                $values['user'] = $that->em->getUserRepository()->find($that->getUser()->getId());
            }

            $entity = $that->formGenerator->processForm($form, $values, true);
            if($entity) {
                $entity->setApprove($that->em->getApproveRepository()->find($values2['approve']));
                $that->em->flush();
            }

            if ($that->isAjax()) {
                $that->redrawControl('docsTable');
            } else {
                $that->redirect('Approve:edit',  ['id' => $values2['approve'], 'openTab' => '#docs']);
            }
        };

        return $form;
    }

    public function handleCheckApproveDoc() {
        $values = $this->request->getPost();
        if($values['doc']) {
            $entity = $this->em->getApproveDocumentRepository()->find($values['doc']);
            $arr = $this->ed->get($entity);
            $this['docModalForm']->setDefaults($arr);
            $this->template->modalDoc = $entity;
        }
        $this->redrawControl('docModal');
    }

    public function handleRemoveApproveDoc() {
        $values = $this->request->getPost();
        $entity = $this->em->getApproveDocumentRepository()->find($values['doc']);
        $this->em->remove($entity);
        $this->em->flush();

        $this->redrawControl('docsTable');
    }

    /**
     * ACL name='Formulář pro přidání/edit dokumentu položky schvalování'
     */
    public function createComponentDocPartModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(ApprovePartDocument::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit dokument', 'success'], ['Nepodařilo se uložit dokument!', 'error']);
        $form->isRedirect = false;

        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            if (empty($values->id)) {
                $values['user'] = $that->em->getUserRepository()->find($that->getUser()->getId());
            }

            unset($values['approvePart']);
            $approvePart = null;
            $entity = $that->formGenerator->processForm($form, $values, true);
            if($entity) {
                $approvePart = $that->em->getApprovePartRepository()->find($values2['approvePart']);
                $entity->setApprovePart($approvePart);
                $that->em->flush();
            }

            if ($that->isAjax()) {
                $that->redrawControl('partTable');
            } else {
                if($approvePart) {
                    $that->redirect('Approve:edit',  ['id' => $approvePart->approve->id]);
                } else {
                    $that->redirect('this');
                }

            }
        };

        return $form;
    }

    public function handleCheckApproveDocPart() {
        $values = $this->request->getPost();
        if($values['part']) {
            $this['docPartModalForm']->setDefaults(['approvePart' => $values['part']]);
        }
        $this->redrawControl('docPartModal');
    }

    public function handleRemoveApproveDocPart() {
        $values = $this->request->getPost();
        $entity = $this->em->getApprovePartDocumentRepository()->find($values['doc']);
        $this->em->remove($entity);
        $this->em->flush();

        $this->redrawControl('partTable');
    }


    /**
     * ACL name='Formulář pro přidání/edit položek schvalování'
     */
    public function createComponentPartModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(ApprovePart::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit položku', 'success'], ['Nepodařilo se uložit položku!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            $approveResultTk = $approveResultChpu = $approveResultVpu = $approveResultRefo = $approveResultTpv = $approveResultKoop = $approveResultPers = NULL;
            if($values2['id']) {
                $oldEntity = $that->em->getApprovePartRepository()->find($values2['id']);
                $approveResultTk = $oldEntity->approveResultTk;
                $approveResultChpu = $oldEntity->approveResultChpu;
                $approveResultVpu = $oldEntity->approveResultVpu;
                $approveResultRefo = $oldEntity->approveResultRefo;
                $approveResultTpv = $oldEntity->approveResultTpv;
                $approveResultKoop = $oldEntity->approveResultKoop;
                $approveResultPers = $oldEntity->approveResultPers;
            }

            $entity = $that->formGenerator->processForm($form, $values, true);
            if($entity) {
                $entity->setApprove($that->em->getApproveRepository()->find($values2['approve']));
                $that->em->flush();
            }

            /*$techTotalFrom = floatval($entity->techZnFrom) + floatval($entity->techKtlFrom) + floatval($entity->techPraFrom) +
                floatval($entity->techZnFrom2) + floatval($entity->techKtlFrom2) + floatval($entity->techPraFrom2);
            $entity->setTechTotalFrom($techTotalFrom);
            $techTotalTo = floatval($entity->techZnTo) + floatval($entity->techKtlTo) + floatval($entity->techPraTo) +
                floatval($entity->techZnTo2) + floatval($entity->techKtlTo2) + floatval($entity->techPraTo2);
            $entity->setTechTotalTo($techTotalTo);*/

            if($entity->approveResultTk && $entity->approveResultTk != $approveResultTk) {
                $entity->setApproveUserTk($that->em->getUserRepository()->find($that->getUser()->getId()));
                $entity->setApproveDateTk(new \DateTime());
            }
            if($entity->approveResultChpu && $entity->approveResultChpu != $approveResultChpu) {
                $entity->setApproveUserChpu($that->em->getUserRepository()->find($that->getUser()->getId()));
                $entity->setApproveDateChpu(new \DateTime());
            }
            if($entity->approveResultVpu && $entity->approveResultVpu != $approveResultVpu) {
                $entity->setApproveUserVpu($that->em->getUserRepository()->find($that->getUser()->getId()));
                $entity->setApproveDateVpu(new \DateTime());
            }
            if($entity->approveResultRefo && $entity->approveResultRefo != $approveResultRefo) {
                $entity->setApproveUserRefo($that->em->getUserRepository()->find($that->getUser()->getId()));
                $entity->setApproveDateRefo(new \DateTime());
            }
            if($entity->approveResultTpv && $entity->approveResultTpv != $approveResultTpv) {
                $entity->setApproveUserTpv($that->em->getUserRepository()->find($that->getUser()->getId()));
                $entity->setApproveDateTpv(new \DateTime());
            }
            if($entity->approveResultKoop && $entity->approveResultKoop != $approveResultKoop) {
                $entity->setApproveUserKoop($that->em->getUserRepository()->find($that->getUser()->getId()));
                $entity->setApproveDateKoop(new \DateTime());
            }
            if($entity->approveResultPers && $entity->approveResultPers != $approveResultPers) {
                $entity->setApproveUserPers($that->em->getUserRepository()->find($that->getUser()->getId()));
                $entity->setApproveDatePers(new \DateTime());
            }
            $that->em->flush();

            $approve = $that->em->getApproveRepository()->find($values2['approve']);
            $approveParts = $that->em->getApprovePartRepository()->findBy(['approve' => $approve]);
            $doneAll = 1;
            foreach ($approveParts as $approvePart) {
                if($approvePart->approveState && $approvePart->approveState->id != 3) {
                    $doneAll = 0;
                }
            }

            if(!$approve->sendFinish && $doneAll) {
                $mailer = new SendmailMailer();
                $fromMask = 'Info Webrex demo <info@webrex.eu>';

                $email_body = '';
                $email_body .= '<table>';
                $email_body .= '<tr><td><b>Název</b></td><td>';
                $email_body .= '<a href="https://'.$_SERVER['SERVER_NAME'].$this->link('Approve:edit', ['id' => $approve->id]).'" target="_blank">'.$approve->name.'</a>';
                $email_body .= '</td></tr>';
                if($entity->description) {
                    $email_body .= '<tr><td><b>Poznámka</b></td><td>';
                    $email_body .= nl2br($entity->description);
                    $email_body .= '</td></tr>';
                }
                if($entity->customerShort) {
                    $email_body .= '<tr><td><b>Zákazník</b></td><td>';
                    $email_body .= $entity->customerShort;
                    $email_body .= '</td></tr>';
                }
                if($entity->startDate) {
                    $email_body .= '<tr><td><b>Ze dne</b></td><td>';
                    $email_body .= $entity->startDate->format('d. m. Y');
                    $email_body .= '</td></tr>';
                }
                if($entity->deadlineDate) {
                    $email_body .= '<tr><td><b>Termín</b></td><td>';
                    $email_body .= $entity->deadlineDate->format('d. m. Y');
                    $email_body .= '</td></tr>';
                }
                $email_body .= '</table><br>';

                $subjects = ['info@webrex.eu'];
                foreach ($subjects as $subject) {
                    if($subject) {
                        $mail = new Message();
                        $mail->setFrom($fromMask)
                            ->addTo($subject)
                            ->setSubject('Webrex demo - V nabídce je vše schváleno (' . $entity->name . ')');
                        $mail->setHtmlBody($email_body);
                        //$mailer->send($mail);
                    }
                }

                $approve->setSendFinish(1);
                $that->em->flush();
            }


            if ($that->isAjax()) {
                $that->redrawControl('partTable');
            } else {
                $that->redirect('Approve:edit',  ['id' => $values2['approve']]);
            }
        };

        return $form;
    }

    public function handleCheckApprovePart() {
        $values = $this->request->getPost();
        if($values['part']) {
            $entity = $this->em->getApprovePartRepository()->find($values['part']);
            $arr = $this->ed->get($entity);
            $this['partModalForm']->setDefaults($arr);
            $this->template->modalPart = $entity;
        }
        $this->redrawControl('partModal');
    }

    public function handleRemoveApprovePart() {
        $values = $this->request->getPost();
        $entity = $this->em->getApprovePartRepository()->find($values['part']);
        $this->em->remove($entity);
        $this->em->flush();

        $this->redrawControl('partTable');
    }

    public function getNormForSelect() {
        $normArr = array();
        $countResults = 1;
        $failSafePage = 1;
        while($countResults > 0 && $failSafePage < 50) {
            $curl = curl_init();
            $qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1000));
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://192.168.1.112:3000/v1/inmedias/norm?" . $qParams,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: multipart/form-data",
                    "cache-control: no-cache"
                ),
            )); //  ..
            $response = curl_exec($curl);
            $data = json_decode($response, true);

            foreach ($data as $cusArray) {
                $normArr[$cusArray['id']] = $cusArray['norm'] . ' (' . $cusArray['name'] . ')';
            }

            $countResults = count($data);
            $failSafePage++;
        }

        asort($normArr);
        return $normArr;
    }

    public function inmediasWriteApprove($approveId, $new = 1) {
        $approve = $this->em->getApproveRepository()->find($approveId);
        $approveParts = $this->em->getApprovePartRepository()->findBy(['approve' => $approve]);

        $approveArr = array();
        $approveArr['id'] = $approve->id;
        $approveArr['name'] = $approve->name;
        $approveArr['customerShort'] = $approve->customerShort;
        $approveArr['request'] = $approve->request;
        $approveArr['startDate'] = $approve->startDate;
        $approveArr['deadlineDate'] = $approve->deadlineDate;
        $approveArr['description'] = $approve->description;
        $approveArr['approveType'] = $approve->approveType;
        $approveArr['short'] = $approve->short ? true : false;
        $approveArr['parts'] = array();
        foreach ($approveParts as $approvePart) {
            $approvePartArr = array();
            $approvePartArr['id'] = $approvePart->id;
            $approvePartArr['interNumber'] = $approvePart->interNumber;
            $approvePartArr['interMark'] = $approvePart->interMark;
            $approvePartArr['interClass'] = $approvePart->interClass;
            $approvePartArr['interName'] = $approvePart->interName;
            $approvePartArr['interArea'] = $approvePart->interArea;
            $approvePartArr['cusNumber'] = $approvePart->cusNumber;
            $approvePartArr['cusName'] = $approvePart->cusName;
            $approvePartArr['blasting'] = $approvePart->blasting ? true : false;
            $approvePartArr['techTotalFrom'] = $approvePart->techTotalFrom;
            $approvePartArr['techTotalTo'] = $approvePart->techTotalTo;
            $approvePartArr['techZnFrom'] = $approvePart->techZnFrom;
            $approvePartArr['techZnTo'] = $approvePart->techZnTo;
            $approvePartArr['techKtlFrom'] = $approvePart->techKtlFrom;
            $approvePartArr['techKtlTo'] = $approvePart->techKtlTo;
            $approvePartArr['techPraFrom'] = $approvePart->techPraFrom;
            $approvePartArr['techPraTo'] = $approvePart->techPraTo;
            $approvePartArr['techZnFrom2'] = $approvePart->techZnFrom2;
            $approvePartArr['techZnTo2'] = $approvePart->techZnTo2;
            $approvePartArr['techKtlFrom2'] = $approvePart->techKtlFrom2;
            $approvePartArr['techKtlTo2'] = $approvePart->techKtlTo2;
            $approvePartArr['techPraFrom2'] = $approvePart->techPraFrom2;
            $approvePartArr['techPraTo2'] = $approvePart->techPraTo2;
            $approvePartArr['techZnFrom3'] = $approvePart->techZnFrom3;
            $approvePartArr['techZnTo3'] = $approvePart->techZnTo3;
            $approvePartArr['techKtlFrom3'] = $approvePart->techKtlFrom3;
            $approvePartArr['techKtlTo3'] = $approvePart->techKtlTo3;
            $approvePartArr['techPraFrom3'] = $approvePart->techPraFrom3;
            $approvePartArr['techPraTo3'] = $approvePart->techPraTo3;
            $approvePartArr['timeNorm'] = $approvePart->timeNorm;
            $approvePartArr['normFile1'] = $approvePart->normFile1;
            $approvePartArr['normFile2'] = $approvePart->normFile2;
            $approvePartArr['techDemand1'] = $approvePart->techDemand1 ? true : false;
            $approvePartArr['techDemand2'] = $approvePart->techDemand2 ? true : false;
            $approvePartArr['techDemand3'] = $approvePart->techDemand3 ? true : false;
            $approvePartArr['techDemand4'] = $approvePart->techDemand4 ? true : false;
            $approvePartArr['techDemand5'] = $approvePart->techDemand5 ? true : false;
            $approvePartArr['techDemand6'] = $approvePart->techDemand6 ? true : false;
            $approvePartArr['techDemand7'] = $approvePart->techDemand7;
            $approvePartArr['interDemand1'] = $approvePart->interDemand1;
            $approvePartArr['interDemand2'] = $approvePart->interDemand2;
            $approvePartArr['interDemand3'] = $approvePart->interDemand3;
            $approvePartArr['capacity'] = $approvePart->capacity;
            $approvePartArr['note'] = $approvePart->note;
            $approveArr['parts'][] = $approvePartArr;
        }
        json_encode($approveArr);

        $curl = curl_init();
        $qParams = http_build_query(array('id' => $approve->id));
        $curlUrl = "http://192.168.1.112:3000/v1/inmedias/approve" . ($new ? "" : ("?" . $qParams));
        curl_setopt_array($curl, array(
            CURLOPT_URL => $curlUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS, json_encode($approveArr),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: multipart/form-data",
                "cache-control: no-cache"
            ),
        )); //  ..
        $response = curl_exec($curl);
        $data = json_decode($response, true);

    }

    public function handlePrintApproveList($id) {
        $path = $this->pdfPrinter->handlePrintApproveList($id, 'F');
        if($path){
            $entity = $this->em->getApproveRepository()->find($id);
            $appDoc = new ApproveDocument();
            $appDoc->setApprove($entity);
            $appDoc->setDocument($path);
            $appDoc->setDescription('Výstup schvalování');
            $appDoc->setUser($this->em->getUserRepository()->find($this->user->identity->id));
            $appDoc->setCreatedAt();
            $appDoc->setUpdatedAt();
            $this->em->persist($appDoc);
            $this->em->flush();
            $this->flashMessage('Podařilo se exportovat data.', 'success');
        }else{
            $this->flashMessage('Nepodařilo se exportovat data.', 'warning');
        }
        $this->redirect('Approve:edit',  ['id' => $id, 'openTab' => '#docs']);
    }

    public function handlePrintApproveListXlsx($id) {
        $approve = $this->em->getApproveRepository()->find($id);
        if (!$approve) {
            $this->flashMessage('Nepodařilo se exportovat data.', 'warning');
            $this->redirect('this');
        }
        $approveParts = $this->em->getApprovePartRepository()->findBy(['approve' => $approve]);
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $aSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet);
        $spreadsheet->addSheet($aSheet, 0);

        //hlavička
        $aSheet->mergeCells('A1:F3');
        $aSheet->mergeCells('A4:F4');
        $aSheet->mergeCells('G1:R1');
        $aSheet->mergeCells('G2:R4');
        $aSheet->mergeCells('S1:V1');
        $aSheet->mergeCells('S2:V2');
        $aSheet->mergeCells('S3:T3');
        $aSheet->mergeCells('U3:V3');
        $aSheet->mergeCells('S4:T4');
        $aSheet->mergeCells('U4:V4');
        $aSheet->mergeCells('A5:V5');

        $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $objDrawing->setPath('assets/img/logo.png');
        $objDrawing->setCoordinates('B2');
        $objDrawing->setWidth(180);
        $objDrawing->setWorksheet($aSheet);

        $aSheet->setCellValue('A4', 'Provoz: Humpolec');
        $aSheet->setCellValue('G1', 'Název formuláře:');
        $aSheet->setCellValue('G2', $approve->name);
        $aSheet->setCellValue('S1', 'Zkratka zákazníka');
        $aSheet->setCellValue('S2', $approve->customerShort);
        $aSheet->setCellValue('S3', 'poptávka');
        $aSheet->setCellValue('U3', 'ze dne:');
        $aSheet->setCellValue('S4', $approve->request);
        $aSheet->setCellValue('U4', $approve->startDate->format('j.n.y'));

        $aSheet->getStyle('G2:R4')->getAlignment()->setHorizontal('center')->setVertical('center');
        $aSheet->getStyle('G2:R4')->getFont()->setSize('22')->setBold(true);
        $aSheet->getStyle('S2:V2')->getFont()->setSize('22')->setBold(true);
        //položky - hlavička
        $aSheet->mergeCells('A6:G6');
        $aSheet->mergeCells('H6:P6');
        $aSheet->mergeCells('Q6:T6');
        $aSheet->mergeCells('V6:V9');
        $aSheet->mergeCells('A7:G7');
        $aSheet->mergeCells('H7:K7');
        $aSheet->mergeCells('M7:M9');
        $aSheet->mergeCells('N7:N9');
        $aSheet->mergeCells('O7:O9');
        $aSheet->mergeCells('P7:P9');
        $aSheet->mergeCells('Q7:Q9');
        $aSheet->mergeCells('R7:R9');
        $aSheet->mergeCells('S7:S9');
        $aSheet->mergeCells('U7:U9');
        $aSheet->mergeCells('A8:A9');
        $aSheet->mergeCells('B8:B9');
        $aSheet->mergeCells('C8:C9');
        $aSheet->mergeCells('D8:D9');
        $aSheet->mergeCells('E8:E9');
        $aSheet->mergeCells('F8:F9');
        $aSheet->mergeCells('G8:G9');
        $aSheet->mergeCells('T8:T9');
        $aSheet->mergeCells('A10:V10');
        $aSheet->getRowDimension('10')->setRowHeight(5);

        $aSheet->setCellValue('A6', 'Identifikace dílu');
        $aSheet->setCellValue('H6', 'Technické požadavky zákazníka');
        $aSheet->setCellValue('Q6', 'Internetní požadavky');
        $aSheet->setCellValue('U6', 'Trys.');
        $aSheet->setCellValue('V6', 'Poznámky');
        $aSheet->setCellValue('A7', 'interní');
        $aSheet->setCellValue('H7', 'Tl. vrstvy (μm)');
        $aSheet->setCellValue('M7', 'Krýt závit/ otvor/ plochu');
        $aSheet->setCellValue('N7', 'Kontrolovat závit/ rozměr');
        $aSheet->setCellValue('O7', 'Zvláštni znak (CC,SC,D díl)');
        $aSheet->setCellValue('P7', 'Jiné požadavky');
        $aSheet->setCellValue('Q7', 'počet ks/závěs');
        $aSheet->setCellValue('R7', 'Čas navěš.');
        $aSheet->setCellValue('S7', 'kg/buben');
        $aSheet->setCellValue('T7', 'závěs');
        $aSheet->setCellValue('U7', 'kapacitní ověření (%z celkových kapacit)');
        $aSheet->setCellValue('A8', 'poř.čís.');
        $aSheet->setCellValue('B8', 'označení');
        $aSheet->setCellValue('C8', 'zatřídění');
        $aSheet->setCellValue('D8', 'číslo výkresu');
        $aSheet->setCellValue('E8', 'název');
        $aSheet->setCellValue('F8', 'plocha (dm²)');
        $aSheet->setCellValue('G8', 'hmotnost');
        $aSheet->setCellValue('H8', 'celková');
        $aSheet->setCellValue('I8', 'Zn/ZnNi');
        $aSheet->setCellValue('J8', 'KTL');
        $aSheet->setCellValue('K8', 'Prá');
        $aSheet->setCellValue('L8', 'norma*');
        $aSheet->setCellValue('T8', 'standardní speciální');
        $aSheet->setCellValue('H9', 'od do');
        $aSheet->setCellValue('I9', 'od do');
        $aSheet->setCellValue('J9', 'od do');
        $aSheet->setCellValue('K9', 'od do');
        $aSheet->setCellValue('L9', 'další norma*');
        //položky - data
        $row = 11;
        foreach ($approveParts as $apPar) {
            $aSheet->mergeCells('A'.$row.':A'.($row+1));
            $aSheet->mergeCells('B'.$row.':B'.($row+1));
            $aSheet->mergeCells('C'.$row.':C'.($row+1));
            $aSheet->mergeCells('D'.$row.':D'.($row+1));
            $aSheet->mergeCells('E'.$row.':E'.($row+1));
            $aSheet->mergeCells('F'.$row.':F'.($row+1));
            $aSheet->mergeCells('G'.$row.':G'.($row+1));
            $aSheet->mergeCells('M'.$row.':M'.($row+1));
            $aSheet->mergeCells('N'.$row.':N'.($row+1));
            $aSheet->mergeCells('O'.$row.':O'.($row+1));
            $aSheet->mergeCells('P'.$row.':P'.($row+1));
            $aSheet->mergeCells('Q'.$row.':Q'.($row+1));
            $aSheet->mergeCells('R'.$row.':R'.($row+1));
            $aSheet->mergeCells('S'.$row.':S'.($row+1));
            $aSheet->mergeCells('T'.$row.':T'.($row+1));
            $aSheet->mergeCells('V'.$row.':V'.($row+1));

            $aSheet->setCellValue('A'.$row, $apPar->interNumber);
            $aSheet->setCellValue('B'.$row, $apPar->interMark);
            $aSheet->setCellValue('C'.$row, $apPar->interClass);
            $aSheet->setCellValue('D'.$row, $apPar->cusNumber);
            $aSheet->setCellValue('E'.$row, $apPar->interName);
            $aSheet->setCellValue('F'.$row, $apPar->interArea);
            $aSheet->setCellValue('G'.$row, $apPar->cusName);
            $aSheet->setCellValue('H'.$row, $apPar->techTotalFrom);
            if ($apPar->techZnFrom) {
                $techZnFrom = $apPar->techZnFrom;
            } elseif ($apPar->techZnFrom2) {
                $techZnFrom = $apPar->techZnFrom2;
            } else {
                $techZnFrom = $apPar->techZnFrom3;
            }
            $aSheet->setCellValue('I'.$row, $techZnFrom);
            if ($apPar->techKtlFrom) {
                $techKtlFrom = $apPar->techKtlFrom;
            } elseif ($apPar->techKtlFrom2) {
                $techKtlFrom = $apPar->techKtlFrom2;
            } else {
                $techKtlFrom = $apPar->techKtlFrom3;
            }
            $aSheet->setCellValue('J'.$row, $techKtlFrom);
            if ($apPar->techPraFrom) {
                $techPraFrom = $apPar->techPraFrom;
            } elseif ($apPar->techPraFrom2) {
                $techPraFrom = $apPar->techPraFrom2;
            } else {
                $techPraFrom = $apPar->techPraFrom3;
            }
            $aSheet->setCellValue('K'.$row, $techPraFrom);
            //$aSheet->setCellValue('L'.$row, $apPar->norm1);
            if ($apPar->techDemand1) {
                $tech1 = 'A';
            } else {
                $tech1 = 'N';
            }
            $tech1 .= ' / ';
            if ($apPar->techDemand2) {
                $tech1 .= 'A';
            } else {
                $tech1 .= 'N';
            }
            $tech1 .= ' / ';
            if ($apPar->techDemand3) {
                $tech1 .= 'A';
            } else {
                $tech1 .= 'N';
            }
            $aSheet->setCellValue('M'.$row, $tech1);
            if ($apPar->techDemand4) {
                $tech4 = 'A';
            } else {
                $tech4 = 'N';
            }
            $tech4 .= ' / ';
            if ($apPar->techDemand5) {
                $tech4 .= 'A';
            } else {
                $tech4 .= 'N';
            }
            $aSheet->setCellValue('N'.$row, $tech4);
            $aSheet->setCellValue('O'.$row, $apPar->techDemand6);
            $aSheet->setCellValue('P'.$row, $apPar->techDemand7);
            $aSheet->setCellValue('Q'.$row, $apPar->interDemand1);
            $aSheet->setCellValue('R'.$row, $apPar->timeNorm);
            $aSheet->setCellValue('S'.$row, $apPar->interDemand2);
            $aSheet->setCellValue('T'.$row, $apPar->interDemand3);
            if ($apPar->blasting) {
                $blast = 'A';
            } else {
                $blast = 'N';
            }
            $aSheet->setCellValue('U'.$row, $blast);
            $aSheet->setCellValue('V'.$row, $apPar->note);
            $aSheet->setCellValue('H'.($row+1), $apPar->techTotalTo);
            if ($apPar->techZnTo) {
                $techZnTo = $apPar->techZnTo;
            } elseif ($apPar->techZnTo2) {
                $techZnTo = $apPar->techZnTo2;
            } else {
                $techZnTo = $apPar->techZnTo3;
            }
            $aSheet->setCellValue('I'.($row+1), $techZnTo);
            if ($apPar->techKtlTo) {
                $techKtlTo = $apPar->techKtlTo;
            } elseif ($apPar->techKtlTo2) {
                $techKtlTo = $apPar->techKtlTo2;
            } else {
                $techKtlTo = $apPar->techKtlTo3;
            }
            $aSheet->setCellValue('J'.($row+1), $techKtlTo);
            if ($apPar->techPraTo) {
                $techPraTo = $apPar->techPraTo;
            } elseif ($apPar->techPraTo2)  {
                $techPraTo = $apPar->techPraTo2;
            } else {
                $techPraTo = $apPar->techPraTo3;
            }
            $aSheet->setCellValue('K'.($row+1), $techPraTo);
            //$aSheet->setCellValue('L'.($row+1), $apPar->norm2);
            $aSheet->setCellValue('U'.($row+1), $apPar->capacity);

            $row += 2;
        }
        $row -= 1;
        $aSheet->getStyle('A1:V4')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('000000');
        $aSheet->getStyle('A1:V4')->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)->getColor()->setARGB('000000');
        $aSheet->getStyle('A6:V'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('000000');
        $aSheet->getStyle('A6:V'.$row)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)->getColor()->setARGB('000000');
        $aSheet->getStyle('G6:G'.$row)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)->getColor()->setARGB('000000');
        $aSheet->getStyle('K6:K'.$row)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)->getColor()->setARGB('000000');
        $aSheet->getStyle('L6:L'.$row)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)->getColor()->setARGB('000000');
        $aSheet->getStyle('P6:P'.$row)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)->getColor()->setARGB('000000');
        $aSheet->getStyle('T6:T'.$row)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)->getColor()->setARGB('000000');
        $aSheet->getStyle('A10:V10')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)->getColor()->setARGB('000000');
        $aSheet->getStyle('A10:V10')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)->getColor()->setARGB('000000');
        $aSheet->getStyle('G6:G'.$row)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUMDASHDOTDOT);
        $row += 1;
        $aSheet->mergeCells('A'.$row.':V'.$row);
        $aSheet->getStyle('A1:V'.$row)->getAlignment()->setHorizontal('center')->setVertical('center');
        $aSheet->getStyle('G1:R1')->getAlignment()->setHorizontal('left')->setVertical('center');
        $aSheet->getStyle('G2:R4')->getFont()->setSize('22')->setBold(true);
        $aSheet->getStyle('S2:V2')->getFont()->setSize('22')->setBold(true);

        //patička
        $row += 1;
        $aSheet->mergeCells('A'.$row.':B'.$row);
        $aSheet->mergeCells('D'.$row.':E'.$row);
        $aSheet->mergeCells('F'.$row.':V'.$row);
        $aSheet->setCellValue('A'.$row, '*norma přiložena');
        $aSheet->setCellValue('D'.$row, 'Poznámka:');
        $aSheet->setCellValue('F'.$row, $approve->description);
        $aSheet->getStyle('A'.$row.':E'.$row)->getAlignment()->setHorizontal('left')->setVertical('center');
        $aSheet->getStyle('F'.$row.':V'.$row)->getAlignment()->setHorizontal('center')->setVertical('center');
        $aSheet->getStyle('D'.$row.':V'.$row)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('000000');
        $aSheet->getRowDimension($row)->setRowHeight(60);
        $aSheet->getStyle('E'.$row)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)->getColor()->setARGB('000000');

        $row += 1;
        $aSheet->mergeCells('A'.$row.':V'.$row);

        $row += 1;
        $aSheet->mergeCells('A'.$row.':V'.$row);
        $aSheet->setCellValue('A'.$row, 'V rámci přezkoumání poptávky byly prověřeny příslušné specifické požadavky zákazníka, které jsou uvedeny v přehledu CSR (např. rozsah a metodika vzorkování a četnost a rozsah rekvalififkačních zkoušek, atd..). Při přezkoumání byla prověřena znalostní databáze společnosti, kde jsou uvedeny zkušenosti z předchozích projektů (např. kvalita vstupního materiálu, způsob zavěšování atd..), také byly prověřeny kapacitní možnosti pro nabízenou technologii.');
        $aSheet->getStyle('A'.$row)->getFont()->setBold(true);
        $aSheet->getRowDimension($row)->setRowHeight(50);
        $aSheet->getStyle('A'.$row)->getAlignment()->setVertical('center');

        $row += 1;
        $aSheet->mergeCells('A'.$row.':V'.$row);

        $row += 1;
        $aSheet->mergeCells('A'.$row.':V'.$row);
        $aSheet->setCellValue('A'.$row, 'V případě, že součet kapacitního výhledu pro celkovou nabídku tvoří více než 5% z celkových ročních kapacit technologie, je schválení nabídky prováděno celým projektovým týmem.');
        $aSheet->getStyle('A'.$row)->getFont()->setBold(true);

        $row += 1;
        $aSheet->mergeCells('A'.$row.':V'.$row);

        $row += 1;
        $aSheet->mergeCells('A'.$row.':F'.$row);
        $aSheet->mergeCells('G'.$row.':V'.$row);
        $aSheet->setCellValue('A'.$row, 'Nabídku schválil / dne:');
        //$aSheet->setCellValue('G'.$row, '');
        $aSheet->getStyle('A'.$row.':F'.$row)->getAlignment()->setHorizontal('center')->setVertical('center');
        $aSheet->getStyle('A'.$row.':V'.$row)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE)->getColor()->setARGB('000000');
        $aSheet->getStyle('F'.$row)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('000000');

        /*foreach (range('A', 'V') as $ran) {
            $aSheet->getColumnDimension($ran)->setAutoSize(true);
        }*/
        $aSheet->getColumnDimension('P')->setWidth(12);
        $aSheet->getColumnDimension('T')->setWidth(12);
        $aSheet->getColumnDimension('U')->setWidth(12);
        $aSheet->getColumnDimension('V')->setAutoSize(true);
        $aSheet->getStyle('A1:V'.$row)->getAlignment()->setWrapText(true);

        $folder = '_data/approve_documents/'.$id.'/';
        if (!file_exists($folder)) {
            mkdir($folder, 0775);
        }
        $xlsName = $id . '-' . date('Y_m_d_H_i_s') . '-approveList.xlsx';
        $path = $folder . $xlsName;
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($path);

        $appDoc = new ApproveDocument();
        $appDoc->setApprove($approve);
        $appDoc->setDocument($path);
        $appDoc->setDescription('Výstup schvalování - xlsx');
        $appDoc->setUser($this->em->getUserRepository()->find($this->user->identity->id));
        $appDoc->setCreatedAt();
        $appDoc->setUpdatedAt();
        $this->em->persist($appDoc);
        $this->em->flush();
        $this->flashMessage('Podařilo se exportovat data.', 'success');
        $this->redirect('Approve:edit',  ['id' => $id, 'openTab' => '#docs']);
    }
}