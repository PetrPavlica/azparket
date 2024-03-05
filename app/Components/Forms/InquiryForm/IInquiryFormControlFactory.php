<?php

namespace App\Components\Forms\InquiryForm;

interface IInquiryFormControlFactory {

    /** @return InquiryFormControl */
    function create($cParams = []);
}
