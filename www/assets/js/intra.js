import $ from 'jquery';
window.$ = window.jQuery = $;

import PerfectScrollbar from 'perfect-scrollbar';

import toastr from 'toastr';
window.toastr = toastr;

const WOW = require('wowjs');

import 'bootstrap';
import 'bootstrap-datepicker';
import 'bootstrap-datepicker/dist/locales/bootstrap-datepicker.cs.min.js';
import 'bootstrap-select';
import 'bootstrap-select/dist/js/i18n/defaults-cs_CZ';
import 'bootstrap4-toggle';
import '@fancyapps/fancybox/dist/jquery.fancybox.js';
import Swal from 'sweetalert2';
window.Swal = Swal;
import moment from 'moment';
import 'moment/locale/cs';
window.moment = moment;
moment().locale("cs").format('l');

import Dropzone from 'dropzone';
window.Dropzone = Dropzone;
import * as Ladda from 'ladda';
window.Ladda = Ladda;
import SignaturePad from "signature_pad";
window.SignaturePad = SignaturePad;

require('jquery-ui/ui/widgets/sortable');
require('jquery-ui/ui/disable-selection');

require('metismenu');
require('popper.js');
require('tempusdominus-bootstrap-4');
require('daterangepicker/daterangepicker');

require('./nette.ajax.js');
require('./ublaboo-datagrid');
require('./datagrid');
require('../packages/completer/js/completer');

function WriteActualDateAndTime(){
    var xDate = new Date();
    var xDay = xDate.getDate();
    if (xDay < 10) xDay = "0" + xDay;
    var xMonth = xDate.getMonth()+1;
    if (xMonth < 10) xMonth = "0" + xMonth;
    var xYear = xDate.getFullYear();
    var xHours = xDate.getHours();
    if (xHours < 10) xHours = "0" + xHours;
    var xMinutes = xDate.getMinutes();
    if (xMinutes < 10) xMinutes = "0" + xMinutes;
    var xSeconds = xDate.getSeconds();
    if (xSeconds < 10) xSeconds = "0" + xSeconds;
    var xFormatedDate = xDay + "." + xMonth + "." + xYear;
    var xFormatedTime = xHours + ":" + xMinutes + ":" + xSeconds;

    var clock = document.getElementById("clock-time");
    if (clock)
        clock.innerHTML = (xFormatedDate + " " + xFormatedTime);
}

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
$.fn.selectpicker.Constructor.DEFAULTS.virtualScroll = 600;

window.delay = (function () {
    var timer = 0;
    return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();

function initPlugins() {
    Ladda.bind('button[type=submit]');
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

function checkSidebar (thisHelper) {
    thisHelper.width()<1250 ? $(".app-container").addClass("closed-sidebar-mobile closed-sidebar"):$(".app-container").removeClass("closed-sidebar-mobile closed-sidebar");
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

    let rangesTime = $('[data-toggle="datetimerangepicker"]');
    rangesTime.daterangepicker({
        "showDropdowns": true,
        "showWeekNumbers": true,
        "linkedCalendars": false,
        "timePicker": true,
        "timePicker24Hour": true,
        "timePickerSeconds": true,
        ranges: {
            'Dnes': [moment().set({hour:0,minute:0,second:0,millisecond:0}), moment().set({hour:23,minute:59,second:59,millisecond:999})],
            'Včera': [moment().subtract(1, 'days').set({hour:0,minute:0,second:0,millisecond:0}), moment().subtract(1, 'days').set({hour:23,minute:59,second:59,millisecond:999})],
            'Posledních 7 dní': [moment().subtract(6, 'days').set({hour:0,minute:0,second:0,millisecond:0}), moment().set({hour:23,minute:59,second:59,millisecond:999})],
            'Posledních 30 dní': [moment().subtract(29, 'days').set({hour:0,minute:0,second:0,millisecond:0}), moment().set({hour:23,minute:59,second:59,millisecond:999})],
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
            "firstDay": 1,
            format: 'l HH:mm:ss',
        },
        "opens": "center",
        "autoUpdateInput": false,
        /*"startDate": moment().startOf('day'),
        "endDate": moment()*/
    }, function(start, end, label) {}).on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('l HH:mm:ss') + ' - ' + picker.endDate.format('l HH:mm:ss')).trigger('change');
    });
}

$(function () {
    $.nette.init();
    new WOW.WOW().init();
    WriteActualDateAndTime();

    //if (jQuery.isFunction('ckeditor')) {
        $(".ckEditor").ckeditor();
    //}

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

    setInterval(WriteActualDateAndTime, 1000);

    $(document).on('click', 'a[data-activates]', function(e) {
        e.preventDefault();
        var act = $(this).data('activates');
        if (act == 'slide-out') {
            $('body').toggleClass('side-nav-open');
        }
    });

    $(document).on('click', '#sidenav-overlay', function() {
        $('body').removeClass('side-nav-open');
    });

    $(document).on('click', ".saveBackTop", function (e) {
        e.preventDefault();
        $("button[name='sendBack']").click();
    });

    $(document).on('click', ".saveTop", function (e) {
        e.preventDefault();
        $("button[name='send']").click();
    });

    $(document).on('click', ".saveNewTop", function (e) {
        e.preventDefault();
        $("button[name='sendNew']").click();
    });

    $(document).on('click', '.confirmLink', function (e) {
        e.preventDefault();
        var targetUrl = $(this).attr("href");

        $("button[name=ok]").click(function (e) {
            window.location.href = targetUrl;
        });
        $("button[name=close]").click(function (e) {
            $("#dialog-confirm").modal('hide');
        });
        $('#dialog-confirm').modal('show');
    });

    $(document).on("click", ".clickable", function (event) {
        event.preventDefault();
        var to = $(this).parent().attr("data-click-to");
        if (to != undefined && !event.target.classList.contains('datagrid-inline-edit')) {
            location.href = to;
        }
    });

    $(document).on("click", ".menu-plus", function (e) {
        e.preventDefault();
        var to = $(this).attr("data-click-to");
        if (to != undefined) {
            location.href = to;
        }
    });

    $(document).on('click', '#menu-toggle', function (e) {
        e.preventDefault();
        $("body").toggleClass("toggled");
    });

    var currentRowNumber;
    var currentRowCheckbox;

    var url = document.URL;
    var hash = url.substring(url.indexOf('#'));

    $("[role='tablist']").find("li a").each(function(key, val) {
        if (hash == $(val).attr('href')) {
            $(val).click();
        }

        $(val).click(function(ky, vl) {
            location.hash = $(this).attr('href');
        });
    });

    $(document).on('click', '.table-toggle-detail', function() {
        var row = $(this).data('toggle-detail');
        $(this).closest('table').find('.item-detail-' + row).slideToggle('slow');
        $(this).closest('table').find('.item-detail-' + row).find('.item-detail-content').slideToggle();
    });

    $(document).on('click', '.icon-datepicker, .icon-daterangepicker', function() {
        $(this).parent().parent().find('input').trigger('click');
    });

    initDateRangePicker();
    initPlugins();
});

$(document).ready(function() {
    $.fn.datepicker.defaults.language = 'cs';
    $.fn.datepicker.defaults.weekStart = 1;

    setTimeout(function() {
        if ($(".scrollbar-container")[0]) {
            $(".scrollbar-container").each(function() {
                new PerfectScrollbar($(this)[0],{
                    wheelSpeed:2,
                    wheelPropagation:!1,
                    minScrollbarLength:20
                });
            });
        }
        if ($(".scrollbar-sidebar")[0]) {
            new PerfectScrollbar(".scrollbar-sidebar", {
                wheelSpeed: 2,
                wheelPropagation: !1,
                minScrollbarLength: 20
            });
        }
    },500);

    setTimeout(function() {
        $(".vertical-nav-menu").metisMenu({
            toggle: false
        });
    },100);

    $(".search-icon").click(function(){
        $(this).parent().parent().addClass("active")
    });

    $(".search-wrapper .close").click(function() {
        $(this).parent().removeClass("active");
    });

    /*$(".dropdown-menu").on("click",function(e){
        var t=r.a._data(document,"events")||{};t=t.click||[];
        for(var n=0;n<t.length;n++) {
            t[n].selector && (r()(e.target).is(t[n].selector) && t[n].handler.call(e.target, e);
            $(e.target).parents(t[n].selector).each(function () {
                t[n].handler.call(this, e)
            })
        )
        }
        e.stopPropagation();
    });*/

    $(".mobile-toggle-nav").click(function() {
        $(this).toggleClass("is-active");
        $(".app-container").toggleClass("sidebar-mobile-open");
    });

    $(".mobile-toggle-header-nav").click(function() {
        $(this).toggleClass("active");
        $(".app-header__content").toggleClass("header-mobile-open");
    });

    $(window).on("resize",function() {
        checkSidebar($(this));
        initTooltip();
    });

    checkSidebar($(this));

    $(".btn-open-options").click(function() {
        $(".ui-theme-settings").toggleClass("settings-open");
    });

    $(".close-sidebar-btn").click(function() {
        var t = $(this).attr("data-class");
        $(".app-container").toggleClass(t);
        var n = $(this);
        n.hasClass("is-active") ? n.removeClass("is-active") : n.addClass("is-active");
    });

    $(".switch-container-class").on("click",function() {
        var t = $(this).attr("data-class");
        $(".app-container").toggleClass(t);
        $(this).parent().find(".switch-container-class").removeClass("active");
        $(this).addClass("active");
    });

    $(".switch-theme-class").on("click",function() {
        var t = $(this).attr("data-class");
        if ("body-tabs-line" === t) {
            $(".app-container").removeClass("body-tabs-shadow").addClass(t);
        } else if ("body-tabs-shadow" === t) {
            $(".app-container").removeClass("body-tabs-line").addClass(t);
        }
        $(this).parent().find(".switch-theme-class").removeClass("active");
        $(this).addClass("active");
    });

    $(".switch-header-cs-class").on("click",function() {
        var t = $(this).attr("data-class");
        $(".switch-header-cs-class").removeClass("active");
        $(this).addClass("active");
        $(".app-header").attr("class","app-header");
        $(".app-header").addClass("header-shadow "+t);
    });

    $(".switch-sidebar-cs-class").on("click",function(){
        var t = $(this).attr("data-class");
        $(".switch-sidebar-cs-class").removeClass("active");
        $(this).addClass("active");
        $(".app-sidebar").attr("class","app-sidebar");
        $(".app-sidebar").addClass("sidebar-shadow "+t);
    });

    /*$('.dropdown').on('show.bs.dropdown', function () {
        $('body').append($('.dropdown').css({
            position:'absolute',
            left:$('.dropdown').offset().left,
            top:$('.dropdown').offset().top
        }).detach());
    });*/

    /*$('.dropdown').on('hidden.bs.dropdown', function () {
        $('.bs-example').append($('.dropdown').css({
            position:false, left:false, top:false
        }).detach());
    });*/

    $(document).on('change', '#laboratory-order input:not(.form-ignore), #laboratory-order select:not(.selectpicker), #laboratory-order textarea:not(.form-ignore)', function() {
        $('#laboratory-order input[name="changedInput"]').val($(this).attr('name'));
        $('#laboratory-order input[name="ajax"]').trigger('click');
    });

    $(document).on('hidden.bs.select', '#laboratory-order select.selectpicker:not(.form-ignore)', function (e, clickedIndex, isSelected, previousValue) {
        $('#laboratory-order input[name="changedInput"]').val($(this).attr('name'));
        $('#laboratory-order input[name="ajax"]').trigger('click');
    });
});

$(document).ajaxComplete(function( event, request, settings ) {
    initResizeDatagrid();
    initPlugins();
    initDateRangePicker();
    $('.bs-container').remove();
});

$(document).on('click', '.confirmLink', function(e) {
    e.preventDefault();
    let targetUrl = $(this).attr("href");
    let dialog = $('#dialog-confirm');

    $(document).on('click', 'button[name=ok]', function(e) {
        window.location.href = targetUrl;
    });

    $(document).on('click', 'button[name=close]', function(e) {
        dialog.modal('hide');
    });
    dialog.modal('show');
});

(function ($, undefined) {
    $.nette.ext({
        load: function () {
            $(document).on('click', '[data-confirm]', function(event) {
                let obj = this;
                let dialog = $('#dialog-confirm');
                event.preventDefault();
                event.stopImmediatePropagation();
                let modalTextEl = $('#modal-text-p');
                let modalTitleEl = $('#modal-title-h4');
                let oldText = modalTextEl.html();
                let oldTitle = modalTitleEl.html();

                if ($(obj).data('text')) {
                    modalTextEl.html($(obj).data('text'));
                }

                if ($(obj).data('title')) {
                    modalTitleEl.html($(obj).data('title'));
                }

                $(document).on('click', 'button[name=close]', function(e) {
                    obj = null;
                    modalTextEl.html(oldText);
                    modalTitleEl.html(oldTitle);
                    dialog.modal('hide');
                });

                $(document).on('click', 'button[name=ok]', function() {
                    if (obj !== null) {
                        let tagName = $(obj).prop("tagName");
                        if (tagName === 'INPUT') {
                            let form = $(obj).closest('form');
                            form.submit();
                        } else {
                            if ($(obj).data('ajax') === 'on') {
                                $.nette.ajax({
                                    url: obj.href
                                });
                            } else {
                                document.location = obj.href;
                            }
                        }
                    }
                    modalTextEl.html(oldText);
                    modalTitleEl.html(oldTitle);
                    dialog.modal('hide');
                });

                dialog.modal('show');
                return false;
            });

            $(document).on('click', '[data-confirm-sweetalert2]', function(event) {
                let objSw2 = this;
                let dialogSw2 = $('#dialog-confirm-sweetalert2');
                event.preventDefault();
                event.stopImmediatePropagation();
                let modalTextElSw2 = $('#dialog-confirm-sweetalert2 #swal2-content');
                let modalTitleElSw2 = $('#swal2-title');
                let modalBtnConfElSw2 = $('.swal2-confirm');
                let modalBtnCancElSw2 = $('.swal2-cancel');
                let oldTextSw2 = modalTextElSw2.html();
                let oldTitleSw2 = modalTitleElSw2.html();
                let oldBtnConfSw2 = modalBtnConfElSw2.html();
                let oldBtnCancSw2 = modalBtnCancElSw2.html();

                if ($(objSw2).data('text')) {
                    modalTextElSw2.html($(objSw2).data('text'));
                }

                if ($(objSw2).data('title')) {
                    modalTitleElSw2.html($(objSw2).data('title'));
                }

                if ($(objSw2).data('btnConfirm')) {
                    modalBtnConfElSw2.html($(objSw2).data('btnConfirm'));
                }

                if ($(objSw2).data('btnCancel')) {
                    modalBtnCancElSw2.html($(objSw2).data('btnCancel'));
                }

                $('#dialog-confirm-sweetalert2 .swal2-warning').css({"display": "none"});
                $('#dialog-confirm-sweetalert2 .swal2-success').css({"display": "none"});
                $('#dialog-confirm-sweetalert2 .swal2-info').css({"display": "none"});
                $('#dialog-confirm-sweetalert2 .swal2-error').css({"display": "none"});
                $('#dialog-confirm-sweetalert2 .swal2-question').css({"display": "none"});
                if ($(objSw2).data('icon')) {
                    if ($(objSw2).data('icon') === 'success') {
                        $('#dialog-confirm-sweetalert2 .swal2-success').css({"display": "flex"});
                    } else if ($(objSw2).data('icon') === 'info') {
                        $('#dialog-confirm-sweetalert2 .swal2-info').css({"display": "flex"});
                    } else if ($(objSw2).data('icon') === 'error' || $(objSw2).data('icon') === 'danger') {
                        $('#dialog-confirm-sweetalert2 .swal2-error').css({"display": "flex"});
                    } else if ($(objSw2).data('icon') === 'question') {
                        $('#dialog-confirm-sweetalert2 .swal2-question').css({"display": "flex"});
                    } else {
                        $('#dialog-confirm-sweetalert2 .swal2-warning').css({"display": "flex"});
                    }
                } else {
                    $('#dialog-confirm-sweetalert2 .swal2-warning').css({"display": "flex"});
                }

                $(document).on('click', 'button[name=close]', function(e) {
                    objSw2 = null;
                    modalTextElSw2.html(oldTextSw2);
                    modalTitleElSw2.html(oldTitleSw2);
                    modalBtnConfElSw2.html(oldBtnConfSw2);
                    modalBtnCancElSw2.html(oldBtnCancSw2);
                    dialogSw2.hide();
                });

                $(document).on('click', 'button[name=ok]', function() {
                    if (objSw2 !== null) {
                        let tagName = $(objSw2).prop("tagName");
                        if (tagName === 'INPUT' || (tagName === 'BUTTON' && $(objSw2).prop('type') === 'submit')) {
                            let form = $(objSw2).closest('form');
                            form.submit();
                        } else {
                            if ($(objSw2).data('success-click')) {
                                $($(objSw2).data('success-click')).click();
                            }
                            if (objSw2.href) {
                                if ($(objSw2).data('ajax') === 'on') {
                                    $.nette.ajax({
                                        url: objSw2.href
                                    });
                                } else {
                                    document.location = objSw2.href;
                                }
                            }
                        }
                    }
                    objSw2 = null;
                    modalTextElSw2.html(oldTextSw2);
                    modalTitleElSw2.html(oldTitleSw2);
                    modalBtnConfElSw2.html(oldBtnConfSw2);
                    modalBtnCancElSw2.html(oldBtnCancSw2);
                    dialogSw2.hide();
                });

                dialogSw2.show();
                return false;
            });
        }
    });

})(jQuery);