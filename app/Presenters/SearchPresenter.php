<?php

namespace App\Presenters;


class SearchPresenter extends BasePresenter
{
    public function renderDefault($term)
    {
        $this->template->banners = $this->facade->getBanners($this->locale, 'submain');
        $this->template->searchArticles = $this->facade->getArticlesByTerm($term, $this->locale);
        //$this->template->searchProducts = $this->facade->getProductsByTerm($term, $this->locale);
        $this->template->searchTerm = $term;
    }
}