<?php

namespace App\Presenters;

use App\Components\MailSender\MailSender;
use Nette;
use App\Model\Facade\Cron;

class CronPresenter extends Nette\Application\UI\Presenter
{
    /** @var Cron @inject */
    public Cron $cron;

    /** @var MailSender @inject */
    public $mailSender;

    // Generete WorkTender regularly for next year from this year
    // Call on: .../cron/create-worker-tender-regularly-next-year
    // Perioda of call: 1x/year
    public function actionCreateWorkerTenderRegularlyNextYear() {
        $this->cron->generateWorkerTenderRegularlyNextYear();
        die;
    }
}
