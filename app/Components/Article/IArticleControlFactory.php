<?php

namespace App\Components\Article;

interface IArticleControlFactory {

    /** @return ArticleControl */
    function create();
}
