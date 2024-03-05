<?php

namespace App\Model\Facade;

use App\Model\Database\EntityManager;

class Currency
{
    /** @var EntityManager */
    private EntityManager $em;

    /** @var string */
    private $normalCurrency = 'https://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt';

    /** @var string */
    private $exoticCurrency = 'https://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_ostatnich_men/kurzy.txt';

    /** @var string */
    private $ourCurrency = 'CZK';

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Check aktual exchange rates
     * @return array
     */
    public function checkActualExchangeRates()
    {
        $kurzy = file($this->normalCurrency);
        $kurzyExotic = file($this->exoticCurrency);

        $arr = [];
        foreach ($kurzy as $v) {
            $h = explode("|", $v);
            if (isset($h[3]) && isset($h[4]))
                $arr[$h[3]] = str_replace(',', '.', $h[4]);
        }
        foreach ($kurzyExotic as $v) {
            $h = explode("|", $v);
            if (isset($h[3]) && isset($h[4]))
                $arr[$h[3]] = str_replace(',', '.', $h[4]);
        }
        $message = [];
        $entity = $this->em->getCurrencyRepository()->findAll();
        foreach ($entity as $item) {
            if (isset($arr[$item->code])) {
                $item->exchangeRate = $arr[$item->code];
            } else if ($item->code == $this->ourCurrency) {
                continue;
            } else {
                $message[] = "Nepodařilo se najít na lístku ČNB měnu: $item->name [$item->code]";
            }
        }
        $this->em->flush();
        return $message;
    }
}