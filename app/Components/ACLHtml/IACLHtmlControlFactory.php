<?php

namespace App\Components\ACLHtml;

interface IACLHtmlControlFactory {

    /** @return ACLHtmlControl */
    function create();
}
