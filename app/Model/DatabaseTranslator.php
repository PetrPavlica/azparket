<?php

namespace App\Model;

use App\Model\Database\Entity\Language;
use App\Model\Database\Entity\Translations;
use Kdyby\Doctrine\EntityManager;

class DatabaseTranslator extends \Kdyby\Translation\Translator {

    /** @var \Kdyby\Doctrine\EntityManager */
    private $em;

    /** @var \Kdyby\Translation\CatalogueCompiler */
    public $catalogueCompiler;

    public function __construct(\Kdyby\Translation\IUserLocaleResolver $localeResolver,
                                \Symfony\Component\Translation\Formatter\MessageFormatterInterface $formatter,
                                \Kdyby\Translation\CatalogueCompiler $catalogueCompiler,
                                \Kdyby\Translation\FallbackResolver $fallbackResolver,
                                \Kdyby\Translation\IResourceLoader $loader,
                                EntityManager $em
    )
    {
        parent::__construct($localeResolver, $formatter, $catalogueCompiler, $fallbackResolver, $loader);
        $this->catalogueCompiler = $catalogueCompiler;

        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function translate($message, $count = null, $parameters = [], $domain = null, $locale = null)
    {
        $catalogue = $this->getCatalogue($locale);
        if ($domain === null) {
            list($domain, $message) = $this->extractMessageDomain($message);
        }

        if (!$catalogue->has($message, $domain)) {
            $localeDB = $locale ? $locale : $this->getLocale();
            $lang = $this->em->getRepository(Language::class)->findOneBy(['code' => $localeDB]);
            $translation = new Translations();
            $translation->setKeyM($domain . '.' . $message)->setMessage($message)->setLang($lang); //setLocale($locale ? $locale : $this->getLocale());
            $this->em->safePersist($translation);
            //$this->catalogueCompiler->invalidateCache();
        }

        return parent::translate($message, $count, $parameters, $domain, $locale);
    }

    /**
     * @param string $message
     * @return array
     */
    private function extractMessageDomain($message)
    {
        if (strpos($message, '.') !== FALSE && strpos($message, ' ') === FALSE) {
            list($domain, $message) = explode('.', $message, 2);

        } else {
            $domain = 'messages';
        }

        return [$domain, $message];
    }

}