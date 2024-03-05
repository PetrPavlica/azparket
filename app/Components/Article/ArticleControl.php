<?php

namespace App\Components\Article;

use App\Model\Facade\Article as ArticleFacade;
use Nette\Application\UI;
use Nette\Application\UI\ITemplateFactory;
use Ublaboo\ImageStorage\ImageStorage;

class ArticleControl extends UI\Control
{
    /** @var ITemplateFactory */
    public $templateFactory;

    /** @var ImageStorage */
    public $imageStorage = null;

    ///** @var ArticleFacade */
    public $articleFac;

    public function __construct(ITemplateFactory $templateFactory, ImageStorage $imageStorage, ArticleFacade $articleFac)
    {
        $this->templateFactory = $templateFactory;
        $this->imageStorage = $imageStorage;
        $this->articleFac = $articleFac;
    }

    public function render($articleIDS, $menuId = null)
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/articles.latte');
        $articles = '';
        if ($articleIDS) {
            foreach($articleIDS as $idArticle => $type) {
                if ($type == 'default') {
                    $articles .= $this->defaultArticle($idArticle, $menuId);
                } elseif($type == 'new') {
                    $articles .= $this->newArticle($idArticle);
                } elseif ($type == 'event') {
                    $articles .= $this->eventArticle($idArticle);
                } elseif ($type == 'template') {
                    $articles .= $this->templateArticle($idArticle);
                }
            }
        }
        $template->articles = $articles;

        // render template
        $template->render();
    }

    public function defaultArticle($idArticle, $menuId = null)
    {
        $article = $this->articleFac->getDefaultArticle($idArticle, $this->parent->locale);
        if ($article && $article->active) {
            $template = $this->templateFactory->createTemplate();
            $template->getLatte()->addProvider('uiControl', $this->parent);
            /*if ($menuId == 6) {
                $template->setFile(__DIR__ . '/templates/download.latte');
            } else {*/
            $template->setFile(__DIR__ . '/templates/default.latte');
            //}
            $template->locale = $this->parent->locale;
            $template->imageStorage = $this->imageStorage;
            $template->article = $article;
            if ($article->imagesCount) {
                $template->images = $this->articleFac->getImages($idArticle);
            } else {
                $template->images = [];
            }
            if ($article->filesCount) {
                $template->files = $this->articleFac->getFiles($idArticle, $this->parent->locale);
            } else {
                $template->files = [];
            }
        } else {
            $template = '';
        }

        return $template;
    }

    public function newArticle($idArticle)
    {
        $article = $this->articleFac->getNewArticle($idArticle, $this->parent->locale);
        if ($article && $article->active) {
            $template = $this->templateFactory->createTemplate();
            $template->getLatte()->addProvider('uiControl', $this->parent);
            $template->setFile(__DIR__ . '/templates/new.latte');
            $template->locale = $this->parent->locale;
            $template->imageStorage = $this->imageStorage;
            $template->article = $article;
        } else {
            $template = '';
        }

        return $template;
    }

    public function templateArticle($idArticle)
    {
        $article = $this->articleFac->getTemplateArticle($idArticle, $this->parent->locale);
        if ($article && $article->active) {
            $template = $this->templateFactory->createTemplate();
            $template->getLatte()->addProvider('uiControl', $this->parent);
            $template->setFile(__DIR__ . '/templates/template.latte');
            $template->locale = $this->parent->locale;
            $template->imageStorage = $this->imageStorage;
            $template->article = $article;
        } else {
            $template = '';
        }

        return $template;
    }

    public function eventArticle($idArticle)
    {
        $article = $this->articleFac->getEventArticle($idArticle, $this->parent->locale);
        if ($article && $article->active) {
            $template = $this->templateFactory->createTemplate();
            $template->getLatte()->addProvider('uiControl', $this->parent);
            $template->setFile(__DIR__ . '/templates/event.latte');
            $template->locale = $this->parent->locale;
            $template->imageStorage = $this->imageStorage;
            $template->article = $article;
        } else {
            $template = '';
        }

        return $template;
    }
}
