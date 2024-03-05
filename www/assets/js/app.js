import $ from 'jquery';
window.$ = window.jQuery = $;

import toastr from 'toastr';
window.toastr = toastr;
import * as Ladda from "ladda";
window.Ladda = Ladda;

require('jquery-ui/ui/widgets/sortable');
require('jquery-ui/ui/disable-selection');

import 'bootstrap';
import 'bootstrap-datepicker';
import 'bootstrap-datepicker/dist/locales/bootstrap-datepicker.cs.min.js';
import 'bootstrap-select';
import 'bootstrap-select/dist/js/i18n/defaults-cs_CZ';
import '@fancyapps/fancybox/dist/jquery.fancybox.js';

import moment from 'moment';
import 'moment/locale/cs';
window.moment = moment;
moment().locale("cs").format('l');

require('tempusdominus-bootstrap-4');
require('daterangepicker/daterangepicker');

require('metismenu');
require('popper.js');
require('./nette.ajax.js');
require('./ublaboo-datagrid');
require('./datagrid');
require('owl.carousel');

/*import { createWorker } from 'tesseract.js';

const worker = createWorker({
    logger: m => console.log(m),
    workerPath: 'https://unpkg.com/tesseract.js@v2.0.0/dist/worker.min.js',
    langPath: 'https://tessdata.projectnaptha.com/4.0.0',
    corePath: 'https://unpkg.com/tesseract.js-core@v2.0.0/tesseract-core.wasm.js',
    //langPath: basePath + '/assets/tesseract/',
});*/

require('./nette.ajax.js');

$.fn.selectpicker.Constructor.DEFAULTS.style = 'btn-light';
$.fn.selectpicker.Constructor.BootstrapVersion = '3';
$.fn.selectpicker.Constructor.DEFAULTS.iconBase = 'glyphicon';
$.fn.selectpicker.Constructor.DEFAULTS.container = 'body';
$.fn.selectpicker.Constructor.DEFAULTS.noneSelectedText = 'Nic není vybráno';
$.fn.selectpicker.Constructor.DEFAULTS.noneResultsText = 'Žádné výsledky {0}';
$.fn.selectpicker.Constructor.DEFAULTS.countSelectedText = 'Označeno {0} z {1}';
$.fn.selectpicker.Constructor.DEFAULTS.maxOptionsText = ['Limit překročen ({n} {var} max)', 'Limit skupiny překročen ({n} {var} max)', ['položek', 'položka']];
$.fn.selectpicker.Constructor.DEFAULTS.multipleSeparator = ', ';
$.fn.selectpicker.Constructor.DEFAULTS.selectAllText = 'Vybrat Vše';
$.fn.selectpicker.Constructor.DEFAULTS.deselectAllText = 'Odznačit Vše';

function initPlugins() {
    Ladda.bind('button[type=submit]');
    Ladda.bind('input[type=submit]');
    Ladda.bind('.btn-loading');
    $('.tooltip').remove();
    $('[data-toggle="popover"]').popover();
    initTooltip();
}

function initTooltip() {
    if ($(window).width() <= 400) {
        $('[data-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            container: 'body',
            placement: 'bottom'
        });
    } else {
        $('[data-toggle="tooltip"]').tooltip({
            trigger: 'hover',
            container: 'body'
        });
    }
}

function initDateRangePicker() {
    let ranges = $('[data-toggle="daterangepicker"]');
    ranges.daterangepicker({
        "showDropdowns": true,
        "showWeekNumbers": true,
        "linkedCalendars": false,
        ranges: {
            'Dnes': [moment(), moment()],
            'Včera': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Posledních 7 dní': [moment().subtract(6, 'days'), moment()],
            'Posledních 30 dní': [moment().subtract(29, 'days'), moment()],
            'Tento měsíc': [moment().startOf('month'), moment().endOf('month')],
            'Minulý měsíc': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        "locale": {
            "separator": " - ",
            "applyLabel": "Použít",
            "cancelLabel": "Zrušit",
            "fromLabel": "Od",
            "toLabel": "Do",
            "customRangeLabel": "Vlastní",
            "weekLabel": "W",
            "daysOfWeek": [
                "Ne",
                "Po",
                "Út",
                "St",
                "Čt",
                "Pá",
                "So"
            ],
            "monthNames": [
                "Leden",
                "Únor",
                "Březen",
                "Duben",
                "Květen",
                "Červen",
                "Červenec",
                "Srpen",
                "Září",
                "Říjen",
                "Listopad",
                "Prosinec"
            ],
            "firstDay": 1
        },
        "opens": "center",
        "autoUpdateInput": false,
        /*"startDate": moment().startOf('day'),
        "endDate": moment()*/
    }, function(start, end, label) {}).on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('l') + ' - ' + picker.endDate.format('l')).trigger('change');
    });
}

$(function () {
    $.nette.init();
    initPlugins();

    $('nav .hamburger').click(function() {
        if ($(this).hasClass('is-active')) {
            $(this).removeClass('is-active');
            $('html').removeClass('fixed-menu');
        } else {
            $(this).addClass('is-active');
            $('html').addClass('fixed-menu');
            $([document.documentElement, document.body]).animate({
                scrollTop: $(".menu").offset().top
            }, 1000);
        }
        $('nav ul').fadeToggle();
    });

    $(document).on('change', '#laboratory-order input:not(.form-ignore), #laboratory-order select:not(.selectpicker), #laboratory-order textarea:not(.form-ignore)', function() {
        $('#laboratory-order input[name="changedInput"]').val($(this).attr('name'));
        $('#laboratory-order input[name="ajax"]').trigger('click');
    });

    $(document).on('hidden.bs.select', '#laboratory-order select.selectpicker:not(.form-ignore)', function (e, clickedIndex, isSelected, previousValue) {
        $('#laboratory-order input[name="changedInput"]').val($(this).attr('name'));
        $('#laboratory-order input[name="ajax"]').trigger('click');
    });

    $(document).on('click', '.icon-datepicker, .icon-daterangepicker', function() {
        $(this).parent().parent().find('input').trigger('click');
    });

    // hold onto the drop down menu
    var dropdownMenu = null;

    // and when you show it, move it to the body
    $(window).on('show.bs.dropdown', function (e) {
        if ($(e.target).hasClass('bootstrap-select')) {
            return;
        }
        // grab the menu
        dropdownMenu = $(e.target).find('.dropdown-menu');

        // detach it and append it to the body
        $('body').append(dropdownMenu.detach());

        // grab the new offset position
        var eOffset = $(e.target).offset();

        // make sure to place it where it would normally go (this could be improved)
        dropdownMenu.css({
            'display': 'block',
            'top': eOffset.top + $(e.target).outerHeight(),
            'left': eOffset.left
        });
    });

    // and when you hide it, reattach the drop down, and hide it normally
    $(window).on('hide.bs.dropdown', function (e) {
        if (!dropdownMenu) {
            return;
        }
        $(e.target).append(dropdownMenu.detach());
        dropdownMenu.hide();
        dropdownMenu = null;
    });

    $(document).on("click", ".clickable", function (event) {
        event.preventDefault();
        var to = $(this).parent().attr("data-click-to");
        if (to != undefined && !event.target.classList.contains('datagrid-inline-edit')) {
            location.href = to;
        }
    });

    initDateRangePicker();

    /*$(document).on('change', '#laboratory-order #upload', function() {
        var file    = document.querySelector('input[type=file]').files[0];
        var reader  = new FileReader();

        reader.onloadend = function () {
            (async () => {
                await worker.load();
                await worker.loadLanguage('eng');
                await worker.initialize('eng');
                await worker.setParameters({
                    lang: 'eng',
                    tessedit_char_whitelist: '0123456789',
                });
                const { data: { text } } = await worker.recognize(reader.result);
                console.log(text);
                await worker.terminate();
            })();
        }

        if (file) {
            reader.readAsDataURL(file);
        }
    });*/
});

$(document).ajaxComplete(function() {
    initPlugins();
    initResizeDatagrid();
    initDateRangePicker();
    $('.bs-container').remove();
});

$(document).on("click", ".templateDropdown .right .more-link", function () {
    var el = $(this).parent().parent().children(".contentTemplateDropdown");
    if ($(this).hasClass('more-link-hide')) {
        el.css('max-height', '0px').removeClass('active');
        $(this).siblings('article .right .more-link:not(.more-link-hide)').show();
    } else {
        let contentDropdown = el.find('.content-dropdown');
        el.css('max-height', (contentDropdown.outerHeight() + (el.innerHeight() - el.height())) + 'px').addClass('active');
        $(this).siblings('article .right .more-link-hide').show();
    }
    $(this).hide();
});