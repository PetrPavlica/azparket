<?php

namespace App\Presenters;

use App\Model\Facade\BaseFront;

class ArticlePresenter extends BasePresenter
{
    
    /** @var BaseFront @inject */
    public $facade;
    
    public function renderDefault($id, $slug)
    {
        $article = $this->facade->getArticle($id, $this->locale);
        if ($article) {
            $this->template->article = $article;
            $menu = $this->facade->getArticleMenu($id);
            if ($menu) {
                $this->menuID = $menu;
            }
            $this->template->banners = $this->facade->getBanners($this->locale, 'submain');
        } else {
            $this->flashMessage('Hledaný článek nebyl nalezen.', 'info');
            $this->redirect('Homepage:default');
        }
    }
}