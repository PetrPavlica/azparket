<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\ProductInPlan;
use App\Model\Database\Entity\ProductionPlan;
use App\Model\Database\Entity\ReservationPlan;
use App\Model\Database\Entity\ReservationProduct;
use Doctrine\Common\Collections\Criteria;
use Nette\Application\UI\Form;

class ProductionPlanPresenter extends BasePresenter
{
    /** @var integer @persistent */
    public $week;

    /** @var integer @persistent */
    public $day;

    /** @var integer @persistent */
    public $month;

    /** @var integer @persistent */
    public $year;

    /** @var string @persistent */
    public $type;

    /** @var integer @persistent */
    public $modalSpot;

    /**
     * ACL name='Plánování výroby'
     * ACL rejection='Nemáte přístup ke plánování výroby.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s plánováním výroby'
     */
    public function renderDefault($type)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        $this->template->hideTitleHeading = true;
        if($type) {
            $this->type = $type;
        } elseif (!$this->type) {
            $this->type = 'KTL';
        }
        $this->template->aaType = $this->type;

        if(!$this->isAjax()) {
            if(file_exists("dfiles/productSelect.txt") && date('Y-m-d') == date('Y-m-d', filemtime("dfiles/productSelect.txt")) && (date('H') - date('H', filemtime("dfiles/productSelect.txt"))) < 5) {
                $productSelect = unserialize(file_get_contents("dfiles/productSelect.txt"));
            } else {
                $productSelect = $this->getProductExternalForSelect();
                file_put_contents("dfiles/productSelect.txt", serialize($productSelect));
            }

            if(file_exists("dfiles/customerSelect.txt") && date('Y-m-d') == date('Y-m-d', filemtime("dfiles/customerSelect.txt")) && (date('H') - date('H', filemtime("dfiles/customerSelect.txt"))) < 5) {
                $customerSelect = unserialize(file_get_contents("dfiles/customerSelect.txt"));
            } else {
                $customerSelect = $this->getCustomerExternalForSelect();
                file_put_contents("dfiles/customerSelect.txt", serialize($customerSelect));
            }

            if(file_exists("dfiles/orderSelect.txt") && date('Y-m-d') == date('Y-m-d', filemtime("dfiles/orderSelect.txt")) && (date('H') - date('H', filemtime("dfiles/orderSelect.txt"))) < 5) {
                $orderSelect = unserialize(file_get_contents("dfiles/orderSelect.txt"));
            } else {
                $orderSelect = $this->getOrderExternalForSelect();
                file_put_contents("dfiles/orderSelect.txt", serialize($orderSelect));
            }
        } else {
            $productSelect = unserialize(file_get_contents("dfiles/productSelect.txt"));
            $customerSelect = unserialize(file_get_contents("dfiles/customerSelect.txt"));
            $orderSelect = unserialize(file_get_contents("dfiles/orderSelect.txt"));
        }
        $orderItemSelect = unserialize(file_get_contents("dfiles/orderItemSelect.txt"));
        $this->template->productSelect = $productSelect;
        $this->template->orderItemSelect = $orderItemSelect;
        $this->template->customerSelect = $customerSelect;
        $this->template->orderSelect = $orderSelect;

        if (!$this->week) {
            $this->week = date('W');
            if ($this->week > 10 && date('n') == 1) {
                $this->year = date('Y', strtotime('-1 year'));
            } else {
                $this->year = date('Y');
            }
        }

        $weekStart = new \DateTime();
        $weekStart->setISODate($this->year, $this->week);
        $weekStart = new \DateTime($weekStart->format('Y').'-'.$weekStart->format('m').'-'.$weekStart->format('d').' 00:00:00');
        $startDate = clone $weekStart;
        $endDate = clone $weekStart;

        $futureDate = clone $weekStart;
        $futureDate->modify('+1month');
        $this->template->futureDate = $futureDate->format('d. m. Y');

        $this->template->dateInput = $weekStart->format('Y-m-d');
        $this->template->year = $this->year;
        $this->template->week = $this->week;

        $weekStart = $weekStart->modify('-1 week');

        $this->template->previousWeek = $weekStart->format('W');
        $this->template->previousYear = $weekStart->format('Y');

        $weekStart = $weekStart->modify('+2 week');

        $this->template->nextWeek = $weekStart->format('W');
        $this->template->nextYear = $weekStart->format('Y');

        $startDate->setTime(0,0,0);
        $endDate = $endDate->modify('+6 days');
        $endDate->setTime(23,59,59);


        $normalFillRest = '#ffffff';
        $customerFillRest = '#f7ff85';
        $styleString = 'background: -webkit-linear-gradient(left, #00ff00 0%, #00ff00 $A$%, #cbf5ff $A$%, #cbf5ff $B$%, $FILL$ $B$%, $FILL$ 100%);
                            background: -moz-linear-gradient(left, #00ff00 0%, #00ff00 $A$%, #cbf5ff $A$%, #cbf5ff $B$%, $FILL$ $B$%, $FILL$ 100%);
                            background: -o-linear-gradient(left, #00ff00 0%, #00ff00 $A$%, #cbf5ff $A$%, #cbf5ff $B$%, $FILL$ $B$%, $FILL$ 100%);
                            background: -ms-linear-gradient(left, #00ff00 0%, #00ff00 $A$%, #cbf5ff $A$%, #cbf5ff $B$%, $FILL$ $B$%, $FILL$ 100%);
                            background: linear-gradient(left, #00ff00 0%, #00ff00 $A$%, #cbf5ff $A$%, #cbf5ff $B$%, $FILL$ $B$%, $FILL$ 100%);';

        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
        $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', $this->type));
        $plans = $this->em->getProductionPlanRepository()->matching($criteriaStart);
        $spotsA = array();
        $spotsB = array();
        //$productAll = $this->getProductExternalAll();
        $productHinges = unserialize(file_get_contents("dfiles/productHinges.txt"));
        $productNumbers = unserialize(file_get_contents("dfiles/productNumbers.txt"));
        foreach ($plans as $plan) {
            $keyString = $plan->dateString . '_' . $plan->name;
            if($plan->products && count($plan->products)) {
                $counterStock = 0;
                $counterNonStock = 0;
                $description = '';
                $title = '';
                $orderName = '';
                // :o:  close();
                if($plan->products && count($plan->products)) {
                    foreach ($plan->products as $conn) {
                        //$prod = $this->getProductExternalById($conn->productId);
                        $prodHin = $productHinges[$conn->productId];
                        if($conn->orderId) {
                            $counterStock += $conn->counts / $prodHin;
                        } else {
                            $counterNonStock += $conn->counts / $prodHin;
                        }
                        $description .= $conn->product . '(' . $conn->counts .'), ';
                        $title .= $productNumbers[$conn->productId] .',';
                        if($conn->orderName) {
                            $orderName = explode(' ', $conn->orderName)[0];
                        }
                    }
                }
                if($description) {
                    $description = substr($description, 0, -2);
                }
                if($plan->customerId) {
                    $description .= '<br><br>' . $plan->customer;
                }
                if($title) {
                    $title = $orderName . ' : ' . substr($title, 0, -1);
                }

                $firstPercent = floor($counterStock * 100);
                $secondPercent = floor($counterNonStock * 100);

                $planStyle = str_replace('$A$', $firstPercent, $styleString);
                $planStyle = str_replace('$B$', $secondPercent, $planStyle);
                if($plan->customerId) {
                    $planStyle = str_replace('$FILL$', $customerFillRest, $planStyle);
                } else {
                    $planStyle = str_replace('$FILL$', $normalFillRest, $planStyle);
                }

                if($plan->shift == 'A') {
                    $spotsA[$keyString] = array();
                    $spotsA[$keyString]['plan'] = $plan;
                    $spotsA[$keyString]['desc'] = $description;
                    $spotsA[$keyString]['title'] = $title;
                    $spotsA[$keyString]['style'] = $planStyle;
                } else {
                    $spotsB[$keyString] = array();
                    $spotsB[$keyString]['plan'] = $plan;
                    $spotsB[$keyString]['desc'] = $description;
                    $spotsB[$keyString]['title'] = $title;
                    $spotsB[$keyString]['style'] = $planStyle;
                }
            } elseif($plan->customerId) {
                $planStyle = str_replace('$A$', '0', $styleString);
                $planStyle = str_replace('$B$', '0', $planStyle);
                $planStyle = str_replace('$FILL$', $customerFillRest, $planStyle);

                $description = $plan->customer;
                if($plan->shift == 'A') {
                    $spotsA[$keyString] = array();
                    $spotsA[$keyString]['plan'] = $plan;
                    $spotsA[$keyString]['desc'] = $description;
                    $spotsA[$keyString]['title'] = $description;
                    $spotsA[$keyString]['style'] = $planStyle;
                } else {
                    $spotsB[$keyString] = array();
                    $spotsB[$keyString]['plan'] = $plan;
                    $spotsB[$keyString]['desc'] = $description;
                    $spotsB[$keyString]['title'] = $description;
                    $spotsB[$keyString]['style'] = $planStyle;
                }
            }
        }


        $dateLoop = clone $startDate;
        $columnsA = array(-1);
        $dateHelpA = array(-1);
        for($n = 0; $n < 7; $n++) {
            $columnsA[$dateLoop->format('Y-m-d')] = $dateLoop->format('j. n. Y');
            $dateHelpA[$dateLoop->format('Y-m-d')] = ['d' => $dateLoop->format('j'), 'm' => $dateLoop->format('n'), 'y' => $dateLoop->format('Y')];
            $dateLoop = $dateLoop->modify('+1 days');
        }

        $numOfFloors = 54;
        $settEntity = $this->em->getProductionSettingRepository()->find(1);
        if($settEntity) {
            $numOfFloors = $settEntity->value;
        }

        $this->template->planType = $this->type;

        $this->template->cusDays = ['', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'];
        $this->template->spotsA = $spotsA;
        $this->template->columnsA = $columnsA;
        $this->template->dateHelpA = $dateHelpA;
        $this->template->floorsA = array_merge(['-1'], range(1,$numOfFloors));
        $this->template->placesA = array(1);

        $this->template->spotsB = $spotsB;
        $this->template->columnsB = $columnsA;
        $this->template->dateHelpB = $dateHelpA;
        $this->template->floorsB = array_merge(['-1'], range(1,$numOfFloors));
        $this->template->placesB = array(1);

        if(isset($this->sess->turnoverExportXlsPlan)){
            header('Content-Disposition: attachment; filename='.$this->sess->turnoverExportXlsPlan['name'] );
            header('Content-Type: application/zip');
            header('Content-Length: ' . filesize($this->sess->turnoverExportXlsPlan['file']));
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            readfile($this->sess->turnoverExportXlsPlan['file']);

            unlink($this->sess->turnoverExportXlsPlan['file']);
            unset($this->sess->turnoverExportXlsPlan);
        }
    }

    /**
     * ACL name='Zobrazení stránky s plánováním výroby'
     */
    public function renderView($type)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        $this->template->hideTitleHeading = true;
        if($type) {
            $this->type = $type;
        } elseif (!$this->type) {
            $this->type = 1;
        }
        $this->template->aaType = $this->type;

        if (!$this->day) {
            $this->day = date('j');
            $this->month = date('n');
            $this->year = date('Y');
        }

        $weekStart = new \DateTime();
        $weekStart->setDate($this->year, $this->month, $this->day);
        $weekStart = new \DateTime($weekStart->format('Y').'-'.$weekStart->format('m').'-'.$weekStart->format('d').' 00:00:00');
        $startDate = clone $weekStart;
        $endDate = clone $weekStart;

        $futureDate = clone $weekStart;
        $futureDate->modify('+1month');
        $this->template->futureDate = $futureDate->format('d. m. Y');

        $this->template->dateInput = $weekStart->format('Y-m-d');
        $this->template->day = $this->day;
        $this->template->month = $this->month;
        $this->template->year = $this->year;

        $weekStart = $weekStart->modify('-1 day');

        $this->template->previousDay = $weekStart->format('j');
        $this->template->previousMonth = $weekStart->format('n');
        $this->template->previousYear = $weekStart->format('Y');

        $weekStart = $weekStart->modify('+2 day');

        $this->template->nextDay = $weekStart->format('j');
        $this->template->nextMonth = $weekStart->format('n');
        $this->template->nextYear = $weekStart->format('Y');

        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);

        $normalFillRest = '#ffffff';
        $customerFillRest = '#f7ff85';
        $stockFillRest = '#00ff00';
        $notStockFillRest = '#cbf5ff';
        $styleString = 'background: -webkit-linear-gradient(left, #00ff00 0%, #00ff00 $A$%, #cbf5ff $A$%, #cbf5ff $B$%, $FILL$ $B$%, $FILL$ 100%);
                            background: -moz-linear-gradient(left, #00ff00 0%, #00ff00 $A$%, #cbf5ff $A$%, #cbf5ff $B$%, $FILL$ $B$%, $FILL$ 100%);
                            background: -o-linear-gradient(left, #00ff00 0%, #00ff00 $A$%, #cbf5ff $A$%, #cbf5ff $B$%, $FILL$ $B$%, $FILL$ 100%);
                            background: -ms-linear-gradient(left, #00ff00 0%, #00ff00 $A$%, #cbf5ff $A$%, #cbf5ff $B$%, $FILL$ $B$%, $FILL$ 100%);
                            background: linear-gradient(left, #00ff00 0%, #00ff00 $A$%, #cbf5ff $A$%, #cbf5ff $B$%, $FILL$ $B$%, $FILL$ 100%);';
        $beforeStyleString = 'background-color: ';

        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
        $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', $this->type));
        $plans = $this->em->getProductionPlanRepository()->matching($criteriaStart);
        $spotsA = array();
        $spotsB = array();
        $counterA = 0;
        $counterB = 0;
        //$productAll = $this->getProductExternalAll();
        $productHinges = unserialize(file_get_contents("dfiles/productHinges.txt"));
        $productNumbers = unserialize(file_get_contents("dfiles/productNumbers.txt"));
        foreach ($plans as $plan) {
            $keyString = $plan->dateString . '_' . $plan->name;
            if($plan->products && count($plan->products)) {
                $counterStock = 0;
                $counterNonStock = 0;
                $description = '';
                $title = '';
                $orderName = '';
                $code = '';
                $hinges = '';
                $counts = '';
                // :o:  close();
                if($plan->products && count($plan->products)) {
                    foreach ($plan->products as $conn) {
                        //$prod = $this->getProductExternalById($conn->productId);
                        $prodHin = $productHinges[$conn->productId];
                        if($conn->orderId) {
                            $counterStock += $conn->counts / $prodHin;
                        } else {
                            $counterNonStock += $conn->counts / $prodHin;
                        }
                        $description .= $conn->product . '(' . $conn->counts .'), ';
                        $hinges .= $prodHin .',';
                        $counts .= $conn->counts .',';
                        $title .= explode(';', $conn->product)[0] .',';
                        $code .= $productNumbers[$conn->productId] .',';

                        if($conn->orderName) {
                            $orderName = explode(' ', $conn->orderName)[0];
                        }
                    }
                }
                if($description) {
                    $description = substr($description, 0, -2);
                }
                if($plan->customerId) {
                    $description .= '<br><br>' . $plan->customer;
                }
                if($title) { $title = substr($title, 0, -1); }
                if($code) { $code = substr($code, 0, -1); }
                if($hinges) { $hinges = substr($hinges, 0, -1); }
                if($counts) { $counts = substr($counts, 0, -1); }

                $firstPercent = floor($counterStock * 100);
                $secondPercent = floor($counterNonStock * 100);

                $planStyle = str_replace('$A$', $firstPercent, $styleString);
                $planStyle = str_replace('$B$', $secondPercent, $planStyle);
                if($plan->customerId) {
                    $planStyle = str_replace('$FILL$', $customerFillRest, $planStyle);
                } else {
                    $planStyle = str_replace('$FILL$', $normalFillRest, $planStyle);
                }
                if($firstPercent > 0) {
                    $beforeStyle = $beforeStyleString . $stockFillRest;
                } else {
                    $beforeStyle = $beforeStyleString . $notStockFillRest;
                }

                if($plan->shift == 'A') {
                    $spotsA[$keyString] = array();
                    $spotsA[$keyString]['plan'] = $plan;
                    $spotsA[$keyString]['desc'] = $description;
                    $spotsA[$keyString]['code'] = $code;
                    $spotsA[$keyString]['title'] = $title;
                    $spotsA[$keyString]['order'] = $orderName;
                    $spotsA[$keyString]['hinges'] = $hinges;
                    $spotsA[$keyString]['counts'] = $counts;
                    $spotsA[$keyString]['style'] = $planStyle;
                    $spotsA[$keyString]['beforeStyle'] = $beforeStyle;

                    if(!$plan->rodSend) {
                        $counterA++;
                    }
                } else {
                    $spotsB[$keyString] = array();
                    $spotsB[$keyString]['plan'] = $plan;
                    $spotsB[$keyString]['desc'] = $description;
                    $spotsB[$keyString]['code'] = $code;
                    $spotsB[$keyString]['title'] = $title;
                    $spotsB[$keyString]['order'] = $orderName;
                    $spotsB[$keyString]['hinges'] = $hinges;
                    $spotsB[$keyString]['counts'] = $counts;
                    $spotsB[$keyString]['style'] = $planStyle;
                    $spotsB[$keyString]['beforeStyle'] = $beforeStyle;

                    if(!$plan->rodSend) {
                        $counterB++;
                    }
                }
            } elseif($plan->customerId) {
                $planStyle = str_replace('$A$', '0', $styleString);
                $planStyle = str_replace('$B$', '0', $planStyle);
                $planStyle = str_replace('$FILL$', $customerFillRest, $planStyle);
                $beforeStyle = $beforeStyleString . $customerFillRest;

                $description = $plan->customer;
                if($plan->shift == 'A') {
                    $spotsA[$keyString] = array();
                    $spotsA[$keyString]['plan'] = $plan;
                    $spotsA[$keyString]['desc'] = $description;
                    $spotsA[$keyString]['code'] = '';
                    $spotsA[$keyString]['title'] = $description;
                    $spotsA[$keyString]['order'] = '';
                    $spotsA[$keyString]['hinges'] = '';
                    $spotsA[$keyString]['counts'] = '';
                    $spotsA[$keyString]['style'] = $planStyle;
                    $spotsA[$keyString]['beforeStyle'] = $beforeStyle;

                    if(!$plan->rodSend) {
                        $counterA++;
                    }
                } else {
                    $spotsB[$keyString] = array();
                    $spotsB[$keyString]['plan'] = $plan;
                    $spotsB[$keyString]['desc'] = $description;
                    $spotsB[$keyString]['code'] = '';
                    $spotsB[$keyString]['title'] = $description;
                    $spotsB[$keyString]['order'] = '';
                    $spotsB[$keyString]['hinges'] = '';
                    $spotsB[$keyString]['counts'] = '';
                    $spotsB[$keyString]['style'] = $planStyle;
                    $spotsB[$keyString]['beforeStyle'] = $beforeStyle;

                    if(!$plan->rodSend) {
                        $counterB++;
                    }
                }
            }
        }

        $numOfFloors = 54;
        $settEntity = $this->em->getProductionSettingRepository()->find(1);
        if($settEntity) {
            $numOfFloors = $settEntity->value;
        }

        $this->template->planType = $this->type;
        $this->template->cusDays = ['', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'];
        $this->template->rodsTotal = [1 => $counterA.'/'.$numOfFloors, 2 => $counterB.'/'.$numOfFloors];
        $this->template->ymd = $startDate->format('Y-m-d');

        $this->template->spotsA = $spotsA;
        $this->template->floorsA = range(1,$numOfFloors);
        $this->template->placesA = array(1);

        $this->template->spotsB = $spotsB;
        $this->template->floorsB = range(1,$numOfFloors);
        $this->template->placesB = array(1);
    }

    public function handleRodHangChange() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getProductionPlanRepository()->find($values['item']);
            if($values['val']) {
                $entity->setRodHang(1);
            } else {
                $entity->setRodHang(0);
            }
            $this->em->flush();
        }
        $this->redrawControl('rodsTop');
        $this->redrawControl('rodsBot');
    }

    public function handleRodSendChange() {
        $values = $this->request->getPost();
        if($values['item']) {
            $entity = $this->em->getProductionPlanRepository()->find($values['item']);
            if($values['val']) {
                $entity->setRodSend(1);
            } else {
                $entity->setRodSend(0);
            }
            $this->em->flush();
        }
        $this->redrawControl('rodsTop');
        $this->redrawControl('rodsBot');
    }

    public function getProductExternalForSelect() {
        $oldFiles = 1;
        $oldFilesNameCounter = 1;
        while($oldFiles) {
            if(file_exists("dfiles/productAll-".$oldFilesNameCounter.".txt")) {
                unlink("dfiles/productAll-".$oldFilesNameCounter.".txt");
            } else {
                $oldFiles = 0;
            }
            $oldFilesNameCounter++;
        }

        $productArr = array();
        $productAll = array();
        $productHinges = array();
        $productNumbers = array();
        $countResults = 1;
        $failSafePage = 1;
        while($countResults > 0 && $failSafePage < 50) {
            $curl = curl_init();
            $qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1000));
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://192.168.1.112:3000/v1/product?" . $qParams,
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

            $productPattern = '/^(?!\.\.|11\/).*(?<!\/P)$/';
            foreach ($data as $prodArray) {
                if (preg_match($productPattern, $prodArray['productNumber'])) {
                    $productArr[$prodArray['id']] = $prodArray['productNumber'].' - '.$prodArray['name'];
                    $productHinges[$prodArray['id']] = $prodArray['countPerHinge'];
                    $productNumbers[$prodArray['id']] = $prodArray['productNumber'];
                }
                $productAll[$prodArray['id']] = $prodArray;
            }

            if($productAll) {
                file_put_contents("dfiles/productAll-".$failSafePage.".txt", serialize($productAll));
                $productAll = array();
            }

            $countResults = count($data);
            $failSafePage++;
        }

        file_put_contents("dfiles/productHinges.txt", serialize($productHinges));
        file_put_contents("dfiles/productNumbers.txt", serialize($productNumbers));
        asort($productArr);
        return $productArr;
    }

    public function getProductExternalById($productId) {
        $foundData = 0;

        $failSafePage = 1;
        if(file_exists("dfiles/productAll-1.txt") && date('Y-m-d') == date('Y-m-d', filemtime("dfiles/productAll-1.txt"))) {
            $oldFiles = 1;
            $oldFilesNameCounter = 1;
            while($oldFiles) {
                if(file_exists("dfiles/productAll-".$oldFilesNameCounter.".txt")) {
                    $productAll = unserialize(file_get_contents("dfiles/productAll-".$oldFilesNameCounter.".txt"));
                    if(isset($productAll[$productId])) {
                        $data = $productAll[$productId];
                        $oldFiles = 0;
                        $foundData = 1;
                    }
                } else {
                    $oldFiles = 0;
                }
                $oldFilesNameCounter++;
            }
        }

        if(!$foundData)  {
            $curl = curl_init();
            $qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1));
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://192.168.1.112:3000/v1/product/".$productId."?" . $qParams,
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
        }

        return $data;
    }

    public function getProductExternalAll() {
        if(file_exists("dfiles/productAll.txt") && date('Y-m-d') == date('Y-m-d', filemtime("dfiles/productAll.txt"))) {
            $productAll = unserialize(file_get_contents("dfiles/productAll.txt"));
        } else {
            $this->getProductExternalForSelect();
            $productAll = unserialize(file_get_contents("dfiles/productAll.txt"));
        }
        return $productAll;
    }

    public function getCustomerExternalForSelect() {
        $customerArr = array();
        $customerAll = array();
        $countResults = 1;
        $failSafePage = 1;
        while($countResults > 0 && $failSafePage < 50) {
            $curl = curl_init();
            $qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1000));
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://192.168.1.112:3000/v1/customer?" . $qParams,
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
                $customerArr[$cusArray['id']] = $cusArray['name'];
                $customerAll[$cusArray['id']] = $cusArray;
            }

            $countResults = count($data);
            $failSafePage++;
        }

        file_put_contents("dfiles/customerAll.txt", serialize($customerAll));
        asort($customerArr);
        return $customerArr;
    }

    public function getCustomerExternalById($customerId) {
        $failSafePage = 1;
        if(file_exists("dfiles/customerAll.txt") && date('Y-m-d') == date('Y-m-d', filemtime("dfiles/customerAll.txt"))) {
            $customerAll = unserialize(file_get_contents("dfiles/customerAll.txt"));
            $data = $customerAll[$customerId];
        } else {
            $curl = curl_init();
            $qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1));
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://192.168.1.112:3000/v1/customer/".$customerId."?" . $qParams,
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
        }

        return $data;
    }

    public function getOrderExternalForSelect() {
        $orderArr = array();
        $orderItemArr = array();
        $orderAll = array();
        //$productAll = $this->getProductExternalAll();
        $productSelect = unserialize(file_get_contents("dfiles/productSelect.txt"));
        $countResults = 1;
        $failSafePage = 1;
        $todayDate = date('Y-m-d');
        while($countResults > 0 && $failSafePage < 50) {
            $curl = curl_init();
            $qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1000));
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://192.168.1.112:3000/v1/order?" . $qParams,
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
            foreach ($data as $ordArray) {
                $dateFormated = '';
                if($ordArray['dateOfDelivery']) {
                    $dateHelpSub = substr($ordArray['dateOfDelivery'], 0, strpos($ordArray['dateOfDelivery'], 'T'));
                    $dateHelpAfter = new \DateTime($dateHelpSub);
                    $dateFormated = ' - ' . $dateHelpAfter->format('j.n.Y');
                }

                if (!$ordArray['dateOfDelivery'] || ($todayDate <= substr($ordArray['dateOfDelivery'], 0, strpos($ordArray['dateOfDelivery'], 'T')))) {
                    $orderArr[$ordArray['id']] = $ordArray['contractNumber'].' - '.$ordArray['name'].' ('.$ordArray['orderNumber'].') '.($dateFormated ? $dateFormated : '');

                    foreach ($ordArray['orderItems'] as $ordItem) {
                        if($ordItem['id'] && $ordItem['quantity'] && $ordItem['productId'] && isset($productSelect[$ordItem['productId']])) {
                            $orderItemArr[$ordItem['id'] . '_' . $ordItem['quantity'] . '_' . $ordItem['productId'] . '_' . $ordArray['id'] . '_' . 'ORDER'] = 'Objednávka ' . $ordArray['contractNumber'].' - '.$ordArray['name'] . ' Položka: ' . $productSelect[$ordItem['productId']] . '('.$ordItem['quantity'].'ks)';
                        }
                    }


                }
                $orderAll[$ordArray['id']] = $ordArray;
            }

            $countResults = count($data);
            $failSafePage++;
        }

        file_put_contents("dfiles/orderAll.txt", serialize($orderAll));
        asort($orderItemArr);
        file_put_contents("dfiles/orderItemSelect.txt", serialize($orderItemArr));
        asort($orderArr);
        return $orderArr;
    }

    public function getOrderExternalById($orderId) {
        $failSafePage = 1;
        if(file_exists("dfiles/orderAll.txt") && date('Y-m-d') == date('Y-m-d', filemtime("dfiles/orderAll.txt"))) {
            $orderAll = unserialize(file_get_contents("dfiles/orderAll.txt"));
            $data = $orderAll[$orderId];
        } else {
            $curl = curl_init();
            $qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1));
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://192.168.1.112:3000/v1/order/".$orderId."?" . $qParams,
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
        }

        return $data;
    }

    public function getOrderExternalAll() {
        if(file_exists("dfiles/orderAll.txt") && date('Y-m-d') == date('Y-m-d', filemtime("dfiles/orderAll.txt"))) {
            $orderAll = unserialize(file_get_contents("dfiles/orderAll.txt"));
        } else {
            $this->getProductExternalForSelect();
            $orderAll = unserialize(file_get_contents("dfiles/orderAll.txt"));
        }
        return $orderAll;
    }

    public function handleCheckSpotPlan() {
        $values = $this->request->getPost();
        $stats = explode('_', $values['spot']);

        $modalSpot = $this->em->getProductionPlanRepository()->findOneBy(['shift' => $stats[0], 'dateString' => $stats[1], 'name' => $stats[2], 'productionLine' => $stats[3]]);
        if(!$modalSpot) {
            $modalSpot = new ProductionPlan();
            $modalSpot->setName($stats[2]);
            $modalSpot->setShift($stats[0]);
            $modalSpot->setDateString($stats[1]);
            $modalSpot->setDatePlan(new \DateTime($stats[1]));
            $modalSpot->setProductionLine($stats[3]);

            $this->em->persist($modalSpot);
            $this->em->flush();
        }
        $this->template->modalSpot = $modalSpot;
        $futureDate = clone $modalSpot->datePlan;
        $futureDate->modify('+1month');
        $this->template->futureDate = $futureDate->format('d. m. Y');
        /*$rod = NULL;
        if($modalSpot && $modalSpot->productRod) {
            $rod = $modalSpot->productRod;
        }*/

        //$this->template->productSelect = $this->getProductExternalForSelect();
        //$this->template->customerSelect = $this->getCustomerExternalForSelect();
        $this->redrawControl('planModal');
    }

    public function handleRemoveProductSpot() {
        $values = $this->request->getPost();
        $entity = $this->em->getProductInPlanRepository()->find($values['plan']);
        //$entity->product->setCountsLeft($entity->product->countsLeft + $entity->counts);
        $planId = $entity->plan->id;
        $this->em->remove($entity);
        $this->em->flush();

        /*$checkOthers = $this->em->getProductInPlanRepository()->findOneBy(['plan' => $planId]);
        if(!$checkOthers) {
            $plan = $this->em->getProductionPlanRepository()->find($planId);
            $plan->setProductRod(NULL);
            $this->em->flush();
        }*/
        //$this->redirect('this');

        $modalSpot = $this->em->getProductionPlanRepository()->find($planId);
        $this->template->modalSpot = $modalSpot;
        $futureDate = clone $modalSpot->datePlan;
        $futureDate->modify('+1month');
        $this->template->futureDate = $futureDate->format('d. m. Y');
        $this->redrawControl('planModal');
        $this->redrawControl('planTop');
        $this->redrawControl('planBot');
    }

    public function handleRemoveProductSpotAll() {
        $values = $this->request->getPost();
        $modalSpot = $this->em->getProductionPlanRepository()->find($values['id']);

        $entities = $this->em->getProductInPlanRepository()->findBy(['plan' => $modalSpot]);
        foreach ($entities as $entity) {
            $this->em->remove($entity);
        }
        $this->em->flush();

        $this->template->modalSpot = $modalSpot;
        $futureDate = clone $modalSpot->datePlan;
        $futureDate->modify('+1month');
        $this->template->futureDate = $futureDate->format('d. m. Y');
        $this->redrawControl('planModal');
        $this->redrawControl('planTop');
        $this->redrawControl('planBot');
    }

    public function handleRemoveCustomerSpot() {
        $values = $this->request->getPost();
        $entity = $this->em->getProductionPlanRepository()->find($values['plan']);
        $entity->setCustomer(NULL);
        $entity->setCustomerId(NULL);
        $this->em->flush();

        $this->redirect('this');
    }

    public function handleRemoveReservationSpot() {
        $values = $this->request->getPost();
        $entity = $this->em->getReservationPlanRepository()->find($values['reservation']);

        $plans = $this->em->getProductionPlanRepository()->findBy(['reservation' => $entity]);
        foreach ($plans as $plan) {
            $plan->setReservation(NULL);
        }
        $this->em->flush();
        $this->em->remove($entity);
        $this->em->flush();

        $this->redirect('this');
    }

    public function handleRemoveReservationProductSpot() {
        $values = $this->request->getPost();
        $entity = $this->em->getReservationProductRepository()->find($values['reservation']);

        $plans = $this->em->getProductInPlanRepository()->findBy(['reservation' => $entity]);
        foreach ($plans as $plan) {
            $plan->setReservation(NULL);
        }
        $this->em->flush();
        $this->em->remove($entity);
        $this->em->flush();

        $this->redirect('this');
    }

    public function handleChangeOrderSelect() {
        $values = $this->request->getPost();
        $order = $this->getOrderExternalById($values['orderId']);

        $orderItems = array();
        foreach ($order['orderItems'] as $oItem) {
            $product = $this->getProductExternalById($oItem['productId']);
            $orderItems[] = ['name' => $product['productNumber'].' - '.$product['name'], 'quantity' => $oItem['quantity'], 'productId' => $oItem['productId'], 'id' => $oItem['id']];
        }

        $this->template->orderItems = $orderItems;
        $this->redrawControl('planModalOrder');
    }

    public function handleDragProductSpot() {
        $values = $this->request->getPost();
        // :o:  close();
        $statsStart = explode('_', $values['start']);
        $spotStart = $this->em->getProductionPlanRepository()->findOneBy(['shift' => $statsStart[0], 'dateString' => $statsStart[1], 'name' => $statsStart[2], 'productionLine' => $statsStart[3]]);
        if(!$spotStart) {
            $spotStart = new ProductionPlan();
            $spotStart->setName($statsStart[2]);
            $spotStart->setShift($statsStart[0]);
            $spotStart->setDateString($statsStart[1]);
            $spotStart->setDatePlan(new \DateTime($statsStart[1]));
            $spotStart->setProductionLine($statsStart[3]);

            $this->em->persist($spotStart);
            $this->em->flush();
        }

        $statsEnd = explode('_', $values['end']);
        $spotEnd = $this->em->getProductionPlanRepository()->findOneBy(['shift' => $statsEnd[0], 'dateString' => $statsEnd[1], 'name' => $statsEnd[2], 'productionLine' => $statsEnd[3]]);
        if(!$spotEnd) {
            $spotEnd = new ProductionPlan();
            $spotEnd->setName($statsEnd[2]);
            $spotEnd->setShift($statsEnd[0]);
            $spotEnd->setDateString($statsEnd[1]);
            $spotEnd->setDatePlan(new \DateTime($statsEnd[1]));
            $spotEnd->setProductionLine($statsEnd[3]);

            $this->em->persist($spotEnd);
            $this->em->flush();
        }

        $plansStart = $this->em->getProductInPlanRepository()->findBy(['plan' => $spotStart]);
        $plansEnd = $this->em->getProductInPlanRepository()->findBy(['plan' => $spotEnd]);
        foreach ($plansStart as $plan) {
            $plan->setPlan($spotEnd);
        }
        foreach ($plansEnd as $plan) {
            $plan->setPlan($spotStart);
        }

        $customerStart = $spotStart->customer;
        $customerIdStart = $spotStart->customerId;
        $reservationStart = $spotStart->reservation;
        $spotStart->setCustomer($spotEnd->customer);
        $spotStart->setCustomerId($spotEnd->customerId);
        $spotStart->setReservation($spotEnd->reservation);
        $spotEnd->setCustomer($customerStart);
        $spotEnd->setCustomerId($customerIdStart);
        $spotEnd->setReservation($reservationStart);

        $this->em->flush();

        //$this->redrawControl('planTop');
        //$this->redrawControl('planBot');
    }

    public function createComponentPlanModalForm()
    {
        $that = $this;

        $form = new Form();
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();
            if(isset($values2['spot']) && $values2['spot']) {
                if(isset($values2['reserve']) && isset($values2['customer']) && $values2['customer']) {
                    $plan = $this->em->getProductionPlanRepository()->find($values2['spot']);
                    $customer = $this->getCustomerExternalById($values2['customer']);

                    if($customer) {
                        $reserveRepeat = 0;
                        if(isset($values2['reserveRepeat']) && $values2['reserveRepeat']) {
                            $reserveRepeat = $values2['reserveRepeat'];
                        }

                        if($reserveRepeat) {
                            $reservation = new ReservationPlan();
                            $reservation->setName($values2['reserveRepeat']);
                            $this->em->persist($reservation);
                        }

                        //$plan->setReservation($reservation);

                        $numOfFloors = 54;
                        $settEntity = $this->em->getProductionSettingRepository()->find(1);
                        if($settEntity) {
                            $numOfFloors = $settEntity->value;
                        }

                        if($reserveRepeat) {
                            $maxDay = new \DateTime();
                            $maxDay->setTimestamp(strtotime('saturday next week ' . $plan->dateString));
                            $maxDateString = $maxDay->format('Y-m-d');
                            if(isset($values2['reserveEndDate']) && $values2['reserveEndDate']) {
                                $maxDay = date_create_from_format('d. m. Y', $values2['reserveEndDate']);
                                if($maxDay) {
                                    $maxDateString = $maxDay->format('Y-m-d');
                                }
                            }
                        } else {
                            $maxDay = new \DateTime($plan->datePlan->format('Y-m-d').' 00:00:00');
                            $maxDay->modify('+2 day');
                            $maxDateString = $maxDay->format('Y-m-d');
                        }

                        $addNumberSpace = 1;
                        if(isset($values2['reserveFillSpace']) && $values2['reserveFillSpace']) {
                            $addNumberSpace += $values2['reserveFillSpace'];
                        }

                        $nDateString = $plan->datePlan->format('Y-m-d');
                        $notFirstLoop = 1;
                        while($nDateString < $maxDateString) {
                            if($notFirstLoop) {
                                $notFirstLoop = 0;
                            } else {
                                $newDate = new \DateTime($nDateString.' 00:00:00');
                                if($reserveRepeat) {
                                    if($reservation->name == 'D') {
                                        $newDate->modify('+1 day');
                                    } elseif($reservation->name == 'T') {
                                        $newDate->modify('+7 days');
                                    } else {
                                        $newDate->modify('+1 month');
                                    }
                                } else {
                                    $newDate->modify('+10 day');
                                }
                                $nDateString = $newDate->format('Y-m-d');
                            }

                            $spotsLeft = 1;
                            if(isset($values2['reserveNumber']) && $values2['reserveNumber']) {
                                $spotsLeft = $values2['reserveNumber'];
                            }
                            $cycleHelper = 0;
                            $nShift = $plan->shift;
                            $nName = $plan->name - 1;
                            $lastSpotWasGood = 0;
                            while($spotsLeft) {
                                if($lastSpotWasGood) {
                                    $nName += $addNumberSpace;
                                } else {
                                    $nName += 1;
                                }
                                if($nName > $numOfFloors) {
                                    if($nShift == 'A') {
                                        $nShift = 'B';
                                    } else {
                                        $nShift = 'A';
                                    }
                                    $nName -= $numOfFloors;
                                }

                                if($nShift == $plan->shift && $nName == $plan->name) {
                                    $cycleHelper++;
                                    if($cycleHelper >= 2) {
                                        break;
                                    }
                                }

                                $emptySpot = 0;
                                $modalSpot = NULL;
                                $modalSpot = $this->em->getProductionPlanRepository()->findOneBy(['shift' => $nShift, 'dateString' => $nDateString, 'name' => $nName, 'productionLine' => $plan->productionLine]);
                                if(!$modalSpot) {
                                    $modalSpot = new ProductionPlan();
                                    $modalSpot->setName($nName);
                                    $modalSpot->setShift($nShift);
                                    $modalSpot->setDateString($nDateString);
                                    $modalSpot->setDatePlan(new \DateTime($nDateString));
                                    $modalSpot->setProductionLine($plan->productionLine);

                                    $this->em->persist($modalSpot);

                                    $emptySpot = 1;
                                } else {
                                    if(!$modalSpot->customerId && !($modalSpot->products && count($modalSpot->products))) {
                                        $emptySpot = 1;
                                    }
                                }

                                if(!$emptySpot) {
                                    $lastSpotWasGood = 0;
                                    continue;
                                }
                                $lastSpotWasGood = 1;

                                $modalSpot->setCustomer($customer['name']);
                                $modalSpot->setCustomerId($customer['id']);
                                if($reserveRepeat) {
                                    $modalSpot->setReservation($reservation);
                                }
                                $spotsLeft--;
                            }

                            if(!$reserveRepeat) {
                                $newDate = new \DateTime($nDateString.' 00:00:00');
                                $newDate->modify('+10 day');
                                $nDateString = $newDate->format('Y-m-d');
                            }
                        }
                    }
                } elseif(isset($values2['ordered']) && isset($values2['orders']) && $values2['orders']) {
                    $order = $this->getOrderExternalById($values2['orders']);
                    if($order) {
                        $numOfFloors = 54;
                        $settEntity = $this->em->getProductionSettingRepository()->find(1);
                        if($settEntity) {
                            $numOfFloors = $settEntity->value;
                        }
                        $plan = $this->em->getProductionPlanRepository()->find($values2['spot']);
                        //$productAll = $this->getProductExternalAll();

                        $nShift = $plan->shift;
                        $nDateString = $plan->dateString;
                        $nName = $plan->name;
                        $addNumberSpace = 1;
                        if(isset($values2['orderFillSpace']) && $values2['orderFillSpace']) {
                            $addNumberSpace += $values2['orderFillSpace'];
                        }

                        $fillRods = 1000;
                        if(isset($values2['orderFillRods']) && $values2['orderFillRods']) {
                            $fillRods = $values2['orderFillRods'];
                        }

                        $orderRepeat = 0;
                        $maxDay = new \DateTime('2030-12-31 23:59:59');
                        $maxDateString = $maxDay->format('Y-m-d');
                        if(isset($values2['orderRepeat']) && $values2['orderRepeat']) {
                            $orderRepeat = $values2['orderRepeat'];
                            if(isset($values2['orderEndDate']) && $values2['orderEndDate']) {
                                $maxDay = date_create_from_format('d. m. Y', $values2['orderEndDate']);
                                if($maxDay) {
                                    $maxDateString = $maxDay->format('Y-m-d');
                                }
                            }
                        }

                        $dateFormated = '';
                        if($order['dateOfDelivery']) {
                            $dateHelpSub = substr($order['dateOfDelivery'], 0, strpos($order['dateOfDelivery'], 'T'));
                            $dateHelpAfter = new \DateTime($dateHelpSub);
                            $dateFormated = ' - ' . $dateHelpAfter->format('j.n.Y');
                        }
                        $orderName = $order['contractNumber'].' - '.$order['name'].' ('.$order['orderNumber'].') '.($dateFormated ? $dateFormated : '');
                        // :o:  close();
                        $rodsFilled = 0;
                        $rodsFillIds = array();
                        $baseDateStringForRepeat = $nDateString;
                        foreach ($order['orderItems'] as $oItem) {
                            if(!isset($values2['orderUse-' . $oItem['id']])) {
                                continue;
                            }
                            $product = $this->getProductExternalById($oItem['productId']);
                            if($product) {
                                if(isset($values2['orderQuantity-' . $oItem['id']])) {
                                    $productsLeft = $values2['orderQuantity-' . $oItem['id']];
                                } else {
                                    $productsLeft = $oItem['quantity'];
                                }

                                $lastSpotWasGood = 1;
                                $newItemFromOrder = 1;
                                while($productsLeft) {
                                    if($rodsFilled >= $fillRods) {
                                        if($orderRepeat) {
                                            $newDate = new \DateTime($baseDateStringForRepeat . ' 00:00:00');
                                            if ($orderRepeat == 'D') {
                                                $newDate->modify('+1 day');
                                            } elseif ($orderRepeat == 'T') {
                                                $newDate->modify('+7 days');
                                            } else {
                                                $newDate->modify('+1 month');
                                            }
                                            $nDateString = $newDate->format('Y-m-d');
                                            $baseDateStringForRepeat = $nDateString;

                                            if($nDateString > $maxDateString) {
                                                break 2;
                                            }
                                            $newItemFromOrder = 1;
                                            $nName = $plan->name;
                                            $nShift = $plan->shift;

                                            $rodsFilled = 0;
                                            $rodsFillIds = array();
                                        } else {
                                            break 2;
                                        }
                                    }

                                    if($newItemFromOrder) {
                                        $newItemFromOrder = 0;
                                    } else {
                                        if($lastSpotWasGood) {
                                            $nName += $addNumberSpace;
                                        } else {
                                            $nName += 1;
                                        }
                                    }
                                    while($nName > $numOfFloors) {
                                        if($nShift == 'A') {
                                            $nShift = 'B';
                                        } else {
                                            $nShift = 'A';
                                            $newDate = new \DateTime($nDateString.' 00:00:00');
                                            $newDate->modify('+1 day');
                                            $nDateString = $newDate->format('Y-m-d');
                                        }
                                        $nName -= $numOfFloors;
                                    }

                                    $emptySpot = 0;
                                    $modalSpot = NULL;
                                    $modalSpot = $this->em->getProductionPlanRepository()->findOneBy(['shift' => $nShift, 'dateString' => $nDateString, 'name' => $nName, 'productionLine' => $plan->productionLine]);
                                    if(!$modalSpot) {
                                        $modalSpot = new ProductionPlan();
                                        $modalSpot->setName($nName);
                                        $modalSpot->setShift($nShift);
                                        $modalSpot->setDateString($nDateString);
                                        $modalSpot->setDatePlan(new \DateTime($nDateString));
                                        $modalSpot->setProductionLine($plan->productionLine);

                                        $this->em->persist($modalSpot);
                                        $this->em->flush();

                                        $emptySpot = 1;
                                    }
                                    if(!$modalSpot->products || !count($modalSpot->products)) {
                                        $emptySpot = 1;
                                    }

                                    if(!$emptySpot) {
                                        $lastSpotWasGood = 0;
                                        continue;
                                    }
                                    $lastSpotWasGood = 1;

                                    $freeHooksPercent = 1;
                                    $modalSpotProducts = $this->em->getProductInPlanRepository()->findBy(['plan' => $modalSpot->id]);
                                    if($modalSpotProducts && count($modalSpotProducts)) {
                                        foreach ($modalSpotProducts as $conn) {
                                            $prod = $this->getProductExternalById($conn->productId);
                                            $freeHooksPercent -= $conn->counts / $prod['countPerHinge'];
                                        }
                                    }
                                    if($freeHooksPercent <= 0) {
                                        $lastSpotWasGood = 0;
                                        continue;
                                    }

                                    $maxAdd = floor($freeHooksPercent * $product['countPerHinge']);
                                    $addCounts = $maxAdd;
                                    if($maxAdd > $productsLeft) {
                                        $addCounts = $productsLeft;
                                    }

                                    if($addCounts) {
                                        $prodInPlan = new ProductInPlan();
                                        $prodInPlan->setProduct($product['name']);
                                        $prodInPlan->setProductId($product['id']);
                                        $prodInPlan->setPlan($modalSpot);
                                        $prodInPlan->setCounts($addCounts);
                                        $prodInPlan->setOrderName($orderName);
                                        $prodInPlan->setOrderId($order['id']);
                                        $prodInPlan->setOrderItemId($oItem['id']);
                                        $this->em->persist($prodInPlan);
                                        $this->em->flush();
                                    }

                                    if(!in_array($modalSpot->id, $rodsFillIds)) {
                                        $rodsFillIds[] = $modalSpot->id;
                                        $rodsFilled++;
                                    }
                                    $productsLeft -= $addCounts;
                                }
                            }
                        }
                    }
                } elseif(isset($values2['product']) && $values2['product']) {
                    //$product = $this->em->getProductRepository()->find($values2['product']);
                    $orderItemId = 0;
                    $order = 0;
                    $itemCounts = 0;
                    $orderName = '';
                    if(strpos($values2['product'], 'ORDER') !== false) {
                        $valArray = explode('_', $values2['product']);
                        $orderItemId = $valArray[0];
                        $itemCounts = $valArray[1];
                        $product = $this->getProductExternalById($valArray[2]);
                        $order = $this->getOrderExternalById($valArray[3]);

                        if($order) {
                            $dateFormated = '';
                            if($order['dateOfDelivery']) {
                                $dateHelpSub = substr($order['dateOfDelivery'], 0, strpos($order['dateOfDelivery'], 'T'));
                                $dateHelpAfter = new \DateTime($dateHelpSub);
                                $dateFormated = ' - ' . $dateHelpAfter->format('j.n.Y');
                            }
                            $orderName = $order['contractNumber'].' - '.$order['name'].' ('.$order['orderNumber'].') '.($dateFormated ? $dateFormated : '');
                        }
                    } else {
                        $product = $this->getProductExternalById($values2['product']);
                    }

                    if(isset($values2['fillAll']) && $values2['fillAll']) {
                        if($values2['fillAll'] > $itemCounts) {
                            $itemCounts = $values2['fillAll'];
                        }
                    }
                    if($product) {
                        $plan = $this->em->getProductionPlanRepository()->find($values2['spot']);
                        //$productAll = $this->getProductExternalAll();

                        $reservation = NULL;
                        if(isset($values2['reserveProduct']) && $values2['reserveProduct']) {
                            $reservation = new ReservationProduct();
                            $reservation->setName($values2['reserveProduct']);
                            $this->em->persist($reservation);

                            $maxDay = new \DateTime();
                            $maxDay->setTimestamp(strtotime('saturday next week ' . $plan->dateString));
                            $maxDateString = $maxDay->format('Y-m-d');
                            if(isset($values2['reserveProductEndDate']) && $values2['reserveProductEndDate']) {
                                $maxDay = date_create_from_format('d. m. Y', $values2['reserveProductEndDate']);
                                if($maxDay) {
                                    $maxDateString = $maxDay->format('Y-m-d');
                                }
                            }
                        } else {
                            // :o:  close();
                            $maxDay = new \DateTime($plan->datePlan->format('Y-m-d').' 00:00:00');
                            $maxDay->modify('+1 day');
                            $maxDateString = $maxDay->format('Y-m-d');
                        }

                        $nDateString = $plan->datePlan->format('Y-m-d');
                        $firstRunNotDo = 1;
                        $helperForNonReservation = 1;
                        while($nDateString < $maxDateString && $helperForNonReservation) {
                            if($firstRunNotDo) {
                                $firstRunNotDo = 0;
                            } else {
                                if($reservation) {
                                    $newDate = new \DateTime($nDateString . ' 00:00:00');
                                    if ($reservation->name == 'D') {
                                        $newDate->modify('+1 day');
                                    } elseif ($reservation->name == 'T') {
                                        $newDate->modify('+7 days');
                                    } else {
                                        $newDate->modify('+1 month');
                                    }
                                    $nDateString = $newDate->format('Y-m-d');
                                }
                            }

                            if(!$reservation) {
                                $helperForNonReservation = 0;
                            }

                            if(!$itemCounts) {
                                $itemCounts = $product['countPerHinge'];
                            }
                            if($itemCounts) {
                                $numOfFloors = 54;
                                $settEntity = $this->em->getProductionSettingRepository()->find(1);
                                if($settEntity) {
                                    $numOfFloors = $settEntity->value;
                                }

                                $productsLeft = $itemCounts;
                                $addNumberSpace = 1;
                                if(isset($values2['fillSpace']) && $values2['fillSpace']) {
                                    $addNumberSpace += $values2['fillSpace'];
                                }

                                $nShift = $plan->shift;
                                $nName = $plan->name - 1;
                                $lastSpotWasGood = 0;

                                while($productsLeft) {
                                    if($lastSpotWasGood) {
                                        $nName += $addNumberSpace;
                                    } else {
                                        $nName += 1;
                                    }
                                    while($nName > $numOfFloors) {
                                        if($nShift == 'A') {
                                            $nShift = 'B';
                                        } else {
                                            $nShift = 'A';
                                            $newDate = new \DateTime($nDateString.' 00:00:00');
                                            $newDate->modify('+1 day');
                                            $nDateString = $newDate->format('Y-m-d');
                                        }
                                        $nName -= $numOfFloors;
                                    }

                                    $emptySpot = 0;
                                    $modalSpot = NULL;
                                    $modalSpot = $this->em->getProductionPlanRepository()->findOneBy(['shift' => $nShift, 'dateString' => $nDateString, 'name' => $nName, 'productionLine' => $plan->productionLine]);
                                    if(!$modalSpot) {
                                        $modalSpot = new ProductionPlan();
                                        $modalSpot->setName($nName);
                                        $modalSpot->setShift($nShift);
                                        $modalSpot->setDateString($nDateString);
                                        $modalSpot->setDatePlan(new \DateTime($nDateString));
                                        $modalSpot->setProductionLine($plan->productionLine);

                                        $this->em->persist($modalSpot);
                                        $this->em->flush();

                                        //$emptySpot = 1;
                                    }
                                    /*if(!$modalSpot->products || !count($modalSpot->products)) {
                                        //$emptySpot = 1;
                                    }

                                    if(!$emptySpot) {
                                        $lastSpotWasGood = 0;
                                        continue;
                                    }*/
                                    $lastSpotWasGood = 1;

                                    $freeHooksPercent = 1;
                                    if($modalSpot->products && count($modalSpot->products)) {
                                        foreach ($modalSpot->products as $conn) {
                                            $prod = $this->getProductExternalById($conn->productId);
                                            $freeHooksPercent -= $conn->counts / $prod['countPerHinge'];
                                        }
                                    }

                                    $maxAdd = floor($freeHooksPercent * $product['countPerHinge']);
                                    $addCounts = $maxAdd;
                                    if($maxAdd > $productsLeft) {
                                        $addCounts = $productsLeft;
                                    }

                                    if($addCounts) {
                                        $prodInPlan = new ProductInPlan();
                                        $prodInPlan->setProduct($product['name']);
                                        $prodInPlan->setProductId($product['id']);
                                        $prodInPlan->setPlan($modalSpot);
                                        $prodInPlan->setCounts($addCounts);
                                        if($reservation) {
                                            $prodInPlan->setReservation($reservation);
                                        }
                                        if($orderName) {
                                            $prodInPlan->setOrderName($orderName);
                                        }
                                        if($order && isset($order['id']) && $order['id']) {
                                            $prodInPlan->setOrderId($order['id']);
                                        }
                                        if($orderItemId) {
                                            $prodInPlan->setOrderItemId($orderItemId);
                                        }
                                        $this->em->persist($prodInPlan);
                                        $this->em->flush();
                                    } else {
                                        $lastSpotWasGood = 0;
                                    }

                                    $productsLeft -= $addCounts;
                                }
                            }
                        }
                    }
                }

                $this->em->flush();
            }

            $that->redirect('this');
        };

        return $form;
    }

    public function createComponentPlanPrintForm()
    {
        $that = $this;

        $form = new Form();
        $form->addHidden('type', $this->type);
        $form->addText('date')->setRequired('Vyberte datum');
        $form->addSubmit('send', 'tisk');
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();
            if (isset($values2['send'])) {
                $this->printPlanToExcel($values->date, $values->type);
            }
            $that->redirect('this');
        };

        return $form;
    }

    public function printPlanToExcel($date, $type) {
        $datetime = new \datetime($date);

        /*--start data--*/
        $startDate = clone $datetime;
        $endDate = clone $datetime;
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);
        $criteriaStart = new Criteria();
        $criteriaStart->where(Criteria::expr()->gte('datePlan', $startDate));
        $criteriaStart->andWhere(Criteria::expr()->lte('datePlan', $endDate));
        $criteriaStart->andWhere(Criteria::expr()->eq('productionLine', $type));
        $plans = $this->em->getProductionPlanRepository()->matching($criteriaStart);
        $spotsA = array();
        $spotsB = array();
        //$productAll = $this->getProductExternalAll();
        $productHinges = unserialize(file_get_contents("dfiles/productHinges.txt"));
        $productNumbers = unserialize(file_get_contents("dfiles/productNumbers.txt"));
        foreach ($plans as $plan) {
            /*$keyString = $plan->dateString . '_' . $plan->name;*/
            $keyString = $plan->name;
            if($plan->products && count($plan->products)) {
                $counterStock = 0;
                $counterNonStock = 0;
                $description = '';
                $title = '';
                $orderName = '';
                // :o:  close();
                if($plan->products && count($plan->products)) {
                    foreach ($plan->products as $conn) {
                        //$prod = $this->getProductExternalById($conn->productId);
                        $prodHin = $productHinges[$conn->productId];
                        if($conn->orderId) {
                            $counterStock += $conn->counts / $prodHin;
                        } else {
                            $counterNonStock += $conn->counts / $prodHin;
                        }
                        $description .= $conn->product . '(' . $conn->counts .'), ';
                        $title .= $productNumbers[$conn->productId] .',';
                        if($conn->orderName) {
                            $orderName = explode(' ', $conn->orderName)[0];
                        }
                    }
                }
                if($description) {
                    $description = substr($description, 0, -2);
                }
                if($plan->customerId) {
                    $description .= '<br><br>' . $plan->customer;
                }
                if($title) {
                    $title = $orderName . ' : ' . substr($title, 0, -1);
                }

                if($plan->shift == 'A') {
                    $spotsA[$keyString] = $description;
                    /*$spotsA[$keyString] = array();
                    $spotsA[$keyString]['plan'] = $plan;
                    $spotsA[$keyString]['desc'] = $description;
                    $spotsA[$keyString]['title'] = $title;*/
                } else {
                    $spotsB[$keyString] = $description;
                    /*$spotsB[$keyString] = array();
                    $spotsB[$keyString]['plan'] = $plan;
                    $spotsB[$keyString]['desc'] = $description;
                    $spotsB[$keyString]['title'] = $title;*/
                }
            } elseif($plan->customerId) {
                $description = $plan->customer;
                if($plan->shift == 'A') {
                    $spotsA[$keyString] = $description;
                    /*$spotsA[$keyString] = array();
                    $spotsA[$keyString]['plan'] = $plan;
                    $spotsA[$keyString]['desc'] = $description;
                    $spotsA[$keyString]['title'] = $description;*/
                } else {
                    $spotsB[$keyString] = $description;
                    /*$spotsB[$keyString] = array();
                    $spotsB[$keyString]['plan'] = $plan;
                    $spotsB[$keyString]['desc'] = $description;
                    $spotsB[$keyString]['title'] = $description;*/
                }
            }
        }
        /*--end data--*/
        $numOfFloors = 54;
        $settEntity = $this->em->getProductionSettingRepository()->find(1);
        if($settEntity) {
            $numOfFloors = $settEntity->value;
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $aSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet);
        $spreadsheet->addSheet($aSheet, 0);
        $aSheet->mergeCells('A1:J2');
        $aSheet->mergeCells('B3:C3');
        $aSheet->mergeCells('D3:F3');
        $aSheet->mergeCells('G3:H3');
        $aSheet->mergeCells('I3:J3');
        $aSheet->mergeCells('B4:F4');
        $aSheet->mergeCells('G4:H4');
        $aSheet->mergeCells('I4:J4');
        $aSheet->getColumnDimension('I')->setWidth(20);
        $aSheet->getStyle('A1:J4')->getAlignment()->setHorizontal('center')->setVertical('center');
        $aSheet->getStyle('A1:J2')->getFont()->setSize('22')->setBold(true);
        $aSheet->getStyle('B4:J4')->getFont()->setBold(true);
        $aSheet->getStyle('A1:J2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bfbfbf');
        $aSheet->getStyle('A3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bfbfbf');
        $aSheet->getStyle('G3:H3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bfbfbf');
        $aSheet->getStyle('A4:J4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bfbfbf');
        $aSheet->setCellValue('A1', 'Plán výroby');
        $aSheet->setCellValue('A3', 'datum:');
        $aSheet->setCellValue('B3', $datetime->format('j.n.Y'));
        $aSheet->setCellValue('G3', 'směna:');
        $aSheet->setCellValue('I3', 'DENNÍ');
        $aSheet->setCellValue('A4', 'pořadí:');
        $aSheet->setCellValue('B4', 'VÝROBKY');
        $aSheet->setCellValue('G4', 'SPLNĚNO');
        $aSheet->setCellValue('I4', 'POZNÁMKA');
        //$aSheet = $spreadsheet->getActiveSheet();
        $col = 1;
        for ($i = 1; $i <= $numOfFloors; $i++) {
            $col = $i+4;
            $aSheet->mergeCells('B'.$col.':F'.$col);
            $aSheet->mergeCells('I'.$col.':J'.$col);
            $aSheet->getStyle('A'.$col)->getAlignment()->setHorizontal('center')->setVertical('center');
            $aSheet->getStyle('A'.$col)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d9d9d9');
            $aSheet->setCellValue('A'.$col, $i.'.');
            if (isset($spotsA[$i])) {
                $aSheet->setCellValue('B' . $col, $spotsA[$i]);
            }
        }
        $aSheet->getStyle('A1:J'.$col)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('000000');
        $aSheet->getStyle('A1:J2')->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)->getColor()->setARGB('000000');
        $aSheet->getStyle('A1:J'.$col)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)->getColor()->setARGB('000000');

        $bSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet);
        $spreadsheet->addSheet($bSheet, 1);
        $bSheet->mergeCells('A1:J2');
        $bSheet->mergeCells('B3:C3');
        $bSheet->mergeCells('D3:F3');
        $bSheet->mergeCells('G3:H3');
        $bSheet->mergeCells('I3:J3');
        $bSheet->mergeCells('B4:F4');
        $bSheet->mergeCells('G4:H4');
        $bSheet->mergeCells('I4:J4');
        $bSheet->getColumnDimension('I')->setWidth(20);
        $bSheet->getStyle('A1:J4')->getAlignment()->setHorizontal('center')->setVertical('center');
        $bSheet->getStyle('A1:J2')->getFont()->setSize('22')->setBold(true);
        $bSheet->getStyle('B4:J4')->getFont()->setBold(true);
        $bSheet->getStyle('A1:J2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bfbfbf');
        $bSheet->getStyle('A3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bfbfbf');
        $bSheet->getStyle('G3:H3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bfbfbf');
        $bSheet->getStyle('A4:J4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('bfbfbf');
        $bSheet->setCellValue('A1', 'Plán výroby');
        $bSheet->setCellValue('A3', 'datum:');
        $bSheet->setCellValue('B3', $datetime->format('j.n.Y'));
        $bSheet->setCellValue('G3', 'směna:');
        $bSheet->setCellValue('I3', 'NOČNÍ');
        $bSheet->setCellValue('A4', 'pořadí:');
        $bSheet->setCellValue('B4', 'VÝROBKY');
        $bSheet->setCellValue('G4', 'SPLNĚNO');
        $bSheet->setCellValue('I4', 'POZNÁMKA');
        $col = 1;
        for ($i = 1; $i <= $numOfFloors; $i++) {
            $col = $i+4;
            $bSheet->mergeCells('B'.$col.':F'.$col);
            $bSheet->mergeCells('I'.$col.':J'.$col);
            $bSheet->getStyle('A'.$col)->getAlignment()->setHorizontal('center')->setVertical('center');
            $bSheet->getStyle('A'.$col)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('d9d9d9');
            $bSheet->setCellValue('A'.$col, $i.'.');
            if (isset($spotsB[$i])) {
                $bSheet->setCellValue('B' . $col, $spotsB[$i]);
            }
        }
        $bSheet->getStyle('A1:J'.$col)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('000000');
        $bSheet->getStyle('A1:J2')->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)->getColor()->setARGB('000000');
        $bSheet->getStyle('A1:J'.$col)->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM)->getColor()->setARGB('000000');

        $spreadsheet->setActiveSheetIndex(0);

        $folder = '_data/temp-files/';
        if (!file_exists($folder)) {
            mkdir($folder, 0775);
        }
        $xlsName = $date.'_'.$type.'_plan.xlsx';
        $filename = $folder.$xlsName;
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filename);
        $this->sess->turnoverExportXlsPlan['name'] = $xlsName;
        $this->sess->turnoverExportXlsPlan['file'] = $filename;
        $this->redirect('this');
        /*header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$date.'_plan.xlsx"');
        $writer->save('php://output');*/
        //$writer->save('hello world.xlsx');
        return;
    }
}