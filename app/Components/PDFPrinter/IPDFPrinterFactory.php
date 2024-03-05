<?php

namespace App\Components\PDFPrinter;

interface IPDFPrinterFactory {

    /** @return PDFPrinterControl */
    function create();
}
