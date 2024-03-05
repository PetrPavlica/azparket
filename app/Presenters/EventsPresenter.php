<?php

namespace App\Presenters;


use App\Model\Facade\Article as ArticleFacade;

class EventsPresenter extends BasePresenter
{
    /** @var ArticleFacade @inject */
    public $articleFac;

    /** @var int */
    private $eventsMenuId = 12;

    public function renderDefault($id, $slug)
    {
        $article = $this->articleFac->getEventArticleDetail($id, $this->locale);
        if (!$article) {
            $this->flashMessage('Akce nebyla nalezena.', 'info');
            $this->redirect('Page:default', ['id' => $this->eventsMenuId]);
        }
        $menu = $this->facade->getMenu($this->eventsMenuId, $this->locale);
        if ($menu) {
            $this->template->menu = $menu;
            $this->menuID = $menu->id;
        }
        $this->template->article = $article;
        if ($article->imagesCount) {
            $this->template->images = $this->articleFac->getImages($id);
        } else {
            $this->template->images = [];
        }
        if ($article->filesCount) {
            $this->template->files = $this->articleFac->getFiles($id, $this->locale);
        } else {
            $this->template->files = [];
        }
    }
}