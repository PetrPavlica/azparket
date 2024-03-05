<?php

namespace App\Model;

use App\Model\DatabaseResource\DatabaseResource;
use Kdyby\Translation\InvalidResourceException;
use Kdyby\Translation\MessageCatalogue;
use Symfony\Component\Translation\Loader\LoaderInterface;

class TranslationLoader implements LoaderInterface
{
    /** @var Translation */
    private $translationFac;

    public function setFacade($facade)
    {
        $this->translationFac = $facade;
    }

    public function load($resource, $locale, $domain = 'messages')
    {
        $catalogue = new MessageCatalogue($locale);
        $messagesDB = $this->translationFac->getMessages($locale);
        //$messages = [];
        if ($messagesDB) {
            foreach($messagesDB as $m) {
                $catalogue->set(str_replace($domain.'.', '', $m->key_m), $m->message, $domain);
                //$messages[str_replace($domain.'.', '', $m->key_m)] = $m->message;
            }
        }

        /*$catalogue = new MessageCatalogue($locale);
        $catalogue->add($messages, $domain);*/
        $catalogue->addResource(new DatabaseResource($resource, null));

        return $catalogue;
    }
}