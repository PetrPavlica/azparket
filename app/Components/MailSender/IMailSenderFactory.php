<?php

namespace App\Components\MailSender;

interface IMailSenderFactory
{

    /** @return MailSender */
    function create();
}