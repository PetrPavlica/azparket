<?php

namespace App\Components\PDFPrinter;

use App\Model\Database\EntityManager;
use Nette\Application\UI;
use Nette\Application\UI\TemplateFactory;
use Nette\Utils\Html;

class PDFPrinterControl extends UI\Control
{
    /** @var EntityManager */
    public $em;

    public function __construct(TemplateFactory $templateFactory, EntityManager $em)
    {
        $this->setTemplateFactory($templateFactory);
        $this->em = $em;
    }

    public function renderOrder($id, $text = "", $i = "file-pdf-o")
    {
        $template = $this->template;
        $template->id = $id;
        $template->text = $text;
        $template->i = $i;
        $template->type = 'refund';
        $template->setFile(__DIR__ . '/templates/default.latte');
        $template->render();
    }

    public function handlePrintOrder($orderId, $isRS = false, $output = 'D')
    {
        $order = $this->em->getOrderRepository()->find($orderId);
        if (!$order) {
            return null;
        }
        $template = $this->createTemplate();
        $template->order = $order;
        $template->isRS = $isRS;
        $description = $order->description;
        if ($order->description && !empty($order->description)) {
            $domd = new \DOMDocument('1.0', 'utf-8');
            //$domd->encoding = 'utf-8';
            \libxml_use_internal_errors(true);
            $domd->loadHTML(mb_convert_encoding($order->description, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            \libxml_use_internal_errors(false);

            $domx = new \DOMXPath($domd);
            $items = $domx->query("//*[@style]");

            foreach ($items as $item) {
                $item->removeAttribute("style");
            }
            $description = $domd->saveHTML();
        }

        $template->description = $description;

        $template->orderNumber = $order->getOrderNumber();
        $template->orderTypes = [
            1 => 'limitovaná',
            2 => 'individuální'
        ];
        $template->typesOfSend = [
            1 => 'poštou',
            2 => 'telefonicky',
            3 => 'faxem',
            4 => 'e-mailem',
            5 => 'osobně',
            6 => 'e-shop',
        ];
        $template->setFile(__DIR__ . '/templates/order.latte');
        $mpdf = new \Mpdf\Mpdf([
            'mode' => '',
            'format' => 'A4',
            'default_font_size' => 16,
            'default_font' => 'Arial',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'orientation' => 'P',
        ]);
        $mpdf->WriteHTML($template, 0);

        $arr = [];
        if ($order->orderNumber) {
            $arr[] = $order->orderNumber;
        } else {
            $arr[] = '-';
        }
        $arr[] = $order->createdAt->format('Y');
        if ($order->center == 2) {
            $arr[] = 'CB';
        }
        $orderNumber = implode('/', $arr);

        if ($output == 'S') {
            return [$orderNumber . '.pdf', $mpdf->Output($orderNumber . '.pdf', $output)];
        } elseif ($output == 'D' || $output == 'I') {
            $mpdf->Output($orderNumber . '.pdf', $output);
            die;
        } else {
            return $mpdf->Output($orderNumber . '.pdf', $output);
        }
    }

    public function handlePrintApproveList($approveId, $output = 'D')
    {
        $approve = $this->em->getApproveRepository()->find($approveId);
        if (!$approve) {
            return null;
        }
        $stylesheet = file_get_contents('css/pdf_approve.css'); // external css
        $template = $this->createTemplate();
        $template->approve = $approve;
        $template->approveParts = $this->em->getApprovePartRepository()->findBy(['approve' => $approve]);
        $template->normSelect = unserialize(file_get_contents("dfiles/normSelect.txt"));

        $template->setFile(__DIR__ . '/templates/approveList.latte');
        $mpdf = new \Mpdf\Mpdf([
            'mode' => '',
            'format' => 'A4-L',
            'default_font_size' => 10,
            'default_font' => 'Arial',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'orientation' => 'P',
        ]);
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($template, 0);

        $folder = '_data/approve_documents/'.$approveId.'/';
        if (!file_exists($folder)) {
            mkdir($folder, 0775);
        }
        $path = $folder . $approveId . '-' . date('Y_m_d_h_i_s') . '-approveList.pdf';
        $pdfName = 'approveList-'.$approveId.'.pdf';

        if ($output == 'S') {
            return [$pdfName, $mpdf->Output($pdfName, $output)];
        } elseif ($output == 'D' || $output == 'I') {
            $mpdf->Output($pdfName, $output);
            die;
        }elseif($output == 'F') {
            $mpdf->Output($path, $output);
            return $path;
        } else {
            return $mpdf->Output($pdfName, $output);
        }
    }

    public function handlePrintFutureWorkerShift($data, $worker, $dateFrom, $dateTo, $pdfName, $output = 'D') {
        $template = $this->createTemplate();
        $template->futureShiftWork = $data;
        $template->worker = $worker;
        $template->nameTrans = [1=>'Ranní', 2=>'Noční'];
        $template->lineTrans = [1=>'KTL', 2=>'ZN'];
        $dateToShow = ' ';
        if ($dateFrom) {
            $dateToShow = $dateToShow.'('.$dateFrom->format('j.n.Y').' - ';
        } else {
            $date = new \DateTime();
            $dateToShow = $dateToShow.'('.$date->format('j.n.Y').' - ';
        }
        if ($dateTo) {
            $dateToShow = $dateToShow.$dateTo->format('j.n.Y').') ';
        } else {
            $dateToShow = $dateToShow.') ';
        }

        $template->setFile(__DIR__ . '/templates/futureWorkerShift.latte');
        $mpdf = new \Mpdf\Mpdf([
            'mode' => '',
            'format' => 'A4',
            'default_font_size' => 16,
            'default_font' => 'Arial',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 9,
            'margin_footer' => 9,
            'orientation' => 'P',
        ]);
        $mpdf->setHTMLHeader('<div style="width: 100%; text-align: center;">Zaměstanec: '.$worker->name.' '.$worker->surname.' &nbsp;&nbsp;<span style="font-size: 12px;">'.$dateToShow.'</span>'.'</div>');
        $mpdf->WriteHTML($template, 0);

        $folder = '_data/temp-files/';
        if (!file_exists($folder)) {
            mkdir($folder, 0775);
        }
        $path = $folder.date('Ymdhis').'_workerFutureShift.pdf';

        if ($output == 'S') {
            return [$pdfName, $mpdf->Output($pdfName, $output)];
        } elseif ($output == 'D' || $output == 'I') {
            $mpdf->Output($pdfName, $output);
            die;
        }elseif($output == 'F') {
            $mpdf->Output($path, $output);
            return $path;
        } else {
            return $mpdf->Output($pdfName, $output);
        }
    }

    /**
     * @param mixed $output
     * @return array<string>|null|string
     */
    public function handlePrintOffer($offer, $pdfName, $userId = null, $date, $output = 'D')
    {
        if (is_numeric($offer)) {
            $offer = $this->em->getOfferRepository()->find($offer);
        }

        $stylesheet = file_get_contents('css/pdf-list.css');
        $template = $this->createTemplate();
        $template->offer = $offer;
        $template->date = $date;
        //$template->bankAccNo = $this->em->getSettingRepository()->findOneBy(['code' => 'BANK_ACC_NO'])->value;
        $template->dic = $this->em->getSettingRepository()->findOneBy(['code' => 'ICO'])->value;
        $template->ico = $this->em->getSettingRepository()->findOneBy(['code' => 'DIC'])->value;
        if ($userId !== null) {
            $template->user = $this->em->getUserRepository()->find($userId);
        } else {
            $template->user = null;
        }

        $template->setFile(__DIR__ . '/templates/offer.latte');
        $mpdf = new \Mpdf\Mpdf([
            'mode' => '',
            'format' => 'A4',
            'default_font_size' => 14,
            'default_font' => 'Arial',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'margin_header' => 8,
            'margin_footer' => 8,
            'orientation' => 'P',
        ]);
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($template, 0);

        $folder = \App\Model\Facade\Offer::OFFER_PATH . $offer->id . '/';
        if (!file_exists($folder)) {
            mkdir($folder, 0775, true);
        }
        $path = $folder . $pdfName;

        if ($output == 'S') {
            return [$pdfName, $mpdf->Output($pdfName, $output)];
        } elseif ($output == 'D' || $output == 'I') {
            $mpdf->Output($pdfName, $output);
            die;
        }elseif($output == 'F') {
            $mpdf->Output($path, $output);
            return $path;
        } else {
            return $mpdf->Output($pdfName, $output);
        }
    }
}
