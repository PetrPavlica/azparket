<?php

namespace App\Presenters;

use App\Model\Facade\BaseFront;
use Nette\Utils\Strings;
use App\Components\Forms\InquiryForm\IInquiryFormControlFactory;

class ProductPresenter extends BasePresenter
{
    
    /** @var BaseFront @inject */
    public $facade;

    /** @var IInquiryFormControlFactory @inject */
    public $inquiryFormFac;
    
    public function renderDefault($id, $slug = null)
    {
        //addFunction add in latte/latte v2.6.x and php 7.1
        /*$latte = $this->template->getLatte();
        $latte->addFunction('formatFilesize', function ($byte, $precision = 2){
            $str = 'kMGTPEZY';
            $f = floor((strlen($byte) - 1) / 3);
            $res = sprintf("%." . $precision . "f", $byte/pow(1024, $f)).' '.@$str[$f-1].'B';
            echo $res;
        });
        $latte->addFunction('remoteFilesize', function ($url){
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FILETIME, true);
            $result = curl_exec($ch);
            //$timestamp= curl_getinfo($ch, CURLINFO_FILETIME);
            //$date = date("Y-m-d H:i:s", $timestamp);
            $info = curl_getinfo($ch);
            $res = $info['download_content_length'];
            curl_close($ch);
            echo $res;
        });*/

        if($id) {
            //$product = $this->getIntraOneProduct("p.active = 1 AND p.id = ?", [$id]);
            $product = $this->facade->getProduct($id, $this->locale);
            if ($product) {
                $this->template->product = $product;
                /* Dokumenty k produktům */

                $this->template->productFiles = $this->facade->getProductFiles($id, $this->locale);

                $this->template->vykresy = $this->em->getProductFileRepository()->createQueryBuilder('f')
                    ->leftJoin('f.langs', 'fl')
                    ->leftJoin('fl.lang', 'l')
                    ->where('f.product = :id AND f.section = :section AND l.code = :locale')
                    ->setParameters(['id' => $id, 'section' => '1', 'locale' => $this->locale])
                    ->getQuery()->getResult();
                bdump($this->template->vykresy);
                $this->template->models3D = $this->em->getProductFileRepository()->createQueryBuilder('f')
                    ->leftJoin('f.langs', 'fl')
                    ->leftJoin('fl.lang', 'l')
                    ->where('f.product = :id AND f.section = :section AND l.code = :locale')
                    ->setParameters(['id' => $id, 'section' => '2', 'locale' => $this->locale])
                    ->getQuery()->getResult();;

                
                /*$docs = [];
                $docs['1'] = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE document_type = 1 AND product_id = ?', $id)->fetchAll();
                $docs['2'] = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE document_type = 2 AND product_id = ?', $id)->fetchAll();
                $docs['3'] = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE document_type = 3 AND product_id = ?', $id)->fetchAll();
                $docs['4'] = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE document_type = 4 AND product_id = ?', $id)->fetchAll();
                $this->template->product_document = $docs;
                if ($product->base_type == 3) {
                    $baseName = 'Servozesilovac';
                } else {
                    $baseName = 'Servomotor';
                }
                $this->template->docTypeName = ['1' => $this->translator->translate('Manuály'), '2' => $this->translator->translate('Katalogy'), '3' => $this->translator->translate('Databáze'), '4' => $this->translator->translate('Ostatní')];
                $this->template->model = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE product_id = ? AND description = "model_img"', $id)->fetch();
                $this->template->dataList = $this->databaseContext->dbIntra->queryArgs('SELECT * FROM product_data_list WHERE product_id = ?', [$product->id])->fetchAll();
                if ($product->base_type == 1) {
                    // Servomotory 
                    $this->template->modelXml = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE product_id = ? AND description = "Servomotory_3DModel_xhtml"', $id)->fetch();
                    $this->template->vykres = $vykres = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE product_id = ? AND description = ?', $id, $baseName . "_web_vykres")->fetch();
                    if ($vykres) {
                        $this->template->vykresPageCount = $this->getPdfPageCount($this->webIntra . $vykres->path);
                    }
                    $this->template->rated_data = $ratedData = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE product_id = ? AND description = ?', $id, $baseName . "_web_parametr")->fetch();
                    if ($ratedData) {
                        $this->template->ratedDataPageCount = $this->getPdfPageCount($this->webIntra . $ratedData->path);
                    }
                } elseif ($product->base_type == 2) {
                    // Převodovky 
                } elseif ($product->base_type == 3) {
                    //Servozesilovače
                    $this->template->servozesParamImg = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE product_id = ? AND description = "Servozesilovace_Parametr_img"', $id)->fetch();
                    $this->template->servozesSizesImg = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE product_id = ? AND description = "Servozesilovace_Rozmer_img"', $id)->fetch();
                    $this->template->servozesCommunImg = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE product_id = ? AND description = "Servozesilovace_Komunikace_img"', $id)->fetch();
                    $this->template->modelXml = $this->databaseContext->dbIntra->query('SELECT * FROM product_document WHERE product_id = ? AND description = "Servozesilovace_3DModel_xhtml"', $id)->fetch();
                    // Firmwary k produktům 
                    $firmawares = [];
                    $firmawaresEth = $this->databaseContext->dbIntra->query('SELECT DISTINCT firmware_feedback FROM product_document_firmware WHERE active = 1 AND firmware_communication = "EtherCAT" AND product_id = ?', $id)->fetchAll();
                    foreach ($firmawaresEth as $f) {
                        $firmawares['EtherCAT'][$f['firmware_feedback']] = $this->databaseContext->dbIntra->query('SELECT * FROM product_document_firmware WHERE active = 1 AND product_id = ? AND firmware_communication = "EtherCAT" AND firmware_feedback = ?  ORDER BY version_date DESC', $id, $f['firmware_feedback'])->fetchAll();
                    }
                    $firmawaresPro = $this->databaseContext->dbIntra->query('SELECT DISTINCT firmware_feedback FROM product_document_firmware WHERE active = 1 AND firmware_communication = "Profinet" AND product_id = ?', $id)->fetchAll();
                    foreach ($firmawaresPro as $f) {
                        $firmawares['Profinet'][$f['firmware_feedback']] = $this->databaseContext->dbIntra->query('SELECT * FROM product_document_firmware WHERE active = 1 AND product_id = ? AND firmware_communication = "Profinet" AND firmware_feedback = ?  ORDER BY version_date DESC', $id, $f['firmware_feedback'])->fetchAll();
                    }
                    $this->template->firmwares = $firmawares;//$this->databaseContext->dbIntra->query('SELECT * FROM product_document_firmware WHERE product_id = ? ORDER BY version_date DESC', $id)->fetchAll();
                } elseif ($product->base_type == 4) {
                    // Kabely 
                } elseif ($product->base_type == 5) {
                    // Příslušenství
                }
                */
            } else {
                $this->flashMessage('Hledaný produkt nebyl nalezen.', 'info');
                $this->redirect('Homepage:default');
            }
        } else {
            $this->flashMessage('Hledaný produkt nebyl nalezen.', 'info');
            $this->redirect('Homepage:default');
        }

        if (isset($this->sess->turnoverDownoladFile)){
            /*header('Content-Disposition: attachment; filename='.$this->sess->turnoverDownoladFile['name'] );
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Length: ' . filesize($this->sess->turnoverDownoladFile['file']));
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            readfile($this->sess->turnoverDownoladFile['file']);

            unlink($this->sess->turnoverDownoladFile['file']);
            unset($this->sess->turnoverDownoladFile);*/

            $ctype = "";
            $file = $this->sess->turnoverDownoladFile['file'];

            switch (pathinfo($file)['extension']) {
                case "pdf": $ctype = "application/pdf"; break;
                case "exe": $ctype = "application/octet-stream"; break;
                case "zip": $ctype = "application/zip"; break;
                case "doc": $ctype = "application/msword"; break;
                case "xls": $ctype = "application/vnd.ms-excel"; break;
                case "ppt": $ctype = "application/vnd.ms-powerpoint"; break;
                case "gif": $ctype = "image/gif"; break;
                case "png": $ctype = "image/png"; break;
                case "jpe": case "jpeg":
                case "jpg": $ctype = "image/jpg"; break;
                default: $ctype = "application/force-download";
            }

            if (!empty($ctype)) {
                $fsize = $this->getFileSizeRemote($file);
                header('Content-type: '.$ctype);
                header('Content-Disposition: inline; filename="' . $this->sess->turnoverDownoladFile['name'] . '"');
                header("Cache-control: private");
                header("Content-length: $fsize");

                if ($fsize < 66060288) { // 63 MB
                    @readfile($file);
                } else {
                    $handle = fopen($file, "rb");
                    if ($handle) {

                        while(!feof($handle)) {
                            $buffer = fread($handle, 1048576); // 1 MB
                            echo $buffer;
                            ob_flush();
                            flush();
                        }
                    }
                    fclose($handle);
                }
            }
            unset($this->sess->turnoverDownoladFile);
        }
    }

    public function createComponentInquiryForm() {
        return $this->inquiryFormFac->create(['productId' => $this->getParameter('id'), 'hideButtons' => 1]);
    }

    public function getPdfPageCount($path)
    {
        $pdf = file_get_contents($path);
        $number = preg_match_all("/\/Page\W/", $pdf, $dummy);
        return $number;
    }

    public function handleDownloadFile($filePath, $fileName) {
        $this->sess->turnoverDownoladFile['name'] = $fileName;
        $this->sess->turnoverDownoladFile['file'] = $filePath;
        $this->redirect('this');
    }

    private function getFileSizeRemote($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILETIME, true);
        $result = curl_exec($ch);
        //$timestamp= curl_getinfo($ch, CURLINFO_FILETIME);
        //$date = date("Y-m-d H:i:s", $timestamp);
        $info = curl_getinfo($ch);
        $res = $info['download_content_length'];
        curl_close($ch);
        return $res;
    }
}