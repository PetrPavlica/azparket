<?php

namespace App\Model\Facade;

use App\Components\MailSender\MailSender;
use App\Components\PDFPrinter\PDFPrinterControl;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\SQLHelper;
use App\Model\Database\Entity\User;
use App\Model\Database\Entity\Offer as OfferEnt;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\Facade\BaseFront;
use Nette\Application\UI\Multiplier;
use App\Model\Utils\GoogleMaps;
use Doctrine\Common\Collections\ArrayCollection;

class Offer
{
    /** @var EntityManager */
    private EntityManager $em;

    /** @var SQLHelper */
    private $SQLHelper;

    /** @var MailSender */
    public $mailSender;

    /** @var ManagerRegistry */
    public $mr;

    /** @var BaseFront */
    public $baseFrontFac;

    /** @var GoogleMaps */
    private $googleMaps;

    /** @var PDFPrinterControl */
    private $pdfPrinter;

    const OFFER_PATH = '_data/offers/';

    const OFFER_PREFIX = 'offer_';

    public function __construct(EntityManager $em, SQLHelper $sql, MailSender $mailSender, ManagerRegistry $mr, BaseFront $baseFrontFac, GoogleMaps $googleMaps, PDFPrinterControl $pdfPrinter)
    {
        $this->em = $em;
        $this->SQLHelper = $sql;
        $this->mailSender = $mailSender;
        $this->mr = $mr;
        $this->baseFrontFac = $baseFrontFac;
        $this->googleMaps = $googleMaps;
        $this->pdfPrinter = $pdfPrinter;
    }

    public function getNextOfferNo() {
        // find highest offer numbers then choose suitable one bcs it's values can vary
        $qb = $this->em->getOfferRepository()->createQueryBuilder('o');
        $qb->select('o')
            ->where('o.createdAt >= ' . date('Y') . '-01-01')
            ->orderBy('o.offerNo', 'DESC')
            ->setMaxResults(10);
        $offers = $qb->getQuery()->getResult();
        $newOfferNo = date('Y') . '000';
        if ($offers) {
            foreach ($offers as $o) {
                $offerNo = intval($o->offerNo);
                if (strlen($offerNo) == 7 && $offerNo = intval($offerNo)) {
                    $newOfferNo = $offerNo + 1 . '';
                    break;
                }
            }
        }
        return $newOfferNo;
    }

    /**
     * @param OfferEnt $offer
     * @return void
     */
    public function prepareAndSendOffer($offer, $emailTo, $emailCopy, $subject, $text = '', $locale = 'cs')
    {
        
        if (is_numeric($offer)) {
            $offer = $this->em->getOfferRepository()->find($offer);
        }
        if (!$offer) {
            return false;
        }
        if (!$this->assingCode($offer)) {
            return false;
        }
        
        $file = self::OFFER_PATH . $offer->id . '/' . self::OFFER_PREFIX . $offer->id . '.pdf';
        if (!file_exists($file)) {
            $file = $this->pdfPrinter->handlePrintOffer($offer, self::OFFER_PREFIX . $offer->id . '.pdf', null, new \DateTime(), 'F');
        
        }

        if ($this->mailSender->sendOffer($offer->id, $emailTo, $emailCopy, $subject, $text, $locale)) {
            $offer->setSendDate(new \DateTime());
            $offer->setState(1);
            $this->em->flush();
            return true;
        } else {
            return false;
        }
    }

    public function assingCode($offer)
    {
        if (is_numeric($offer)) {
            $offer = $this->mr->getManager()->getRepository(OfferEnt::class)->find($offer);
        }
        if (!$offer) {
            return false;
        }

        $try = 0;
        do {
            $offer->setAcceptCode($this->getRandomString(32));
            try {
                $try++;
                $this->mr->getManager()->flush();
                break;
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException | \Doctrine\ORM\Exception\EntityManagerClosed $e) {
                $this->mr->resetManager();
            }
        } while ($try < 5);
        if ($try >= 5) {
            return false;
        }
        return true;
    }

    function getRandomString($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, 61)];
        }
        return $string;
    }

    public function acceptOffer($values)
    {
        $entity = $this->em->getOfferRepository()->findOneBy(['acceptCode' => $values->acceptCode, 'id' => $values->id]);

        if (!$entity) {
            return false;
        }

        $customer = $entity->customer;
        $customer->setName($values->name);
        $customer->setSurname($values->surname);
        $customer->setFullname($values->name . ' ' . $values->surname);
        $customer->setEmail($values->email);
        $customer->setPhone($values->phone);
        if ($values->company) {
            $customer->setCompany($values->company);
        }
        if ($values->ico) {
            $customer->setIdNo($values->idNo);
        }
        $customer->setActive(2);
        
        $entity->setAcceptDate(new \DateTime());
        $entity->setState(2);
        
        $this->em->flush($entity);

        return $entity;
    }

    public function dismissOffer($acceptCode)
    {
        $entity = $this->em->getOfferRepository()->findOneBy(['acceptCode' => $acceptCode]);

        if (!$entity) {
            return false;
        }

        $entity->setState(3);
        
        $this->em->flush($entity);

        return $entity;
    }

    public function createOfferFromInquiry($inquiry, $auto = 0, $userId = 0)
    {
        if (is_numeric($inquiry)) {
            $inquiry = $this->em->getInquiryRepository()->find($inquiry);
        }

        if (!$inquiry) {
            return null;
        }

        $baseCoords = $this->em->getSettingRepository()->findOneBy(['code' => 'offer_base_coordinations']);
        $destCoords = $this->googleMaps->geocodeToLatLangArr(['city' => $inquiry->installCity, 'zip' => $inquiry->installZip]);//['city' => 'Jihlava', 'zip' => '586 01']);

        $distRes = $this->googleMaps->distanceValueAndTime(['origins' => [$baseCoords->value], 'destinations' => $destCoords]);
        if ($auto && (!$baseCoords || $destCoords === false || $distRes === false)) {
            $inquiry->setNeedsSalesman(1);
            $this->em->flush();
            if ($auto) {
                return null;
            }
        }

        $offer = new OfferEnt();
        $offer->setOfferNo($this->getNextOfferNo());
        $offer->setCustomer($inquiry->customer);
        $offer->setInstallCity($inquiry->installCity);
        $offer->setInstallZip($inquiry->installZip);
        $offer->setState(0);
        $offer->setInquiry($inquiry);

        if ($userId !== 0) {
            $user = $this->em->getUserRepository()->find($userId);
            $offer->setOriginator($user);
        }

        if ($distRes) {
            $offer->setTransportTime(round($distRes['duration'] / 3600, 2)); // to hours
            $offer->setInstallDistance(round($distRes['distance'] / 1000, 2)); // to km
        }

        if ($inquiry->customer && ($inquiry->customer->idNo || $inquiry->customer->company)) {
            $vat = $this->em->getVatRepository()->find(1);
        } else if ($inquiry->forFamilyHouse) {
            $vat = $this->em->getVatRepository()->find(2);
        } else {
            $vat = $this->em->getVatRepository()->find(3);
        }
        if ($vat) {
            $offer->setVat($vat);
        }

        if ($auto) {
            // plan send
            $settings = $this->baseFrontFac->getSettings();
            $now = new \DateTime();//('today 4pm');
            $todaySendFrom = (new \DateTime('today'))->setTime($settings['offer_auto_from'], 0);
            if ($todaySendFrom < $now) {
                $todaySendFrom = $now;
            }
            $todaySendTo = (new \DateTime('today'))->setTime($settings['offer_auto_to'], 0);
            $randomAddition = mt_rand(20, intval($settings['offer_auto_rand']));
            $plannedSend = (clone $todaySendFrom)->modify('+' . $randomAddition . ' minutes');

            if ($plannedSend > $todaySendTo) {
                $tOverflow = $todaySendTo->diff($plannedSend);
                $plannedSend = (new \DateTime('tomorrow'))->setTime($settings['offer_auto_from'], 0);
                $plannedSend->add($tOverflow);
            }

            $offer->setAutoSend(1);
            $offer->setPlannedSendDate($plannedSend);
        }
        $this->em->persist($offer);
        try {
            $this->em->flush();
        } catch (\Exception $e) {
            return null;
        }

        $this->addOfferProductsFromInquiry($offer, $inquiry);

        // add prices
        $this->calcPrice($offer, true);

        return $offer;
    }

    public function addOfferProductsFromInquiry($offer, $inquiry)
    {
        if (!$offer || !$inquiry) {
            return null;
        }

        if ($offer->products === null) {
            $prodColl = new ArrayCollection();
        } else {
            $prodColl = $offer->products;
        }
        foreach ($inquiry->products as $ip) {
            $prodColl->add($this->addOfferProduct($offer, $ip->product, 0, $ip->price, $ip->count));
        }

        $offer->setProducts($prodColl);
        return true;
    }

    public function addOfferProduct(
        $offer,
        $product,
        $oProdId = 0,
        $price = -1,
        $count = 1
    ) {
        if (is_numeric($offer)) {
            $offer = $this->em->getOfferRepository()->find($offer);
        }
        if (!$offer) {
            return null;
        }

        if (!$product) {
            $product = $this->em->getProductRepository()->find($product);
            if (!$product) {
                return null;
            }
        }

        if ($oProdId != 0) {
            $oProd = $this->em->getOfferProductRepository()->find($oProdId);
        } else {
            $oProd = new \App\Model\Database\Entity\OfferProduct();
            $this->em->persist($oProd);
        }

        $oProd->setOffer($offer);
        $oProd->setProduct($product);
        $oProd->setCount($count);
        if ($price < 0) {
            $oProd->setPrice($product->evid_cena_pol);
        } else {
            $oProd->setPrice($price);
        }
        $oProd->setKlic_polozky($product->klic_polozky);

        $this->em->flush($oProd);

        return $oProd;
    }

    public function calcPriceDelivery($offer)
    {
        if (is_numeric($offer)) {
            $offer = $this->em->getOfferRepository()->find($offer);
            if (!$offer) {
                return null;
            }
        }

        $multiplier = $this->em->getDeliveryPriceRepository()->createQueryBuilder('dp')
            ->where('dp.minDist <= :dist AND dp.maxDist >= :dist')
            ->setMaxResults(1)
            ->setParameters(['dist' => $offer->installDistance])
            ->getQuery()->getResult();

        if (!$multiplier) {
            return false;
        } else {
            $multiplier = $multiplier[0];
        }

        if ($multiplier->flat) {
            $pricePart = $multiplier->price;
        } else {
            $pricePart = $offer->installDistance * $multiplier->price;
        }

        if ($offer->transportCount) {
            $transportCount = $offer->transportCount;
        } else {
            $transportCountSett = $this->em->getSettingRepository()->findOneBy(['code' => 'default_transport_count']);
            if ($transportCountSett) {
                $transportCount = floatval($transportCountSett->value);
            } else {
                $transportCount = 1;
            }
        }

        $offer->setPriceDelivery($pricePart * $transportCount);
        $this->em->flush();

        return $offer;
    }

    public function calcPriceInstall($offer)
    {
        if (is_numeric($offer)) {
            $offer = $this->em->getOfferRepository()->find($offer);
            if (!$offer) {
                return null;
            }
        }

        if ($offer->installWorkers) {
            $workers = $offer->installWorkers;
        } else {
            $workerSett = $this->em->getSettingRepository()->findOneBy(['code' => 'default_install_workers']);
            if ($workerSett) {
                $workers = floatval($workerSett->value);
            } else {
                $workers = 2;
            }
        }

        // hodinova sazba
        $hourPriceProduct = $this->em->getProductRepository()->findOneBy(['klic_polozky' => 1297]);
        // nakladova sazba doprava
        $deliveryPriceProduct = $this->em->getProductRepository()->findOneBy(['klic_polozky' => 1298]);

        $timePrice = $hourPriceProduct->evid_cena_pol *  $workers *  ($offer->transportTime ? $offer->transportTime : 1) * 2;
        $transportPrice = $deliveryPriceProduct->evid_cena_pol * $offer->installDistance;
        $productPriceInstall = 0;
        foreach ($offer->products as $p) {
            $productPriceInstall += $p->price * $p->count;
        }

        $offer->setPriceInstall($timePrice + $transportPrice + $productPriceInstall);
        $this->em->flush();
        return $offer;
    }

    public function calcPrice($offer, $recalcAll = false)
    {
        if (is_numeric($offer)) {
            $offer = $this->em->getOfferRepository()->find($offer);
            if (!$offer) {
                return null;
            }
        }
        
        // product prices
        $price = 0;
        if ($offer->products) {
            foreach ($offer->products as $oProd) {
                if (is_numeric($oProd->price)) {
                    $price += $oProd->price;
                }
            }
        }

        // crane
        $price += $offer->priceCrane;

        // calc rest prices if set
        if ($recalcAll) {
            $this->calcPriceDelivery($offer);
            $this->calcPriceInstall($offer);
        }

        // delivery
        if (is_numeric($offer->priceDelivery)) {
            $price += $offer->priceDelivery;
        }

        // install
        if (is_numeric($offer->priceInstall)) {
            $price += $offer->priceInstall;
        }

        $offer->setPrice($price);
        $this->em->flush();
        return $offer;
    }
}