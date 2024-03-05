<?php

namespace App\Components\FormRenderer;

interface IFormRendererFactory {

    /** @return FormRendererControl */
    function create();
}
