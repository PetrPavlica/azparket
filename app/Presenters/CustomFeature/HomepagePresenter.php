<?php

declare(strict_types=1);

namespace App\Presenters\CustomFeature;


class HomepagePresenter extends \App\Presenters\HomepagePresenter
{
    public function renderDefault(): void
    {
        $this->template->anyVariable = 'sss';
    }
}