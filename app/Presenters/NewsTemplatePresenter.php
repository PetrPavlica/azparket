<?php

namespace App\Presenters;


use App\Model\Facade\Article as ArticleFacade;

class NewsTemplatePresenter extends BasePresenter
{
    /** @var ArticleFacade @inject */
    public $articleFac;

    /** @var int */
    private $newsMenuId = 8;

    public function renderDefault($id, $slug)
    {
        $article = $this->articleFac->getTemplateArticleDetail($id, $this->locale);
        $article = $this->articleFac->getTemplateArticleDetail($id, $this->locale);
        if (!$article) {
            $this->flashMessage('Šablon nebyl nalezen.', 'info');
            $this->redirect('Page:default', ['id' => $this->newsMenuId]);
        }
        $menu = $this->facade->getMenu($this->newsMenuId, $this->locale);
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
        $this->template->banners = $this->facade->getBanners($this->locale, 'submain');
    }
}