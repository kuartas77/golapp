$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': token.csrfToken
    }
});
const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
        confirmButton: 'btn btn-success',
        cancelButton: 'btn btn-danger'
    },
    buttonsStyling: false
})
jQuery(document).ready(function ($) {
    $.extend(true, $.fn.dataTable.defaults, {
        "info": true,
        "language": {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });
});
// //
// // Pipelining function for DataTables. To be used to the `ajax` option of DataTables
// //
$.fn.dataTable.pipeline = function (opts) {
    // Configuration options
    let conf = $.extend({
        pages: 5,     // number of pages to cache
        url: '',      // script url
        data: null,   // function or object with parameters to send to the server
                      // matching how `ajax.data` works in DataTables
        method: 'GET' // Ajax HTTP method
    }, opts);
    // Private variables for storing the cache
    let cacheLower = -1;
    let cacheUpper = null;
    let cacheLastRequest = null;
    let cacheLastJson = null;
    return function (request, drawCallback, settings) {
        let ajax = false;
        let requestStart = request.start;
        let drawStart = request.start;
        let requestLength = request.length;
        let requestEnd = requestStart + requestLength, json;
        if (settings.clearCache) {
            // API requested that the cache be cleared
            ajax = true;
            settings.clearCache = false;
        } else if (cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper) {
            // outside cached data - need to make a request
            ajax = true;
        } else if (JSON.stringify(request.order) !== JSON.stringify(cacheLastRequest.order) ||
            JSON.stringify(request.columns) !== JSON.stringify(cacheLastRequest.columns) ||
            JSON.stringify(request.search) !== JSON.stringify(cacheLastRequest.search)
        ) {
            // properties changed (ordering, columns, searching)
            ajax = true;
        }
        // Store the request for checking next time around
        cacheLastRequest = $.extend(true, {}, request);
        if (ajax) {
            // Need data from the server
            if (requestStart < cacheLower) {
                requestStart = requestStart - (requestLength * (conf.pages - 1));

                if (requestStart < 0) {
                    requestStart = 0;
                }
            }
            cacheLower = requestStart;
            cacheUpper = requestStart + (requestLength * conf.pages);
            request.start = requestStart;
            request.length = requestLength * conf.pages;
            // Provide the same `data` options as DataTables.
            if ($.isFunction(conf.data)) {
                // As a function it is executed with the data object as an arg
                // for manipulation. If an object is returned, it is used as the
                // data object to submit
                let d = conf.data(request);
                if (d) {
                    $.extend(request, d);
                }
            } else if ($.isPlainObject(conf.data)) {
                // As an object, the data given extends the default
                $.extend(request, conf.data);
            }
            settings.jqXHR = $.ajax({
                "type": conf.method,
                "url": conf.url,
                "data": request,
                "dataType": "json",
                "cache": false,
                "success": function (json) {
                    cacheLastJson = $.extend(true, {}, json);

                    if (cacheLower !== drawStart) {
                        json.data.splice(0, drawStart - cacheLower);
                    }
                    if (requestLength > -1) {
                        json.data.splice(requestLength, json.data.length);
                    }
                    drawCallback(json);
                }
            });
        } else {
            json = $.extend(true, {}, cacheLastJson);
            json.draw = request.draw; // Update the echo for each response
            json.data.splice(0, requestStart - cacheLower);
            json.data.splice(requestLength, json.data.length);
            drawCallback(json);
        }
    }
};
// Register an API method that will empty the pipelined data, forcing an Ajax
// fetch on the next draw (i.e. `table.clearPipeline().draw()`)
$.fn.dataTable.Api.register('clearPipeline()', function () {
    return this.iterator('table', function (settings) {
        settings.clearCache = true;
    });
});

daterangepicker.prototype.setMinDate = function (minDate) {
    if (typeof minDate === 'string')
        this.minDate = moment(minDate, this.locale.format);

    if (typeof minDate === 'object')
        this.minDate = moment(minDate);

    if (!this.isShowing)
        this.updateElement();

    this.updateMonthsInView();
};
let locale_var = {
    format: 'MM/DD/YYYY hh:mm A',
    cancelLabel: 'Clear',
    "separator": " - ",
    "applyLabel": "Aplicar",
    "cancelLabel": "Cancelar",
    "weekLabel": "S",
    "daysOfWeek": [
        "Do",
        "Lu",
        "Ma",
        "Mi",
        "Ju",
        "Vi",
        "Sa"
    ],
    "monthNames": [
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre"
    ],
    "firstDay": 1
};
let fechaActual = moment();
let hoy = moment();
let optionsDateRangePicker = {
    maxSpan: {"days": 1},
    locale: locale_var,
    timePickerIncrement: 30,
    singleDatePicker: true,
    showDropdowns: true,
    timePicker: true,
    autoApply: true,
    alwaysShowCalendars: true,
    startDate: fechaActual,
    // endDate: fechaActual,
    // minDate: fechaActual,
    opens: 'center'
};
let optionRangosPicker = {
    startDate: fechaActual,
    // endDate: fechaActual,
    // maxDate: fechaActual,
    "alwaysShowCalendars": true,
    ranges: {
        'Hoy': [moment(), moment()],
        'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Los últimos 7 días': [moment().subtract(6, 'days'), moment()],
        'Los últimos 30 días': [moment().subtract(29, 'days'), moment()],
        'Este mes': [moment().startOf('month'), moment().endOf('month')],
        'El mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    },
    locale: locale_var
}

let optionsDateTimePicker = {
    maxSpan: {"days": 1},
    locale: locale_var,
    timePickerIncrement: 30,
    singleDatePicker: true,
    showDropdowns: true,
    timePicker: true,
    autoApply: true,
    alwaysShowCalendars: true,
    startDate: fechaActual,
    // endDate: fechaActual,
    // minDate: fechaActual,
    opens: 'center'
};

let optionsDateRangeTimePicker = {
    maxSpan: {"days": 1},
    locale: locale_var,
    timePickerIncrement: 30,
    singleDatePicker: true,
    showDropdowns: true,
    timePicker: true,
    autoApply: true,
    alwaysShowCalendars: true,
    startDate: hoy,
    // endDate: hoy,
    // minDate: hoy,
    opens: 'center'
};

let reportDateRangePicker = {
    locale: locale_var,
    "opens": "center"
};

jQuery.validator.setDefaults({
    // success: function(label, element) {
    //     let elemento = $(element);
    //     if (!elemento.is("select")) {
    //         label.parent().addClass("has-success");
    //     }
    // },
    errorPlacement: function (error, element) {
        if (element.hasClass('datapicker')) {
            error.appendTo(element.closest("div.form-group"));
        } else {
            error.appendTo(element.parent());
        }
        if (element.is(":radio")) {
            error.appendTo(element.parent());
        } else { // This is the default behavior
            error.insertAfter(element);
        }
        if (element.is(":file")) {
            error.appendTo(element.closest("div.form-group"));
        } else { // This is the default behavior
            error.insertAfter(element);
        }
        if (element.is(":checkbox")) {
            error.appendTo(element.closest("div.form-group"));
        } else { // This is the default behavior
            error.insertAfter(element);
        }
    }
    /*     ,errorElement: 'span',
        highlight: function(element, errorClass, validClass) {
          $(element).parents("div.form-group").addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function(element, errorClass, validClass) {
          $(element).parents(".error").removeClass(errorClass).addClass(validClass);
        } */
    /*    ,submitHandler: function (form) {
            $(".loading").removeClass("ocultar");
            form.submit();
        }*/
});
jQuery.validator.addMethod("numbers", function (value, element) {
    return value.match(/^[0-9]+$/);
}, "Ingresa sólo números!");
jQuery.validator.addMethod("positiveNumber", function (value, element) {
    return value.match(/^\+?[0-9]*\.?[0-9]+$/);
}, "Ingresa sólo números positivos!");
jQuery.validator.addMethod("emails", function (value, element) {
    return /^[a-z0-9]+([-._][a-z0-9]+)*@([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,4}$/.test(value)
        && /^(?=.{1,64}@.{4,64}$)(?=.{6,100}$).*/.test(value);
}, "Por favor, escribe una dirección de correo válida.");
jQuery.validator.addMethod("validDate", function (value, element) {
    return value.match(/^(0[1-9]|[12][0-9]|3[01])[\/|-](0[1-9]|1[012])[\/|-](19\d\d|2\d\d\d)$/) && moment(value, "DD-MM-YYYY").isValid();
}, "Formato DD-MM-YYYY");
$.validator.addMethod("checkone", function(value, elem, param) {
    return $(".chk-col-blue:checkbox:checked").length === 1;
},"Debes Seleccionar Sólo Uno!");


function nl2br(str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    let breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

Number.prototype.format = function (n, x, s, c) {
    let re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\D' : '$') + ')',
        num = this.toFixed(Math.max(0, ~~n));

    return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
};
$.fn.select2.defaults.set("placeholder", "Seleccione una opción");
$.fn.select2.defaults.set("width", "100%");
$.fn.select2.defaults.set("language", "es");
$.fn.select2.defaults.set("allowClear", true);
$(document).ajaxStart(function () {
    //$(".loading").removeClass("ocultar");
    $(".preloader").fadeIn();
});
$(document).ajaxComplete(function () {
    //$(".loading").addClass("ocultar");
    $(".preloader").fadeIn();
});
$(document).ajaxStop(function () {
    //$(".loading").addClass("ocultar");
    $(".preloader").fadeOut();
});
$(document).ajaxError(function () {
   // $(".loading").addClass("ocultar");
    $(".preloader").fadeOut();
});
//configuracion botones menu
$('#zoomBtn').click(function () {
    $('.zoom-btn-sm').toggleClass('scale-out');
    if (!$('.zoom-card').hasClass('scale-out')) {
        $('.zoom-card').toggleClass('scale-out');
    }
});

Inputmask.extendAliases({
    pesos: {
        prefix: "$ ",
        groupSeparator: ".",
        alias: "numeric",
        placeholder: "0",
        autoGroup: !0,
        digits: 0,
        digitsOptional: !1,
        clearMaskOnLostFocus: !1
    }
});

function formatMoney(value) {
    return new Intl.NumberFormat('es-CO').format(Number(value));
}
window.onload = function () {
    $(".preloader").fadeOut()
}

