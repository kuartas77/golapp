
$(function () {
    "use strict";
    jQuery(document).on("click", ".mega-dropdown", function (e) {
        e.stopPropagation();
    });
    var e = function () {
        (0 < window.innerWidth ? window.innerWidth : this.screen.width) < 4170
            ? ($("body").addClass("mini-sidebar"), $(".navbar-brand span").hide(), $(".scroll-sidebar, .slimScrollDiv").css("overflow-x", "visible").parent().css("overflow", "visible"), $(".sidebartoggler i").addClass("ti-menu"))
            : ($("body").removeClass("mini-sidebar"), $(".navbar-brand span").show());
        var e = (0 < window.innerHeight ? window.innerHeight : this.screen.height) - 1;
        (e -= 70) < 1 && (e = 1), 70 < e && $(".page-wrapper").css("min-height", e + "px");
    };
    $(window).ready(e),
        $(window).on("resize", e),
        $(".sidebartoggler").on("click", function () {
            $("body").hasClass("mini-sidebar")
                ? ($("body").trigger("resize"),
                    $(".scroll-sidebar, .slimScrollDiv").css("overflow", "hidden").parent().css("overflow", "visible"),
                    $("body").removeClass("mini-sidebar"),
                    $(".navbar-brand span").show(),
                    (localStorage.sidebartoggler = 0))
                : ($("body").trigger("resize"),
                    $(".scroll-sidebar, .slimScrollDiv").css("overflow-x", "visible").parent().css("overflow", "visible"),
                    $("body").addClass("mini-sidebar"),
                    $(".navbar-brand span").hide(),
                    (localStorage.sidebartoggler = 0));
        }),
        $(".fix-header .topbar").stick_in_parent({}),
        $(".floating-labels .form-control")
            .on("focus blur", function (e) {
                $(this)
                    .parents(".form-group")
                    .toggleClass("focused", "focus" === e.type || 0 < this.value.length);
            })
            .trigger("blur"),
        $(".nav-toggler").click(function () {
            $("body").toggleClass("show-sidebar"), $(".nav-toggler i").toggleClass("ti-menu"), $(".nav-toggler i").addClass("ti-close");
        }),
        $(".sidebartoggler").on("click", function () { }),
        $(".search-box a, .search-box .app-search .srh-btn").on("click", function () {
            $(".app-search").toggle(200);
        }),
        $(".right-side-toggle").click(function () {
            $(".right-sidebar").slideDown(50), $(".right-sidebar").toggleClass("shw-rside");
        }),
        $(function () {
            $("#sidebarnav").metisMenu();
        }),
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        }),
        $(function () {
            $('[data-toggle="popover"]').popover();
        }),
        $(".scroll-sidebar").slimScroll({ position: "left", size: "5px", height: "100%", color: "#dcdcdc" }),
        $(".message-center").slimScroll({ position: "right", size: "10px", color: "#dcdcdc" }),
        $(".aboutscroll").slimScroll({ position: "right", size: "5px", height: "80", color: "#dcdcdc" }),
        $(".message-scroll").slimScroll({ position: "right", size: "5px", height: "570", color: "#dcdcdc" }),
        $(".chat-box").slimScroll({ position: "right", size: "5px", height: "470", color: "#dcdcdc" }),
        $(".slimscrollright").slimScroll({ height: "100%", position: "right", size: "5px", color: "#dcdcdc" }),
        $("body").trigger("resize"),
        $(".list-task li label").click(function () {
            $(this).toggleClass("task-done");
        }),
        $("#to-recover").on("click", function () {
            $("#loginform").slideUp(), $("#recoverform").fadeIn();
        }),
        $('a[data-action="collapse"]').on("click", function (e) {
            e.preventDefault(), $(this).closest(".card").find('[data-action="collapse"] i').toggleClass("ti-minus ti-plus"), $(this).closest(".card").children(".card-body").collapse("toggle");
        }),
        $('a[data-action="expand"]').on("click", function (e) {
            e.preventDefault(), $(this).closest(".card").find('[data-action="expand"] i').toggleClass("mdi-arrow-expand mdi-arrow-compress"), $(this).closest(".card").toggleClass("card-fullscreen");
        }),
        $('a[data-action="close"]').on("click", function () {
            $(this).closest(".card").removeClass().slideUp("fast");
        }),
        $("#monthchart").sparkline([5, 6, 2, 9, 4, 7, 10, 12], { type: "bar", height: "35", barWidth: "4", resize: !0, barSpacing: "4", barColor: "#1e88e5" }),
        $("#lastmonthchart").sparkline([5, 6, 2, 9, 4, 7, 10, 12], { type: "bar", height: "35", barWidth: "4", resize: !0, barSpacing: "4", barColor: "#7460ee" });
}),
(document.onreadystatechange = function () {
    "complete" == document.readyState && "true" == localStorage.sidebartoggler && $(".sidebartoggler").trigger("click");
}),
function () {
    var e, t;
    (e = window.jQuery),
        (t = e(window)),
        (e.fn.stick_in_parent = function (n) {
            var i, a, r, s, o, l, d, u, c, h, f, p;
            for (
                null == n && (n = {}),
                    p = n.sticky_class,
                    o = n.inner_scrolling,
                    f = n.recalc_every,
                    h = n.parent,
                    u = n.offset_top,
                    d = n.spacer,
                    a = n.bottoming,
                    null == u && (u = 0),
                    null == h && (h = void 0),
                    null == o && (o = !0),
                    null == p && (p = "is_stuck"),
                    i = e(document),
                    null == a && (a = !0),
                    c = function (e) {
                        var t;
                        return window.getComputedStyle
                            ? ((e = window.getComputedStyle(e[0])),
                              (t = parseFloat(e.getPropertyValue("width")) + parseFloat(e.getPropertyValue("margin-left")) + parseFloat(e.getPropertyValue("margin-right"))),
                              "border-box" !== e.getPropertyValue("box-sizing") &&
                                  (t +=
                                      parseFloat(e.getPropertyValue("border-left-width")) +
                                      parseFloat(e.getPropertyValue("border-right-width")) +
                                      parseFloat(e.getPropertyValue("padding-left")) +
                                      parseFloat(e.getPropertyValue("padding-right"))),
                              t)
                            : e.outerWidth(!0);
                    },
                    r = function (n, r, s, l, m, g, _, y) {
                        var v, b, w, M, x, k, D, L, S, T, Y, C;
                        if (!n.data("sticky_kit")) {
                            if ((n.data("sticky_kit", !0), (x = i.height()), (D = n.parent()), null != h && (D = D.closest(h)), !D.length)) throw "failed to find stick parent";
                            if (
                                ((v = w = !1),
                                (Y = null != d ? d && n.closest(d) : e("<div />")) && Y.css("position", n.css("position")),
                                (L = function () {
                                    var e, t, a;
                                    if (
                                        !y &&
                                        ((x = i.height()),
                                        (e = parseInt(D.css("border-top-width"), 10)),
                                        (t = parseInt(D.css("padding-top"), 10)),
                                        (r = parseInt(D.css("padding-bottom"), 10)),
                                        (s = D.offset().top + e + t),
                                        (l = D.height()),
                                        w && ((v = w = !1), null == d && (n.insertAfter(Y), Y.detach()), n.css({ position: "", top: "", width: "", bottom: "" }).removeClass(p), (a = !0)),
                                        (m = n.offset().top - (parseInt(n.css("margin-top"), 10) || 0) - u),
                                        (g = n.outerHeight(!0)),
                                        (_ = n.css("float")),
                                        Y && Y.css({ width: c(n), height: g, display: n.css("display"), "vertical-align": n.css("vertical-align"), float: _ }),
                                        a)
                                    )
                                        return C();
                                }),
                                L(),
                                g !== l)
                            )
                                return (
                                    (M = void 0),
                                    (k = u),
                                    (T = f),
                                    (C = function () {
                                        var e, c, h, b;
                                        if (
                                            !y &&
                                            ((h = !1),
                                            null != T && 0 >= --T && ((T = f), L(), (h = !0)),
                                            h || i.height() === x || L(),
                                            (h = t.scrollTop()),
                                            null != M && (c = h - M),
                                            (M = h),
                                            w
                                                ? (a && ((b = h + g + k > l + s), v && !b && ((v = !1), n.css({ position: "fixed", bottom: "", top: k }).trigger("sticky_kit:unbottom"))),
                                                  h < m &&
                                                      ((w = !1),
                                                      (k = u),
                                                      null == d && (("left" !== _ && "right" !== _) || n.insertAfter(Y), Y.detach()),
                                                      (e = { position: "", width: "", top: "" }),
                                                      n.css(e).removeClass(p).trigger("sticky_kit:unstick")),
                                                  o && ((e = t.height()), g + u > e && !v && ((k -= c), (k = Math.max(e - g, k)), (k = Math.min(u, k)), w && n.css({ top: k + "px" }))))
                                                : h > m &&
                                                  ((w = !0),
                                                  ((e = { position: "fixed", top: k }).width = "border-box" === n.css("box-sizing") ? n.outerWidth() + "px" : n.width() + "px"),
                                                  n.css(e).addClass(p),
                                                  null == d && (n.after(Y), ("left" !== _ && "right" !== _) || Y.append(n)),
                                                  n.trigger("sticky_kit:stick")),
                                            w && a && (null == b && (b = h + g + k > l + s), !v && b))
                                        )
                                            return (v = !0), "static" === D.css("position") && D.css({ position: "relative" }), n.css({ position: "absolute", bottom: r, top: "auto" }).trigger("sticky_kit:bottom");
                                    }),
                                    (S = function () {
                                        return L(), C();
                                    }),
                                    (b = function () {
                                        if (
                                            ((y = !0),
                                            t.off("touchmove", C),
                                            t.off("scroll", C),
                                            t.off("resize", S),
                                            e(document.body).off("sticky_kit:recalc", S),
                                            n.off("sticky_kit:detach", b),
                                            n.removeData("sticky_kit"),
                                            n.css({ position: "", bottom: "", top: "", width: "" }),
                                            D.position("position", ""),
                                            w)
                                        )
                                            return null == d && (("left" !== _ && "right" !== _) || n.insertAfter(Y), Y.remove()), n.removeClass(p);
                                    }),
                                    t.on("touchmove", C),
                                    t.on("scroll", C),
                                    t.on("resize", S),
                                    e(document.body).on("sticky_kit:recalc", S),
                                    n.on("sticky_kit:detach", b),
                                    setTimeout(C, 0)
                                );
                        }
                    },
                    s = 0,
                    l = this.length;
                s < l;
                s++
            )
                (n = this[s]), r(e(n));
            return this;
        });
}.call(this),
(function (e) {
    e.fn.extend({
        slimScroll: function (n) {
            var i = e.extend(
                {
                    width: "auto",
                    height: "250px",
                    size: "7px",
                    color: "#000",
                    position: "right",
                    distance: "1px",
                    start: "top",
                    opacity: 0.4,
                    alwaysVisible: !1,
                    disableFadeOut: !1,
                    railVisible: !1,
                    railColor: "#333",
                    railOpacity: 0.2,
                    railDraggable: !0,
                    railClass: "slimScrollRail",
                    barClass: "slimScrollBar",
                    wrapperClass: "slimScrollDiv",
                    allowPageScroll: !1,
                    wheelStep: 20,
                    touchScrollStep: 200,
                    borderRadius: "7px",
                    railBorderRadius: "7px",
                },
                n
            );
            return (
                this.each(function () {
                    function a(t) {
                        if (d) {
                            var n = 0;
                            (t = t || window.event).wheelDelta && (n = -t.wheelDelta / 120), t.detail && (n = t.detail / 3);
                            var a = t.target || t.srcTarget || t.srcElement;
                            e(a)
                                .closest("." + i.wrapperClass)
                                .is(b.parent()) && r(n, !0),
                                t.preventDefault && !v && t.preventDefault(),
                                v || (t.returnValue = !1);
                        }
                    }
                    function r(e, t, n) {
                        v = !1;
                        var a = e,
                            r = b.outerHeight() - D.outerHeight();
                        if (
                            (t && ((a = parseInt(D.css("top")) + ((e * parseInt(i.wheelStep)) / 100) * D.outerHeight()), (a = Math.min(Math.max(a, 0), r)), (a = e > 0 ? Math.ceil(a) : Math.floor(a)), D.css({ top: a + "px" })),
                            (a = (m = parseInt(D.css("top")) / (b.outerHeight() - D.outerHeight())) * (b[0].scrollHeight - b.outerHeight())),
                            n)
                        ) {
                            var s = ((a = e) / b[0].scrollHeight) * b.outerHeight();
                            (s = Math.min(Math.max(s, 0), r)), D.css({ top: s + "px" });
                        }
                        b.scrollTop(a), b.trigger("slimscrolling", ~~a), o(), l();
                    }
                    function s() {
                        (p = Math.max((b.outerHeight() / b[0].scrollHeight) * b.outerHeight(), y)), D.css({ height: p + "px" });
                        var e = p == b.outerHeight() ? "none" : "block";
                        D.css({ display: e });
                    }
                    function o() {
                        if ((s(), clearTimeout(h), m == ~~m)) {
                            if (((v = i.allowPageScroll), g != m)) {
                                var e = 0 == ~~m ? "top" : "bottom";
                                b.trigger("slimscroll", e);
                            }
                        } else v = !1;
                        return (g = m), p >= b.outerHeight() ? void (v = !0) : (D.stop(!0, !0).fadeIn("fast"), void (i.railVisible && k.stop(!0, !0).fadeIn("fast")));
                    }
                    function l() {
                        i.alwaysVisible ||
                            (h = setTimeout(function () {
                                (i.disableFadeOut && d) || u || c || (D.fadeOut("slow"), k.fadeOut("slow"));
                            }, 1e3));
                    }
                    var d,
                        u,
                        c,
                        h,
                        f,
                        p,
                        m,
                        g,
                        _ = "<div></div>",
                        y = 30,
                        v = !1,
                        b = e(this);
                    if (b.parent().hasClass(i.wrapperClass)) {
                        var w = b.scrollTop();
                        if (((D = b.closest("." + i.barClass)), (k = b.closest("." + i.railClass)), s(), e.isPlainObject(n))) {
                            if ("height" in n && "auto" == n.height) {
                                b.parent().css("height", "auto"), b.css("height", "auto");
                                var M = b.parent().parent().height();
                                b.parent().css("height", M), b.css("height", M);
                            }
                            if ("scrollTo" in n) w = parseInt(i.scrollTo);
                            else if ("scrollBy" in n) w += parseInt(i.scrollBy);
                            else if ("destroy" in n) return D.remove(), k.remove(), void b.unwrap();
                            r(w, !1, !0);
                        }
                    } else if (!e.isPlainObject(n) || !("destroy" in n)) {
                        i.height = "auto" == i.height ? b.parent().height() : i.height;
                        var x = e(_).addClass(i.wrapperClass).css({ position: "relative", overflow: "hidden", width: i.width, height: i.height });
                        b.css({ overflow: "hidden", width: i.width, height: i.height });
                        var k = e(_)
                                .addClass(i.railClass)
                                .css({
                                    width: i.size,
                                    height: "100%",
                                    position: "absolute",
                                    top: 0,
                                    display: i.alwaysVisible && i.railVisible ? "block" : "none",
                                    "border-radius": i.railBorderRadius,
                                    background: i.railColor,
                                    opacity: i.railOpacity,
                                    zIndex: 90,
                                }),
                            D = e(_)
                                .addClass(i.barClass)
                                .css({
                                    background: i.color,
                                    width: i.size,
                                    position: "absolute",
                                    top: 0,
                                    opacity: i.opacity,
                                    display: i.alwaysVisible ? "block" : "none",
                                    "border-radius": i.borderRadius,
                                    BorderRadius: i.borderRadius,
                                    MozBorderRadius: i.borderRadius,
                                    WebkitBorderRadius: i.borderRadius,
                                    zIndex: 99,
                                }),
                            L = "right" == i.position ? { right: i.distance } : { left: i.distance };
                        k.css(L),
                            D.css(L),
                            b.wrap(x),
                            b.parent().append(D),
                            b.parent().append(k),
                            i.railDraggable &&
                                D.bind("mousedown", function (n) {
                                    var i = e(document);
                                    return (
                                        (c = !0),
                                        (t = parseFloat(D.css("top"))),
                                        (pageY = n.pageY),
                                        i.bind("mousemove.slimscroll", function (e) {
                                            (currTop = t + e.pageY - pageY), D.css("top", currTop), r(0, D.position().top, !1);
                                        }),
                                        i.bind("mouseup.slimscroll", function (e) {
                                            (c = !1), l(), i.unbind(".slimscroll");
                                        }),
                                        !1
                                    );
                                }).bind("selectstart.slimscroll", function (e) {
                                    return e.stopPropagation(), e.preventDefault(), !1;
                                }),
                            k.hover(
                                function () {
                                    o();
                                },
                                function () {
                                    l();
                                }
                            ),
                            D.hover(
                                function () {
                                    u = !0;
                                },
                                function () {
                                    u = !1;
                                }
                            ),
                            b.hover(
                                function () {
                                    (d = !0), o(), l();
                                },
                                function () {
                                    (d = !1), l();
                                }
                            ),
                            b.bind("touchstart", function (e, t) {
                                e.originalEvent.touches.length && (f = e.originalEvent.touches[0].pageY);
                            }),
                            b.bind("touchmove", function (e) {
                                v || e.originalEvent.preventDefault(), e.originalEvent.touches.length && (r((f - e.originalEvent.touches[0].pageY) / i.touchScrollStep, !0), (f = e.originalEvent.touches[0].pageY));
                            }),
                            s(),
                            "bottom" === i.start ? (D.css({ top: b.outerHeight() - D.outerHeight() }), r(0, !0)) : "top" !== i.start && (r(e(i.start).position().top, null, !0), i.alwaysVisible || D.hide()),
                            (function (e) {
                                window.addEventListener ? (e.addEventListener("DOMMouseScroll", a, !1), e.addEventListener("mousewheel", a, !1)) : document.attachEvent("onmousewheel", a);
                            })(this);
                    }
                }),
                this
            );
        },
    }),
        e.fn.extend({ slimscroll: e.fn.slimScroll });
})(jQuery),
(function (e, t, n) {
    !(function (e) {
        "function" == typeof define && define.amd ? define(["jquery"], e) : jQuery && !jQuery.fn.sparkline && e(jQuery);
    })(function (i) {
        "use strict";
        var a,
            r,
            s,
            o,
            l,
            d,
            u,
            c,
            h,
            f,
            p,
            m,
            g,
            _,
            y,
            v,
            b,
            w,
            M,
            x,
            k,
            D,
            L,
            S,
            T,
            Y,
            C,
            E,
            A,
            I = {},
            F = 0;
        (a = function () {
            return {
                common: {
                    type: "line",
                    lineColor: "#00f",
                    fillColor: "#cdf",
                    defaultPixelsPerValue: 3,
                    width: "auto",
                    height: "auto",
                    composite: !1,
                    tagValuesAttribute: "values",
                    tagOptionsPrefix: "spark",
                    enableTagOptions: !1,
                    enableHighlight: !0,
                    highlightLighten: 1.4,
                    tooltipSkipNull: !0,
                    tooltipPrefix: "",
                    tooltipSuffix: "",
                    disableHiddenCheck: !1,
                    numberFormatter: !1,
                    numberDigitGroupCount: 3,
                    numberDigitGroupSep: ",",
                    numberDecimalMark: ".",
                    disableTooltips: !1,
                    disableInteraction: !1,
                },
                line: {
                    spotColor: "#f80",
                    highlightSpotColor: "#5f5",
                    highlightLineColor: "#f22",
                    spotRadius: 1.5,
                    minSpotColor: "#f80",
                    maxSpotColor: "#f80",
                    lineWidth: 1,
                    normalRangeMin: n,
                    normalRangeMax: n,
                    normalRangeColor: "#ccc",
                    drawNormalOnTop: !1,
                    chartRangeMin: n,
                    chartRangeMax: n,
                    chartRangeMinX: n,
                    chartRangeMaxX: n,
                    tooltipFormat: new s('<span style="color: {{color}}">&#9679;</span> {{prefix}}{{y}}{{suffix}}'),
                },
                bar: {
                    barColor: "#3366cc",
                    negBarColor: "#f44",
                    stackedBarColor: ["#3366cc", "#dc3912", "#ff9900", "#109618", "#66aa00", "#dd4477", "#0099c6", "#990099"],
                    zeroColor: n,
                    nullColor: n,
                    zeroAxis: !0,
                    barWidth: 4,
                    barSpacing: 1,
                    chartRangeMax: n,
                    chartRangeMin: n,
                    chartRangeClip: !1,
                    colorMap: n,
                    tooltipFormat: new s('<span style="color: {{color}}">&#9679;</span> {{prefix}}{{value}}{{suffix}}'),
                },
                tristate: {
                    barWidth: 4,
                    barSpacing: 1,
                    posBarColor: "#6f6",
                    negBarColor: "#f44",
                    zeroBarColor: "#999",
                    colorMap: {},
                    tooltipFormat: new s('<span style="color: {{color}}">&#9679;</span> {{value:map}}'),
                    tooltipValueLookups: { map: { "-1": "Loss", 0: "Draw", 1: "Win" } },
                },
                discrete: { lineHeight: "auto", thresholdColor: n, thresholdValue: 0, chartRangeMax: n, chartRangeMin: n, chartRangeClip: !1, tooltipFormat: new s("{{prefix}}{{value}}{{suffix}}") },
                bullet: {
                    targetColor: "#f33",
                    targetWidth: 3,
                    performanceColor: "#33f",
                    rangeColors: ["#d3dafe", "#a8b6ff", "#7f94ff"],
                    base: n,
                    tooltipFormat: new s("{{fieldkey:fields}} - {{value}}"),
                    tooltipValueLookups: { fields: { r: "Range", p: "Performance", t: "Target" } },
                },
                pie: {
                    offset: 0,
                    sliceColors: ["#3366cc", "#dc3912", "#ff9900", "#109618", "#66aa00", "#dd4477", "#0099c6", "#990099"],
                    borderWidth: 0,
                    borderColor: "#000",
                    tooltipFormat: new s('<span style="color: {{color}}">&#9679;</span> {{value}} ({{percent.1}}%)'),
                },
                box: {
                    raw: !1,
                    boxLineColor: "#000",
                    boxFillColor: "#cdf",
                    whiskerColor: "#000",
                    outlierLineColor: "#333",
                    outlierFillColor: "#fff",
                    medianColor: "#f00",
                    showOutliers: !0,
                    outlierIQR: 1.5,
                    spotRadius: 1.5,
                    target: n,
                    targetColor: "#4a2",
                    chartRangeMax: n,
                    chartRangeMin: n,
                    tooltipFormat: new s("{{field:fields}}: {{value}}"),
                    tooltipFormatFieldlistKey: "field",
                    tooltipValueLookups: { fields: { lq: "Lower Quartile", med: "Median", uq: "Upper Quartile", lo: "Left Outlier", ro: "Right Outlier", lw: "Left Whisker", rw: "Right Whisker" } },
                },
            };
        }),
            (r = function () {
                var e, t;
                return (
                    (e = function () {
                        this.init.apply(this, arguments);
                    }),
                    arguments.length > 1
                        ? (arguments[0] ? ((e.prototype = i.extend(new arguments[0](), arguments[arguments.length - 1])), (e._super = arguments[0].prototype)) : (e.prototype = arguments[arguments.length - 1]),
                          arguments.length > 2 && ((t = Array.prototype.slice.call(arguments, 1, -1)).unshift(e.prototype), i.extend.apply(i, t)))
                        : (e.prototype = arguments[0]),
                    (e.prototype.cls = e),
                    e
                );
            }),
            (i.SPFormatClass = s = r({
                fre: /\{\{([\w.]+?)(:(.+?))?\}\}/g,
                precre: /(\w+)\.(\d+)/,
                init: function (e, t) {
                    (this.format = e), (this.fclass = t);
                },
                render: function (e, t, i) {
                    var a,
                        r,
                        s,
                        o,
                        l,
                        d = this,
                        u = e;
                    return this.format.replace(this.fre, function () {
                        return (
                            (r = arguments[1]),
                            (s = arguments[3]),
                            (a = d.precre.exec(r)) ? ((l = a[2]), (r = a[1])) : (l = !1),
                            (o = u[r]) === n
                                ? ""
                                : s && t && t[s]
                                ? t[s].get
                                    ? t[s].get(o) || o
                                    : t[s][o] || o
                                : (h(o) && (o = i.get("numberFormatter") ? i.get("numberFormatter")(o) : g(o, l, i.get("numberDigitGroupCount"), i.get("numberDigitGroupSep"), i.get("numberDecimalMark"))), o)
                        );
                    });
                },
            })),
            (i.spformat = function (e, t) {
                return new s(e, t);
            }),
            (o = function (e, t, n) {
                return e < t ? t : e > n ? n : e;
            }),
            (l = function (e, n) {
                var i;
                return 2 === n
                    ? ((i = t.floor(e.length / 2)), e.length % 2 ? e[i] : (e[i - 1] + e[i]) / 2)
                    : e.length % 2
                    ? (i = (e.length * n + n) / 4) % 1
                        ? (e[t.floor(i)] + e[t.floor(i) - 1]) / 2
                        : e[i - 1]
                    : (i = (e.length * n + 2) / 4) % 1
                    ? (e[t.floor(i)] + e[t.floor(i) - 1]) / 2
                    : e[i - 1];
            }),
            (d = function (e) {
                var t;
                switch (e) {
                    case "undefined":
                        e = n;
                        break;
                    case "null":
                        e = null;
                        break;
                    case "true":
                        e = !0;
                        break;
                    case "false":
                        e = !1;
                        break;
                    default:
                        e == (t = parseFloat(e)) && (e = t);
                }
                return e;
            }),
            (u = function (e) {
                var t,
                    n = [];
                for (t = e.length; t--; ) n[t] = d(e[t]);
                return n;
            }),
            (c = function (e, t) {
                var n,
                    i,
                    a = [];
                for (n = 0, i = e.length; n < i; n++) e[n] !== t && a.push(e[n]);
                return a;
            }),
            (h = function (e) {
                return !isNaN(parseFloat(e)) && isFinite(e);
            }),
            (g = function (e, t, n, a, r) {
                var s, o;
                for (e = (!1 === t ? parseFloat(e).toString() : e.toFixed(t)).split(""), (s = (s = i.inArray(".", e)) < 0 ? e.length : s) < e.length && (e[s] = r), o = s - n; o > 0; o -= n) e.splice(o, 0, a);
                return e.join("");
            }),
            (f = function (e, t, n) {
                var i;
                for (i = t.length; i--; ) if ((!n || null !== t[i]) && t[i] !== e) return !1;
                return !0;
            }),
            (m = function (e) {
                return i.isArray(e) ? e : [e];
            }),
            (p = function (t) {
                var n;
                e.createStyleSheet
                    ? (e.createStyleSheet().cssText = t)
                    : (((n = e.createElement("style")).type = "text/css"), e.getElementsByTagName("head")[0].appendChild(n), (n["string" == typeof e.body.style.WebkitAppearance ? "innerText" : "innerHTML"] = t));
            }),
            (i.fn.simpledraw = function (t, a, r, s) {
                var o, l;
                if (r && (o = this.data("_jqs_vcanvas"))) return o;
                if (!1 === i.fn.sparkline.canvas) return !1;
                if (i.fn.sparkline.canvas === n) {
                    var d = e.createElement("canvas");
                    if (d.getContext && d.getContext("2d"))
                        i.fn.sparkline.canvas = function (e, t, n, i) {
                            return new C(e, t, n, i);
                        };
                    else {
                        if (!e.namespaces || e.namespaces.v) return (i.fn.sparkline.canvas = !1), !1;
                        e.namespaces.add("v", "urn:schemas-microsoft-com:vml", "#default#VML"),
                            (i.fn.sparkline.canvas = function (e, t, n, i) {
                                return new E(e, t, n);
                            });
                    }
                }
                return t === n && (t = i(this).innerWidth()), a === n && (a = i(this).innerHeight()), (o = i.fn.sparkline.canvas(t, a, this, s)), (l = i(this).data("_jqs_mhandler")) && l.registerCanvas(o), o;
            }),
            (i.fn.cleardraw = function () {
                var e = this.data("_jqs_vcanvas");
                e && e.reset();
            }),
            (i.RangeMapClass = _ = r({
                init: function (e) {
                    var t,
                        n,
                        i = [];
                    for (t in e)
                        e.hasOwnProperty(t) &&
                            "string" == typeof t &&
                            t.indexOf(":") > -1 &&
                            (((n = t.split(":"))[0] = 0 === n[0].length ? -1 / 0 : parseFloat(n[0])), (n[1] = 0 === n[1].length ? 1 / 0 : parseFloat(n[1])), (n[2] = e[t]), i.push(n));
                    (this.map = e), (this.rangelist = i || !1);
                },
                get: function (e) {
                    var t,
                        i,
                        a,
                        r = this.rangelist;
                    if ((a = this.map[e]) !== n) return a;
                    if (r) for (t = r.length; t--; ) if ((i = r[t])[0] <= e && i[1] >= e) return i[2];
                    return n;
                },
            })),
            (i.range_map = function (e) {
                return new _(e);
            }),
            (y = r({
                init: function (e, t) {
                    var n = i(e);
                    (this.$el = n),
                        (this.options = t),
                        (this.currentPageX = 0),
                        (this.currentPageY = 0),
                        (this.el = e),
                        (this.splist = []),
                        (this.tooltip = null),
                        (this.over = !1),
                        (this.displayTooltips = !t.get("disableTooltips")),
                        (this.highlightEnabled = !t.get("disableHighlight"));
                },
                registerSparkline: function (e) {
                    this.splist.push(e), this.over && this.updateDisplay();
                },
                registerCanvas: function (e) {
                    var t = i(e.canvas);
                    (this.canvas = e), (this.$canvas = t), t.mouseenter(i.proxy(this.mouseenter, this)), t.mouseleave(i.proxy(this.mouseleave, this)), t.click(i.proxy(this.mouseclick, this));
                },
                reset: function (e) {
                    (this.splist = []), this.tooltip && e && (this.tooltip.remove(), (this.tooltip = n));
                },
                mouseclick: function (e) {
                    var t = i.Event("sparklineClick");
                    (t.originalEvent = e), (t.sparklines = this.splist), this.$el.trigger(t);
                },
                mouseenter: function (t) {
                    i(e.body).unbind("mousemove.jqs"),
                        i(e.body).bind("mousemove.jqs", i.proxy(this.mousemove, this)),
                        (this.over = !0),
                        (this.currentPageX = t.pageX),
                        (this.currentPageY = t.pageY),
                        (this.currentEl = t.target),
                        !this.tooltip && this.displayTooltips && ((this.tooltip = new v(this.options)), this.tooltip.updatePosition(t.pageX, t.pageY)),
                        this.updateDisplay();
                },
                mouseleave: function () {
                    i(e.body).unbind("mousemove.jqs");
                    var t,
                        n = this.splist,
                        a = n.length,
                        r = !1;
                    for (this.over = !1, this.currentEl = null, this.tooltip && (this.tooltip.remove(), (this.tooltip = null)), t = 0; t < a; t++) n[t].clearRegionHighlight() && (r = !0);
                    r && this.canvas.render();
                },
                mousemove: function (e) {
                    (this.currentPageX = e.pageX), (this.currentPageY = e.pageY), (this.currentEl = e.target), this.tooltip && this.tooltip.updatePosition(e.pageX, e.pageY), this.updateDisplay();
                },
                updateDisplay: function () {
                    var e,
                        t,
                        n,
                        a,
                        r = this.splist,
                        s = r.length,
                        o = !1,
                        l = this.$canvas.offset(),
                        d = this.currentPageX - l.left,
                        u = this.currentPageY - l.top;
                    if (this.over) {
                        for (t = 0; t < s; t++) (n = r[t].setRegionHighlight(this.currentEl, d, u)) && (o = !0);
                        if (o) {
                            if ((((a = i.Event("sparklineRegionChange")).sparklines = this.splist), this.$el.trigger(a), this.tooltip)) {
                                for (e = "", t = 0; t < s; t++) e += r[t].getCurrentRegionTooltip();
                                this.tooltip.setContent(e);
                            }
                            this.disableHighlight || this.canvas.render();
                        }
                        null === n && this.mouseleave();
                    }
                },
            })),
            (v = r({
                sizeStyle: "position: static !important;display: block !important;visibility: hidden !important;float: left !important;",
                init: function (t) {
                    var n,
                        a = t.get("tooltipClassname", "jqstooltip"),
                        r = this.sizeStyle;
                    (this.container = t.get("tooltipContainer") || e.body),
                        (this.tooltipOffsetX = t.get("tooltipOffsetX", 10)),
                        (this.tooltipOffsetY = t.get("tooltipOffsetY", 12)),
                        i("#jqssizetip").remove(),
                        i("#jqstooltip").remove(),
                        (this.sizetip = i("<div/>", { id: "jqssizetip", style: r, class: a })),
                        (this.tooltip = i("<div/>", { id: "jqstooltip", class: a }).appendTo(this.container)),
                        (n = this.tooltip.offset()),
                        (this.offsetLeft = n.left),
                        (this.offsetTop = n.top),
                        (this.hidden = !0),
                        i(window).unbind("resize.jqs scroll.jqs"),
                        i(window).bind("resize.jqs scroll.jqs", i.proxy(this.updateWindowDims, this)),
                        this.updateWindowDims();
                },
                updateWindowDims: function () {
                    (this.scrollTop = i(window).scrollTop()), (this.scrollLeft = i(window).scrollLeft()), (this.scrollRight = this.scrollLeft + i(window).width()), this.updatePosition();
                },
                getSize: function (e) {
                    this.sizetip.html(e).appendTo(this.container), (this.width = this.sizetip.width() + 1), (this.height = this.sizetip.height()), this.sizetip.remove();
                },
                setContent: function (e) {
                    if (!e) return this.tooltip.css("visibility", "hidden"), void (this.hidden = !0);
                    this.getSize(e), this.tooltip.html(e).css({ width: this.width, height: this.height, visibility: "visible" }), this.hidden && ((this.hidden = !1), this.updatePosition());
                },
                updatePosition: function (e, t) {
                    if (e === n) {
                        if (this.mousex === n) return;
                        (e = this.mousex - this.offsetLeft), (t = this.mousey - this.offsetTop);
                    } else (this.mousex = e -= this.offsetLeft), (this.mousey = t -= this.offsetTop);
                    this.height &&
                        this.width &&
                        !this.hidden &&
                        ((t -= this.height + this.tooltipOffsetY),
                        (e += this.tooltipOffsetX),
                        t < this.scrollTop && (t = this.scrollTop),
                        e < this.scrollLeft ? (e = this.scrollLeft) : e + this.width > this.scrollRight && (e = this.scrollRight - this.width),
                        this.tooltip.css({ left: e, top: t }));
                },
                remove: function () {
                    this.tooltip.remove(), this.sizetip.remove(), (this.sizetip = this.tooltip = n), i(window).unbind("resize.jqs scroll.jqs");
                },
            })),
            i(function () {
                p(
                    '.jqstooltip { position: absolute;left: 0px;top: 0px;visibility: hidden;background: rgb(0, 0, 0) transparent;background-color: rgba(0,0,0,0.6);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000);-ms-filter: "progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000)";color: white;font: 10px arial, san serif;text-align: left;white-space: nowrap;padding: 5px;border: 1px solid white;z-index: 10000;}.jqsfield { color: white;font: 10px arial, san serif;text-align: left;}'
                );
            }),
            (A = []),
            (i.fn.sparkline = function (t, a) {
                return this.each(function () {
                    var r,
                        s,
                        o = new i.fn.sparkline.options(this, a),
                        l = i(this);
                    if (
                        ((r = function () {
                            var a, r, s, d, u, c, h;
                            "html" === t || t === n ? (((h = this.getAttribute(o.get("tagValuesAttribute"))) !== n && null !== h) || (h = l.html()), (a = h.replace(/(^\s*<!--)|(-->\s*$)|\s+/g, "").split(","))) : (a = t),
                                (r = "auto" === o.get("width") ? a.length * o.get("defaultPixelsPerValue") : o.get("width")),
                                "auto" === o.get("height")
                                    ? (o.get("composite") && i.data(this, "_jqs_vcanvas")) || (((d = e.createElement("span")).innerHTML = "a"), l.html(d), (s = i(d).innerHeight() || i(d).height()), i(d).remove(), (d = null))
                                    : (s = o.get("height")),
                                o.get("disableInteraction") ? (u = !1) : (u = i.data(this, "_jqs_mhandler")) ? o.get("composite") || u.reset() : ((u = new y(this, o)), i.data(this, "_jqs_mhandler", u)),
                                !o.get("composite") || i.data(this, "_jqs_vcanvas")
                                    ? ((c = new i.fn.sparkline[o.get("type")](this, a, o, r, s)).render(), u && u.registerSparkline(c))
                                    : i.data(this, "_jqs_errnotify") || (alert("Attempted to attach a composite sparkline to an element with no existing sparkline"), i.data(this, "_jqs_errnotify", !0));
                        }),
                        (i(this).html() && !o.get("disableHiddenCheck") && i(this).is(":hidden")) || !i(this).parents("body").length)
                    ) {
                        if (!o.get("composite") && i.data(this, "_jqs_pending")) for (s = A.length; s; s--) A[s - 1][0] == this && A.splice(s - 1, 1);
                        A.push([this, r]), i.data(this, "_jqs_pending", !0);
                    } else r.call(this);
                });
            }),
            (i.fn.sparkline.defaults = a()),
            (i.sparkline_display_visible = function () {
                var e,
                    t,
                    n,
                    a = [];
                for (t = 0, n = A.length; t < n; t++)
                    (e = A[t][0]),
                        i(e).is(":visible") && !i(e).parents().is(":hidden")
                            ? (A[t][1].call(e), i.data(A[t][0], "_jqs_pending", !1), a.push(t))
                            : !i(e).closest("html").length && !i.data(e, "_jqs_pending") && (i.data(A[t][0], "_jqs_pending", !1), a.push(t));
                for (t = a.length; t; t--) A.splice(a[t - 1], 1);
            }),
            (i.fn.sparkline.options = r({
                init: function (e, t) {
                    var n, a, r, s;
                    (this.userOptions = t = t || {}),
                        (this.tag = e),
                        (this.tagValCache = {}),
                        (r = (a = i.fn.sparkline.defaults).common),
                        (this.tagOptionsPrefix = t.enableTagOptions && (t.tagOptionsPrefix || r.tagOptionsPrefix)),
                        (n = (s = this.getTagSetting("type")) === I ? a[t.type || r.type] : a[s]),
                        (this.mergedOptions = i.extend({}, r, n, t));
                },
                getTagSetting: function (e) {
                    var t,
                        i,
                        a,
                        r,
                        s = this.tagOptionsPrefix;
                    if (!1 === s || s === n) return I;
                    if (this.tagValCache.hasOwnProperty(e)) t = this.tagValCache.key;
                    else {
                        if ((t = this.tag.getAttribute(s + e)) === n || null === t) t = I;
                        else if ("[" === t.substr(0, 1)) for (i = (t = t.substr(1, t.length - 2).split(",")).length; i--; ) t[i] = d(t[i].replace(/(^\s*)|(\s*$)/g, ""));
                        else if ("{" === t.substr(0, 1)) for (a = t.substr(1, t.length - 2).split(","), t = {}, i = a.length; i--; ) t[(r = a[i].split(":", 2))[0].replace(/(^\s*)|(\s*$)/g, "")] = d(r[1].replace(/(^\s*)|(\s*$)/g, ""));
                        else t = d(t);
                        this.tagValCache.key = t;
                    }
                    return t;
                },
                get: function (e, t) {
                    var i,
                        a = this.getTagSetting(e);
                    return a !== I ? a : (i = this.mergedOptions[e]) === n ? t : i;
                },
            })),
            (i.fn.sparkline._base = r({
                disabled: !1,
                init: function (e, t, a, r, s) {
                    (this.el = e), (this.$el = i(e)), (this.values = t), (this.options = a), (this.width = r), (this.height = s), (this.currentRegion = n);
                },
                initTarget: function () {
                    var e = !this.options.get("disableInteraction");
                    (this.target = this.$el.simpledraw(this.width, this.height, this.options.get("composite"), e)) ? ((this.canvasWidth = this.target.pixelWidth), (this.canvasHeight = this.target.pixelHeight)) : (this.disabled = !0);
                },
                render: function () {
                    return !this.disabled || ((this.el.innerHTML = ""), !1);
                },
                getRegion: function (e, t) {},
                setRegionHighlight: function (e, t, i) {
                    var a,
                        r = this.currentRegion,
                        s = !this.options.get("disableHighlight");
                    return t > this.canvasWidth || i > this.canvasHeight || t < 0 || i < 0
                        ? null
                        : r !== (a = this.getRegion(e, t, i)) && (r !== n && s && this.removeHighlight(), (this.currentRegion = a), a !== n && s && this.renderHighlight(), !0);
                },
                clearRegionHighlight: function () {
                    return this.currentRegion !== n && (this.removeHighlight(), (this.currentRegion = n), !0);
                },
                renderHighlight: function () {
                    this.changeHighlight(!0);
                },
                removeHighlight: function () {
                    this.changeHighlight(!1);
                },
                changeHighlight: function (e) {},
                getCurrentRegionTooltip: function () {
                    var e,
                        t,
                        a,
                        r,
                        o,
                        l,
                        d,
                        u,
                        c,
                        h,
                        f,
                        p,
                        m,
                        g,
                        _ = this.options,
                        y = "",
                        v = [];
                    if (this.currentRegion === n) return "";
                    if (((e = this.getCurrentRegionFields()), (f = _.get("tooltipFormatter")))) return f(this, _, e);
                    if ((_.get("tooltipChartTitle") && (y += '<div class="jqs jqstitle">' + _.get("tooltipChartTitle") + "</div>\n"), !(t = this.options.get("tooltipFormat")))) return "";
                    if ((i.isArray(t) || (t = [t]), i.isArray(e) || (e = [e]), (d = this.options.get("tooltipFormatFieldlist")), (u = this.options.get("tooltipFormatFieldlistKey")), d && u)) {
                        for (c = [], l = e.length; l--; ) (h = e[l][u]), -1 != (g = i.inArray(h, d)) && (c[g] = e[l]);
                        e = c;
                    }
                    for (a = t.length, m = e.length, l = 0; l < a; l++)
                        for ("string" == typeof (p = t[l]) && (p = new s(p)), r = p.fclass || "jqsfield", g = 0; g < m; g++)
                            (e[g].isNull && _.get("tooltipSkipNull")) ||
                                (i.extend(e[g], { prefix: _.get("tooltipPrefix"), suffix: _.get("tooltipSuffix") }), (o = p.render(e[g], _.get("tooltipValueLookups"), _)), v.push('<div class="' + r + '">' + o + "</div>"));
                    return v.length ? y + v.join("\n") : "";
                },
                getCurrentRegionFields: function () {},
                calcHighlightColor: function (e, n) {
                    var i,
                        a,
                        r,
                        s,
                        l = n.get("highlightColor"),
                        d = n.get("highlightLighten");
                    if (l) return l;
                    if (d && (i = /^#([0-9a-f])([0-9a-f])([0-9a-f])$/i.exec(e) || /^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(e))) {
                        for (r = [], a = 4 === e.length ? 16 : 1, s = 0; s < 3; s++) r[s] = o(t.round(parseInt(i[s + 1], 16) * a * d), 0, 255);
                        return "rgb(" + r.join(",") + ")";
                    }
                    return e;
                },
            })),
            (b = {
                changeHighlight: function (e) {
                    var t,
                        n = this.currentRegion,
                        a = this.target,
                        r = this.regionShapes[n];
                    r &&
                        ((t = this.renderRegion(n, e)),
                        i.isArray(t) || i.isArray(r)
                            ? (a.replaceWithShapes(r, t),
                              (this.regionShapes[n] = i.map(t, function (e) {
                                  return e.id;
                              })))
                            : (a.replaceWithShape(r, t), (this.regionShapes[n] = t.id)));
                },
                render: function () {
                    var e,
                        t,
                        n,
                        a,
                        r = this.values,
                        s = this.target,
                        o = this.regionShapes;
                    if (this.cls._super.render.call(this)) {
                        for (n = r.length; n--; )
                            if ((e = this.renderRegion(n)))
                                if (i.isArray(e)) {
                                    for (t = [], a = e.length; a--; ) e[a].append(), t.push(e[a].id);
                                    o[n] = t;
                                } else e.append(), (o[n] = e.id);
                            else o[n] = null;
                        s.render();
                    }
                },
            }),
            (i.fn.sparkline.line = w = r(i.fn.sparkline._base, {
                type: "line",
                init: function (e, t, n, i, a) {
                    w._super.init.call(this, e, t, n, i, a),
                        (this.vertices = []),
                        (this.regionMap = []),
                        (this.xvalues = []),
                        (this.yvalues = []),
                        (this.yminmax = []),
                        (this.hightlightSpotId = null),
                        (this.lastShapeId = null),
                        this.initTarget();
                },
                getRegion: function (e, t, i) {
                    var a,
                        r = this.regionMap;
                    for (a = r.length; a--; ) if (null !== r[a] && t >= r[a][0] && t <= r[a][1]) return r[a][2];
                    return n;
                },
                getCurrentRegionFields: function () {
                    var e = this.currentRegion;
                    return { isNull: null === this.yvalues[e], x: this.xvalues[e], y: this.yvalues[e], color: this.options.get("lineColor"), fillColor: this.options.get("fillColor"), offset: e };
                },
                renderHighlight: function () {
                    var e,
                        t,
                        i = this.currentRegion,
                        a = this.target,
                        r = this.vertices[i],
                        s = this.options,
                        o = s.get("spotRadius"),
                        l = s.get("highlightSpotColor"),
                        d = s.get("highlightLineColor");
                    r &&
                        (o && l && ((e = a.drawCircle(r[0], r[1], o, n, l)), (this.highlightSpotId = e.id), a.insertAfterShape(this.lastShapeId, e)),
                        d && ((t = a.drawLine(r[0], this.canvasTop, r[0], this.canvasTop + this.canvasHeight, d)), (this.highlightLineId = t.id), a.insertAfterShape(this.lastShapeId, t)));
                },
                removeHighlight: function () {
                    var e = this.target;
                    this.highlightSpotId && (e.removeShapeId(this.highlightSpotId), (this.highlightSpotId = null)), this.highlightLineId && (e.removeShapeId(this.highlightLineId), (this.highlightLineId = null));
                },
                scanValues: function () {
                    var e,
                        n,
                        i,
                        a,
                        r,
                        s = this.values,
                        o = s.length,
                        l = this.xvalues,
                        d = this.yvalues,
                        u = this.yminmax;
                    for (e = 0; e < o; e++)
                        (n = s[e]),
                            (i = "string" == typeof s[e]),
                            (a = "object" == typeof s[e] && s[e] instanceof Array),
                            (r = i && s[e].split(":")),
                            i && 2 === r.length
                                ? (l.push(Number(r[0])), d.push(Number(r[1])), u.push(Number(r[1])))
                                : a
                                ? (l.push(n[0]), d.push(n[1]), u.push(n[1]))
                                : (l.push(e), null === s[e] || "null" === s[e] ? d.push(null) : (d.push(Number(n)), u.push(Number(n))));
                    this.options.get("xvalues") && (l = this.options.get("xvalues")),
                        (this.maxy = this.maxyorg = t.max.apply(t, u)),
                        (this.miny = this.minyorg = t.min.apply(t, u)),
                        (this.maxx = t.max.apply(t, l)),
                        (this.minx = t.min.apply(t, l)),
                        (this.xvalues = l),
                        (this.yvalues = d),
                        (this.yminmax = u);
                },
                processRangeOptions: function () {
                    var e = this.options,
                        t = e.get("normalRangeMin"),
                        i = e.get("normalRangeMax");
                    t !== n && (t < this.miny && (this.miny = t), i > this.maxy && (this.maxy = i)),
                        e.get("chartRangeMin") !== n && (e.get("chartRangeClip") || e.get("chartRangeMin") < this.miny) && (this.miny = e.get("chartRangeMin")),
                        e.get("chartRangeMax") !== n && (e.get("chartRangeClip") || e.get("chartRangeMax") > this.maxy) && (this.maxy = e.get("chartRangeMax")),
                        e.get("chartRangeMinX") !== n && (e.get("chartRangeClipX") || e.get("chartRangeMinX") < this.minx) && (this.minx = e.get("chartRangeMinX")),
                        e.get("chartRangeMaxX") !== n && (e.get("chartRangeClipX") || e.get("chartRangeMaxX") > this.maxx) && (this.maxx = e.get("chartRangeMaxX"));
                },
                drawNormalRange: function (e, i, a, r, s) {
                    var o = this.options.get("normalRangeMin"),
                        l = this.options.get("normalRangeMax"),
                        d = i + t.round(a - a * ((l - this.miny) / s)),
                        u = t.round((a * (l - o)) / s);
                    this.target.drawRect(e, d, r, u, n, this.options.get("normalRangeColor")).append();
                },
                render: function () {
                    var e,
                        a,
                        r,
                        s,
                        o,
                        l,
                        d,
                        u,
                        c,
                        h,
                        f,
                        p,
                        m,
                        g,
                        y,
                        v,
                        b,
                        M,
                        x,
                        k,
                        D,
                        L,
                        S,
                        T,
                        Y = this.options,
                        C = this.target,
                        E = this.canvasWidth,
                        A = this.canvasHeight,
                        I = this.vertices,
                        F = Y.get("spotRadius"),
                        H = this.regionMap;
                    if (w._super.render.call(this) && (this.scanValues(), this.processRangeOptions(), (L = this.xvalues), (S = this.yvalues), this.yminmax.length && !(this.yvalues.length < 2))) {
                        for (
                            s = o = 0,
                                e = this.maxx - this.minx == 0 ? 1 : this.maxx - this.minx,
                                a = this.maxy - this.miny == 0 ? 1 : this.maxy - this.miny,
                                r = this.yvalues.length - 1,
                                F && (E < 4 * F || A < 4 * F) && (F = 0),
                                F &&
                                    (((k = Y.get("highlightSpotColor") && !Y.get("disableInteraction")) || Y.get("minSpotColor") || (Y.get("spotColor") && S[r] === this.miny)) && (A -= t.ceil(F)),
                                    (k || Y.get("maxSpotColor") || (Y.get("spotColor") && S[r] === this.maxy)) && ((A -= t.ceil(F)), (s += t.ceil(F))),
                                    (k || ((Y.get("minSpotColor") || Y.get("maxSpotColor")) && (S[0] === this.miny || S[0] === this.maxy))) && ((o += t.ceil(F)), (E -= t.ceil(F))),
                                    (k || Y.get("spotColor") || Y.get("minSpotColor") || (Y.get("maxSpotColor") && (S[r] === this.miny || S[r] === this.maxy))) && (E -= t.ceil(F))),
                                A--,
                                Y.get("normalRangeMin") !== n && !Y.get("drawNormalOnTop") && this.drawNormalRange(o, s, A, E, a),
                                u = [(d = [])],
                                m = g = null,
                                y = S.length,
                                T = 0;
                            T < y;
                            T++
                        )
                            (c = L[T]),
                                (f = L[T + 1]),
                                (h = S[T]),
                                (g = (p = o + t.round((c - this.minx) * (E / e))) + ((T < y - 1 ? o + t.round((f - this.minx) * (E / e)) : E) - p) / 2),
                                (H[T] = [m || 0, g, T]),
                                (m = g),
                                null === h
                                    ? T && (null !== S[T - 1] && ((d = []), u.push(d)), I.push(null))
                                    : (h < this.miny && (h = this.miny), h > this.maxy && (h = this.maxy), d.length || d.push([p, s + A]), (l = [p, s + t.round(A - A * ((h - this.miny) / a))]), d.push(l), I.push(l));
                        for (v = [], b = [], M = u.length, T = 0; T < M; T++)
                            (d = u[T]).length && (Y.get("fillColor") && (d.push([d[d.length - 1][0], s + A]), b.push(d.slice(0)), d.pop()), d.length > 2 && (d[0] = [d[0][0], d[1][1]]), v.push(d));
                        for (M = b.length, T = 0; T < M; T++) C.drawShape(b[T], Y.get("fillColor"), Y.get("fillColor")).append();
                        for (Y.get("normalRangeMin") !== n && Y.get("drawNormalOnTop") && this.drawNormalRange(o, s, A, E, a), M = v.length, T = 0; T < M; T++) C.drawShape(v[T], Y.get("lineColor"), n, Y.get("lineWidth")).append();
                        if (F && Y.get("valueSpots"))
                            for ((x = Y.get("valueSpots")).get === n && (x = new _(x)), T = 0; T < y; T++)
                                (D = x.get(S[T])) && C.drawCircle(o + t.round((L[T] - this.minx) * (E / e)), s + t.round(A - A * ((S[T] - this.miny) / a)), F, n, D).append();
                        F && Y.get("spotColor") && null !== S[r] && C.drawCircle(o + t.round((L[L.length - 1] - this.minx) * (E / e)), s + t.round(A - A * ((S[r] - this.miny) / a)), F, n, Y.get("spotColor")).append(),
                            this.maxy !== this.minyorg &&
                                (F &&
                                    Y.get("minSpotColor") &&
                                    ((c = L[i.inArray(this.minyorg, S)]), C.drawCircle(o + t.round((c - this.minx) * (E / e)), s + t.round(A - A * ((this.minyorg - this.miny) / a)), F, n, Y.get("minSpotColor")).append()),
                                F &&
                                    Y.get("maxSpotColor") &&
                                    ((c = L[i.inArray(this.maxyorg, S)]), C.drawCircle(o + t.round((c - this.minx) * (E / e)), s + t.round(A - A * ((this.maxyorg - this.miny) / a)), F, n, Y.get("maxSpotColor")).append())),
                            (this.lastShapeId = C.getLastShapeId()),
                            (this.canvasTop = s),
                            C.render();
                    }
                },
            })),
            (i.fn.sparkline.bar = M = r(i.fn.sparkline._base, b, {
                type: "bar",
                init: function (e, a, r, s, l) {
                    var h,
                        f,
                        p,
                        m,
                        g,
                        y,
                        v,
                        b,
                        w,
                        x,
                        k,
                        D,
                        L,
                        S,
                        T,
                        Y,
                        C,
                        E,
                        A,
                        I,
                        F,
                        H = parseInt(r.get("barWidth"), 10),
                        P = parseInt(r.get("barSpacing"), 10),
                        j = r.get("chartRangeMin"),
                        O = r.get("chartRangeMax"),
                        N = r.get("chartRangeClip"),
                        R = 1 / 0,
                        $ = -1 / 0;
                    for (M._super.init.call(this, e, a, r, s, l), y = 0, v = a.length; y < v; y++)
                        ((h = "string" == typeof (I = a[y]) && I.indexOf(":") > -1) || i.isArray(I)) &&
                            ((T = !0), h && (I = a[y] = u(I.split(":"))), (I = c(I, null)), (f = t.min.apply(t, I)) < R && (R = f), (p = t.max.apply(t, I)) > $ && ($ = p));
                    (this.stacked = T),
                        (this.regionShapes = {}),
                        (this.barWidth = H),
                        (this.barSpacing = P),
                        (this.totalBarWidth = H + P),
                        (this.width = s = a.length * H + (a.length - 1) * P),
                        this.initTarget(),
                        N && ((L = j === n ? -1 / 0 : j), (S = O === n ? 1 / 0 : O)),
                        (g = []),
                        (m = T ? [] : g);
                    var W = [],
                        B = [];
                    for (y = 0, v = a.length; y < v; y++)
                        if (T)
                            for (Y = a[y], a[y] = A = [], W[y] = 0, m[y] = B[y] = 0, C = 0, E = Y.length; C < E; C++)
                                null !== (I = A[C] = N ? o(Y[C], L, S) : Y[C]) && (I > 0 && (W[y] += I), R < 0 && $ > 0 ? (I < 0 ? (B[y] += t.abs(I)) : (m[y] += I)) : (m[y] += t.abs(I - (I < 0 ? $ : R))), g.push(I));
                        else (I = N ? o(a[y], L, S) : a[y]), null !== (I = a[y] = d(I)) && g.push(I);
                    (this.max = D = t.max.apply(t, g)),
                        (this.min = k = t.min.apply(t, g)),
                        (this.stackMax = $ = T ? t.max.apply(t, W) : D),
                        (this.stackMin = R = T ? t.min.apply(t, g) : k),
                        r.get("chartRangeMin") !== n && (r.get("chartRangeClip") || r.get("chartRangeMin") < k) && (k = r.get("chartRangeMin")),
                        r.get("chartRangeMax") !== n && (r.get("chartRangeClip") || r.get("chartRangeMax") > D) && (D = r.get("chartRangeMax")),
                        (this.zeroAxis = w = r.get("zeroAxis", !0)),
                        (x = k <= 0 && D >= 0 && w ? 0 : 0 == w || k > 0 ? k : D),
                        (this.xaxisOffset = x),
                        (b = T ? t.max.apply(t, m) + t.max.apply(t, B) : D - k),
                        (this.canvasHeightEf = w && k < 0 ? this.canvasHeight - 2 : this.canvasHeight - 1),
                        k < x ? (F = (((T && D >= 0 ? $ : D) - x) / b) * this.canvasHeight) !== t.ceil(F) && ((this.canvasHeightEf -= 2), (F = t.ceil(F))) : (F = this.canvasHeight),
                        (this.yoffset = F),
                        i.isArray(r.get("colorMap"))
                            ? ((this.colorMapByIndex = r.get("colorMap")), (this.colorMapByValue = null))
                            : ((this.colorMapByIndex = null), (this.colorMapByValue = r.get("colorMap")), this.colorMapByValue && this.colorMapByValue.get === n && (this.colorMapByValue = new _(this.colorMapByValue))),
                        (this.range = b);
                },
                getRegion: function (e, i, a) {
                    var r = t.floor(i / this.totalBarWidth);
                    return r < 0 || r >= this.values.length ? n : r;
                },
                getCurrentRegionFields: function () {
                    var e,
                        t,
                        n = this.currentRegion,
                        i = m(this.values[n]),
                        a = [];
                    for (t = i.length; t--; ) (e = i[t]), a.push({ isNull: null === e, value: e, color: this.calcColor(t, e, n), offset: n });
                    return a;
                },
                calcColor: function (e, t, a) {
                    var r,
                        s,
                        o = this.colorMapByIndex,
                        l = this.colorMapByValue,
                        d = this.options;
                    return (
                        (r = this.stacked ? d.get("stackedBarColor") : t < 0 ? d.get("negBarColor") : d.get("barColor")),
                        0 === t && d.get("zeroColor") !== n && (r = d.get("zeroColor")),
                        l && (s = l.get(t)) ? (r = s) : o && o.length > a && (r = o[a]),
                        i.isArray(r) ? r[e % r.length] : r
                    );
                },
                renderRegion: function (e, a) {
                    var r,
                        s,
                        o,
                        l,
                        d,
                        u,
                        c,
                        h,
                        p,
                        m,
                        g = this.values[e],
                        _ = this.options,
                        y = this.xaxisOffset,
                        v = [],
                        b = this.range,
                        w = this.stacked,
                        M = this.target,
                        x = e * this.totalBarWidth,
                        k = this.canvasHeightEf,
                        D = this.yoffset;
                    if (((c = (g = i.isArray(g) ? g : [g]).length), (h = g[0]), (l = f(null, g)), (m = f(y, g, !0)), l))
                        return _.get("nullColor") ? ((o = a ? _.get("nullColor") : this.calcHighlightColor(_.get("nullColor"), _)), (r = D > 0 ? D - 1 : D), M.drawRect(x, r, this.barWidth - 1, 0, o, o)) : n;
                    for (d = D, u = 0; u < c; u++) {
                        if (((h = g[u]), w && h === y)) {
                            if (!m || p) continue;
                            p = !0;
                        }
                        (s = b > 0 ? t.floor(k * (t.abs(h - y) / b)) + 1 : 1),
                            h < y || (h === y && 0 === D) ? ((r = d), (d += s)) : ((r = D - s), (D -= s)),
                            (o = this.calcColor(u, h, e)),
                            a && (o = this.calcHighlightColor(o, _)),
                            v.push(M.drawRect(x, r, this.barWidth - 1, s - 1, o, o));
                    }
                    return 1 === v.length ? v[0] : v;
                },
            })),
            (i.fn.sparkline.tristate = x = r(i.fn.sparkline._base, b, {
                type: "tristate",
                init: function (e, t, a, r, s) {
                    var o = parseInt(a.get("barWidth"), 10),
                        l = parseInt(a.get("barSpacing"), 10);
                    x._super.init.call(this, e, t, a, r, s),
                        (this.regionShapes = {}),
                        (this.barWidth = o),
                        (this.barSpacing = l),
                        (this.totalBarWidth = o + l),
                        (this.values = i.map(t, Number)),
                        (this.width = r = t.length * o + (t.length - 1) * l),
                        i.isArray(a.get("colorMap"))
                            ? ((this.colorMapByIndex = a.get("colorMap")), (this.colorMapByValue = null))
                            : ((this.colorMapByIndex = null), (this.colorMapByValue = a.get("colorMap")), this.colorMapByValue && this.colorMapByValue.get === n && (this.colorMapByValue = new _(this.colorMapByValue))),
                        this.initTarget();
                },
                getRegion: function (e, n, i) {
                    return t.floor(n / this.totalBarWidth);
                },
                getCurrentRegionFields: function () {
                    var e = this.currentRegion;
                    return { isNull: this.values[e] === n, value: this.values[e], color: this.calcColor(this.values[e], e), offset: e };
                },
                calcColor: function (e, t) {
                    var n,
                        i = this.values,
                        a = this.options,
                        r = this.colorMapByIndex,
                        s = this.colorMapByValue;
                    return s && (n = s.get(e)) ? n : r && r.length > t ? r[t] : i[t] < 0 ? a.get("negBarColor") : i[t] > 0 ? a.get("posBarColor") : a.get("zeroBarColor");
                },
                renderRegion: function (e, n) {
                    var i,
                        a,
                        r,
                        s,
                        o,
                        l,
                        d = this.values,
                        u = this.options,
                        c = this.target;
                    if (((i = c.pixelHeight), (r = t.round(i / 2)), (s = e * this.totalBarWidth), d[e] < 0 ? ((o = r), (a = r - 1)) : d[e] > 0 ? ((o = 0), (a = r - 1)) : ((o = r - 1), (a = 2)), null !== (l = this.calcColor(d[e], e))))
                        return n && (l = this.calcHighlightColor(l, u)), c.drawRect(s, o, this.barWidth - 1, a - 1, l, l);
                },
            })),
            (i.fn.sparkline.discrete = k = r(i.fn.sparkline._base, b, {
                type: "discrete",
                init: function (e, a, r, s, o) {
                    k._super.init.call(this, e, a, r, s, o),
                        (this.regionShapes = {}),
                        (this.values = a = i.map(a, Number)),
                        (this.min = t.min.apply(t, a)),
                        (this.max = t.max.apply(t, a)),
                        (this.range = this.max - this.min),
                        (this.width = s = "auto" === r.get("width") ? 2 * a.length : this.width),
                        (this.interval = t.floor(s / a.length)),
                        (this.itemWidth = s / a.length),
                        r.get("chartRangeMin") !== n && (r.get("chartRangeClip") || r.get("chartRangeMin") < this.min) && (this.min = r.get("chartRangeMin")),
                        r.get("chartRangeMax") !== n && (r.get("chartRangeClip") || r.get("chartRangeMax") > this.max) && (this.max = r.get("chartRangeMax")),
                        this.initTarget(),
                        this.target && (this.lineHeight = "auto" === r.get("lineHeight") ? t.round(0.3 * this.canvasHeight) : r.get("lineHeight"));
                },
                getRegion: function (e, n, i) {
                    return t.floor(n / this.itemWidth);
                },
                getCurrentRegionFields: function () {
                    var e = this.currentRegion;
                    return { isNull: this.values[e] === n, value: this.values[e], offset: e };
                },
                renderRegion: function (e, n) {
                    var i,
                        a,
                        r,
                        s,
                        l = this.values,
                        d = this.options,
                        u = this.min,
                        c = this.max,
                        h = this.range,
                        f = this.interval,
                        p = this.target,
                        m = this.canvasHeight,
                        g = this.lineHeight,
                        _ = m - g;
                    return (
                        (a = o(l[e], u, c)),
                        (s = e * f),
                        (i = t.round(_ - _ * ((a - u) / h))),
                        (r = d.get("thresholdColor") && a < d.get("thresholdValue") ? d.get("thresholdColor") : d.get("lineColor")),
                        n && (r = this.calcHighlightColor(r, d)),
                        p.drawLine(s, i, s, i + g, r)
                    );
                },
            })),
            (i.fn.sparkline.bullet = D = r(i.fn.sparkline._base, {
                type: "bullet",
                init: function (e, i, a, r, s) {
                    var o, l, d;
                    D._super.init.call(this, e, i, a, r, s),
                        (this.values = i = u(i)),
                        ((d = i.slice())[0] = null === d[0] ? d[2] : d[0]),
                        (d[1] = null === i[1] ? d[2] : d[1]),
                        (o = t.min.apply(t, i)),
                        (l = t.max.apply(t, i)),
                        (o = a.get("base") === n ? (o < 0 ? o : 0) : a.get("base")),
                        (this.min = o),
                        (this.max = l),
                        (this.range = l - o),
                        (this.shapes = {}),
                        (this.valueShapes = {}),
                        (this.regiondata = {}),
                        (this.width = r = "auto" === a.get("width") ? "4.0em" : r),
                        (this.target = this.$el.simpledraw(r, s, a.get("composite"))),
                        i.length || (this.disabled = !0),
                        this.initTarget();
                },
                getRegion: function (e, t, i) {
                    var a = this.target.getShapeAt(e, t, i);
                    return a !== n && this.shapes[a] !== n ? this.shapes[a] : n;
                },
                getCurrentRegionFields: function () {
                    var e = this.currentRegion;
                    return { fieldkey: e.substr(0, 1), value: this.values[e.substr(1)], region: e };
                },
                changeHighlight: function (e) {
                    var t,
                        n = this.currentRegion,
                        i = this.valueShapes[n];
                    switch ((delete this.shapes[i], n.substr(0, 1))) {
                        case "r":
                            t = this.renderRange(n.substr(1), e);
                            break;
                        case "p":
                            t = this.renderPerformance(e);
                            break;
                        case "t":
                            t = this.renderTarget(e);
                    }
                    (this.valueShapes[n] = t.id), (this.shapes[t.id] = n), this.target.replaceWithShape(i, t);
                },
                renderRange: function (e, n) {
                    var i = this.values[e],
                        a = t.round(this.canvasWidth * ((i - this.min) / this.range)),
                        r = this.options.get("rangeColors")[e - 2];
                    return n && (r = this.calcHighlightColor(r, this.options)), this.target.drawRect(0, 0, a - 1, this.canvasHeight - 1, r, r);
                },
                renderPerformance: function (e) {
                    var n = this.values[1],
                        i = t.round(this.canvasWidth * ((n - this.min) / this.range)),
                        a = this.options.get("performanceColor");
                    return e && (a = this.calcHighlightColor(a, this.options)), this.target.drawRect(0, t.round(0.3 * this.canvasHeight), i - 1, t.round(0.4 * this.canvasHeight) - 1, a, a);
                },
                renderTarget: function (e) {
                    var n = this.values[0],
                        i = t.round(this.canvasWidth * ((n - this.min) / this.range) - this.options.get("targetWidth") / 2),
                        a = t.round(0.1 * this.canvasHeight),
                        r = this.canvasHeight - 2 * a,
                        s = this.options.get("targetColor");
                    return e && (s = this.calcHighlightColor(s, this.options)), this.target.drawRect(i, a, this.options.get("targetWidth") - 1, r - 1, s, s);
                },
                render: function () {
                    var e,
                        t,
                        n = this.values.length,
                        i = this.target;
                    if (D._super.render.call(this)) {
                        for (e = 2; e < n; e++) (t = this.renderRange(e).append()), (this.shapes[t.id] = "r" + e), (this.valueShapes["r" + e] = t.id);
                        null !== this.values[1] && ((t = this.renderPerformance().append()), (this.shapes[t.id] = "p1"), (this.valueShapes.p1 = t.id)),
                            null !== this.values[0] && ((t = this.renderTarget().append()), (this.shapes[t.id] = "t0"), (this.valueShapes.t0 = t.id)),
                            i.render();
                    }
                },
            })),
            (i.fn.sparkline.pie = L = r(i.fn.sparkline._base, {
                type: "pie",
                init: function (e, n, a, r, s) {
                    var o,
                        l = 0;
                    if ((L._super.init.call(this, e, n, a, r, s), (this.shapes = {}), (this.valueShapes = {}), (this.values = n = i.map(n, Number)), "auto" === a.get("width") && (this.width = this.height), n.length > 0))
                        for (o = n.length; o--; ) l += n[o];
                    (this.total = l), this.initTarget(), (this.radius = t.floor(t.min(this.canvasWidth, this.canvasHeight) / 2));
                },
                getRegion: function (e, t, i) {
                    var a = this.target.getShapeAt(e, t, i);
                    return a !== n && this.shapes[a] !== n ? this.shapes[a] : n;
                },
                getCurrentRegionFields: function () {
                    var e = this.currentRegion;
                    return { isNull: this.values[e] === n, value: this.values[e], percent: (this.values[e] / this.total) * 100, color: this.options.get("sliceColors")[e % this.options.get("sliceColors").length], offset: e };
                },
                changeHighlight: function (e) {
                    var t = this.currentRegion,
                        n = this.renderSlice(t, e),
                        i = this.valueShapes[t];
                    delete this.shapes[i], this.target.replaceWithShape(i, n), (this.valueShapes[t] = n.id), (this.shapes[n.id] = t);
                },
                renderSlice: function (e, i) {
                    var a,
                        r,
                        s,
                        o,
                        l,
                        d = this.target,
                        u = this.options,
                        c = this.radius,
                        h = u.get("borderWidth"),
                        f = u.get("offset"),
                        p = 2 * t.PI,
                        m = this.values,
                        g = this.total,
                        _ = f ? 2 * t.PI * (f / 360) : 0;
                    for (o = m.length, s = 0; s < o; s++) {
                        if (((a = _), (r = _), g > 0 && (r = _ + p * (m[s] / g)), e === s))
                            return (l = u.get("sliceColors")[s % u.get("sliceColors").length]), i && (l = this.calcHighlightColor(l, u)), d.drawPieSlice(c, c, c - h, a, r, n, l);
                        _ = r;
                    }
                },
                render: function () {
                    var e,
                        i,
                        a = this.target,
                        r = this.values,
                        s = this.options,
                        o = this.radius,
                        l = s.get("borderWidth");
                    if (L._super.render.call(this)) {
                        for (l && a.drawCircle(o, o, t.floor(o - l / 2), s.get("borderColor"), n, l).append(), i = r.length; i--; ) r[i] && ((e = this.renderSlice(i).append()), (this.valueShapes[i] = e.id), (this.shapes[e.id] = i));
                        a.render();
                    }
                },
            })),
            (i.fn.sparkline.box = S = r(i.fn.sparkline._base, {
                type: "box",
                init: function (e, t, n, a, r) {
                    S._super.init.call(this, e, t, n, a, r), (this.values = i.map(t, Number)), (this.width = "auto" === n.get("width") ? "4.0em" : a), this.initTarget(), this.values.length || (this.disabled = 1);
                },
                getRegion: function () {
                    return 1;
                },
                getCurrentRegionFields: function () {
                    var e = [
                        { field: "lq", value: this.quartiles[0] },
                        { field: "med", value: this.quartiles[1] },
                        { field: "uq", value: this.quartiles[2] },
                    ];
                    return (
                        this.loutlier !== n && e.push({ field: "lo", value: this.loutlier }),
                        this.routlier !== n && e.push({ field: "ro", value: this.routlier }),
                        this.lwhisker !== n && e.push({ field: "lw", value: this.lwhisker }),
                        this.rwhisker !== n && e.push({ field: "rw", value: this.rwhisker }),
                        e
                    );
                },
                render: function () {
                    var e,
                        i,
                        a,
                        r,
                        s,
                        o,
                        d,
                        u,
                        c,
                        h,
                        f,
                        p = this.target,
                        m = this.values,
                        g = m.length,
                        _ = this.options,
                        y = this.canvasWidth,
                        v = this.canvasHeight,
                        b = _.get("chartRangeMin") === n ? t.min.apply(t, m) : _.get("chartRangeMin"),
                        w = _.get("chartRangeMax") === n ? t.max.apply(t, m) : _.get("chartRangeMax"),
                        M = 0;
                    if (S._super.render.call(this)) {
                        if (_.get("raw")) _.get("showOutliers") && m.length > 5 ? ((i = m[0]), (e = m[1]), (r = m[2]), (s = m[3]), (o = m[4]), (d = m[5]), (u = m[6])) : ((e = m[0]), (r = m[1]), (s = m[2]), (o = m[3]), (d = m[4]));
                        else if (
                            (m.sort(function (e, t) {
                                return e - t;
                            }),
                            (r = l(m, 1)),
                            (s = l(m, 2)),
                            (a = (o = l(m, 3)) - r),
                            _.get("showOutliers"))
                        ) {
                            for (e = d = n, c = 0; c < g; c++) e === n && m[c] > r - a * _.get("outlierIQR") && (e = m[c]), m[c] < o + a * _.get("outlierIQR") && (d = m[c]);
                            (i = m[0]), (u = m[g - 1]);
                        } else (e = m[0]), (d = m[g - 1]);
                        (this.quartiles = [r, s, o]),
                            (this.lwhisker = e),
                            (this.rwhisker = d),
                            (this.loutlier = i),
                            (this.routlier = u),
                            (f = y / (w - b + 1)),
                            _.get("showOutliers") &&
                                ((M = t.ceil(_.get("spotRadius"))),
                                (f = (y -= 2 * t.ceil(_.get("spotRadius"))) / (w - b + 1)),
                                i < e && p.drawCircle((i - b) * f + M, v / 2, _.get("spotRadius"), _.get("outlierLineColor"), _.get("outlierFillColor")).append(),
                                u > d && p.drawCircle((u - b) * f + M, v / 2, _.get("spotRadius"), _.get("outlierLineColor"), _.get("outlierFillColor")).append()),
                            p.drawRect(t.round((r - b) * f + M), t.round(0.1 * v), t.round((o - r) * f), t.round(0.8 * v), _.get("boxLineColor"), _.get("boxFillColor")).append(),
                            p.drawLine(t.round((e - b) * f + M), t.round(v / 2), t.round((r - b) * f + M), t.round(v / 2), _.get("lineColor")).append(),
                            p.drawLine(t.round((e - b) * f + M), t.round(v / 4), t.round((e - b) * f + M), t.round(v - v / 4), _.get("whiskerColor")).append(),
                            p.drawLine(t.round((d - b) * f + M), t.round(v / 2), t.round((o - b) * f + M), t.round(v / 2), _.get("lineColor")).append(),
                            p.drawLine(t.round((d - b) * f + M), t.round(v / 4), t.round((d - b) * f + M), t.round(v - v / 4), _.get("whiskerColor")).append(),
                            p.drawLine(t.round((s - b) * f + M), t.round(0.1 * v), t.round((s - b) * f + M), t.round(0.9 * v), _.get("medianColor")).append(),
                            _.get("target") &&
                                ((h = t.ceil(_.get("spotRadius"))),
                                p.drawLine(t.round((_.get("target") - b) * f + M), t.round(v / 2 - h), t.round((_.get("target") - b) * f + M), t.round(v / 2 + h), _.get("targetColor")).append(),
                                p.drawLine(t.round((_.get("target") - b) * f + M - h), t.round(v / 2), t.round((_.get("target") - b) * f + M + h), t.round(v / 2), _.get("targetColor")).append()),
                            p.render();
                    }
                },
            })),
            (T = r({
                init: function (e, t, n, i) {
                    (this.target = e), (this.id = t), (this.type = n), (this.args = i);
                },
                append: function () {
                    return this.target.appendShape(this), this;
                },
            })),
            (Y = r({
                _pxregex: /(\d+)(px)?\s*$/i,
                init: function (e, t, n) {
                    e && ((this.width = e), (this.height = t), (this.target = n), (this.lastShapeId = null), n[0] && (n = n[0]), i.data(n, "_jqs_vcanvas", this));
                },
                drawLine: function (e, t, n, i, a, r) {
                    return this.drawShape(
                        [
                            [e, t],
                            [n, i],
                        ],
                        a,
                        r
                    );
                },
                drawShape: function (e, t, n, i) {
                    return this._genShape("Shape", [e, t, n, i]);
                },
                drawCircle: function (e, t, n, i, a, r) {
                    return this._genShape("Circle", [e, t, n, i, a, r]);
                },
                drawPieSlice: function (e, t, n, i, a, r, s) {
                    return this._genShape("PieSlice", [e, t, n, i, a, r, s]);
                },
                drawRect: function (e, t, n, i, a, r) {
                    return this._genShape("Rect", [e, t, n, i, a, r]);
                },
                getElement: function () {
                    return this.canvas;
                },
                getLastShapeId: function () {
                    return this.lastShapeId;
                },
                reset: function () {
                    alert("reset not implemented");
                },
                _insert: function (e, t) {
                    i(t).html(e);
                },
                _calculatePixelDims: function (e, t, n) {
                    var a;
                    (a = this._pxregex.exec(t)), (this.pixelHeight = a ? a[1] : i(n).height()), (a = this._pxregex.exec(e)), (this.pixelWidth = a ? a[1] : i(n).width());
                },
                _genShape: function (e, t) {
                    var n = F++;
                    return t.unshift(n), new T(this, n, e, t);
                },
                appendShape: function (e) {
                    alert("appendShape not implemented");
                },
                replaceWithShape: function (e, t) {
                    alert("replaceWithShape not implemented");
                },
                insertAfterShape: function (e, t) {
                    alert("insertAfterShape not implemented");
                },
                removeShapeId: function (e) {
                    alert("removeShapeId not implemented");
                },
                getShapeAt: function (e, t, n) {
                    alert("getShapeAt not implemented");
                },
                render: function () {
                    alert("render not implemented");
                },
            })),
            (C = r(Y, {
                init: function (t, a, r, s) {
                    C._super.init.call(this, t, a, r),
                        (this.canvas = e.createElement("canvas")),
                        r[0] && (r = r[0]),
                        i.data(r, "_jqs_vcanvas", this),
                        i(this.canvas).css({ display: "inline-block", width: t, height: a, verticalAlign: "top" }),
                        this._insert(this.canvas, r),
                        this._calculatePixelDims(t, a, this.canvas),
                        (this.canvas.width = this.pixelWidth),
                        (this.canvas.height = this.pixelHeight),
                        (this.interact = s),
                        (this.shapes = {}),
                        (this.shapeseq = []),
                        (this.currentTargetShapeId = n),
                        i(this.canvas).css({ width: this.pixelWidth, height: this.pixelHeight });
                },
                _getContext: function (e, t, i) {
                    var a = this.canvas.getContext("2d");
                    return e !== n && (a.strokeStyle = e), (a.lineWidth = i === n ? 1 : i), t !== n && (a.fillStyle = t), a;
                },
                reset: function () {
                    this._getContext().clearRect(0, 0, this.pixelWidth, this.pixelHeight), (this.shapes = {}), (this.shapeseq = []), (this.currentTargetShapeId = n);
                },
                _drawShape: function (e, t, i, a, r) {
                    var s,
                        o,
                        l = this._getContext(i, a, r);
                    for (l.beginPath(), l.moveTo(t[0][0] + 0.5, t[0][1] + 0.5), s = 1, o = t.length; s < o; s++) l.lineTo(t[s][0] + 0.5, t[s][1] + 0.5);
                    i !== n && l.stroke(), a !== n && l.fill(), this.targetX !== n && this.targetY !== n && l.isPointInPath(this.targetX, this.targetY) && (this.currentTargetShapeId = e);
                },
                _drawCircle: function (e, i, a, r, s, o, l) {
                    var d = this._getContext(s, o, l);
                    d.beginPath(), d.arc(i, a, r, 0, 2 * t.PI, !1), this.targetX !== n && this.targetY !== n && d.isPointInPath(this.targetX, this.targetY) && (this.currentTargetShapeId = e), s !== n && d.stroke(), o !== n && d.fill();
                },
                _drawPieSlice: function (e, t, i, a, r, s, o, l) {
                    var d = this._getContext(o, l);
                    d.beginPath(),
                        d.moveTo(t, i),
                        d.arc(t, i, a, r, s, !1),
                        d.lineTo(t, i),
                        d.closePath(),
                        o !== n && d.stroke(),
                        l && d.fill(),
                        this.targetX !== n && this.targetY !== n && d.isPointInPath(this.targetX, this.targetY) && (this.currentTargetShapeId = e);
                },
                _drawRect: function (e, t, n, i, a, r, s) {
                    return this._drawShape(
                        e,
                        [
                            [t, n],
                            [t + i, n],
                            [t + i, n + a],
                            [t, n + a],
                            [t, n],
                        ],
                        r,
                        s
                    );
                },
                appendShape: function (e) {
                    return (this.shapes[e.id] = e), this.shapeseq.push(e.id), (this.lastShapeId = e.id), e.id;
                },
                replaceWithShape: function (e, t) {
                    var n,
                        i = this.shapeseq;
                    for (this.shapes[t.id] = t, n = i.length; n--; ) i[n] == e && (i[n] = t.id);
                    delete this.shapes[e];
                },
                replaceWithShapes: function (e, t) {
                    var n,
                        i,
                        a,
                        r = this.shapeseq,
                        s = {};
                    for (i = e.length; i--; ) s[e[i]] = !0;
                    for (i = r.length; i--; ) s[(n = r[i])] && (r.splice(i, 1), delete this.shapes[n], (a = i));
                    for (i = t.length; i--; ) r.splice(a, 0, t[i].id), (this.shapes[t[i].id] = t[i]);
                },
                insertAfterShape: function (e, t) {
                    var n,
                        i = this.shapeseq;
                    for (n = i.length; n--; ) if (i[n] === e) return i.splice(n + 1, 0, t.id), void (this.shapes[t.id] = t);
                },
                removeShapeId: function (e) {
                    var t,
                        n = this.shapeseq;
                    for (t = n.length; t--; )
                        if (n[t] === e) {
                            n.splice(t, 1);
                            break;
                        }
                    delete this.shapes[e];
                },
                getShapeAt: function (e, t, n) {
                    return (this.targetX = t), (this.targetY = n), this.render(), this.currentTargetShapeId;
                },
                render: function () {
                    var e,
                        t,
                        n = this.shapeseq,
                        i = this.shapes,
                        a = n.length;
                    for (this._getContext().clearRect(0, 0, this.pixelWidth, this.pixelHeight), t = 0; t < a; t++) this["_draw" + (e = i[n[t]]).type].apply(this, e.args);
                    this.interact || ((this.shapes = {}), (this.shapeseq = []));
                },
            })),
            (E = r(Y, {
                init: function (t, n, a) {
                    var r;
                    E._super.init.call(this, t, n, a),
                        a[0] && (a = a[0]),
                        i.data(a, "_jqs_vcanvas", this),
                        (this.canvas = e.createElement("span")),
                        i(this.canvas).css({ display: "inline-block", position: "relative", overflow: "hidden", width: t, height: n, margin: "0px", padding: "0px", verticalAlign: "top" }),
                        this._insert(this.canvas, a),
                        this._calculatePixelDims(t, n, this.canvas),
                        (this.canvas.width = this.pixelWidth),
                        (this.canvas.height = this.pixelHeight),
                        (r =
                            '<v:group coordorigin="0 0" coordsize="' + this.pixelWidth + " " + this.pixelHeight + '" style="position:absolute;top:0;left:0;width:' + this.pixelWidth + "px;height=" + this.pixelHeight + 'px;"></v:group>'),
                        this.canvas.insertAdjacentHTML("beforeEnd", r),
                        (this.group = i(this.canvas).children()[0]),
                        (this.rendered = !1),
                        (this.prerender = "");
                },
                _drawShape: function (e, t, i, a, r) {
                    var s,
                        o,
                        l,
                        d,
                        u,
                        c,
                        h = [];
                    for (c = 0, u = t.length; c < u; c++) h[c] = t[c][0] + "," + t[c][1];
                    return (
                        (s = h.splice(0, 1)),
                        (r = r === n ? 1 : r),
                        (o = i === n ? ' stroked="false" ' : ' strokeWeight="' + r + 'px" strokeColor="' + i + '" '),
                        (l = a === n ? ' filled="false"' : ' fillColor="' + a + '" filled="true" '),
                        (d = h[0] === h[h.length - 1] ? "x " : ""),
                        '<v:shape coordorigin="0 0" coordsize="' +
                            this.pixelWidth +
                            " " +
                            this.pixelHeight +
                            '"  id="jqsshape' +
                            e +
                            '" ' +
                            o +
                            l +
                            ' style="position:absolute;left:0px;top:0px;height:' +
                            this.pixelHeight +
                            "px;width:" +
                            this.pixelWidth +
                            'px;padding:0px;margin:0px;"  path="m ' +
                            s +
                            " l " +
                            h.join(", ") +
                            " " +
                            d +
                            'e"> </v:shape>'
                    );
                },
                _drawCircle: function (e, t, i, a, r, s, o) {
                    return (
                        '<v:oval  id="jqsshape' +
                        e +
                        '" ' +
                        (r === n ? ' stroked="false" ' : ' strokeWeight="' + o + 'px" strokeColor="' + r + '" ') +
                        (s === n ? ' filled="false"' : ' fillColor="' + s + '" filled="true" ') +
                        ' style="position:absolute;top:' +
                        (i -= a) +
                        "px; left:" +
                        (t -= a) +
                        "px; width:" +
                        2 * a +
                        "px; height:" +
                        2 * a +
                        'px"></v:oval>'
                    );
                },
                _drawPieSlice: function (e, i, a, r, s, o, l, d) {
                    var u, c, h, f, p, m, g;
                    if (s === o) return "";
                    if ((o - s == 2 * t.PI && ((s = 0), (o = 2 * t.PI)), (c = i + t.round(t.cos(s) * r)), (h = a + t.round(t.sin(s) * r)), (f = i + t.round(t.cos(o) * r)), (p = a + t.round(t.sin(o) * r)), c === f && h === p)) {
                        if (o - s < t.PI) return "";
                        (c = f = i + r), (h = p = a);
                    }
                    return c === f && h === p && o - s < t.PI
                        ? ""
                        : ((u = [i - r, a - r, i + r, a + r, c, h, f, p]),
                          (m = l === n ? ' stroked="false" ' : ' strokeWeight="1px" strokeColor="' + l + '" '),
                          (g = d === n ? ' filled="false"' : ' fillColor="' + d + '" filled="true" '),
                          '<v:shape coordorigin="0 0" coordsize="' +
                              this.pixelWidth +
                              " " +
                              this.pixelHeight +
                              '"  id="jqsshape' +
                              e +
                              '" ' +
                              m +
                              g +
                              ' style="position:absolute;left:0px;top:0px;height:' +
                              this.pixelHeight +
                              "px;width:" +
                              this.pixelWidth +
                              'px;padding:0px;margin:0px;"  path="m ' +
                              i +
                              "," +
                              a +
                              " wa " +
                              u.join(", ") +
                              ' x e"> </v:shape>');
                },
                _drawRect: function (e, t, n, i, a, r, s) {
                    return this._drawShape(
                        e,
                        [
                            [t, n],
                            [t, n + a],
                            [t + i, n + a],
                            [t + i, n],
                            [t, n],
                        ],
                        r,
                        s
                    );
                },
                reset: function () {
                    this.group.innerHTML = "";
                },
                appendShape: function (e) {
                    var t = this["_draw" + e.type].apply(this, e.args);
                    return this.rendered ? this.group.insertAdjacentHTML("beforeEnd", t) : (this.prerender += t), (this.lastShapeId = e.id), e.id;
                },
                replaceWithShape: function (e, t) {
                    var n = i("#jqsshape" + e),
                        a = this["_draw" + t.type].apply(this, t.args);
                    n[0].outerHTML = a;
                },
                replaceWithShapes: function (e, t) {
                    var n,
                        a = i("#jqsshape" + e[0]),
                        r = "",
                        s = t.length;
                    for (n = 0; n < s; n++) r += this["_draw" + t[n].type].apply(this, t[n].args);
                    for (a[0].outerHTML = r, n = 1; n < e.length; n++) i("#jqsshape" + e[n]).remove();
                },
                insertAfterShape: function (e, t) {
                    var n = i("#jqsshape" + e),
                        a = this["_draw" + t.type].apply(this, t.args);
                    n[0].insertAdjacentHTML("afterEnd", a);
                },
                removeShapeId: function (e) {
                    var t = i("#jqsshape" + e);
                    this.group.removeChild(t[0]);
                },
                getShapeAt: function (e, t, n) {
                    return e.id.substr(8);
                },
                render: function () {
                    this.rendered || ((this.group.innerHTML = this.prerender), (this.rendered = !0));
                },
            }));
    });
})(document, Math),
(function (e, t) {
    "function" == typeof define && define.amd ? define(["jquery"], t) : "undefined" != typeof exports ? t(require("jquery")) : (t(e.jquery), (e.metisMenu = {}));
})(this, function (e) {
    "use strict";
    var t;
    (t = e) && t.__esModule;
    var n =
            "function" == typeof Symbol && "symbol" == typeof Symbol.iterator
                ? function (e) {
                      return typeof e;
                  }
                : function (e) {
                      return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e;
                  },
        i = (function (e) {
            var t = !1,
                n = { WebkitTransition: "webkitTransitionEnd", MozTransition: "transitionend", OTransition: "oTransitionEnd otransitionend", transition: "transitionend" };
            var i = {
                TRANSITION_END: "mmTransitionEnd",
                triggerTransitionEnd: function (n) {
                    e(n).trigger(t.end);
                },
                supportsTransitionEnd: function () {
                    return Boolean(t);
                },
            };
            return (
                (t = (function () {
                    if (window.QUnit) return !1;
                    var e = document.createElement("mm");
                    for (var t in n) if (void 0 !== e.style[t]) return { end: n[t] };
                    return !1;
                })()),
                (e.fn.emulateTransitionEnd = function (t) {
                    var n = this,
                        a = !1;
                    return (
                        e(this).one(i.TRANSITION_END, function () {
                            a = !0;
                        }),
                        setTimeout(function () {
                            a || i.triggerTransitionEnd(n);
                        }, t),
                        this
                    );
                }),
                i.supportsTransitionEnd() &&
                    (e.event.special[i.TRANSITION_END] = {
                        bindType: t.end,
                        delegateType: t.end,
                        handle: function (t) {
                            if (e(t.target).is(this)) return t.handleObj.handler.apply(this, arguments);
                        },
                    }),
                i
            );
        })(jQuery);
    !(function (e) {
        var t = "metisMenu",
            a = "metisMenu",
            r = e.fn[t],
            s = { toggle: !0, preventDefault: !0, activeClass: "active", collapseClass: "collapse", collapseInClass: "in", collapsingClass: "collapsing", triggerElement: "a", parentTrigger: "li", subMenu: "ul" },
            o = "show.metisMenu",
            l = "shown.metisMenu",
            d = "hide.metisMenu",
            u = "hidden.metisMenu",
            c = "click.metisMenu.data-api",
            h = (function () {
                function t(e, n) {
                    !(function (e, t) {
                        if (!(e instanceof t)) throw new TypeError("Cannot call a class as a function");
                    })(this, t),
                        (this._element = e),
                        (this._config = this._getConfig(n)),
                        (this._transitioning = null),
                        this.init();
                }
                return (
                    (t.prototype.init = function () {
                        var t = this;
                        e(this._element)
                            .find(this._config.parentTrigger + "." + this._config.activeClass)
                            .has(this._config.subMenu)
                            .children(this._config.subMenu)
                            .attr("aria-expanded", !0)
                            .addClass(this._config.collapseClass + " " + this._config.collapseInClass),
                            e(this._element)
                                .find(this._config.parentTrigger)
                                .not("." + this._config.activeClass)
                                .has(this._config.subMenu)
                                .children(this._config.subMenu)
                                .attr("aria-expanded", !1)
                                .addClass(this._config.collapseClass),
                            e(this._element)
                                .find(this._config.parentTrigger)
                                .has(this._config.subMenu)
                                .children(this._config.triggerElement)
                                .on(c, function (n) {
                                    var i = e(this),
                                        a = i.parent(t._config.parentTrigger),
                                        r = a.siblings(t._config.parentTrigger).children(t._config.triggerElement),
                                        s = a.children(t._config.subMenu);
                                    t._config.preventDefault && n.preventDefault(),
                                        "true" !== i.attr("aria-disabled") &&
                                            (a.hasClass(t._config.activeClass) ? (i.attr("aria-expanded", !1), t._hide(s)) : (t._show(s), i.attr("aria-expanded", !0), t._config.toggle && r.attr("aria-expanded", !1)),
                                            t._config.onTransitionStart && t._config.onTransitionStart(n));
                                });
                    }),
                    (t.prototype._show = function (t) {
                        if (!this._transitioning && !e(t).hasClass(this._config.collapsingClass)) {
                            var n = this,
                                a = e(t),
                                r = e.Event(o);
                            if ((a.trigger(r), !r.isDefaultPrevented())) {
                                a.parent(this._config.parentTrigger).addClass(this._config.activeClass),
                                    this._config.toggle &&
                                        this._hide(
                                            a
                                                .parent(this._config.parentTrigger)
                                                .siblings()
                                                .children(this._config.subMenu + "." + this._config.collapseInClass)
                                                .attr("aria-expanded", !1)
                                        ),
                                    a.removeClass(this._config.collapseClass).addClass(this._config.collapsingClass).height(0),
                                    this.setTransitioning(!0);
                                var s = function () {
                                    a
                                        .removeClass(n._config.collapsingClass)
                                        .addClass(n._config.collapseClass + " " + n._config.collapseInClass)
                                        .height("")
                                        .attr("aria-expanded", !0),
                                        n.setTransitioning(!1),
                                        a.trigger(l);
                                };
                                i.supportsTransitionEnd() ? a.height(a[0].scrollHeight).one(i.TRANSITION_END, s).emulateTransitionEnd(350) : s();
                            }
                        }
                    }),
                    (t.prototype._hide = function (t) {
                        if (!this._transitioning && e(t).hasClass(this._config.collapseInClass)) {
                            var n = this,
                                a = e(t),
                                r = e.Event(d);
                            if ((a.trigger(r), !r.isDefaultPrevented())) {
                                a.parent(this._config.parentTrigger).removeClass(this._config.activeClass),
                                    a.height(a.height())[0].offsetHeight,
                                    a.addClass(this._config.collapsingClass).removeClass(this._config.collapseClass).removeClass(this._config.collapseInClass),
                                    this.setTransitioning(!0);
                                var s = function () {
                                    n._transitioning && n._config.onTransitionEnd && n._config.onTransitionEnd(),
                                        n.setTransitioning(!1),
                                        a.trigger(u),
                                        a.removeClass(n._config.collapsingClass).addClass(n._config.collapseClass).attr("aria-expanded", !1);
                                };
                                i.supportsTransitionEnd() ? (0 == a.height() || "none" == a.css("display") ? s() : a.height(0).one(i.TRANSITION_END, s).emulateTransitionEnd(350)) : s();
                            }
                        }
                    }),
                    (t.prototype.setTransitioning = function (e) {
                        this._transitioning = e;
                    }),
                    (t.prototype.dispose = function () {
                        e.removeData(this._element, a),
                            e(this._element).find(this._config.parentTrigger).has(this._config.subMenu).children(this._config.triggerElement).off("click"),
                            (this._transitioning = null),
                            (this._config = null),
                            (this._element = null);
                    }),
                    (t.prototype._getConfig = function (t) {
                        return e.extend({}, s, t);
                    }),
                    (t._jQueryInterface = function (i) {
                        return this.each(function () {
                            var r = e(this),
                                o = r.data(a),
                                l = e.extend({}, s, r.data(), "object" === (void 0 === i ? "undefined" : n(i)) && i);
                            if ((!o && /dispose/.test(i) && this.dispose(), o || ((o = new t(this, l)), r.data(a, o)), "string" == typeof i)) {
                                if (void 0 === o[i]) throw new Error('No method named "' + i + '"');
                                o[i]();
                            }
                        });
                    }),
                    t
                );
            })();
        (e.fn[t] = h._jQueryInterface),
            (e.fn[t].Constructor = h),
            (e.fn[t].noConflict = function () {
                return (e.fn[t] = r), h._jQueryInterface;
            });
    })(jQuery);
}),
(function (e, t) {
    "object" == typeof exports && "undefined" != typeof module
        ? t(exports, require("jquery"), require("popper.js"))
        : "function" == typeof define && define.amd
        ? define(["exports", "jquery", "popper.js"], t)
        : t((e.bootstrap = {}), e.jQuery, e.Popper);
})(this, function (e, t, n) {
    "use strict";
    function i(e, t) {
        for (var n = 0; n < t.length; n++) {
            var i = t[n];
            (i.enumerable = i.enumerable || !1), (i.configurable = !0), "value" in i && (i.writable = !0), Object.defineProperty(e, i.key, i);
        }
    }
    function a(e, t, n) {
        return t && i(e.prototype, t), n && i(e, n), e;
    }
    function r(e) {
        for (var t = 1; t < arguments.length; t++) {
            var n = null != arguments[t] ? arguments[t] : {},
                i = Object.keys(n);
            "function" == typeof Object.getOwnPropertySymbols &&
                (i = i.concat(
                    Object.getOwnPropertySymbols(n).filter(function (e) {
                        return Object.getOwnPropertyDescriptor(n, e).enumerable;
                    })
                )),
                i.forEach(function (t) {
                    var i, a, r;
                    (i = e), (r = n[(a = t)]), a in i ? Object.defineProperty(i, a, { value: r, enumerable: !0, configurable: !0, writable: !0 }) : (i[a] = r);
                });
        }
        return e;
    }
    (t = t && t.hasOwnProperty("default") ? t.default : t), (n = n && n.hasOwnProperty("default") ? n.default : n);
    var s,
        o,
        l,
        d,
        u,
        c,
        h,
        f,
        p,
        m,
        g,
        _,
        y,
        v,
        b,
        w,
        M,
        x,
        k,
        D,
        L,
        S,
        T,
        Y,
        C,
        E,
        A,
        I,
        F,
        H,
        P,
        j,
        O,
        N,
        R,
        $,
        W,
        B,
        z,
        U,
        q,
        V,
        G,
        J,
        X,
        K,
        Z,
        Q,
        ee,
        te,
        ne,
        ie,
        ae,
        re,
        se,
        oe,
        le,
        de,
        ue,
        ce,
        he,
        fe,
        pe,
        me,
        ge,
        _e,
        ye,
        ve,
        be,
        we,
        Me,
        xe,
        ke,
        De,
        Le,
        Se,
        Te,
        Ye,
        Ce,
        Ee,
        Ae,
        Ie,
        Fe,
        He,
        Pe,
        je,
        Oe,
        Ne,
        Re,
        $e,
        We,
        Be,
        ze,
        Ue,
        qe,
        Ve,
        Ge,
        Je,
        Xe,
        Ke,
        Ze,
        Qe,
        et,
        tt,
        nt,
        it,
        at,
        rt,
        st,
        ot,
        lt,
        dt,
        ut,
        ct,
        ht,
        ft,
        pt,
        mt,
        gt,
        _t,
        yt,
        vt,
        bt,
        wt,
        Mt,
        xt,
        kt,
        Dt = (function (e) {
            var t = "transitionend",
                n = {
                    TRANSITION_END: "bsTransitionEnd",
                    getUID: function (e) {
                        for (; (e += ~~(1e6 * Math.random())), document.getElementById(e); );
                        return e;
                    },
                    getSelectorFromElement: function (t) {
                        var n = t.getAttribute("data-target");
                        (n && "#" !== n) || (n = t.getAttribute("href") || "");
                        try {
                            return 0 < e(document).find(n).length ? n : null;
                        } catch (t) {
                            return null;
                        }
                    },
                    getTransitionDurationFromElement: function (t) {
                        if (!t) return 0;
                        var n = e(t).css("transition-duration");
                        return parseFloat(n) ? ((n = n.split(",")[0]), 1e3 * parseFloat(n)) : 0;
                    },
                    reflow: function (e) {
                        return e.offsetHeight;
                    },
                    triggerTransitionEnd: function (n) {
                        e(n).trigger(t);
                    },
                    supportsTransitionEnd: function () {
                        return Boolean(t);
                    },
                    isElement: function (e) {
                        return (e[0] || e).nodeType;
                    },
                    typeCheckConfig: function (e, t, i) {
                        for (var a in i)
                            if (Object.prototype.hasOwnProperty.call(i, a)) {
                                var r = i[a],
                                    s = t[a],
                                    o =
                                        s && n.isElement(s)
                                            ? "element"
                                            : ((l = s),
                                              {}.toString
                                                  .call(l)
                                                  .match(/\s([a-z]+)/i)[1]
                                                  .toLowerCase());
                                if (!new RegExp(r).test(o)) throw new Error(e.toUpperCase() + ': Option "' + a + '" provided type "' + o + '" but expected type "' + r + '".');
                            }
                        var l;
                    },
                };
            return (
                (e.fn.emulateTransitionEnd = function (t) {
                    var i = this,
                        a = !1;
                    return (
                        e(this).one(n.TRANSITION_END, function () {
                            a = !0;
                        }),
                        setTimeout(function () {
                            a || n.triggerTransitionEnd(i);
                        }, t),
                        this
                    );
                }),
                (e.event.special[n.TRANSITION_END] = {
                    bindType: t,
                    delegateType: t,
                    handle: function (t) {
                        if (e(t.target).is(this)) return t.handleObj.handler.apply(this, arguments);
                    },
                }),
                n
            );
        })(t),
        Lt =
            ((o = "alert"),
            (d = "." + (l = "bs.alert")),
            (u = (s = t).fn[o]),
            (c = { CLOSE: "close" + d, CLOSED: "closed" + d, CLICK_DATA_API: "click" + d + ".data-api" }),
            (h = (function () {
                function e(e) {
                    this._element = e;
                }
                var t = e.prototype;
                return (
                    (t.close = function (e) {
                        var t = this._element;
                        e && (t = this._getRootElement(e)), this._triggerCloseEvent(t).isDefaultPrevented() || this._removeElement(t);
                    }),
                    (t.dispose = function () {
                        s.removeData(this._element, l), (this._element = null);
                    }),
                    (t._getRootElement = function (e) {
                        var t = Dt.getSelectorFromElement(e),
                            n = !1;
                        return t && (n = s(t)[0]), n || (n = s(e).closest(".alert")[0]), n;
                    }),
                    (t._triggerCloseEvent = function (e) {
                        var t = s.Event(c.CLOSE);
                        return s(e).trigger(t), t;
                    }),
                    (t._removeElement = function (e) {
                        var t = this;
                        if ((s(e).removeClass("show"), s(e).hasClass("fade"))) {
                            var n = Dt.getTransitionDurationFromElement(e);
                            s(e)
                                .one(Dt.TRANSITION_END, function (n) {
                                    return t._destroyElement(e, n);
                                })
                                .emulateTransitionEnd(n);
                        } else this._destroyElement(e);
                    }),
                    (t._destroyElement = function (e) {
                        s(e).detach().trigger(c.CLOSED).remove();
                    }),
                    (e._jQueryInterface = function (t) {
                        return this.each(function () {
                            var n = s(this),
                                i = n.data(l);
                            i || ((i = new e(this)), n.data(l, i)), "close" === t && i[t](this);
                        });
                    }),
                    (e._handleDismiss = function (e) {
                        return function (t) {
                            t && t.preventDefault(), e.close(this);
                        };
                    }),
                    a(e, null, [
                        {
                            key: "VERSION",
                            get: function () {
                                return "4.1.1";
                            },
                        },
                    ]),
                    e
                );
            })()),
            s(document).on(c.CLICK_DATA_API, '[data-dismiss="alert"]', h._handleDismiss(new h())),
            (s.fn[o] = h._jQueryInterface),
            (s.fn[o].Constructor = h),
            (s.fn[o].noConflict = function () {
                return (s.fn[o] = u), h._jQueryInterface;
            }),
            h),
        St =
            ((p = "button"),
            (g = "." + (m = "bs.button")),
            (_ = ".data-api"),
            (y = (f = t).fn[p]),
            (v = "active"),
            (b = '[data-toggle^="button"]'),
            (w = ".btn"),
            (M = { CLICK_DATA_API: "click" + g + _, FOCUS_BLUR_DATA_API: "focus" + g + _ + " blur" + g + _ }),
            (x = (function () {
                function e(e) {
                    this._element = e;
                }
                var t = e.prototype;
                return (
                    (t.toggle = function () {
                        var e = !0,
                            t = !0,
                            n = f(this._element).closest('[data-toggle="buttons"]')[0];
                        if (n) {
                            var i = f(this._element).find("input")[0];
                            if (i) {
                                if ("radio" === i.type)
                                    if (i.checked && f(this._element).hasClass(v)) e = !1;
                                    else {
                                        var a = f(n).find(".active")[0];
                                        a && f(a).removeClass(v);
                                    }
                                if (e) {
                                    if (i.hasAttribute("disabled") || n.hasAttribute("disabled") || i.classList.contains("disabled") || n.classList.contains("disabled")) return;
                                    (i.checked = !f(this._element).hasClass(v)), f(i).trigger("change");
                                }
                                i.focus(), (t = !1);
                            }
                        }
                        t && this._element.setAttribute("aria-pressed", !f(this._element).hasClass(v)), e && f(this._element).toggleClass(v);
                    }),
                    (t.dispose = function () {
                        f.removeData(this._element, m), (this._element = null);
                    }),
                    (e._jQueryInterface = function (t) {
                        return this.each(function () {
                            var n = f(this).data(m);
                            n || ((n = new e(this)), f(this).data(m, n)), "toggle" === t && n[t]();
                        });
                    }),
                    a(e, null, [
                        {
                            key: "VERSION",
                            get: function () {
                                return "4.1.1";
                            },
                        },
                    ]),
                    e
                );
            })()),
            f(document)
                .on(M.CLICK_DATA_API, b, function (e) {
                    e.preventDefault();
                    var t = e.target;
                    f(t).hasClass("btn") || (t = f(t).closest(w)), x._jQueryInterface.call(f(t), "toggle");
                })
                .on(M.FOCUS_BLUR_DATA_API, b, function (e) {
                    var t = f(e.target).closest(w)[0];
                    f(t).toggleClass("focus", /^focus(in)?$/.test(e.type));
                }),
            (f.fn[p] = x._jQueryInterface),
            (f.fn[p].Constructor = x),
            (f.fn[p].noConflict = function () {
                return (f.fn[p] = y), x._jQueryInterface;
            }),
            x),
        Tt =
            ((D = "carousel"),
            (S = "." + (L = "bs.carousel")),
            (T = ".data-api"),
            (Y = (k = t).fn[D]),
            (C = { interval: 5e3, keyboard: !0, slide: !1, pause: "hover", wrap: !0 }),
            (E = { interval: "(number|boolean)", keyboard: "boolean", slide: "(boolean|string)", pause: "(string|boolean)", wrap: "boolean" }),
            (A = "next"),
            (I = "prev"),
            (F = { SLIDE: "slide" + S, SLID: "slid" + S, KEYDOWN: "keydown" + S, MOUSEENTER: "mouseenter" + S, MOUSELEAVE: "mouseleave" + S, TOUCHEND: "touchend" + S, LOAD_DATA_API: "load" + S + T, CLICK_DATA_API: "click" + S + T }),
            (H = "active"),
            (P = {
                ACTIVE: ".active",
                ACTIVE_ITEM: ".active.carousel-item",
                ITEM: ".carousel-item",
                NEXT_PREV: ".carousel-item-next, .carousel-item-prev",
                INDICATORS: ".carousel-indicators",
                DATA_SLIDE: "[data-slide], [data-slide-to]",
                DATA_RIDE: '[data-ride="carousel"]',
            }),
            (j = (function () {
                function e(e, t) {
                    (this._items = null),
                        (this._interval = null),
                        (this._activeElement = null),
                        (this._isPaused = !1),
                        (this._isSliding = !1),
                        (this.touchTimeout = null),
                        (this._config = this._getConfig(t)),
                        (this._element = k(e)[0]),
                        (this._indicatorsElement = k(this._element).find(P.INDICATORS)[0]),
                        this._addEventListeners();
                }
                var t = e.prototype;
                return (
                    (t.next = function () {
                        this._isSliding || this._slide(A);
                    }),
                    (t.nextWhenVisible = function () {
                        !document.hidden && k(this._element).is(":visible") && "hidden" !== k(this._element).css("visibility") && this.next();
                    }),
                    (t.prev = function () {
                        this._isSliding || this._slide(I);
                    }),
                    (t.pause = function (e) {
                        e || (this._isPaused = !0), k(this._element).find(P.NEXT_PREV)[0] && (Dt.triggerTransitionEnd(this._element), this.cycle(!0)), clearInterval(this._interval), (this._interval = null);
                    }),
                    (t.cycle = function (e) {
                        e || (this._isPaused = !1),
                            this._interval && (clearInterval(this._interval), (this._interval = null)),
                            this._config.interval && !this._isPaused && (this._interval = setInterval((document.visibilityState ? this.nextWhenVisible : this.next).bind(this), this._config.interval));
                    }),
                    (t.to = function (e) {
                        var t = this;
                        this._activeElement = k(this._element).find(P.ACTIVE_ITEM)[0];
                        var n = this._getItemIndex(this._activeElement);
                        if (!(e > this._items.length - 1 || e < 0))
                            if (this._isSliding)
                                k(this._element).one(F.SLID, function () {
                                    return t.to(e);
                                });
                            else {
                                if (n === e) return this.pause(), void this.cycle();
                                var i = n < e ? A : I;
                                this._slide(i, this._items[e]);
                            }
                    }),
                    (t.dispose = function () {
                        k(this._element).off(S),
                            k.removeData(this._element, L),
                            (this._items = null),
                            (this._config = null),
                            (this._element = null),
                            (this._interval = null),
                            (this._isPaused = null),
                            (this._isSliding = null),
                            (this._activeElement = null),
                            (this._indicatorsElement = null);
                    }),
                    (t._getConfig = function (e) {
                        return (e = r({}, C, e)), Dt.typeCheckConfig(D, e, E), e;
                    }),
                    (t._addEventListeners = function () {
                        var e = this;
                        this._config.keyboard &&
                            k(this._element).on(F.KEYDOWN, function (t) {
                                return e._keydown(t);
                            }),
                            "hover" === this._config.pause &&
                                (k(this._element)
                                    .on(F.MOUSEENTER, function (t) {
                                        return e.pause(t);
                                    })
                                    .on(F.MOUSELEAVE, function (t) {
                                        return e.cycle(t);
                                    }),
                                "ontouchstart" in document.documentElement &&
                                    k(this._element).on(F.TOUCHEND, function () {
                                        e.pause(),
                                            e.touchTimeout && clearTimeout(e.touchTimeout),
                                            (e.touchTimeout = setTimeout(function (t) {
                                                return e.cycle(t);
                                            }, 500 + e._config.interval));
                                    }));
                    }),
                    (t._keydown = function (e) {
                        if (!/input|textarea/i.test(e.target.tagName))
                            switch (e.which) {
                                case 37:
                                    e.preventDefault(), this.prev();
                                    break;
                                case 39:
                                    e.preventDefault(), this.next();
                            }
                    }),
                    (t._getItemIndex = function (e) {
                        return (this._items = k.makeArray(k(e).parent().find(P.ITEM))), this._items.indexOf(e);
                    }),
                    (t._getItemByDirection = function (e, t) {
                        var n = e === A,
                            i = e === I,
                            a = this._getItemIndex(t),
                            r = this._items.length - 1;
                        if (((i && 0 === a) || (n && a === r)) && !this._config.wrap) return t;
                        var s = (a + (e === I ? -1 : 1)) % this._items.length;
                        return -1 === s ? this._items[this._items.length - 1] : this._items[s];
                    }),
                    (t._triggerSlideEvent = function (e, t) {
                        var n = this._getItemIndex(e),
                            i = this._getItemIndex(k(this._element).find(P.ACTIVE_ITEM)[0]),
                            a = k.Event(F.SLIDE, { relatedTarget: e, direction: t, from: i, to: n });
                        return k(this._element).trigger(a), a;
                    }),
                    (t._setActiveIndicatorElement = function (e) {
                        if (this._indicatorsElement) {
                            k(this._indicatorsElement).find(P.ACTIVE).removeClass(H);
                            var t = this._indicatorsElement.children[this._getItemIndex(e)];
                            t && k(t).addClass(H);
                        }
                    }),
                    (t._slide = function (e, t) {
                        var n,
                            i,
                            a,
                            r = this,
                            s = k(this._element).find(P.ACTIVE_ITEM)[0],
                            o = this._getItemIndex(s),
                            l = t || (s && this._getItemByDirection(e, s)),
                            d = this._getItemIndex(l),
                            u = Boolean(this._interval);
                        if ((e === A ? ((n = "carousel-item-left"), (i = "carousel-item-next"), (a = "left")) : ((n = "carousel-item-right"), (i = "carousel-item-prev"), (a = "right")), l && k(l).hasClass(H))) this._isSliding = !1;
                        else if (!this._triggerSlideEvent(l, a).isDefaultPrevented() && s && l) {
                            (this._isSliding = !0), u && this.pause(), this._setActiveIndicatorElement(l);
                            var c = k.Event(F.SLID, { relatedTarget: l, direction: a, from: o, to: d });
                            if (k(this._element).hasClass("slide")) {
                                k(l).addClass(i), Dt.reflow(l), k(s).addClass(n), k(l).addClass(n);
                                var h = Dt.getTransitionDurationFromElement(s);
                                k(s)
                                    .one(Dt.TRANSITION_END, function () {
                                        k(l)
                                            .removeClass(n + " " + i)
                                            .addClass(H),
                                            k(s).removeClass(H + " " + i + " " + n),
                                            (r._isSliding = !1),
                                            setTimeout(function () {
                                                return k(r._element).trigger(c);
                                            }, 0);
                                    })
                                    .emulateTransitionEnd(h);
                            } else k(s).removeClass(H), k(l).addClass(H), (this._isSliding = !1), k(this._element).trigger(c);
                            u && this.cycle();
                        }
                    }),
                    (e._jQueryInterface = function (t) {
                        return this.each(function () {
                            var n = k(this).data(L),
                                i = r({}, C, k(this).data());
                            "object" == typeof t && (i = r({}, i, t));
                            var a = "string" == typeof t ? t : i.slide;
                            if ((n || ((n = new e(this, i)), k(this).data(L, n)), "number" == typeof t)) n.to(t);
                            else if ("string" == typeof a) {
                                if (void 0 === n[a]) throw new TypeError('No method named "' + a + '"');
                                n[a]();
                            } else i.interval && (n.pause(), n.cycle());
                        });
                    }),
                    (e._dataApiClickHandler = function (t) {
                        var n = Dt.getSelectorFromElement(this);
                        if (n) {
                            var i = k(n)[0];
                            if (i && k(i).hasClass("carousel")) {
                                var a = r({}, k(i).data(), k(this).data()),
                                    s = this.getAttribute("data-slide-to");
                                s && (a.interval = !1), e._jQueryInterface.call(k(i), a), s && k(i).data(L).to(s), t.preventDefault();
                            }
                        }
                    }),
                    a(e, null, [
                        {
                            key: "VERSION",
                            get: function () {
                                return "4.1.1";
                            },
                        },
                        {
                            key: "Default",
                            get: function () {
                                return C;
                            },
                        },
                    ]),
                    e
                );
            })()),
            k(document).on(F.CLICK_DATA_API, P.DATA_SLIDE, j._dataApiClickHandler),
            k(window).on(F.LOAD_DATA_API, function () {
                k(P.DATA_RIDE).each(function () {
                    var e = k(this);
                    j._jQueryInterface.call(e, e.data());
                });
            }),
            (k.fn[D] = j._jQueryInterface),
            (k.fn[D].Constructor = j),
            (k.fn[D].noConflict = function () {
                return (k.fn[D] = Y), j._jQueryInterface;
            }),
            j),
        Yt =
            ((N = "collapse"),
            ($ = "." + (R = "bs.collapse")),
            (W = (O = t).fn[N]),
            (B = { toggle: !0, parent: "" }),
            (z = { toggle: "boolean", parent: "(string|element)" }),
            (U = { SHOW: "show" + $, SHOWN: "shown" + $, HIDE: "hide" + $, HIDDEN: "hidden" + $, CLICK_DATA_API: "click" + $ + ".data-api" }),
            (q = "show"),
            (V = "collapse"),
            (G = "collapsing"),
            (J = "collapsed"),
            (X = "width"),
            (K = { ACTIVES: ".show, .collapsing", DATA_TOGGLE: '[data-toggle="collapse"]' }),
            (Z = (function () {
                function e(e, t) {
                    (this._isTransitioning = !1),
                        (this._element = e),
                        (this._config = this._getConfig(t)),
                        (this._triggerArray = O.makeArray(O('[data-toggle="collapse"][href="#' + e.id + '"],[data-toggle="collapse"][data-target="#' + e.id + '"]')));
                    for (var n = O(K.DATA_TOGGLE), i = 0; i < n.length; i++) {
                        var a = n[i],
                            r = Dt.getSelectorFromElement(a);
                        null !== r && 0 < O(r).filter(e).length && ((this._selector = r), this._triggerArray.push(a));
                    }
                    (this._parent = this._config.parent ? this._getParent() : null), this._config.parent || this._addAriaAndCollapsedClass(this._element, this._triggerArray), this._config.toggle && this.toggle();
                }
                var t = e.prototype;
                return (
                    (t.toggle = function () {
                        O(this._element).hasClass(q) ? this.hide() : this.show();
                    }),
                    (t.show = function () {
                        var t,
                            n,
                            i = this;
                        if (
                            !(
                                this._isTransitioning ||
                                O(this._element).hasClass(q) ||
                                (this._parent &&
                                    0 ===
                                        (t = O.makeArray(
                                            O(this._parent)
                                                .find(K.ACTIVES)
                                                .filter('[data-parent="' + this._config.parent + '"]')
                                        )).length &&
                                    (t = null),
                                t && (n = O(t).not(this._selector).data(R)) && n._isTransitioning)
                            )
                        ) {
                            var a = O.Event(U.SHOW);
                            if ((O(this._element).trigger(a), !a.isDefaultPrevented())) {
                                t && (e._jQueryInterface.call(O(t).not(this._selector), "hide"), n || O(t).data(R, null));
                                var r = this._getDimension();
                                O(this._element).removeClass(V).addClass(G), (this._element.style[r] = 0) < this._triggerArray.length && O(this._triggerArray).removeClass(J).attr("aria-expanded", !0), this.setTransitioning(!0);
                                var s = "scroll" + (r[0].toUpperCase() + r.slice(1)),
                                    o = Dt.getTransitionDurationFromElement(this._element);
                                O(this._element)
                                    .one(Dt.TRANSITION_END, function () {
                                        O(i._element).removeClass(G).addClass(V).addClass(q), (i._element.style[r] = ""), i.setTransitioning(!1), O(i._element).trigger(U.SHOWN);
                                    })
                                    .emulateTransitionEnd(o),
                                    (this._element.style[r] = this._element[s] + "px");
                            }
                        }
                    }),
                    (t.hide = function () {
                        var e = this;
                        if (!this._isTransitioning && O(this._element).hasClass(q)) {
                            var t = O.Event(U.HIDE);
                            if ((O(this._element).trigger(t), !t.isDefaultPrevented())) {
                                var n = this._getDimension();
                                if (((this._element.style[n] = this._element.getBoundingClientRect()[n] + "px"), Dt.reflow(this._element), O(this._element).addClass(G).removeClass(V).removeClass(q), 0 < this._triggerArray.length))
                                    for (var i = 0; i < this._triggerArray.length; i++) {
                                        var a = this._triggerArray[i],
                                            r = Dt.getSelectorFromElement(a);
                                        null !== r && (O(r).hasClass(q) || O(a).addClass(J).attr("aria-expanded", !1));
                                    }
                                this.setTransitioning(!0), (this._element.style[n] = "");
                                var s = Dt.getTransitionDurationFromElement(this._element);
                                O(this._element)
                                    .one(Dt.TRANSITION_END, function () {
                                        e.setTransitioning(!1), O(e._element).removeClass(G).addClass(V).trigger(U.HIDDEN);
                                    })
                                    .emulateTransitionEnd(s);
                            }
                        }
                    }),
                    (t.setTransitioning = function (e) {
                        this._isTransitioning = e;
                    }),
                    (t.dispose = function () {
                        O.removeData(this._element, R), (this._config = null), (this._parent = null), (this._element = null), (this._triggerArray = null), (this._isTransitioning = null);
                    }),
                    (t._getConfig = function (e) {
                        return ((e = r({}, B, e)).toggle = Boolean(e.toggle)), Dt.typeCheckConfig(N, e, z), e;
                    }),
                    (t._getDimension = function () {
                        return O(this._element).hasClass(X) ? X : "height";
                    }),
                    (t._getParent = function () {
                        var t = this,
                            n = null;
                        Dt.isElement(this._config.parent) ? ((n = this._config.parent), void 0 !== this._config.parent.jquery && (n = this._config.parent[0])) : (n = O(this._config.parent)[0]);
                        var i = '[data-toggle="collapse"][data-parent="' + this._config.parent + '"]';
                        return (
                            O(n)
                                .find(i)
                                .each(function (n, i) {
                                    t._addAriaAndCollapsedClass(e._getTargetFromElement(i), [i]);
                                }),
                            n
                        );
                    }),
                    (t._addAriaAndCollapsedClass = function (e, t) {
                        if (e) {
                            var n = O(e).hasClass(q);
                            0 < t.length && O(t).toggleClass(J, !n).attr("aria-expanded", n);
                        }
                    }),
                    (e._getTargetFromElement = function (e) {
                        var t = Dt.getSelectorFromElement(e);
                        return t ? O(t)[0] : null;
                    }),
                    (e._jQueryInterface = function (t) {
                        return this.each(function () {
                            var n = O(this),
                                i = n.data(R),
                                a = r({}, B, n.data(), "object" == typeof t && t ? t : {});
                            if ((!i && a.toggle && /show|hide/.test(t) && (a.toggle = !1), i || ((i = new e(this, a)), n.data(R, i)), "string" == typeof t)) {
                                if (void 0 === i[t]) throw new TypeError('No method named "' + t + '"');
                                i[t]();
                            }
                        });
                    }),
                    a(e, null, [
                        {
                            key: "VERSION",
                            get: function () {
                                return "4.1.1";
                            },
                        },
                        {
                            key: "Default",
                            get: function () {
                                return B;
                            },
                        },
                    ]),
                    e
                );
            })()),
            O(document).on(U.CLICK_DATA_API, K.DATA_TOGGLE, function (e) {
                "A" === e.currentTarget.tagName && e.preventDefault();
                var t = O(this),
                    n = Dt.getSelectorFromElement(this);
                O(n).each(function () {
                    var e = O(this),
                        n = e.data(R) ? "toggle" : t.data();
                    Z._jQueryInterface.call(e, n);
                });
            }),
            (O.fn[N] = Z._jQueryInterface),
            (O.fn[N].Constructor = Z),
            (O.fn[N].noConflict = function () {
                return (O.fn[N] = W), Z._jQueryInterface;
            }),
            Z),
        Ct =
            ((ee = "dropdown"),
            (ne = "." + (te = "bs.dropdown")),
            (ie = ".data-api"),
            (ae = (Q = t).fn[ee]),
            (re = new RegExp("38|40|27")),
            (se = { HIDE: "hide" + ne, HIDDEN: "hidden" + ne, SHOW: "show" + ne, SHOWN: "shown" + ne, CLICK: "click" + ne, CLICK_DATA_API: "click" + ne + ie, KEYDOWN_DATA_API: "keydown" + ne + ie, KEYUP_DATA_API: "keyup" + ne + ie }),
            (oe = "disabled"),
            (le = "show"),
            (de = "dropdown-menu-right"),
            (ue = '[data-toggle="dropdown"]'),
            (ce = ".dropdown-menu"),
            (he = { offset: 0, flip: !0, boundary: "scrollParent", reference: "toggle", display: "dynamic" }),
            (fe = { offset: "(number|string|function)", flip: "boolean", boundary: "(string|element)", reference: "(string|element)", display: "string" }),
            (pe = (function () {
                function e(e, t) {
                    (this._element = e), (this._popper = null), (this._config = this._getConfig(t)), (this._menu = this._getMenuElement()), (this._inNavbar = this._detectNavbar()), this._addEventListeners();
                }
                var t = e.prototype;
                return (
                    (t.toggle = function () {
                        if (!this._element.disabled && !Q(this._element).hasClass(oe)) {
                            var t = e._getParentFromElement(this._element),
                                i = Q(this._menu).hasClass(le);
                            if ((e._clearMenus(), !i)) {
                                var a = { relatedTarget: this._element },
                                    r = Q.Event(se.SHOW, a);
                                if ((Q(t).trigger(r), !r.isDefaultPrevented())) {
                                    if (!this._inNavbar) {
                                        if (void 0 === n) throw new TypeError("Bootstrap dropdown require Popper.js (https://popper.js.org)");
                                        var s = this._element;
                                        "parent" === this._config.reference ? (s = t) : Dt.isElement(this._config.reference) && ((s = this._config.reference), void 0 !== this._config.reference.jquery && (s = this._config.reference[0])),
                                            "scrollParent" !== this._config.boundary && Q(t).addClass("position-static"),
                                            (this._popper = new n(s, this._menu, this._getPopperConfig()));
                                    }
                                    "ontouchstart" in document.documentElement && 0 === Q(t).closest(".navbar-nav").length && Q(document.body).children().on("mouseover", null, Q.noop),
                                        this._element.focus(),
                                        this._element.setAttribute("aria-expanded", !0),
                                        Q(this._menu).toggleClass(le),
                                        Q(t).toggleClass(le).trigger(Q.Event(se.SHOWN, a));
                                }
                            }
                        }
                    }),
                    (t.dispose = function () {
                        Q.removeData(this._element, te), Q(this._element).off(ne), (this._element = null), (this._menu = null) !== this._popper && (this._popper.destroy(), (this._popper = null));
                    }),
                    (t.update = function () {
                        (this._inNavbar = this._detectNavbar()), null !== this._popper && this._popper.scheduleUpdate();
                    }),
                    (t._addEventListeners = function () {
                        var e = this;
                        Q(this._element).on(se.CLICK, function (t) {
                            t.preventDefault(), t.stopPropagation(), e.toggle();
                        });
                    }),
                    (t._getConfig = function (e) {
                        return (e = r({}, this.constructor.Default, Q(this._element).data(), e)), Dt.typeCheckConfig(ee, e, this.constructor.DefaultType), e;
                    }),
                    (t._getMenuElement = function () {
                        if (!this._menu) {
                            var t = e._getParentFromElement(this._element);
                            this._menu = Q(t).find(ce)[0];
                        }
                        return this._menu;
                    }),
                    (t._getPlacement = function () {
                        var e = Q(this._element).parent(),
                            t = "bottom-start";
                        return (
                            e.hasClass("dropup")
                                ? ((t = "top-start"), Q(this._menu).hasClass(de) && (t = "top-end"))
                                : e.hasClass("dropright")
                                ? (t = "right-start")
                                : e.hasClass("dropleft")
                                ? (t = "left-start")
                                : Q(this._menu).hasClass(de) && (t = "bottom-end"),
                            t
                        );
                    }),
                    (t._detectNavbar = function () {
                        return 0 < Q(this._element).closest(".navbar").length;
                    }),
                    (t._getPopperConfig = function () {
                        var e = this,
                            t = {};
                        "function" == typeof this._config.offset
                            ? (t.fn = function (t) {
                                  return (t.offsets = r({}, t.offsets, e._config.offset(t.offsets) || {})), t;
                              })
                            : (t.offset = this._config.offset);
                        var n = { placement: this._getPlacement(), modifiers: { offset: t, flip: { enabled: this._config.flip }, preventOverflow: { boundariesElement: this._config.boundary } } };
                        return "static" === this._config.display && (n.modifiers.applyStyle = { enabled: !1 }), n;
                    }),
                    (e._jQueryInterface = function (t) {
                        return this.each(function () {
                            var n = Q(this).data(te);
                            if ((n || ((n = new e(this, "object" == typeof t ? t : null)), Q(this).data(te, n)), "string" == typeof t)) {
                                if (void 0 === n[t]) throw new TypeError('No method named "' + t + '"');
                                n[t]();
                            }
                        });
                    }),
                    (e._clearMenus = function (t) {
                        if (!t || (3 !== t.which && ("keyup" !== t.type || 9 === t.which)))
                            for (var n = Q.makeArray(Q(ue)), i = 0; i < n.length; i++) {
                                var a = e._getParentFromElement(n[i]),
                                    r = Q(n[i]).data(te),
                                    s = { relatedTarget: n[i] };
                                if (r) {
                                    var o = r._menu;
                                    if (Q(a).hasClass(le) && !(t && (("click" === t.type && /input|textarea/i.test(t.target.tagName)) || ("keyup" === t.type && 9 === t.which)) && Q.contains(a, t.target))) {
                                        var l = Q.Event(se.HIDE, s);
                                        Q(a).trigger(l),
                                            l.isDefaultPrevented() ||
                                                ("ontouchstart" in document.documentElement && Q(document.body).children().off("mouseover", null, Q.noop),
                                                n[i].setAttribute("aria-expanded", "false"),
                                                Q(o).removeClass(le),
                                                Q(a).removeClass(le).trigger(Q.Event(se.HIDDEN, s)));
                                    }
                                }
                            }
                    }),
                    (e._getParentFromElement = function (e) {
                        var t,
                            n = Dt.getSelectorFromElement(e);
                        return n && (t = Q(n)[0]), t || e.parentNode;
                    }),
                    (e._dataApiKeydownHandler = function (t) {
                        if (
                            (/input|textarea/i.test(t.target.tagName) ? !(32 === t.which || (27 !== t.which && ((40 !== t.which && 38 !== t.which) || Q(t.target).closest(ce).length))) : re.test(t.which)) &&
                            (t.preventDefault(), t.stopPropagation(), !this.disabled && !Q(this).hasClass(oe))
                        ) {
                            var n = e._getParentFromElement(this),
                                i = Q(n).hasClass(le);
                            if ((i || (27 === t.which && 32 === t.which)) && (!i || (27 !== t.which && 32 !== t.which))) {
                                var a = Q(n).find(".dropdown-menu .dropdown-item:not(.disabled):not(:disabled)").get();
                                if (0 !== a.length) {
                                    var r = a.indexOf(t.target);
                                    38 === t.which && 0 < r && r--, 40 === t.which && r < a.length - 1 && r++, r < 0 && (r = 0), a[r].focus();
                                }
                            } else {
                                if (27 === t.which) {
                                    var s = Q(n).find(ue)[0];
                                    Q(s).trigger("focus");
                                }
                                Q(this).trigger("click");
                            }
                        }
                    }),
                    a(e, null, [
                        {
                            key: "VERSION",
                            get: function () {
                                return "4.1.1";
                            },
                        },
                        {
                            key: "Default",
                            get: function () {
                                return he;
                            },
                        },
                        {
                            key: "DefaultType",
                            get: function () {
                                return fe;
                            },
                        },
                    ]),
                    e
                );
            })()),
            Q(document)
                .on(se.KEYDOWN_DATA_API, ue, pe._dataApiKeydownHandler)
                .on(se.KEYDOWN_DATA_API, ce, pe._dataApiKeydownHandler)
                .on(se.CLICK_DATA_API + " " + se.KEYUP_DATA_API, pe._clearMenus)
                .on(se.CLICK_DATA_API, ue, function (e) {
                    e.preventDefault(), e.stopPropagation(), pe._jQueryInterface.call(Q(this), "toggle");
                })
                .on(se.CLICK_DATA_API, ".dropdown form", function (e) {
                    e.stopPropagation();
                }),
            (Q.fn[ee] = pe._jQueryInterface),
            (Q.fn[ee].Constructor = pe),
            (Q.fn[ee].noConflict = function () {
                return (Q.fn[ee] = ae), pe._jQueryInterface;
            }),
            pe),
        Et =
            ((ge = "modal"),
            (ye = "." + (_e = "bs.modal")),
            (ve = (me = t).fn[ge]),
            (be = { backdrop: !0, keyboard: !0, focus: !0, show: !0 }),
            (we = { backdrop: "(boolean|string)", keyboard: "boolean", focus: "boolean", show: "boolean" }),
            (Me = {
                HIDE: "hide" + ye,
                HIDDEN: "hidden" + ye,
                SHOW: "show" + ye,
                SHOWN: "shown" + ye,
                FOCUSIN: "focusin" + ye,
                RESIZE: "resize" + ye,
                CLICK_DISMISS: "click.dismiss" + ye,
                KEYDOWN_DISMISS: "keydown.dismiss" + ye,
                MOUSEUP_DISMISS: "mouseup.dismiss" + ye,
                MOUSEDOWN_DISMISS: "mousedown.dismiss" + ye,
                CLICK_DATA_API: "click" + ye + ".data-api",
            }),
            (xe = "modal-open"),
            (ke = "fade"),
            (De = "show"),
            (Le = {
                DIALOG: ".modal-dialog",
                DATA_TOGGLE: '[data-toggle="modal"]',
                DATA_DISMISS: '[data-dismiss="modal"]',
                FIXED_CONTENT: ".fixed-top, .fixed-bottom, .is-fixed, .sticky-top",
                STICKY_CONTENT: ".sticky-top",
                NAVBAR_TOGGLER: ".navbar-toggler",
            }),
            (Se = (function () {
                function e(e, t) {
                    (this._config = this._getConfig(t)),
                        (this._element = e),
                        (this._dialog = me(e).find(Le.DIALOG)[0]),
                        (this._backdrop = null),
                        (this._isShown = !1),
                        (this._isBodyOverflowing = !1),
                        (this._ignoreBackdropClick = !1),
                        (this._scrollbarWidth = 0);
                }
                var t = e.prototype;
                return (
                    (t.toggle = function (e) {
                        return this._isShown ? this.hide() : this.show(e);
                    }),
                    (t.show = function (e) {
                        var t = this;
                        if (!this._isTransitioning && !this._isShown) {
                            me(this._element).hasClass(ke) && (this._isTransitioning = !0);
                            var n = me.Event(Me.SHOW, { relatedTarget: e });
                            me(this._element).trigger(n),
                                this._isShown ||
                                    n.isDefaultPrevented() ||
                                    ((this._isShown = !0),
                                    this._checkScrollbar(),
                                    this._setScrollbar(),
                                    this._adjustDialog(),
                                    me(document.body).addClass(xe),
                                    this._setEscapeEvent(),
                                    this._setResizeEvent(),
                                    me(this._element).on(Me.CLICK_DISMISS, Le.DATA_DISMISS, function (e) {
                                        return t.hide(e);
                                    }),
                                    me(this._dialog).on(Me.MOUSEDOWN_DISMISS, function () {
                                        me(t._element).one(Me.MOUSEUP_DISMISS, function (e) {
                                            me(e.target).is(t._element) && (t._ignoreBackdropClick = !0);
                                        });
                                    }),
                                    this._showBackdrop(function () {
                                        return t._showElement(e);
                                    }));
                        }
                    }),
                    (t.hide = function (e) {
                        var t = this;
                        if ((e && e.preventDefault(), !this._isTransitioning && this._isShown)) {
                            var n = me.Event(Me.HIDE);
                            if ((me(this._element).trigger(n), this._isShown && !n.isDefaultPrevented())) {
                                this._isShown = !1;
                                var i = me(this._element).hasClass(ke);
                                if (
                                    (i && (this._isTransitioning = !0),
                                    this._setEscapeEvent(),
                                    this._setResizeEvent(),
                                    me(document).off(Me.FOCUSIN),
                                    me(this._element).removeClass(De),
                                    me(this._element).off(Me.CLICK_DISMISS),
                                    me(this._dialog).off(Me.MOUSEDOWN_DISMISS),
                                    i)
                                ) {
                                    var a = Dt.getTransitionDurationFromElement(this._element);
                                    me(this._element)
                                        .one(Dt.TRANSITION_END, function (e) {
                                            return t._hideModal(e);
                                        })
                                        .emulateTransitionEnd(a);
                                } else this._hideModal();
                            }
                        }
                    }),
                    (t.dispose = function () {
                        me.removeData(this._element, _e),
                            me(window, document, this._element, this._backdrop).off(ye),
                            (this._config = null),
                            (this._element = null),
                            (this._dialog = null),
                            (this._backdrop = null),
                            (this._isShown = null),
                            (this._isBodyOverflowing = null),
                            (this._ignoreBackdropClick = null),
                            (this._scrollbarWidth = null);
                    }),
                    (t.handleUpdate = function () {
                        this._adjustDialog();
                    }),
                    (t._getConfig = function (e) {
                        return (e = r({}, be, e)), Dt.typeCheckConfig(ge, e, we), e;
                    }),
                    (t._showElement = function (e) {
                        var t = this,
                            n = me(this._element).hasClass(ke);
                        (this._element.parentNode && this._element.parentNode.nodeType === Node.ELEMENT_NODE) || document.body.appendChild(this._element),
                            (this._element.style.display = "block"),
                            this._element.removeAttribute("aria-hidden"),
                            (this._element.scrollTop = 0),
                            n && Dt.reflow(this._element),
                            me(this._element).addClass(De),
                            this._config.focus && this._enforceFocus();
                        var i = me.Event(Me.SHOWN, { relatedTarget: e }),
                            a = function () {
                                t._config.focus && t._element.focus(), (t._isTransitioning = !1), me(t._element).trigger(i);
                            };
                        if (n) {
                            var r = Dt.getTransitionDurationFromElement(this._element);
                            me(this._dialog).one(Dt.TRANSITION_END, a).emulateTransitionEnd(r);
                        } else a();
                    }),
                    (t._enforceFocus = function () {
                        var e = this;
                        me(document)
                            .off(Me.FOCUSIN)
                            .on(Me.FOCUSIN, function (t) {
                                document !== t.target && e._element !== t.target && 0 === me(e._element).has(t.target).length && e._element.focus();
                            });
                    }),
                    (t._setEscapeEvent = function () {
                        var e = this;
                        this._isShown && this._config.keyboard
                            ? me(this._element).on(Me.KEYDOWN_DISMISS, function (t) {
                                  27 === t.which && (t.preventDefault(), e.hide());
                              })
                            : this._isShown || me(this._element).off(Me.KEYDOWN_DISMISS);
                    }),
                    (t._setResizeEvent = function () {
                        var e = this;
                        this._isShown
                            ? me(window).on(Me.RESIZE, function (t) {
                                  return e.handleUpdate(t);
                              })
                            : me(window).off(Me.RESIZE);
                    }),
                    (t._hideModal = function () {
                        var e = this;
                        (this._element.style.display = "none"),
                            this._element.setAttribute("aria-hidden", !0),
                            (this._isTransitioning = !1),
                            this._showBackdrop(function () {
                                me(document.body).removeClass(xe), e._resetAdjustments(), e._resetScrollbar(), me(e._element).trigger(Me.HIDDEN);
                            });
                    }),
                    (t._removeBackdrop = function () {
                        this._backdrop && (me(this._backdrop).remove(), (this._backdrop = null));
                    }),
                    (t._showBackdrop = function (e) {
                        var t = this,
                            n = me(this._element).hasClass(ke) ? ke : "";
                        if (this._isShown && this._config.backdrop) {
                            if (
                                ((this._backdrop = document.createElement("div")),
                                (this._backdrop.className = "modal-backdrop"),
                                n && me(this._backdrop).addClass(n),
                                me(this._backdrop).appendTo(document.body),
                                me(this._element).on(Me.CLICK_DISMISS, function (e) {
                                    t._ignoreBackdropClick ? (t._ignoreBackdropClick = !1) : e.target === e.currentTarget && ("static" === t._config.backdrop ? t._element.focus() : t.hide());
                                }),
                                n && Dt.reflow(this._backdrop),
                                me(this._backdrop).addClass(De),
                                !e)
                            )
                                return;
                            if (!n) return void e();
                            var i = Dt.getTransitionDurationFromElement(this._backdrop);
                            me(this._backdrop).one(Dt.TRANSITION_END, e).emulateTransitionEnd(i);
                        } else if (!this._isShown && this._backdrop) {
                            me(this._backdrop).removeClass(De);
                            var a = function () {
                                t._removeBackdrop(), e && e();
                            };
                            if (me(this._element).hasClass(ke)) {
                                var r = Dt.getTransitionDurationFromElement(this._backdrop);
                                me(this._backdrop).one(Dt.TRANSITION_END, a).emulateTransitionEnd(r);
                            } else a();
                        } else e && e();
                    }),
                    (t._adjustDialog = function () {
                        var e = this._element.scrollHeight > document.documentElement.clientHeight;
                        !this._isBodyOverflowing && e && (this._element.style.paddingLeft = this._scrollbarWidth + "px"), this._isBodyOverflowing && !e && (this._element.style.paddingRight = this._scrollbarWidth + "px");
                    }),
                    (t._resetAdjustments = function () {
                        (this._element.style.paddingLeft = ""), (this._element.style.paddingRight = "");
                    }),
                    (t._checkScrollbar = function () {
                        var e = document.body.getBoundingClientRect();
                        (this._isBodyOverflowing = e.left + e.right < window.innerWidth), (this._scrollbarWidth = this._getScrollbarWidth());
                    }),
                    (t._setScrollbar = function () {
                        var e = this;
                        if (this._isBodyOverflowing) {
                            me(Le.FIXED_CONTENT).each(function (t, n) {
                                var i = me(n)[0].style.paddingRight,
                                    a = me(n).css("padding-right");
                                me(n)
                                    .data("padding-right", i)
                                    .css("padding-right", parseFloat(a) + e._scrollbarWidth + "px");
                            }),
                                me(Le.STICKY_CONTENT).each(function (t, n) {
                                    var i = me(n)[0].style.marginRight,
                                        a = me(n).css("margin-right");
                                    me(n)
                                        .data("margin-right", i)
                                        .css("margin-right", parseFloat(a) - e._scrollbarWidth + "px");
                                }),
                                me(Le.NAVBAR_TOGGLER).each(function (t, n) {
                                    var i = me(n)[0].style.marginRight,
                                        a = me(n).css("margin-right");
                                    me(n)
                                        .data("margin-right", i)
                                        .css("margin-right", parseFloat(a) + e._scrollbarWidth + "px");
                                });
                            var t = document.body.style.paddingRight,
                                n = me(document.body).css("padding-right");
                            me(document.body)
                                .data("padding-right", t)
                                .css("padding-right", parseFloat(n) + this._scrollbarWidth + "px");
                        }
                    }),
                    (t._resetScrollbar = function () {
                        me(Le.FIXED_CONTENT).each(function (e, t) {
                            var n = me(t).data("padding-right");
                            void 0 !== n && me(t).css("padding-right", n).removeData("padding-right");
                        }),
                            me(Le.STICKY_CONTENT + ", " + Le.NAVBAR_TOGGLER).each(function (e, t) {
                                var n = me(t).data("margin-right");
                                void 0 !== n && me(t).css("margin-right", n).removeData("margin-right");
                            });
                        var e = me(document.body).data("padding-right");
                        void 0 !== e && me(document.body).css("padding-right", e).removeData("padding-right");
                    }),
                    (t._getScrollbarWidth = function () {
                        var e = document.createElement("div");
                        (e.className = "modal-scrollbar-measure"), document.body.appendChild(e);
                        var t = e.getBoundingClientRect().width - e.clientWidth;
                        return document.body.removeChild(e), t;
                    }),
                    (e._jQueryInterface = function (t, n) {
                        return this.each(function () {
                            var i = me(this).data(_e),
                                a = r({}, be, me(this).data(), "object" == typeof t && t ? t : {});
                            if ((i || ((i = new e(this, a)), me(this).data(_e, i)), "string" == typeof t)) {
                                if (void 0 === i[t]) throw new TypeError('No method named "' + t + '"');
                                i[t](n);
                            } else a.show && i.show(n);
                        });
                    }),
                    a(e, null, [
                        {
                            key: "VERSION",
                            get: function () {
                                return "4.1.1";
                            },
                        },
                        {
                            key: "Default",
                            get: function () {
                                return be;
                            },
                        },
                    ]),
                    e
                );
            })()),
            me(document).on(Me.CLICK_DATA_API, Le.DATA_TOGGLE, function (e) {
                var t,
                    n = this,
                    i = Dt.getSelectorFromElement(this);
                i && (t = me(i)[0]);
                var a = me(t).data(_e) ? "toggle" : r({}, me(t).data(), me(this).data());
                ("A" !== this.tagName && "AREA" !== this.tagName) || e.preventDefault();
                var s = me(t).one(Me.SHOW, function (e) {
                    e.isDefaultPrevented() ||
                        s.one(Me.HIDDEN, function () {
                            me(n).is(":visible") && n.focus();
                        });
                });
                Se._jQueryInterface.call(me(t), a, this);
            }),
            (me.fn[ge] = Se._jQueryInterface),
            (me.fn[ge].Constructor = Se),
            (me.fn[ge].noConflict = function () {
                return (me.fn[ge] = ve), Se._jQueryInterface;
            }),
            Se),
        At =
            ((Ye = "tooltip"),
            (Ee = "." + (Ce = "bs.tooltip")),
            (Ae = (Te = t).fn[Ye]),
            (Ie = "bs-tooltip"),
            (Fe = new RegExp("(^|\\s)" + Ie + "\\S+", "g")),
            (je = {
                animation: !0,
                template: '<div class="tooltip" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
                trigger: "hover focus",
                title: "",
                delay: 0,
                html: !(Pe = { AUTO: "auto", TOP: "top", RIGHT: "right", BOTTOM: "bottom", LEFT: "left" }),
                selector: !(He = {
                    animation: "boolean",
                    template: "string",
                    title: "(string|element|function)",
                    trigger: "string",
                    delay: "(number|object)",
                    html: "boolean",
                    selector: "(string|boolean)",
                    placement: "(string|function)",
                    offset: "(number|string)",
                    container: "(string|element|boolean)",
                    fallbackPlacement: "(string|array)",
                    boundary: "(string|element)",
                }),
                placement: "top",
                offset: 0,
                container: !1,
                fallbackPlacement: "flip",
                boundary: "scrollParent",
            }),
            (Ne = "out"),
            (Re = {
                HIDE: "hide" + Ee,
                HIDDEN: "hidden" + Ee,
                SHOW: (Oe = "show") + Ee,
                SHOWN: "shown" + Ee,
                INSERTED: "inserted" + Ee,
                CLICK: "click" + Ee,
                FOCUSIN: "focusin" + Ee,
                FOCUSOUT: "focusout" + Ee,
                MOUSEENTER: "mouseenter" + Ee,
                MOUSELEAVE: "mouseleave" + Ee,
            }),
            ($e = "fade"),
            (We = "show"),
            (Be = "hover"),
            (ze = "focus"),
            (Ue = (function () {
                function e(e, t) {
                    if (void 0 === n) throw new TypeError("Bootstrap tooltips require Popper.js (https://popper.js.org)");
                    (this._isEnabled = !0),
                        (this._timeout = 0),
                        (this._hoverState = ""),
                        (this._activeTrigger = {}),
                        (this._popper = null),
                        (this.element = e),
                        (this.config = this._getConfig(t)),
                        (this.tip = null),
                        this._setListeners();
                }
                var t = e.prototype;
                return (
                    (t.enable = function () {
                        this._isEnabled = !0;
                    }),
                    (t.disable = function () {
                        this._isEnabled = !1;
                    }),
                    (t.toggleEnabled = function () {
                        this._isEnabled = !this._isEnabled;
                    }),
                    (t.toggle = function (e) {
                        if (this._isEnabled)
                            if (e) {
                                var t = this.constructor.DATA_KEY,
                                    n = Te(e.currentTarget).data(t);
                                n || ((n = new this.constructor(e.currentTarget, this._getDelegateConfig())), Te(e.currentTarget).data(t, n)),
                                    (n._activeTrigger.click = !n._activeTrigger.click),
                                    n._isWithActiveTrigger() ? n._enter(null, n) : n._leave(null, n);
                            } else {
                                if (Te(this.getTipElement()).hasClass(We)) return void this._leave(null, this);
                                this._enter(null, this);
                            }
                    }),
                    (t.dispose = function () {
                        clearTimeout(this._timeout),
                            Te.removeData(this.element, this.constructor.DATA_KEY),
                            Te(this.element).off(this.constructor.EVENT_KEY),
                            Te(this.element).closest(".modal").off("hide.bs.modal"),
                            this.tip && Te(this.tip).remove(),
                            (this._isEnabled = null),
                            (this._timeout = null),
                            (this._hoverState = null),
                            (this._activeTrigger = null) !== this._popper && this._popper.destroy(),
                            (this._popper = null),
                            (this.element = null),
                            (this.config = null),
                            (this.tip = null);
                    }),
                    (t.show = function () {
                        var e = this;
                        if ("none" === Te(this.element).css("display")) throw new Error("Please use show on visible elements");
                        var t = Te.Event(this.constructor.Event.SHOW);
                        if (this.isWithContent() && this._isEnabled) {
                            Te(this.element).trigger(t);
                            var i = Te.contains(this.element.ownerDocument.documentElement, this.element);
                            if (t.isDefaultPrevented() || !i) return;
                            var a = this.getTipElement(),
                                r = Dt.getUID(this.constructor.NAME);
                            a.setAttribute("id", r), this.element.setAttribute("aria-describedby", r), this.setContent(), this.config.animation && Te(a).addClass($e);
                            var s = "function" == typeof this.config.placement ? this.config.placement.call(this, a, this.element) : this.config.placement,
                                o = this._getAttachment(s);
                            this.addAttachmentClass(o);
                            var l = !1 === this.config.container ? document.body : Te(this.config.container);
                            Te(a).data(this.constructor.DATA_KEY, this),
                                Te.contains(this.element.ownerDocument.documentElement, this.tip) || Te(a).appendTo(l),
                                Te(this.element).trigger(this.constructor.Event.INSERTED),
                                (this._popper = new n(this.element, a, {
                                    placement: o,
                                    modifiers: { offset: { offset: this.config.offset }, flip: { behavior: this.config.fallbackPlacement }, arrow: { element: ".arrow" }, preventOverflow: { boundariesElement: this.config.boundary } },
                                    onCreate: function (t) {
                                        t.originalPlacement !== t.placement && e._handlePopperPlacementChange(t);
                                    },
                                    onUpdate: function (t) {
                                        e._handlePopperPlacementChange(t);
                                    },
                                })),
                                Te(a).addClass(We),
                                "ontouchstart" in document.documentElement && Te(document.body).children().on("mouseover", null, Te.noop);
                            var d = function () {
                                e.config.animation && e._fixTransition();
                                var t = e._hoverState;
                                (e._hoverState = null), Te(e.element).trigger(e.constructor.Event.SHOWN), t === Ne && e._leave(null, e);
                            };
                            if (Te(this.tip).hasClass($e)) {
                                var u = Dt.getTransitionDurationFromElement(this.tip);
                                Te(this.tip).one(Dt.TRANSITION_END, d).emulateTransitionEnd(u);
                            } else d();
                        }
                    }),
                    (t.hide = function (e) {
                        var t = this,
                            n = this.getTipElement(),
                            i = Te.Event(this.constructor.Event.HIDE),
                            a = function () {
                                t._hoverState !== Oe && n.parentNode && n.parentNode.removeChild(n),
                                    t._cleanTipClass(),
                                    t.element.removeAttribute("aria-describedby"),
                                    Te(t.element).trigger(t.constructor.Event.HIDDEN),
                                    null !== t._popper && t._popper.destroy(),
                                    e && e();
                            };
                        if ((Te(this.element).trigger(i), !i.isDefaultPrevented())) {
                            if (
                                (Te(n).removeClass(We),
                                "ontouchstart" in document.documentElement && Te(document.body).children().off("mouseover", null, Te.noop),
                                (this._activeTrigger.click = !1),
                                (this._activeTrigger[ze] = !1),
                                (this._activeTrigger[Be] = !1),
                                Te(this.tip).hasClass($e))
                            ) {
                                var r = Dt.getTransitionDurationFromElement(n);
                                Te(n).one(Dt.TRANSITION_END, a).emulateTransitionEnd(r);
                            } else a();
                            this._hoverState = "";
                        }
                    }),
                    (t.update = function () {
                        null !== this._popper && this._popper.scheduleUpdate();
                    }),
                    (t.isWithContent = function () {
                        return Boolean(this.getTitle());
                    }),
                    (t.addAttachmentClass = function (e) {
                        Te(this.getTipElement()).addClass(Ie + "-" + e);
                    }),
                    (t.getTipElement = function () {
                        return (this.tip = this.tip || Te(this.config.template)[0]), this.tip;
                    }),
                    (t.setContent = function () {
                        var e = Te(this.getTipElement());
                        this.setElementContent(e.find(".tooltip-inner"), this.getTitle()), e.removeClass($e + " " + We);
                    }),
                    (t.setElementContent = function (e, t) {
                        var n = this.config.html;
                        "object" == typeof t && (t.nodeType || t.jquery) ? (n ? Te(t).parent().is(e) || e.empty().append(t) : e.text(Te(t).text())) : e[n ? "html" : "text"](t);
                    }),
                    (t.getTitle = function () {
                        var e = this.element.getAttribute("data-original-title");
                        return e || (e = "function" == typeof this.config.title ? this.config.title.call(this.element) : this.config.title), e;
                    }),
                    (t._getAttachment = function (e) {
                        return Pe[e.toUpperCase()];
                    }),
                    (t._setListeners = function () {
                        var e = this;
                        this.config.trigger.split(" ").forEach(function (t) {
                            if ("click" === t)
                                Te(e.element).on(e.constructor.Event.CLICK, e.config.selector, function (t) {
                                    return e.toggle(t);
                                });
                            else if ("manual" !== t) {
                                var n = t === Be ? e.constructor.Event.MOUSEENTER : e.constructor.Event.FOCUSIN,
                                    i = t === Be ? e.constructor.Event.MOUSELEAVE : e.constructor.Event.FOCUSOUT;
                                Te(e.element)
                                    .on(n, e.config.selector, function (t) {
                                        return e._enter(t);
                                    })
                                    .on(i, e.config.selector, function (t) {
                                        return e._leave(t);
                                    });
                            }
                            Te(e.element)
                                .closest(".modal")
                                .on("hide.bs.modal", function () {
                                    return e.hide();
                                });
                        }),
                            this.config.selector ? (this.config = r({}, this.config, { trigger: "manual", selector: "" })) : this._fixTitle();
                    }),
                    (t._fixTitle = function () {
                        var e = typeof this.element.getAttribute("data-original-title");
                        (this.element.getAttribute("title") || "string" !== e) && (this.element.setAttribute("data-original-title", this.element.getAttribute("title") || ""), this.element.setAttribute("title", ""));
                    }),
                    (t._enter = function (e, t) {
                        var n = this.constructor.DATA_KEY;
                        (t = t || Te(e.currentTarget).data(n)) || ((t = new this.constructor(e.currentTarget, this._getDelegateConfig())), Te(e.currentTarget).data(n, t)),
                            e && (t._activeTrigger["focusin" === e.type ? ze : Be] = !0),
                            Te(t.getTipElement()).hasClass(We) || t._hoverState === Oe
                                ? (t._hoverState = Oe)
                                : (clearTimeout(t._timeout),
                                  (t._hoverState = Oe),
                                  t.config.delay && t.config.delay.show
                                      ? (t._timeout = setTimeout(function () {
                                            t._hoverState === Oe && t.show();
                                        }, t.config.delay.show))
                                      : t.show());
                    }),
                    (t._leave = function (e, t) {
                        var n = this.constructor.DATA_KEY;
                        (t = t || Te(e.currentTarget).data(n)) || ((t = new this.constructor(e.currentTarget, this._getDelegateConfig())), Te(e.currentTarget).data(n, t)),
                            e && (t._activeTrigger["focusout" === e.type ? ze : Be] = !1),
                            t._isWithActiveTrigger() ||
                                (clearTimeout(t._timeout),
                                (t._hoverState = Ne),
                                t.config.delay && t.config.delay.hide
                                    ? (t._timeout = setTimeout(function () {
                                          t._hoverState === Ne && t.hide();
                                      }, t.config.delay.hide))
                                    : t.hide());
                    }),
                    (t._isWithActiveTrigger = function () {
                        for (var e in this._activeTrigger) if (this._activeTrigger[e]) return !0;
                        return !1;
                    }),
                    (t._getConfig = function (e) {
                        return (
                            "number" == typeof (e = r({}, this.constructor.Default, Te(this.element).data(), "object" == typeof e && e ? e : {})).delay && (e.delay = { show: e.delay, hide: e.delay }),
                            "number" == typeof e.title && (e.title = e.title.toString()),
                            "number" == typeof e.content && (e.content = e.content.toString()),
                            Dt.typeCheckConfig(Ye, e, this.constructor.DefaultType),
                            e
                        );
                    }),
                    (t._getDelegateConfig = function () {
                        var e = {};
                        if (this.config) for (var t in this.config) this.constructor.Default[t] !== this.config[t] && (e[t] = this.config[t]);
                        return e;
                    }),
                    (t._cleanTipClass = function () {
                        var e = Te(this.getTipElement()),
                            t = e.attr("class").match(Fe);
                        null !== t && 0 < t.length && e.removeClass(t.join(""));
                    }),
                    (t._handlePopperPlacementChange = function (e) {
                        this._cleanTipClass(), this.addAttachmentClass(this._getAttachment(e.placement));
                    }),
                    (t._fixTransition = function () {
                        var e = this.getTipElement(),
                            t = this.config.animation;
                        null === e.getAttribute("x-placement") && (Te(e).removeClass($e), (this.config.animation = !1), this.hide(), this.show(), (this.config.animation = t));
                    }),
                    (e._jQueryInterface = function (t) {
                        return this.each(function () {
                            var n = Te(this).data(Ce),
                                i = "object" == typeof t && t;
                            if ((n || !/dispose|hide/.test(t)) && (n || ((n = new e(this, i)), Te(this).data(Ce, n)), "string" == typeof t)) {
                                if (void 0 === n[t]) throw new TypeError('No method named "' + t + '"');
                                n[t]();
                            }
                        });
                    }),
                    a(e, null, [
                        {
                            key: "VERSION",
                            get: function () {
                                return "4.1.1";
                            },
                        },
                        {
                            key: "Default",
                            get: function () {
                                return je;
                            },
                        },
                        {
                            key: "NAME",
                            get: function () {
                                return Ye;
                            },
                        },
                        {
                            key: "DATA_KEY",
                            get: function () {
                                return Ce;
                            },
                        },
                        {
                            key: "Event",
                            get: function () {
                                return Re;
                            },
                        },
                        {
                            key: "EVENT_KEY",
                            get: function () {
                                return Ee;
                            },
                        },
                        {
                            key: "DefaultType",
                            get: function () {
                                return He;
                            },
                        },
                    ]),
                    e
                );
            })()),
            (Te.fn[Ye] = Ue._jQueryInterface),
            (Te.fn[Ye].Constructor = Ue),
            (Te.fn[Ye].noConflict = function () {
                return (Te.fn[Ye] = Ae), Ue._jQueryInterface;
            }),
            Ue),
        It =
            ((Ve = "popover"),
            (Je = "." + (Ge = "bs.popover")),
            (Xe = (qe = t).fn[Ve]),
            (Ke = "bs-popover"),
            (Ze = new RegExp("(^|\\s)" + Ke + "\\S+", "g")),
            (Qe = r({}, At.Default, {
                placement: "right",
                trigger: "click",
                content: "",
                template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
            })),
            (et = r({}, At.DefaultType, { content: "(string|element|function)" })),
            (tt = {
                HIDE: "hide" + Je,
                HIDDEN: "hidden" + Je,
                SHOW: "show" + Je,
                SHOWN: "shown" + Je,
                INSERTED: "inserted" + Je,
                CLICK: "click" + Je,
                FOCUSIN: "focusin" + Je,
                FOCUSOUT: "focusout" + Je,
                MOUSEENTER: "mouseenter" + Je,
                MOUSELEAVE: "mouseleave" + Je,
            }),
            (nt = (function (e) {
                var t, n;
                function i() {
                    return e.apply(this, arguments) || this;
                }
                (n = e), ((t = i).prototype = Object.create(n.prototype)), ((t.prototype.constructor = t).__proto__ = n);
                var r = i.prototype;
                return (
                    (r.isWithContent = function () {
                        return this.getTitle() || this._getContent();
                    }),
                    (r.addAttachmentClass = function (e) {
                        qe(this.getTipElement()).addClass(Ke + "-" + e);
                    }),
                    (r.getTipElement = function () {
                        return (this.tip = this.tip || qe(this.config.template)[0]), this.tip;
                    }),
                    (r.setContent = function () {
                        var e = qe(this.getTipElement());
                        this.setElementContent(e.find(".popover-header"), this.getTitle());
                        var t = this._getContent();
                        "function" == typeof t && (t = t.call(this.element)), this.setElementContent(e.find(".popover-body"), t), e.removeClass("fade show");
                    }),
                    (r._getContent = function () {
                        return this.element.getAttribute("data-content") || this.config.content;
                    }),
                    (r._cleanTipClass = function () {
                        var e = qe(this.getTipElement()),
                            t = e.attr("class").match(Ze);
                        null !== t && 0 < t.length && e.removeClass(t.join(""));
                    }),
                    (i._jQueryInterface = function (e) {
                        return this.each(function () {
                            var t = qe(this).data(Ge),
                                n = "object" == typeof e ? e : null;
                            if ((t || !/destroy|hide/.test(e)) && (t || ((t = new i(this, n)), qe(this).data(Ge, t)), "string" == typeof e)) {
                                if (void 0 === t[e]) throw new TypeError('No method named "' + e + '"');
                                t[e]();
                            }
                        });
                    }),
                    a(i, null, [
                        {
                            key: "VERSION",
                            get: function () {
                                return "4.1.1";
                            },
                        },
                        {
                            key: "Default",
                            get: function () {
                                return Qe;
                            },
                        },
                        {
                            key: "NAME",
                            get: function () {
                                return Ve;
                            },
                        },
                        {
                            key: "DATA_KEY",
                            get: function () {
                                return Ge;
                            },
                        },
                        {
                            key: "Event",
                            get: function () {
                                return tt;
                            },
                        },
                        {
                            key: "EVENT_KEY",
                            get: function () {
                                return Je;
                            },
                        },
                        {
                            key: "DefaultType",
                            get: function () {
                                return et;
                            },
                        },
                    ]),
                    i
                );
            })(At)),
            (qe.fn[Ve] = nt._jQueryInterface),
            (qe.fn[Ve].Constructor = nt),
            (qe.fn[Ve].noConflict = function () {
                return (qe.fn[Ve] = Xe), nt._jQueryInterface;
            }),
            nt),
        Ft =
            ((at = "scrollspy"),
            (st = "." + (rt = "bs.scrollspy")),
            (ot = (it = t).fn[at]),
            (lt = { offset: 10, method: "auto", target: "" }),
            (dt = { offset: "number", method: "string", target: "(string|element)" }),
            (ut = { ACTIVATE: "activate" + st, SCROLL: "scroll" + st, LOAD_DATA_API: "load" + st + ".data-api" }),
            (ct = "active"),
            (ht = {
                DATA_SPY: '[data-spy="scroll"]',
                ACTIVE: ".active",
                NAV_LIST_GROUP: ".nav, .list-group",
                NAV_LINKS: ".nav-link",
                NAV_ITEMS: ".nav-item",
                LIST_ITEMS: ".list-group-item",
                DROPDOWN: ".dropdown",
                DROPDOWN_ITEMS: ".dropdown-item",
                DROPDOWN_TOGGLE: ".dropdown-toggle",
            }),
            (ft = "position"),
            (pt = (function () {
                function e(e, t) {
                    var n = this;
                    (this._element = e),
                        (this._scrollElement = "BODY" === e.tagName ? window : e),
                        (this._config = this._getConfig(t)),
                        (this._selector = this._config.target + " " + ht.NAV_LINKS + "," + this._config.target + " " + ht.LIST_ITEMS + "," + this._config.target + " " + ht.DROPDOWN_ITEMS),
                        (this._offsets = []),
                        (this._targets = []),
                        (this._activeTarget = null),
                        (this._scrollHeight = 0),
                        it(this._scrollElement).on(ut.SCROLL, function (e) {
                            return n._process(e);
                        }),
                        this.refresh(),
                        this._process();
                }
                var t = e.prototype;
                return (
                    (t.refresh = function () {
                        var e = this,
                            t = this._scrollElement === this._scrollElement.window ? "offset" : ft,
                            n = "auto" === this._config.method ? t : this._config.method,
                            i = n === ft ? this._getScrollTop() : 0;
                        (this._offsets = []),
                            (this._targets = []),
                            (this._scrollHeight = this._getScrollHeight()),
                            it
                                .makeArray(it(this._selector))
                                .map(function (e) {
                                    var t,
                                        a = Dt.getSelectorFromElement(e);
                                    if ((a && (t = it(a)[0]), t)) {
                                        var r = t.getBoundingClientRect();
                                        if (r.width || r.height) return [it(t)[n]().top + i, a];
                                    }
                                    return null;
                                })
                                .filter(function (e) {
                                    return e;
                                })
                                .sort(function (e, t) {
                                    return e[0] - t[0];
                                })
                                .forEach(function (t) {
                                    e._offsets.push(t[0]), e._targets.push(t[1]);
                                });
                    }),
                    (t.dispose = function () {
                        it.removeData(this._element, rt),
                            it(this._scrollElement).off(st),
                            (this._element = null),
                            (this._scrollElement = null),
                            (this._config = null),
                            (this._selector = null),
                            (this._offsets = null),
                            (this._targets = null),
                            (this._activeTarget = null),
                            (this._scrollHeight = null);
                    }),
                    (t._getConfig = function (e) {
                        if ("string" != typeof (e = r({}, lt, "object" == typeof e && e ? e : {})).target) {
                            var t = it(e.target).attr("id");
                            t || ((t = Dt.getUID(at)), it(e.target).attr("id", t)), (e.target = "#" + t);
                        }
                        return Dt.typeCheckConfig(at, e, dt), e;
                    }),
                    (t._getScrollTop = function () {
                        return this._scrollElement === window ? this._scrollElement.pageYOffset : this._scrollElement.scrollTop;
                    }),
                    (t._getScrollHeight = function () {
                        return this._scrollElement.scrollHeight || Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
                    }),
                    (t._getOffsetHeight = function () {
                        return this._scrollElement === window ? window.innerHeight : this._scrollElement.getBoundingClientRect().height;
                    }),
                    (t._process = function () {
                        var e = this._getScrollTop() + this._config.offset,
                            t = this._getScrollHeight(),
                            n = this._config.offset + t - this._getOffsetHeight();
                        if ((this._scrollHeight !== t && this.refresh(), n <= e)) {
                            var i = this._targets[this._targets.length - 1];
                            this._activeTarget !== i && this._activate(i);
                        } else {
                            if (this._activeTarget && e < this._offsets[0] && 0 < this._offsets[0]) return (this._activeTarget = null), void this._clear();
                            for (var a = this._offsets.length; a--; ) this._activeTarget !== this._targets[a] && e >= this._offsets[a] && (void 0 === this._offsets[a + 1] || e < this._offsets[a + 1]) && this._activate(this._targets[a]);
                        }
                    }),
                    (t._activate = function (e) {
                        (this._activeTarget = e), this._clear();
                        var t = this._selector.split(",");
                        t = t.map(function (t) {
                            return t + '[data-target="' + e + '"],' + t + '[href="' + e + '"]';
                        });
                        var n = it(t.join(","));
                        n.hasClass("dropdown-item")
                            ? (n.closest(ht.DROPDOWN).find(ht.DROPDOWN_TOGGLE).addClass(ct), n.addClass(ct))
                            : (n.addClass(ct),
                              n
                                  .parents(ht.NAV_LIST_GROUP)
                                  .prev(ht.NAV_LINKS + ", " + ht.LIST_ITEMS)
                                  .addClass(ct),
                              n.parents(ht.NAV_LIST_GROUP).prev(ht.NAV_ITEMS).children(ht.NAV_LINKS).addClass(ct)),
                            it(this._scrollElement).trigger(ut.ACTIVATE, { relatedTarget: e });
                    }),
                    (t._clear = function () {
                        it(this._selector).filter(ht.ACTIVE).removeClass(ct);
                    }),
                    (e._jQueryInterface = function (t) {
                        return this.each(function () {
                            var n = it(this).data(rt);
                            if ((n || ((n = new e(this, "object" == typeof t && t)), it(this).data(rt, n)), "string" == typeof t)) {
                                if (void 0 === n[t]) throw new TypeError('No method named "' + t + '"');
                                n[t]();
                            }
                        });
                    }),
                    a(e, null, [
                        {
                            key: "VERSION",
                            get: function () {
                                return "4.1.1";
                            },
                        },
                        {
                            key: "Default",
                            get: function () {
                                return lt;
                            },
                        },
                    ]),
                    e
                );
            })()),
            it(window).on(ut.LOAD_DATA_API, function () {
                for (var e = it.makeArray(it(ht.DATA_SPY)), t = e.length; t--; ) {
                    var n = it(e[t]);
                    pt._jQueryInterface.call(n, n.data());
                }
            }),
            (it.fn[at] = pt._jQueryInterface),
            (it.fn[at].Constructor = pt),
            (it.fn[at].noConflict = function () {
                return (it.fn[at] = ot), pt._jQueryInterface;
            }),
            pt),
        Ht =
            ((_t = "." + (gt = "bs.tab")),
            (yt = (mt = t).fn.tab),
            (vt = { HIDE: "hide" + _t, HIDDEN: "hidden" + _t, SHOW: "show" + _t, SHOWN: "shown" + _t, CLICK_DATA_API: "click" + _t + ".data-api" }),
            (bt = "active"),
            (wt = "show"),
            (Mt = ".active"),
            (xt = "> li > .active"),
            (kt = (function () {
                function e(e) {
                    this._element = e;
                }
                var t = e.prototype;
                return (
                    (t.show = function () {
                        var e = this;
                        if (!((this._element.parentNode && this._element.parentNode.nodeType === Node.ELEMENT_NODE && mt(this._element).hasClass(bt)) || mt(this._element).hasClass("disabled"))) {
                            var t,
                                n,
                                i = mt(this._element).closest(".nav, .list-group")[0],
                                a = Dt.getSelectorFromElement(this._element);
                            if (i) {
                                var r = "UL" === i.nodeName ? xt : Mt;
                                n = (n = mt.makeArray(mt(i).find(r)))[n.length - 1];
                            }
                            var s = mt.Event(vt.HIDE, { relatedTarget: this._element }),
                                o = mt.Event(vt.SHOW, { relatedTarget: n });
                            if ((n && mt(n).trigger(s), mt(this._element).trigger(o), !o.isDefaultPrevented() && !s.isDefaultPrevented())) {
                                a && (t = mt(a)[0]), this._activate(this._element, i);
                                var l = function () {
                                    var t = mt.Event(vt.HIDDEN, { relatedTarget: e._element }),
                                        i = mt.Event(vt.SHOWN, { relatedTarget: n });
                                    mt(n).trigger(t), mt(e._element).trigger(i);
                                };
                                t ? this._activate(t, t.parentNode, l) : l();
                            }
                        }
                    }),
                    (t.dispose = function () {
                        mt.removeData(this._element, gt), (this._element = null);
                    }),
                    (t._activate = function (e, t, n) {
                        var i = this,
                            a = ("UL" === t.nodeName ? mt(t).find(xt) : mt(t).children(Mt))[0],
                            r = n && a && mt(a).hasClass("fade"),
                            s = function () {
                                return i._transitionComplete(e, a, n);
                            };
                        if (a && r) {
                            var o = Dt.getTransitionDurationFromElement(a);
                            mt(a).one(Dt.TRANSITION_END, s).emulateTransitionEnd(o);
                        } else s();
                    }),
                    (t._transitionComplete = function (e, t, n) {
                        if (t) {
                            mt(t).removeClass(wt + " " + bt);
                            var i = mt(t.parentNode).find("> .dropdown-menu .active")[0];
                            i && mt(i).removeClass(bt), "tab" === t.getAttribute("role") && t.setAttribute("aria-selected", !1);
                        }
                        if ((mt(e).addClass(bt), "tab" === e.getAttribute("role") && e.setAttribute("aria-selected", !0), Dt.reflow(e), mt(e).addClass(wt), e.parentNode && mt(e.parentNode).hasClass("dropdown-menu"))) {
                            var a = mt(e).closest(".dropdown")[0];
                            a && mt(a).find(".dropdown-toggle").addClass(bt), e.setAttribute("aria-expanded", !0);
                        }
                        n && n();
                    }),
                    (e._jQueryInterface = function (t) {
                        return this.each(function () {
                            var n = mt(this),
                                i = n.data(gt);
                            if ((i || ((i = new e(this)), n.data(gt, i)), "string" == typeof t)) {
                                if (void 0 === i[t]) throw new TypeError('No method named "' + t + '"');
                                i[t]();
                            }
                        });
                    }),
                    a(e, null, [
                        {
                            key: "VERSION",
                            get: function () {
                                return "4.1.1";
                            },
                        },
                    ]),
                    e
                );
            })()),
            mt(document).on(vt.CLICK_DATA_API, '[data-toggle="tab"], [data-toggle="pill"], [data-toggle="list"]', function (e) {
                e.preventDefault(), kt._jQueryInterface.call(mt(this), "show");
            }),
            (mt.fn.tab = kt._jQueryInterface),
            (mt.fn.tab.Constructor = kt),
            (mt.fn.tab.noConflict = function () {
                return (mt.fn.tab = yt), kt._jQueryInterface;
            }),
            kt);
    !(function (e) {
        if (void 0 === e) throw new TypeError("Bootstrap's JavaScript requires jQuery. jQuery must be included before Bootstrap's JavaScript.");
        var t = e.fn.jquery.split(" ")[0].split(".");
        if ((t[0] < 2 && t[1] < 9) || (1 === t[0] && 9 === t[1] && t[2] < 1) || 4 <= t[0]) throw new Error("Bootstrap's JavaScript requires at least jQuery v1.9.1 but less than v4.0.0");
    })(t),
        (e.Util = Dt),
        (e.Alert = Lt),
        (e.Button = St),
        (e.Carousel = Tt),
        (e.Collapse = Yt),
        (e.Dropdown = Ct),
        (e.Modal = Et),
        (e.Popover = It),
        (e.Scrollspy = Ft),
        (e.Tab = Ht),
        (e.Tooltip = At),
        Object.defineProperty(e, "__esModule", { value: !0 });
}),
(function (e) {
    function t(i) {
        if (n[i]) return n[i].exports;
        var a = (n[i] = { i: i, l: !1, exports: {} });
        return e[i].call(a.exports, a, a.exports, t), (a.l = !0), a.exports;
    }
    var n = {};
    (t.m = e),
        (t.c = n),
        (t.d = function (e, n, i) {
            t.o(e, n) || Object.defineProperty(e, n, { configurable: !1, enumerable: !0, get: i });
        }),
        (t.n = function (e) {
            var n =
                e && e.__esModule
                    ? function () {
                          return e.default;
                      }
                    : function () {
                          return e;
                      };
            return t.d(n, "a", n), n;
        }),
        (t.o = function (e, t) {
            return Object.prototype.hasOwnProperty.call(e, t);
        }),
        (t.p = ""),
        t((t.s = 3));
})([
    function (e, t, n) {
        "use strict";
        var i, a, r;
        "function" == typeof Symbol && Symbol.iterator,
            (a = [n(2)]),
            void 0 !==
                (r =
                    "function" ==
                    typeof (i = function (e) {
                        return e;
                    })
                        ? i.apply(t, a)
                        : i) && (e.exports = r);
    },
    function (e, t, n) {
        "use strict";
        var i,
            a,
            r,
            s =
                "function" == typeof Symbol && "symbol" == typeof Symbol.iterator
                    ? function (e) {
                          return typeof e;
                      }
                    : function (e) {
                          return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e;
                      };
        !(function (s) {
            (a = [n(0), n(10), n(11)]), void 0 !== (r = "function" == typeof (i = s) ? i.apply(t, a) : i) && (e.exports = r);
        })(function (e, t, n, i) {
            function a(t, n, s) {
                if (!(this instanceof a)) return new a(t, n, s);
                (this.el = i),
                    (this.events = {}),
                    (this.maskset = i),
                    (this.refreshValue = !1),
                    !0 !== s &&
                        (e.isPlainObject(t) ? (n = t) : ((n = n || {}).alias = t),
                        (this.opts = e.extend(!0, {}, this.defaults, n)),
                        (this.noMasksCache = n && n.definitions !== i),
                        (this.userOptions = n || {}),
                        (this.isRTL = this.opts.numericInput),
                        r(this.opts.alias, n, this.opts));
            }
            function r(t, n, s) {
                var o = a.prototype.aliases[t];
                return o ? (o.alias && r(o.alias, i, s), e.extend(!0, s, o), e.extend(!0, s, n), !0) : (null === s.mask && (s.mask = t), !1);
            }
            function o(t, n) {
                function r(t, r, s) {
                    var o = !1;
                    if (
                        ((null !== t && "" !== t) || ((o = null !== s.regex) ? (t = (t = s.regex).replace(/^(\^)(.*)(\$)$/, "$2")) : ((o = !0), (t = ".*"))),
                        1 === t.length && !1 === s.greedy && 0 !== s.repeat && (s.placeholder = ""),
                        s.repeat > 0 || "*" === s.repeat || "+" === s.repeat)
                    ) {
                        var l = "*" === s.repeat ? 0 : "+" === s.repeat ? 1 : s.repeat;
                        t = s.groupmarker.start + t + s.groupmarker.end + s.quantifiermarker.start + l + "," + s.repeat + s.quantifiermarker.end;
                    }
                    var d,
                        u = o ? "regex_" + s.regex : s.numericInput ? t.split("").reverse().join("") : t;
                    return (
                        a.prototype.masksCache[u] === i || !0 === n
                            ? ((d = { mask: t, maskToken: a.prototype.analyseMask(t, o, s), validPositions: {}, _buffer: i, buffer: i, tests: {}, metadata: r, maskLength: i }),
                              !0 !== n && ((a.prototype.masksCache[u] = d), (d = e.extend(!0, {}, a.prototype.masksCache[u]))))
                            : (d = e.extend(!0, {}, a.prototype.masksCache[u])),
                        d
                    );
                }
                if ((e.isFunction(t.mask) && (t.mask = t.mask(t)), e.isArray(t.mask))) {
                    if (t.mask.length > 1) {
                        t.keepStatic = null === t.keepStatic || t.keepStatic;
                        var s = t.groupmarker.start;
                        return (
                            e.each(t.numericInput ? t.mask.reverse() : t.mask, function (n, a) {
                                s.length > 1 && (s += t.groupmarker.end + t.alternatormarker + t.groupmarker.start), a.mask === i || e.isFunction(a.mask) ? (s += a) : (s += a.mask);
                            }),
                            r((s += t.groupmarker.end), t.mask, t)
                        );
                    }
                    t.mask = t.mask.pop();
                }
                return t.mask && t.mask.mask !== i && !e.isFunction(t.mask.mask) ? r(t.mask.mask, t.mask, t) : r(t.mask, t.mask, t);
            }
            function l(r, o, d) {
                function p(e, t, n) {
                    t = t || 0;
                    var a,
                        r,
                        s,
                        o = [],
                        l = 0,
                        u = _();
                    do {
                        !0 === e && m().validPositions[l]
                            ? ((r = (s = m().validPositions[l]).match), (a = s.locator.slice()), o.push(!0 === n ? s.input : !1 === n ? r.nativeDef : H(l, r)))
                            : ((r = (s = b(l, a, l - 1)).match),
                              (a = s.locator.slice()),
                              (!1 === d.jitMasking || l < u || ("number" == typeof d.jitMasking && isFinite(d.jitMasking) && d.jitMasking > l)) && o.push(!1 === n ? r.nativeDef : H(l, r))),
                            l++;
                    } while (((V === i || l < V) && (null !== r.fn || "" !== r.def)) || t > l);
                    return "" === o[o.length - 1] && o.pop(), (m().maskLength = l + 1), o;
                }
                function m() {
                    return o;
                }
                function g(e) {
                    var t = m();
                    (t.buffer = i), !0 !== e && ((t.validPositions = {}), (t.p = 0));
                }
                function _(e, t, n) {
                    var a = -1,
                        r = -1,
                        s = n || m().validPositions;
                    for (var o in (e === i && (e = -1), s)) {
                        var l = parseInt(o);
                        s[l] && (t || !0 !== s[l].generatedInput) && (l <= e && (a = l), l >= e && (r = l));
                    }
                    return (-1 !== a && e - a > 1) || r < e ? a : r;
                }
                function y(t, n, a, r) {
                    var s,
                        o = t,
                        l = e.extend(!0, {}, m().validPositions),
                        u = !1;
                    for (m().p = t, s = n - 1; s >= o; s--)
                        m().validPositions[s] !== i &&
                            ((!0 !== a &&
                                ((!m().validPositions[s].match.optionality &&
                                    (function (e) {
                                        var t = m().validPositions[e];
                                        if (t !== i && null === t.match.fn) {
                                            var n = m().validPositions[e - 1],
                                                a = m().validPositions[e + 1];
                                            return n !== i && a !== i;
                                        }
                                        return !1;
                                    })(s)) ||
                                    !1 === d.canClearPosition(m(), s, _(), r, d))) ||
                                delete m().validPositions[s]);
                    for (g(!0), s = o + 1; s <= _(); ) {
                        for (; m().validPositions[o] !== i; ) o++;
                        if ((s < o && (s = o + 1), m().validPositions[s] === i && C(s))) s++;
                        else {
                            var c = b(s);
                            !1 === u && l[o] && l[o].match.def === c.match.def
                                ? ((m().validPositions[o] = e.extend(!0, {}, l[o])), (m().validPositions[o].input = c.input), delete m().validPositions[s], s++)
                                : M(o, c.match.def)
                                ? !1 !== Y(o, c.input || H(s), !0) && (delete m().validPositions[s], s++, (u = !0))
                                : C(s) || (s++, o--),
                                o++;
                        }
                    }
                    g(!0);
                }
                function v(e, t) {
                    for (
                        var n, a = e, r = _(), s = m().validPositions[r] || x(0)[0], o = s.alternation !== i ? s.locator[s.alternation].toString().split(",") : [], l = 0;
                        l < a.length &&
                        (!(
                            (n = a[l]).match &&
                            ((d.greedy && !0 !== n.match.optionalQuantifier) || ((!1 === n.match.optionality || !1 === n.match.newBlockMarker) && !0 !== n.match.optionalQuantifier)) &&
                            (s.alternation === i || s.alternation !== n.alternation || (n.locator[s.alternation] !== i && T(n.locator[s.alternation].toString().split(","), o)))
                        ) ||
                            (!0 === t && (null !== n.match.fn || /[0-9a-bA-Z]/.test(n.match.def))));
                        l++
                    );
                    return n;
                }
                function b(e, t, n) {
                    return m().validPositions[e] || v(x(e, t ? t.slice() : t, n));
                }
                function w(e) {
                    return m().validPositions[e] ? m().validPositions[e] : x(e)[0];
                }
                function M(e, t) {
                    for (var n = !1, i = x(e), a = 0; a < i.length; a++)
                        if (i[a].match && i[a].match.def === t) {
                            n = !0;
                            break;
                        }
                    return n;
                }
                function x(t, n, a) {
                    function r(n, a, s, l) {
                        function c(s, l, g) {
                            function _(t, n) {
                                var i = 0 === e.inArray(t, n.matches);
                                return (
                                    i ||
                                        e.each(n.matches, function (e, a) {
                                            if (!0 === a.isQuantifier && (i = _(t, n.matches[e - 1]))) return !1;
                                        }),
                                    i
                                );
                            }
                            function y(t, n, a) {
                                var r, s;
                                if (m().validPositions[t - 1] && a && m().tests[t]) for (var o = m().validPositions[t - 1].locator, l = m().tests[t][0].locator, d = 0; d < a; d++) if (o[d] !== l[d]) return o.slice(a + 1);
                                return (
                                    (m().tests[t] || m().validPositions[t]) &&
                                        e.each(m().tests[t] || [m().validPositions[t]], function (e, t) {
                                            var o = a !== i ? a : t.alternation,
                                                l = t.locator[o] !== i ? t.locator[o].toString().indexOf(n) : -1;
                                            (s === i || l < s) && -1 !== l && ((r = t), (s = l));
                                        }),
                                    r ? r.locator.slice((a !== i ? a : r.alternation) + 1) : a !== i ? y(t, n) : i
                                );
                            }
                            if (u > 1e4) throw "Inputmask: There is probably an error in your mask definition or in the code. Create an issue on github with an example of the mask you are using. " + m().mask;
                            if (u === t && s.matches === i) return h.push({ match: s, locator: l.reverse(), cd: p }), !0;
                            if (s.matches !== i) {
                                if (s.isGroup && g !== s) {
                                    if ((s = c(n.matches[e.inArray(s, n.matches) + 1], l))) return !0;
                                } else if (s.isOptional) {
                                    var v = s;
                                    if ((s = r(s, a, l, g))) {
                                        if (!_((o = h[h.length - 1].match), v)) return !0;
                                        (f = !0), (u = t);
                                    }
                                } else if (s.isAlternator) {
                                    var b,
                                        w = s,
                                        M = [],
                                        x = h.slice(),
                                        k = l.length,
                                        D = a.length > 0 ? a.shift() : -1;
                                    if (-1 === D || "string" == typeof D) {
                                        var L,
                                            S = u,
                                            T = a.slice(),
                                            Y = [];
                                        if ("string" == typeof D) Y = D.split(",");
                                        else for (L = 0; L < w.matches.length; L++) Y.push(L);
                                        for (var C = 0; C < Y.length; C++) {
                                            if (((L = parseInt(Y[C])), (h = []), (a = y(u, L, k) || T.slice()), !0 !== (s = c(w.matches[L] || n.matches[L], [L].concat(l), g) || s) && s !== i && Y[Y.length - 1] < w.matches.length)) {
                                                var E = e.inArray(s, n.matches) + 1;
                                                n.matches.length > E &&
                                                    (s = c(n.matches[E], [E].concat(l.slice(1, l.length)), g)) &&
                                                    (Y.push(E.toString()),
                                                    e.each(h, function (e, t) {
                                                        t.alternation = l.length - 1;
                                                    }));
                                            }
                                            (b = h.slice()), (u = S), (h = []);
                                            for (var A = 0; A < b.length; A++) {
                                                var I = b[A],
                                                    F = !1;
                                                I.alternation = I.alternation || k;
                                                for (var H = 0; H < M.length; H++) {
                                                    var P = M[H];
                                                    if ("string" != typeof D || -1 !== e.inArray(I.locator[I.alternation].toString(), Y)) {
                                                        if (
                                                            (function (e, t) {
                                                                return e.match.nativeDef === t.match.nativeDef || e.match.def === t.match.nativeDef || e.match.nativeDef === t.match.def;
                                                            })(I, P)
                                                        ) {
                                                            (F = !0),
                                                                I.alternation === P.alternation &&
                                                                    -1 === P.locator[P.alternation].toString().indexOf(I.locator[I.alternation]) &&
                                                                    ((P.locator[P.alternation] = P.locator[P.alternation] + "," + I.locator[I.alternation]), (P.alternation = I.alternation)),
                                                                I.match.nativeDef === P.match.def && ((I.locator[I.alternation] = P.locator[P.alternation]), M.splice(M.indexOf(P), 1, I));
                                                            break;
                                                        }
                                                        if (I.match.def === P.match.def) {
                                                            F = !1;
                                                            break;
                                                        }
                                                        if (
                                                            (function (e, n) {
                                                                return null === e.match.fn && null !== n.match.fn && n.match.fn.test(e.match.def, m(), t, !1, d, !1);
                                                            })(I, P) ||
                                                            (function (e, n) {
                                                                return null !== e.match.fn && null !== n.match.fn && n.match.fn.test(e.match.def.replace(/[\[\]]/g, ""), m(), t, !1, d, !1);
                                                            })(I, P)
                                                        ) {
                                                            I.alternation === P.alternation &&
                                                                -1 === I.locator[I.alternation].toString().indexOf(P.locator[P.alternation].toString().split("")[0]) &&
                                                                ((I.na = I.na || I.locator[I.alternation].toString()),
                                                                -1 === I.na.indexOf(I.locator[I.alternation].toString().split("")[0]) && (I.na = I.na + "," + I.locator[P.alternation].toString().split("")[0]),
                                                                (F = !0),
                                                                (I.locator[I.alternation] = P.locator[P.alternation].toString().split("")[0] + "," + I.locator[I.alternation]),
                                                                M.splice(M.indexOf(P), 0, I));
                                                            break;
                                                        }
                                                    }
                                                }
                                                F || M.push(I);
                                            }
                                        }
                                        "string" == typeof D &&
                                            (M = e.map(M, function (t, n) {
                                                if (isFinite(n)) {
                                                    var a = t.alternation,
                                                        r = t.locator[a].toString().split(",");
                                                    (t.locator[a] = i), (t.alternation = i);
                                                    for (var s = 0; s < r.length; s++)
                                                        -1 !== e.inArray(r[s], Y) && (t.locator[a] !== i ? ((t.locator[a] += ","), (t.locator[a] += r[s])) : (t.locator[a] = parseInt(r[s])), (t.alternation = a));
                                                    if (t.locator[a] !== i) return t;
                                                }
                                            })),
                                            (h = x.concat(M)),
                                            (u = t),
                                            (f = h.length > 0),
                                            (s = M.length > 0),
                                            (a = T.slice());
                                    } else s = c(w.matches[D] || n.matches[D], [D].concat(l), g);
                                    if (s) return !0;
                                } else if (s.isQuantifier && g !== n.matches[e.inArray(s, n.matches) - 1])
                                    for (var j = s, O = a.length > 0 ? a.shift() : 0; O < (isNaN(j.quantifier.max) ? O + 1 : j.quantifier.max) && u <= t; O++) {
                                        var N = n.matches[e.inArray(j, n.matches) - 1];
                                        if ((s = c(N, [O].concat(l), N))) {
                                            if ((((o = h[h.length - 1].match).optionalQuantifier = O > j.quantifier.min - 1), _(o, N))) {
                                                if (O > j.quantifier.min - 1) {
                                                    (f = !0), (u = t);
                                                    break;
                                                }
                                                return !0;
                                            }
                                            return !0;
                                        }
                                    }
                                else if ((s = r(s, a, l, g))) return !0;
                            } else u++;
                        }
                        for (var g = a.length > 0 ? a.shift() : 0; g < n.matches.length; g++)
                            if (!0 !== n.matches[g].isQuantifier) {
                                var _ = c(n.matches[g], [g].concat(s), l);
                                if (_ && u === t) return _;
                                if (u > t) break;
                            }
                    }
                    function s(e) {
                        if (
                            d.keepStatic &&
                            t > 0 &&
                            e.length > 1 + ("" === e[e.length - 1].match.def ? 1 : 0) &&
                            !0 !== e[0].match.optionality &&
                            !0 !== e[0].match.optionalQuantifier &&
                            null === e[0].match.fn &&
                            !/[0-9a-bA-Z]/.test(e[0].match.def)
                        ) {
                            if (m().validPositions[t - 1] === i) return [v(e)];
                            if (m().validPositions[t - 1].alternation === e[0].alternation) return [v(e)];
                            if (m().validPositions[t - 1]) return [v(e)];
                        }
                        return e;
                    }
                    var o,
                        l = m().maskToken,
                        u = n ? a : 0,
                        c = n ? n.slice() : [0],
                        h = [],
                        f = !1,
                        p = n ? n.join("") : "";
                    if (t > -1) {
                        if (n === i) {
                            for (var g, _ = t - 1; (g = m().validPositions[_] || m().tests[_]) === i && _ > -1; ) _--;
                            g !== i &&
                                _ > -1 &&
                                ((c = (function (t) {
                                    var n = [];
                                    return (
                                        e.isArray(t) || (t = [t]),
                                        t.length > 0 &&
                                            (t[0].alternation === i
                                                ? 0 === (n = v(t.slice()).locator.slice()).length && (n = t[0].locator.slice())
                                                : e.each(t, function (e, t) {
                                                      if ("" !== t.def)
                                                          if (0 === n.length) n = t.locator.slice();
                                                          else for (var i = 0; i < n.length; i++) t.locator[i] && -1 === n[i].toString().indexOf(t.locator[i]) && (n[i] += "," + t.locator[i]);
                                                  })),
                                        n
                                    );
                                })(g)),
                                (p = c.join("")),
                                (u = _));
                        }
                        if (m().tests[t] && m().tests[t][0].cd === p) return s(m().tests[t]);
                        for (var y = c.shift(); y < l.length && !((r(l[y], c, [y]) && u === t) || u > t); y++);
                    }
                    return (
                        (0 === h.length || f) && h.push({ match: { fn: null, cardinality: 0, optionality: !0, casing: null, def: "", placeholder: "" }, locator: [], cd: p }),
                        n !== i && m().tests[t] ? s(e.extend(!0, [], h)) : ((m().tests[t] = e.extend(!0, [], h)), s(m().tests[t]))
                    );
                }
                function k() {
                    return m()._buffer === i && ((m()._buffer = p(!1, 1)), m().buffer === i && (m().buffer = m()._buffer.slice())), m()._buffer;
                }
                function D(e) {
                    return (m().buffer !== i && !0 !== e) || (m().buffer = p(!0, _(), !0)), m().buffer;
                }
                function L(e, t, n) {
                    var a, r;
                    if (!0 === e) g(), (e = 0), (t = n.length);
                    else for (a = e; a < t; a++) delete m().validPositions[a];
                    for (r = e, a = e; a < t; a++)
                        if ((g(!0), n[a] !== d.skipOptionalPartCharacter)) {
                            var s = Y(r, n[a], !0, !0);
                            !1 !== s && (g(!0), (r = s.caret !== i ? s.caret : s.pos + 1));
                        }
                }
                function S(t, n, i) {
                    switch (d.casing || n.casing) {
                        case "upper":
                            t = t.toUpperCase();
                            break;
                        case "lower":
                            t = t.toLowerCase();
                            break;
                        case "title":
                            var r = m().validPositions[i - 1];
                            t = 0 === i || (r && r.input === String.fromCharCode(a.keyCode.SPACE)) ? t.toUpperCase() : t.toLowerCase();
                            break;
                        default:
                            if (e.isFunction(d.casing)) {
                                var s = Array.prototype.slice.call(arguments);
                                s.push(m().validPositions), (t = d.casing.apply(this, s));
                            }
                    }
                    return t;
                }
                function T(t, n, a) {
                    for (var r, s = d.greedy ? n : n.slice(0, 1), o = !1, l = a !== i ? a.split(",") : [], u = 0; u < l.length; u++) -1 !== (r = t.indexOf(l[u])) && t.splice(r, 1);
                    for (var c = 0; c < t.length; c++)
                        if (-1 !== e.inArray(t[c], s)) {
                            o = !0;
                            break;
                        }
                    return o;
                }
                function Y(t, n, r, s, o, l) {
                    function u(e) {
                        var t = Z ? e.begin - e.end > 1 || e.begin - e.end == 1 : e.end - e.begin > 1 || e.end - e.begin == 1;
                        return t && 0 === e.begin && e.end === m().maskLength ? "full" : t;
                    }
                    function c(n, a, r) {
                        var o = !1;
                        return (
                            e.each(x(n), function (l, c) {
                                for (var f = c.match, p = a ? 1 : 0, v = "", b = f.cardinality; b > p; b--) v += I(n - (b - 1));
                                if ((a && (v += a), D(!0), !1 !== (o = null != f.fn ? f.fn.test(v, m(), n, r, d, u(t)) : (a === f.def || a === d.skipOptionalPartCharacter) && "" !== f.def && { c: H(n, f, !0) || f.def, pos: n }))) {
                                    var w = o.c !== i ? o.c : a;
                                    w = w === d.skipOptionalPartCharacter && null === f.fn ? H(n, f, !0) || f.def : w;
                                    var M = n,
                                        x = D();
                                    if (
                                        (o.remove !== i &&
                                            (e.isArray(o.remove) || (o.remove = [o.remove]),
                                            e.each(
                                                o.remove.sort(function (e, t) {
                                                    return t - e;
                                                }),
                                                function (e, t) {
                                                    y(t, t + 1, !0);
                                                }
                                            )),
                                        o.insert !== i &&
                                            (e.isArray(o.insert) || (o.insert = [o.insert]),
                                            e.each(
                                                o.insert.sort(function (e, t) {
                                                    return e - t;
                                                }),
                                                function (e, t) {
                                                    Y(t.pos, t.c, !0, s);
                                                }
                                            )),
                                        o.refreshFromBuffer)
                                    ) {
                                        var k = o.refreshFromBuffer;
                                        if ((L(!0 === k ? k : k.start, k.end, x), o.pos === i && o.c === i)) return (o.pos = _()), !1;
                                        if ((M = o.pos !== i ? o.pos : n) !== n) return (o = e.extend(o, Y(M, w, !0, s))), !1;
                                    } else if (!0 !== o && o.pos !== i && o.pos !== n && ((M = o.pos), L(n, M, D().slice()), M !== n)) return (o = e.extend(o, Y(M, w, !0))), !1;
                                    return (!0 === o || o.pos !== i || o.c !== i) && (l > 0 && g(!0), h(M, e.extend({}, c, { input: S(w, f, M) }), s, u(t)) || (o = !1), !1);
                                }
                            }),
                            o
                        );
                    }
                    function h(t, n, a, r) {
                        if (r || (d.insertMode && m().validPositions[t] !== i && a === i)) {
                            var s,
                                o = e.extend(!0, {}, m().validPositions),
                                l = _(i, !0);
                            for (s = t; s <= l; s++) delete m().validPositions[s];
                            m().validPositions[t] = e.extend(!0, {}, n);
                            var u,
                                c = !0,
                                h = m().validPositions,
                                p = !1,
                                y = m().maskLength;
                            for (s = u = t; s <= l; s++) {
                                var v = o[s];
                                if (v !== i)
                                    for (var b = u; b < m().maskLength && ((null === v.match.fn && h[s] && (!0 === h[s].match.optionalQuantifier || !0 === h[s].match.optionality)) || null != v.match.fn); ) {
                                        if ((b++, !1 === p && o[b] && o[b].match.def === v.match.def)) (m().validPositions[b] = e.extend(!0, {}, o[b])), (m().validPositions[b].input = v.input), f(b), (u = b), (c = !0);
                                        else if (M(b, v.match.def)) {
                                            var w = Y(b, v.input, !0, !0);
                                            (c = !1 !== w), (u = w.caret || w.insert ? _() : b), (p = !0);
                                        } else if (!(c = !0 === v.generatedInput) && b >= m().maskLength - 1) break;
                                        if ((m().maskLength < y && (m().maskLength = y), c)) break;
                                    }
                                if (!c) break;
                            }
                            if (!c) return (m().validPositions = e.extend(!0, {}, o)), g(!0), !1;
                        } else m().validPositions[t] = e.extend(!0, {}, n);
                        return g(!0), !0;
                    }
                    function f(t) {
                        for (var n = t - 1; n > -1 && !m().validPositions[n]; n--);
                        var a, r;
                        for (n++; n < t; n++)
                            m().validPositions[n] === i &&
                                (!1 === d.jitMasking || d.jitMasking > n) &&
                                ("" === (r = x(n, b(n - 1).locator, n - 1).slice())[r.length - 1].match.def && r.pop(),
                                (a = v(r)) &&
                                    (a.match.def === d.radixPointDefinitionSymbol || !C(n, !0) || (e.inArray(d.radixPoint, D()) < n && a.match.fn && a.match.fn.test(H(n), m(), n, !1, d))) &&
                                    !1 !== (w = c(n, H(n, a.match, !0) || (null == a.match.fn ? a.match.def : "" !== H(n) ? H(n) : D()[n]), !0)) &&
                                    (m().validPositions[w.pos || n].generatedInput = !0));
                    }
                    r = !0 === r;
                    var p = t;
                    t.begin !== i && (p = Z && !u(t) ? t.end : t.begin);
                    var w = !0,
                        k = e.extend(!0, {}, m().validPositions);
                    if ((e.isFunction(d.preValidation) && !r && !0 !== s && !0 !== l && (w = d.preValidation(D(), p, n, u(t), d)), !0 === w)) {
                        if ((f(p), u(t) && (W(i, a.keyCode.DELETE, t, !0, !0), (p = m().p)), p < m().maskLength && (V === i || p < V) && ((w = c(p, n, r)), (!r || !0 === s) && !1 === w && !0 !== l))) {
                            var A = m().validPositions[p];
                            if (!A || null !== A.match.fn || (A.match.def !== n && n !== d.skipOptionalPartCharacter)) {
                                if ((d.insertMode || m().validPositions[E(p)] === i) && !C(p, !0))
                                    for (var F = p + 1, P = E(p); F <= P; F++)
                                        if (!1 !== (w = c(F, n, r))) {
                                            !(function (t, n) {
                                                var a = m().validPositions[n];
                                                if (a)
                                                    for (var r = a.locator, s = r.length, o = t; o < n; o++)
                                                        if (m().validPositions[o] === i && !C(o, !0)) {
                                                            var l = x(o).slice(),
                                                                d = v(l, !0),
                                                                u = -1;
                                                            "" === l[l.length - 1].match.def && l.pop(),
                                                                e.each(l, function (e, t) {
                                                                    for (var n = 0; n < s; n++) {
                                                                        if (t.locator[n] === i || !T(t.locator[n].toString().split(","), r[n].toString().split(","), t.na)) {
                                                                            var a = r[n],
                                                                                o = d.locator[n],
                                                                                l = t.locator[n];
                                                                            a - o > Math.abs(a - l) && (d = t);
                                                                            break;
                                                                        }
                                                                        u < n && ((u = n), (d = t));
                                                                    }
                                                                }),
                                                                ((d = e.extend({}, d, { input: H(o, d.match, !0) || d.match.def })).generatedInput = !0),
                                                                h(o, d, !0),
                                                                (m().validPositions[n] = i),
                                                                c(n, a.input, !0);
                                                        }
                                            })(p, w.pos !== i ? w.pos : F),
                                                (p = F);
                                            break;
                                        }
                            } else w = { caret: E(p) };
                        }
                        !1 === w &&
                            d.keepStatic &&
                            !r &&
                            !0 !== o &&
                            (w = (function (t, n, a) {
                                var r,
                                    o,
                                    l,
                                    u,
                                    c,
                                    h,
                                    f,
                                    p,
                                    y = e.extend(!0, {}, m().validPositions),
                                    v = !1,
                                    b = _();
                                for (u = m().validPositions[b]; b >= 0; b--)
                                    if ((l = m().validPositions[b]) && l.alternation !== i) {
                                        if (((r = b), (o = m().validPositions[r].alternation), u.locator[l.alternation] !== l.locator[l.alternation])) break;
                                        u = l;
                                    }
                                if (o !== i) {
                                    p = parseInt(r);
                                    var w = u.locator[u.alternation || o] !== i ? u.locator[u.alternation || o] : f[0];
                                    w.length > 0 && (w = w.split(",")[0]);
                                    var M = m().validPositions[p],
                                        k = m().validPositions[p - 1];
                                    e.each(x(p, k ? k.locator : i, p - 1), function (r, l) {
                                        f = l.locator[o] ? l.locator[o].toString().split(",") : [];
                                        for (var u = 0; u < f.length; u++) {
                                            var b = [],
                                                x = 0,
                                                k = 0,
                                                D = !1;
                                            if (w < f[u] && (l.na === i || -1 === e.inArray(f[u], l.na.split(",")) || -1 === e.inArray(w.toString(), f))) {
                                                m().validPositions[p] = e.extend(!0, {}, l);
                                                var L = m().validPositions[p].locator;
                                                for (
                                                    m().validPositions[p].locator[o] = parseInt(f[u]),
                                                        null == l.match.fn
                                                            ? (M.input !== l.match.def && ((D = !0), !0 !== M.generatedInput && b.push(M.input)),
                                                              k++,
                                                              (m().validPositions[p].generatedInput = !/[0-9a-bA-Z]/.test(l.match.def)),
                                                              (m().validPositions[p].input = l.match.def))
                                                            : (m().validPositions[p].input = M.input),
                                                        c = p + 1;
                                                    c < _(i, !0) + 1;
                                                    c++
                                                )
                                                    (h = m().validPositions[c]) && !0 !== h.generatedInput && /[0-9a-bA-Z]/.test(h.input) ? b.push(h.input) : c < t && x++, delete m().validPositions[c];
                                                for (D && b[0] === l.match.def && b.shift(), g(!0), v = !0; b.length > 0; ) {
                                                    var S = b.shift();
                                                    if (S !== d.skipOptionalPartCharacter && !(v = Y(_(i, !0) + 1, S, !1, s, !0))) break;
                                                }
                                                if (v) {
                                                    m().validPositions[p].locator = L;
                                                    var T = _(t) + 1;
                                                    for (c = p + 1; c < _() + 1; c++) ((h = m().validPositions[c]) === i || null == h.match.fn) && c < t + (k - x) && k++;
                                                    v = Y((t += k - x) > T ? T : t, n, a, s, !0);
                                                }
                                                if (v) return !1;
                                                g(), (m().validPositions = e.extend(!0, {}, y));
                                            }
                                        }
                                    });
                                }
                                return v;
                            })(p, n, r)),
                            !0 === w && (w = { pos: p });
                    }
                    if (e.isFunction(d.postValidation) && !1 !== w && !r && !0 !== s && !0 !== l) {
                        var j = d.postValidation(D(!0), w, d);
                        if (j.refreshFromBuffer && j.buffer) {
                            var O = j.refreshFromBuffer;
                            L(!0 === O ? O : O.start, O.end, j.buffer);
                        }
                        w = !0 === j ? w : j;
                    }
                    return w && w.pos === i && (w.pos = p), (!1 !== w && !0 !== l) || (g(!0), (m().validPositions = e.extend(!0, {}, k))), w;
                }
                function C(e, t) {
                    var n = b(e).match;
                    if (("" === n.def && (n = w(e).match), null != n.fn)) return n.fn;
                    if (!0 !== t && e > -1) {
                        var i = x(e);
                        return i.length > 1 + ("" === i[i.length - 1].match.def ? 1 : 0);
                    }
                    return !1;
                }
                function E(e, t) {
                    var n = m().maskLength;
                    if (e >= n) return n;
                    var i = e;
                    for (x(n + 1).length > 1 && (p(!0, n + 1, !0), (n = m().maskLength)); ++i < n && ((!0 === t && (!0 !== w(i).match.newBlockMarker || !C(i))) || (!0 !== t && !C(i))); );
                    return i;
                }
                function A(e, t) {
                    var n,
                        i = e;
                    if (i <= 0) return 0;
                    for (; --i > 0 && ((!0 === t && !0 !== w(i).match.newBlockMarker) || (!0 !== t && !C(i) && ((n = x(i)).length < 2 || (2 === n.length && "" === n[1].match.def)))); );
                    return i;
                }
                function I(e) {
                    return m().validPositions[e] === i ? H(e) : m().validPositions[e].input;
                }
                function F(t, n, a, r, s) {
                    if (r && e.isFunction(d.onBeforeWrite)) {
                        var o = d.onBeforeWrite.call(X, r, n, a, d);
                        if (o) {
                            if (o.refreshFromBuffer) {
                                var l = o.refreshFromBuffer;
                                L(!0 === l ? l : l.start, l.end, o.buffer || n), (n = D(!0));
                            }
                            a !== i && (a = o.caret !== i ? o.caret : a);
                        }
                    }
                    t !== i &&
                        (t.inputmask._valueSet(n.join("")),
                        a === i || (r !== i && "blur" === r.type)
                            ? z(t, a, 0 === n.length)
                            : f && r && "input" === r.type
                            ? setTimeout(function () {
                                  O(t, a);
                              }, 0)
                            : O(t, a),
                        !0 === s && ((ee = !0), e(t).trigger("input")));
                }
                function H(t, n, a) {
                    if ((n = n || w(t).match).placeholder !== i || !0 === a) return e.isFunction(n.placeholder) ? n.placeholder(d) : n.placeholder;
                    if (null === n.fn) {
                        if (t > -1 && m().validPositions[t] === i) {
                            var r,
                                s = x(t),
                                o = [];
                            if (s.length > 1 + ("" === s[s.length - 1].match.def ? 1 : 0))
                                for (var l = 0; l < s.length; l++)
                                    if (
                                        !0 !== s[l].match.optionality &&
                                        !0 !== s[l].match.optionalQuantifier &&
                                        (null === s[l].match.fn || r === i || !1 !== s[l].match.fn.test(r.match.def, m(), t, !0, d)) &&
                                        (o.push(s[l]), null === s[l].match.fn && (r = s[l]), o.length > 1 && /[0-9a-bA-Z]/.test(o[0].match.def))
                                    )
                                        return d.placeholder.charAt(t % d.placeholder.length);
                        }
                        return n.def;
                    }
                    return d.placeholder.charAt(t % d.placeholder.length);
                }
                function P(t, r, s, o, l) {
                    function u(e, t) {
                        return -1 !== k().slice(e, E(e)).join("").indexOf(t) && !C(e) && w(e).match.nativeDef === t.charAt(t.length - 1);
                    }
                    var c = o.slice(),
                        h = "",
                        f = -1,
                        p = i;
                    if ((g(), s || !0 === d.autoUnmask)) f = E(f);
                    else {
                        var y = k().slice(0, E(-1)).join(""),
                            v = c.join("").match(new RegExp("^" + a.escapeRegex(y), "g"));
                        v && v.length > 0 && (c.splice(0, v.length * y.length), (f = E(f)));
                    }
                    if (
                        (-1 === f ? ((m().p = E(f)), (f = 0)) : (m().p = f),
                        e.each(c, function (n, a) {
                            if (a !== i)
                                if (m().validPositions[n] === i && c[n] === H(n) && C(n, !0) && !1 === Y(n, c[n], !0, i, i, !0)) m().p++;
                                else {
                                    var r = new e.Event("_checkval");
                                    (r.which = a.charCodeAt(0)), (h += a);
                                    var o = _(i, !0),
                                        l = m().validPositions[o],
                                        y = b(o + 1, l ? l.locator.slice() : i, o);
                                    if (!u(f, h) || s || d.autoUnmask) {
                                        var v = s ? n : null == y.match.fn && y.match.optionality && o + 1 < m().p ? o + 1 : m().p;
                                        (p = ae.keypressEvent.call(t, r, !0, !1, s, v)), (f = v + 1), (h = "");
                                    } else p = ae.keypressEvent.call(t, r, !0, !1, !0, o + 1);
                                    if (!1 !== p && !s && e.isFunction(d.onBeforeWrite)) {
                                        var w = p;
                                        if (((p = d.onBeforeWrite.call(X, r, D(), p.forwardPosition, d)), (p = e.extend(w, p)) && p.refreshFromBuffer)) {
                                            var M = p.refreshFromBuffer;
                                            L(!0 === M ? M : M.start, M.end, p.buffer), g(!0), p.caret && ((m().p = p.caret), (p.forwardPosition = p.caret));
                                        }
                                    }
                                }
                        }),
                        r)
                    ) {
                        var M = i;
                        n.activeElement === t && p && (M = d.numericInput ? A(p.forwardPosition) : p.forwardPosition), F(t, D(), M, l || new e.Event("checkval"), l && "input" === l.type);
                    }
                }
                function j(t) {
                    if (t) {
                        if (t.inputmask === i) return t.value;
                        t.inputmask && t.inputmask.refreshValue && ae.setValueEvent.call(t);
                    }
                    var n = [],
                        a = m().validPositions;
                    for (var r in a) a[r].match && null != a[r].match.fn && n.push(a[r].input);
                    var s = 0 === n.length ? "" : (Z ? n.reverse() : n).join("");
                    if (e.isFunction(d.onUnMask)) {
                        var o = (Z ? D().slice().reverse() : D()).join("");
                        s = d.onUnMask.call(X, o, s, d);
                    }
                    return s;
                }
                function O(e, a, r, s) {
                    function o(e) {
                        return !0 === s || !Z || "number" != typeof e || (d.greedy && "" === d.placeholder) || (e = D().join("").length - e), e;
                    }
                    var l;
                    if (a === i)
                        return (
                            e.setSelectionRange
                                ? ((a = e.selectionStart), (r = e.selectionEnd))
                                : t.getSelection
                                ? ((l = t.getSelection().getRangeAt(0)).commonAncestorContainer.parentNode !== e && l.commonAncestorContainer !== e) || ((a = l.startOffset), (r = l.endOffset))
                                : n.selection && n.selection.createRange && (r = (a = 0 - (l = n.selection.createRange()).duplicate().moveStart("character", -e.inputmask._valueGet().length)) + l.text.length),
                            { begin: o(a), end: o(r) }
                        );
                    if ((a.begin !== i && ((r = a.end), (a = a.begin)), "number" == typeof a)) {
                        (a = o(a)), (r = "number" == typeof (r = o(r)) ? r : a);
                        var c = parseInt(((e.ownerDocument.defaultView || t).getComputedStyle ? (e.ownerDocument.defaultView || t).getComputedStyle(e, null) : e.currentStyle).fontSize) * r;
                        if (((e.scrollLeft = c > e.scrollWidth ? c : 0), u || !1 !== d.insertMode || a !== r || r++, e.setSelectionRange)) (e.selectionStart = a), (e.selectionEnd = r);
                        else if (t.getSelection) {
                            if (((l = n.createRange()), e.firstChild === i || null === e.firstChild)) {
                                var h = n.createTextNode("");
                                e.appendChild(h);
                            }
                            l.setStart(e.firstChild, a < e.inputmask._valueGet().length ? a : e.inputmask._valueGet().length),
                                l.setEnd(e.firstChild, r < e.inputmask._valueGet().length ? r : e.inputmask._valueGet().length),
                                l.collapse(!0);
                            var f = t.getSelection();
                            f.removeAllRanges(), f.addRange(l);
                        } else e.createTextRange && ((l = e.createTextRange()).collapse(!0), l.moveEnd("character", r), l.moveStart("character", a), l.select());
                        z(e, { begin: a, end: r });
                    }
                }
                function N(t) {
                    var n,
                        a,
                        r = D(),
                        s = r.length,
                        o = _(),
                        l = {},
                        d = m().validPositions[o],
                        u = d !== i ? d.locator.slice() : i;
                    for (n = o + 1; n < r.length; n++) (u = (a = b(n, u, n - 1)).locator.slice()), (l[n] = e.extend(!0, {}, a));
                    var c = d && d.alternation !== i ? d.locator[d.alternation] : i;
                    for (
                        n = s - 1;
                        n > o &&
                        ((a = l[n]).match.optionality ||
                            (a.match.optionalQuantifier && a.match.newBlockMarker) ||
                            (c &&
                                ((c !== l[n].locator[d.alternation] && null != a.match.fn) ||
                                    (null === a.match.fn && a.locator[d.alternation] && T(a.locator[d.alternation].toString().split(","), c.toString().split(",")) && "" !== x(n)[0].def)))) &&
                        r[n] === H(n, a.match);
                        n--
                    )
                        s--;
                    return t ? { l: s, def: l[s] ? l[s].match : i } : s;
                }
                function R(e) {
                    for (
                        var t, n = N(), a = e.length, r = m().validPositions[_()];
                        n < a &&
                        !C(n, !0) &&
                        (t = r !== i ? b(n, r.locator.slice(""), r) : w(n)) &&
                        !0 !== t.match.optionality &&
                        ((!0 !== t.match.optionalQuantifier && !0 !== t.match.newBlockMarker) || (n + 1 === a && "" === (r !== i ? b(n + 1, r.locator.slice(""), r) : w(n + 1)).match.def));

                    )
                        n++;
                    for (; (t = m().validPositions[n - 1]) && t && t.match.optionality && t.input === d.skipOptionalPartCharacter; ) n--;
                    return e.splice(n), e;
                }
                function $(t) {
                    if (e.isFunction(d.isComplete)) return d.isComplete(t, d);
                    if ("*" === d.repeat) return i;
                    var n = !1,
                        a = N(!0),
                        r = A(a.l);
                    if (a.def === i || a.def.newBlockMarker || a.def.optionality || a.def.optionalQuantifier) {
                        n = !0;
                        for (var s = 0; s <= r; s++) {
                            var o = b(s).match;
                            if ((null !== o.fn && m().validPositions[s] === i && !0 !== o.optionality && !0 !== o.optionalQuantifier) || (null === o.fn && t[s] !== H(s, o))) {
                                n = !1;
                                break;
                            }
                        }
                    }
                    return n;
                }
                function W(t, n, r, s, o) {
                    if ((d.numericInput || Z) && (n === a.keyCode.BACKSPACE ? (n = a.keyCode.DELETE) : n === a.keyCode.DELETE && (n = a.keyCode.BACKSPACE), Z)) {
                        var l = r.end;
                        (r.end = r.begin), (r.begin = l);
                    }
                    n === a.keyCode.BACKSPACE && (r.end - r.begin < 1 || !1 === d.insertMode)
                        ? ((r.begin = A(r.begin)), m().validPositions[r.begin] !== i && m().validPositions[r.begin].input === d.groupSeparator && r.begin--)
                        : n === a.keyCode.DELETE &&
                          r.begin === r.end &&
                          ((r.end = C(r.end, !0) && m().validPositions[r.end] && m().validPositions[r.end].input !== d.radixPoint ? r.end + 1 : E(r.end) + 1),
                          m().validPositions[r.begin] !== i && m().validPositions[r.begin].input === d.groupSeparator && r.end++),
                        y(r.begin, r.end, !1, s),
                        !0 !== s &&
                            (function () {
                                if (d.keepStatic) {
                                    for (var n = [], a = _(-1, !0), r = e.extend(!0, {}, m().validPositions), s = m().validPositions[a]; a >= 0; a--) {
                                        var o = m().validPositions[a];
                                        if (o) {
                                            if ((!0 !== o.generatedInput && /[0-9a-bA-Z]/.test(o.input) && n.push(o.input), delete m().validPositions[a], o.alternation !== i && o.locator[o.alternation] !== s.locator[o.alternation]))
                                                break;
                                            s = o;
                                        }
                                    }
                                    if (a > -1)
                                        for (m().p = E(_(-1, !0)); n.length > 0; ) {
                                            var l = new e.Event("keypress");
                                            (l.which = n.pop().charCodeAt(0)), ae.keypressEvent.call(t, l, !0, !1, !1, m().p);
                                        }
                                    else m().validPositions = e.extend(!0, {}, r);
                                }
                            })();
                    var u = _(r.begin, !0);
                    if (u < r.begin) m().p = E(u);
                    else if (!0 !== s && ((m().p = r.begin), !0 !== o)) for (; m().p < u && m().validPositions[m().p] === i; ) m().p++;
                }
                function B(i) {
                    var a = (i.ownerDocument.defaultView || t).getComputedStyle(i, null),
                        r = n.createElement("div");
                    (r.style.width = a.width),
                        (r.style.textAlign = a.textAlign),
                        ((G = n.createElement("div")).className = "im-colormask"),
                        i.parentNode.insertBefore(G, i),
                        i.parentNode.removeChild(i),
                        G.appendChild(r),
                        G.appendChild(i),
                        (i.style.left = r.offsetLeft + "px"),
                        e(i).on("click", function (e) {
                            return (
                                O(
                                    i,
                                    (function (e) {
                                        var t,
                                            r = n.createElement("span");
                                        for (var s in a) isNaN(s) && -1 !== s.indexOf("font") && (r.style[s] = a[s]);
                                        (r.style.textTransform = a.textTransform),
                                            (r.style.letterSpacing = a.letterSpacing),
                                            (r.style.position = "absolute"),
                                            (r.style.height = "auto"),
                                            (r.style.width = "auto"),
                                            (r.style.visibility = "hidden"),
                                            (r.style.whiteSpace = "nowrap"),
                                            n.body.appendChild(r);
                                        var o,
                                            l = i.inputmask._valueGet(),
                                            d = 0;
                                        for (t = 0, o = l.length; t <= o; t++) {
                                            if (((r.innerHTML += l.charAt(t) || "_"), r.offsetWidth >= e)) {
                                                var u = e - d,
                                                    c = r.offsetWidth - e;
                                                (r.innerHTML = l.charAt(t)), (t = (u -= r.offsetWidth / 3) < c ? t - 1 : t);
                                                break;
                                            }
                                            d = r.offsetWidth;
                                        }
                                        return n.body.removeChild(r), t;
                                    })(e.clientX)
                                ),
                                ae.clickEvent.call(i, [e])
                            );
                        }),
                        e(i).on("keydown", function (e) {
                            e.shiftKey ||
                                !1 === d.insertMode ||
                                setTimeout(function () {
                                    z(i);
                                }, 0);
                        });
                }
                function z(e, t, a) {
                    function r() {
                        h || (null !== o.fn && l.input !== i) ? h && ((null !== o.fn && l.input !== i) || "" === o.def) && ((h = !1), (c += "</span>")) : ((h = !0), (c += "<span class='im-static'>"));
                    }
                    function s(i) {
                        (!0 !== i && f !== t.begin) || n.activeElement !== e || (c += "<span class='im-caret' style='border-right-width: 1px;border-right-style: solid;'></span>");
                    }
                    var o,
                        l,
                        u,
                        c = "",
                        h = !1,
                        f = 0;
                    if (G !== i) {
                        var p = D();
                        if ((t === i ? (t = O(e)) : t.begin === i && (t = { begin: t, end: t }), !0 !== a)) {
                            var g = _();
                            do {
                                s(),
                                    m().validPositions[f]
                                        ? ((l = m().validPositions[f]), (o = l.match), (u = l.locator.slice()), r(), (c += p[f]))
                                        : ((l = b(f, u, f - 1)),
                                          (o = l.match),
                                          (u = l.locator.slice()),
                                          (!1 === d.jitMasking || f < g || ("number" == typeof d.jitMasking && isFinite(d.jitMasking) && d.jitMasking > f)) && (r(), (c += H(f, o)))),
                                    f++;
                            } while (((V === i || f < V) && (null !== o.fn || "" !== o.def)) || g > f || h);
                            -1 === c.indexOf("im-caret") && s(!0), h && r();
                        }
                        var y = G.getElementsByTagName("div")[0];
                        (y.innerHTML = c), e.inputmask.positionColorMask(e, y);
                    }
                }
                (o = o || this.maskset), (d = d || this.opts);
                var U,
                    q,
                    V,
                    G,
                    J,
                    X = this,
                    K = this.el,
                    Z = this.isRTL,
                    Q = !1,
                    ee = !1,
                    te = !1,
                    ne = !1,
                    ie = {
                        on: function (t, n, r) {
                            var s = function (t) {
                                if (this.inputmask === i && "FORM" !== this.nodeName) {
                                    var n = e.data(this, "_inputmask_opts");
                                    n ? new a(n).mask(this) : ie.off(this);
                                } else {
                                    if (
                                        "setvalue" === t.type ||
                                        "FORM" === this.nodeName ||
                                        !(this.disabled || (this.readOnly && !(("keydown" === t.type && t.ctrlKey && 67 === t.keyCode) || (!1 === d.tabThrough && t.keyCode === a.keyCode.TAB))))
                                    ) {
                                        switch (t.type) {
                                            case "input":
                                                if (!0 === ee) return (ee = !1), t.preventDefault();
                                                break;
                                            case "keydown":
                                                (Q = !1), (ee = !1);
                                                break;
                                            case "keypress":
                                                if (!0 === Q) return t.preventDefault();
                                                Q = !0;
                                                break;
                                            case "click":
                                                if (c || h) {
                                                    var s = this,
                                                        o = arguments;
                                                    return (
                                                        setTimeout(function () {
                                                            r.apply(s, o);
                                                        }, 0),
                                                        !1
                                                    );
                                                }
                                        }
                                        var l = r.apply(this, arguments);
                                        return !1 === l && (t.preventDefault(), t.stopPropagation()), l;
                                    }
                                    t.preventDefault();
                                }
                            };
                            (t.inputmask.events[n] = t.inputmask.events[n] || []), t.inputmask.events[n].push(s), -1 !== e.inArray(n, ["submit", "reset"]) ? null !== t.form && e(t.form).on(n, s) : e(t).on(n, s);
                        },
                        off: function (t, n) {
                            var i;
                            t.inputmask &&
                                t.inputmask.events &&
                                (n ? ((i = [])[n] = t.inputmask.events[n]) : (i = t.inputmask.events),
                                e.each(i, function (n, i) {
                                    for (; i.length > 0; ) {
                                        var a = i.pop();
                                        -1 !== e.inArray(n, ["submit", "reset"]) ? null !== t.form && e(t.form).off(n, a) : e(t).off(n, a);
                                    }
                                    delete t.inputmask.events[n];
                                }));
                        },
                    },
                    ae = {
                        keydownEvent: function (t) {
                            var i = this,
                                r = e(i),
                                s = t.keyCode,
                                o = O(i);
                            if (
                                s === a.keyCode.BACKSPACE ||
                                s === a.keyCode.DELETE ||
                                (h && s === a.keyCode.BACKSPACE_SAFARI) ||
                                (t.ctrlKey &&
                                    s === a.keyCode.X &&
                                    !(function (e) {
                                        var t = n.createElement("input"),
                                            i = "oncut",
                                            a = i in t;
                                        return a || (t.setAttribute(i, "return;"), (a = "function" == typeof t[i])), (t = null), a;
                                    })())
                            )
                                t.preventDefault(), W(i, s, o), F(i, D(!0), m().p, t, i.inputmask._valueGet() !== D().join("")), i.inputmask._valueGet() === k().join("") ? r.trigger("cleared") : !0 === $(D()) && r.trigger("complete");
                            else if (s === a.keyCode.END || s === a.keyCode.PAGE_DOWN) {
                                t.preventDefault();
                                var l = E(_());
                                d.insertMode || l !== m().maskLength || t.shiftKey || l--, O(i, t.shiftKey ? o.begin : l, l, !0);
                            } else
                                (s === a.keyCode.HOME && !t.shiftKey) || s === a.keyCode.PAGE_UP
                                    ? (t.preventDefault(), O(i, 0, t.shiftKey ? o.begin : 0, !0))
                                    : ((d.undoOnEscape && s === a.keyCode.ESCAPE) || (90 === s && t.ctrlKey)) && !0 !== t.altKey
                                    ? (P(i, !0, !1, U.split("")), r.trigger("click"))
                                    : s !== a.keyCode.INSERT || t.shiftKey || t.ctrlKey
                                    ? !0 === d.tabThrough && s === a.keyCode.TAB
                                        ? (!0 === t.shiftKey
                                              ? (null === w(o.begin).match.fn && (o.begin = E(o.begin)), (o.end = A(o.begin, !0)), (o.begin = A(o.end, !0)))
                                              : ((o.begin = E(o.begin, !0)), (o.end = E(o.begin, !0)), o.end < m().maskLength && o.end--),
                                          o.begin < m().maskLength && (t.preventDefault(), O(i, o.begin, o.end)))
                                        : t.shiftKey ||
                                          (!1 === d.insertMode &&
                                              (s === a.keyCode.RIGHT
                                                  ? setTimeout(function () {
                                                        var e = O(i);
                                                        O(i, e.begin);
                                                    }, 0)
                                                  : s === a.keyCode.LEFT &&
                                                    setTimeout(function () {
                                                        var e = O(i);
                                                        O(i, Z ? e.begin + 1 : e.begin - 1);
                                                    }, 0)))
                                    : ((d.insertMode = !d.insertMode), O(i, d.insertMode || o.begin !== m().maskLength ? o.begin : o.begin - 1));
                            d.onKeyDown.call(this, t, D(), O(i).begin, d), (te = -1 !== e.inArray(s, d.ignorables));
                        },
                        keypressEvent: function (t, n, r, s, o) {
                            var l = this,
                                u = e(l),
                                c = t.which || t.charCode || t.keyCode;
                            if (!(!0 === n || (t.ctrlKey && t.altKey)) && (t.ctrlKey || t.metaKey || te))
                                return (
                                    c === a.keyCode.ENTER &&
                                        U !== D().join("") &&
                                        ((U = D().join("")),
                                        setTimeout(function () {
                                            u.trigger("change");
                                        }, 0)),
                                    !0
                                );
                            if (c) {
                                46 === c && !1 === t.shiftKey && "" !== d.radixPoint && (c = d.radixPoint.charCodeAt(0));
                                var h,
                                    f = n ? { begin: o, end: o } : O(l),
                                    p = String.fromCharCode(c);
                                m().writeOutBuffer = !0;
                                var _ = Y(f, p, s);
                                if (
                                    (!1 !== _ && (g(!0), (h = _.caret !== i ? _.caret : n ? _.pos + 1 : E(_.pos)), (m().p = h)),
                                    !1 !== r &&
                                        (setTimeout(function () {
                                            d.onKeyValidation.call(l, c, _, d);
                                        }, 0),
                                        m().writeOutBuffer && !1 !== _))
                                ) {
                                    var y = D();
                                    F(l, y, d.numericInput && _.caret === i ? A(h) : h, t, !0 !== n),
                                        !0 !== n &&
                                            setTimeout(function () {
                                                !0 === $(y) && u.trigger("complete");
                                            }, 0);
                                }
                                if ((t.preventDefault(), n)) return !1 !== _ && (_.forwardPosition = h), _;
                            }
                        },
                        pasteEvent: function (n) {
                            var i,
                                a = this,
                                r = n.originalEvent || n,
                                s = e(a),
                                o = a.inputmask._valueGet(!0),
                                l = O(a);
                            Z && ((i = l.end), (l.end = l.begin), (l.begin = i));
                            var u = o.substr(0, l.begin),
                                c = o.substr(l.end, o.length);
                            if (
                                (u === (Z ? k().reverse() : k()).slice(0, l.begin).join("") && (u = ""),
                                c === (Z ? k().reverse() : k()).slice(l.end).join("") && (c = ""),
                                Z && ((i = u), (u = c), (c = i)),
                                t.clipboardData && t.clipboardData.getData)
                            )
                                o = u + t.clipboardData.getData("Text") + c;
                            else {
                                if (!r.clipboardData || !r.clipboardData.getData) return !0;
                                o = u + r.clipboardData.getData("text/plain") + c;
                            }
                            var h = o;
                            if (e.isFunction(d.onBeforePaste)) {
                                if (!1 === (h = d.onBeforePaste.call(X, o, d))) return n.preventDefault();
                                h || (h = o);
                            }
                            return P(a, !1, !1, Z ? h.split("").reverse() : h.toString().split("")), F(a, D(), E(_()), n, U !== D().join("")), !0 === $(D()) && s.trigger("complete"), n.preventDefault();
                        },
                        inputFallBackEvent: function (t) {
                            var n = this,
                                i = n.inputmask._valueGet();
                            if (D().join("") !== i) {
                                var r = O(n);
                                if (
                                    !1 ===
                                    (function (t, n, i) {
                                        if (
                                            ("." === n.charAt(i.begin - 1) && "" !== d.radixPoint && (((n = n.split(""))[i.begin - 1] = d.radixPoint.charAt(0)), (n = n.join(""))),
                                            n.charAt(i.begin - 1) === d.radixPoint && n.length > D().length)
                                        ) {
                                            var a = new e.Event("keypress");
                                            return (a.which = d.radixPoint.charCodeAt(0)), ae.keypressEvent.call(t, a, !0, !0, !1, i.begin - 1), !1;
                                        }
                                    })(n, i, r)
                                )
                                    return !1;
                                if (
                                    ((i = i.replace(new RegExp("(" + a.escapeRegex(k().join("")) + ")*"), "")),
                                    !1 ===
                                        (function (t, n, i) {
                                            if (c) {
                                                var a = n.replace(D().join(""), "");
                                                if (1 === a.length) {
                                                    var r = new e.Event("keypress");
                                                    return (r.which = a.charCodeAt(0)), ae.keypressEvent.call(t, r, !0, !0, !1, m().validPositions[i.begin - 1] ? i.begin : i.begin - 1), !1;
                                                }
                                            }
                                        })(n, i, r))
                                )
                                    return !1;
                                r.begin > i.length && (O(n, i.length), (r = O(n)));
                                var s = D().join(""),
                                    o = i.substr(0, r.begin),
                                    l = i.substr(r.begin),
                                    u = s.substr(0, r.begin),
                                    h = s.substr(r.begin),
                                    f = r,
                                    p = "",
                                    g = !1;
                                if (o !== u) {
                                    f.begin = 0;
                                    for (var _ = (g = o.length >= u.length) ? o.length : u.length, y = 0; o.charAt(y) === u.charAt(y) && y < _; y++) f.begin++;
                                    g && (p += o.slice(f.begin, f.end));
                                }
                                l !== h && (l.length > h.length ? g && (f.end = f.begin) : l.length < h.length ? (f.end += h.length - l.length) : l.charAt(0) !== h.charAt(0) && f.end++),
                                    F(n, D(), f),
                                    p.length > 0
                                        ? e.each(p.split(""), function (t, i) {
                                              var a = new e.Event("keypress");
                                              (a.which = i.charCodeAt(0)), (te = !1), ae.keypressEvent.call(n, a);
                                          })
                                        : (f.begin === f.end - 1 && O(n, A(f.begin + 1), f.end), (t.keyCode = a.keyCode.DELETE), ae.keydownEvent.call(n, t)),
                                    t.preventDefault();
                            }
                        },
                        setValueEvent: function (t) {
                            this.inputmask.refreshValue = !1;
                            var n = this,
                                i = n.inputmask._valueGet(!0);
                            e.isFunction(d.onBeforeMask) && (i = d.onBeforeMask.call(X, i, d) || i),
                                (i = i.split("")),
                                P(n, !0, !1, Z ? i.reverse() : i),
                                (U = D().join("")),
                                (d.clearMaskOnLostFocus || d.clearIncomplete) && n.inputmask._valueGet() === k().join("") && n.inputmask._valueSet("");
                        },
                        focusEvent: function (e) {
                            var t = this,
                                n = t.inputmask._valueGet();
                            d.showMaskOnFocus && (!d.showMaskOnHover || (d.showMaskOnHover && "" === n)) && (t.inputmask._valueGet() !== D().join("") ? F(t, D(), E(_())) : !1 === ne && O(t, E(_()))),
                                !0 === d.positionCaretOnTab && !1 === ne && "" !== n && (F(t, D(), O(t)), ae.clickEvent.apply(t, [e, !0])),
                                (U = D().join(""));
                        },
                        mouseleaveEvent: function (e) {
                            var t = this;
                            if (((ne = !1), d.clearMaskOnLostFocus && n.activeElement !== t)) {
                                var i = D().slice(),
                                    a = t.inputmask._valueGet();
                                a !== t.getAttribute("placeholder") && "" !== a && (-1 === _() && a === k().join("") ? (i = []) : R(i), F(t, i));
                            }
                        },
                        clickEvent: function (t, a) {
                            function r(t) {
                                if ("" !== d.radixPoint) {
                                    var n = m().validPositions;
                                    if (n[t] === i || n[t].input === H(t)) {
                                        if (t < E(-1)) return !0;
                                        var a = e.inArray(d.radixPoint, D());
                                        if (-1 !== a) {
                                            for (var r in n) if (a < r && n[r].input !== H(r)) return !1;
                                            return !0;
                                        }
                                    }
                                }
                                return !1;
                            }
                            var s = this;
                            setTimeout(function () {
                                if (n.activeElement === s) {
                                    var e = O(s);
                                    if ((a && (Z ? (e.end = e.begin) : (e.begin = e.end)), e.begin === e.end))
                                        switch (d.positionCaretOnClick) {
                                            case "none":
                                                break;
                                            case "radixFocus":
                                                if (r(e.begin)) {
                                                    var t = D().join("").indexOf(d.radixPoint);
                                                    O(s, d.numericInput ? E(t) : t);
                                                    break;
                                                }
                                            default:
                                                var o = e.begin,
                                                    l = _(o, !0),
                                                    u = E(l);
                                                if (o < u) O(s, C(o, !0) || C(o - 1, !0) ? o : E(o));
                                                else {
                                                    var c = m().validPositions[l],
                                                        h = b(u, c ? c.match.locator : i, c),
                                                        f = H(u, h.match);
                                                    if (("" !== f && D()[u] !== f && !0 !== h.match.optionalQuantifier && !0 !== h.match.newBlockMarker) || (!C(u, !0) && h.match.def === f)) {
                                                        var p = E(u);
                                                        (o >= p || o === u) && (u = p);
                                                    }
                                                    O(s, u);
                                                }
                                        }
                                }
                            }, 0);
                        },
                        dblclickEvent: function (e) {
                            var t = this;
                            setTimeout(function () {
                                O(t, 0, E(_()));
                            }, 0);
                        },
                        cutEvent: function (i) {
                            var r = this,
                                s = e(r),
                                o = O(r),
                                l = i.originalEvent || i,
                                d = t.clipboardData || l.clipboardData,
                                u = Z ? D().slice(o.end, o.begin) : D().slice(o.begin, o.end);
                            d.setData("text", Z ? u.reverse().join("") : u.join("")),
                                n.execCommand && n.execCommand("copy"),
                                W(r, a.keyCode.DELETE, o),
                                F(r, D(), m().p, i, U !== D().join("")),
                                r.inputmask._valueGet() === k().join("") && s.trigger("cleared");
                        },
                        blurEvent: function (t) {
                            var n = e(this),
                                a = this;
                            if (a.inputmask) {
                                var r = a.inputmask._valueGet(),
                                    s = D().slice();
                                "" !== r &&
                                    (d.clearMaskOnLostFocus && (-1 === _() && r === k().join("") ? (s = []) : R(s)),
                                    !1 === $(s) &&
                                        (setTimeout(function () {
                                            n.trigger("incomplete");
                                        }, 0),
                                        d.clearIncomplete && (g(), (s = d.clearMaskOnLostFocus ? [] : k().slice()))),
                                    F(a, s, i, t)),
                                    U !== D().join("") && ((U = s.join("")), n.trigger("change"));
                            }
                        },
                        mouseenterEvent: function (e) {
                            var t = this;
                            (ne = !0), n.activeElement !== t && d.showMaskOnHover && t.inputmask._valueGet() !== D().join("") && F(t, D());
                        },
                        submitEvent: function (e) {
                            U !== D().join("") && q.trigger("change"),
                                d.clearMaskOnLostFocus && -1 === _() && K.inputmask._valueGet && K.inputmask._valueGet() === k().join("") && K.inputmask._valueSet(""),
                                d.removeMaskOnSubmit &&
                                    (K.inputmask._valueSet(K.inputmask.unmaskedvalue(), !0),
                                    setTimeout(function () {
                                        F(K, D());
                                    }, 0));
                        },
                        resetEvent: function (e) {
                            (K.inputmask.refreshValue = !0),
                                setTimeout(function () {
                                    q.trigger("setvalue");
                                }, 0);
                        },
                    };
                if (
                    ((a.prototype.positionColorMask = function (e, t) {
                        e.style.left = t.offsetLeft + "px";
                    }),
                    r !== i)
                )
                    switch (r.action) {
                        case "isComplete":
                            return (K = r.el), $(D());
                        case "unmaskedvalue":
                            return (
                                (K !== i && r.value === i) ||
                                    ((J = r.value),
                                    (J = ((e.isFunction(d.onBeforeMask) && d.onBeforeMask.call(X, J, d)) || J).split("")),
                                    P(i, !1, !1, Z ? J.reverse() : J),
                                    e.isFunction(d.onBeforeWrite) && d.onBeforeWrite.call(X, i, D(), 0, d)),
                                j(K)
                            );
                        case "mask":
                            !(function (t) {
                                ie.off(t);
                                var a = (function (t, a) {
                                    var r = t.getAttribute("type"),
                                        o = ("INPUT" === t.tagName && -1 !== e.inArray(r, a.supportsInputType)) || t.isContentEditable || "TEXTAREA" === t.tagName;
                                    if (!o)
                                        if ("INPUT" === t.tagName) {
                                            var l = n.createElement("input");
                                            l.setAttribute("type", r), (o = "text" === l.type), (l = null);
                                        } else o = "partial";
                                    return (
                                        !1 !== o
                                            ? (function (t) {
                                                  function r() {
                                                      return this.inputmask
                                                          ? this.inputmask.opts.autoUnmask
                                                              ? this.inputmask.unmaskedvalue()
                                                              : -1 !== _() || !0 !== a.nullable
                                                              ? n.activeElement === this && a.clearMaskOnLostFocus
                                                                  ? (Z ? R(D().slice()).reverse() : R(D().slice())).join("")
                                                                  : l.call(this)
                                                              : ""
                                                          : l.call(this);
                                                  }
                                                  function o(t) {
                                                      d.call(this, t), this.inputmask && e(this).trigger("setvalue");
                                                  }
                                                  var l, d;
                                                  if (!t.inputmask.__valueGet) {
                                                      if (!0 !== a.noValuePatching) {
                                                          if (Object.getOwnPropertyDescriptor) {
                                                              "function" != typeof Object.getPrototypeOf &&
                                                                  (Object.getPrototypeOf =
                                                                      "object" === s("test".__proto__)
                                                                          ? function (e) {
                                                                                return e.__proto__;
                                                                            }
                                                                          : function (e) {
                                                                                return e.constructor.prototype;
                                                                            });
                                                              var u = Object.getPrototypeOf ? Object.getOwnPropertyDescriptor(Object.getPrototypeOf(t), "value") : i;
                                                              u && u.get && u.set
                                                                  ? ((l = u.get), (d = u.set), Object.defineProperty(t, "value", { get: r, set: o, configurable: !0 }))
                                                                  : "INPUT" !== t.tagName &&
                                                                    ((l = function () {
                                                                        return this.textContent;
                                                                    }),
                                                                    (d = function (e) {
                                                                        this.textContent = e;
                                                                    }),
                                                                    Object.defineProperty(t, "value", { get: r, set: o, configurable: !0 }));
                                                          } else
                                                              n.__lookupGetter__ &&
                                                                  t.__lookupGetter__("value") &&
                                                                  ((l = t.__lookupGetter__("value")), (d = t.__lookupSetter__("value")), t.__defineGetter__("value", r), t.__defineSetter__("value", o));
                                                          (t.inputmask.__valueGet = l), (t.inputmask.__valueSet = d);
                                                      }
                                                      (t.inputmask._valueGet = function (e) {
                                                          return Z && !0 !== e ? l.call(this.el).split("").reverse().join("") : l.call(this.el);
                                                      }),
                                                          (t.inputmask._valueSet = function (e, t) {
                                                              d.call(this.el, null === e || e === i ? "" : !0 !== t && Z ? e.split("").reverse().join("") : e);
                                                          }),
                                                          l === i &&
                                                              ((l = function () {
                                                                  return this.value;
                                                              }),
                                                              (d = function (e) {
                                                                  this.value = e;
                                                              }),
                                                              (function (t) {
                                                                  if (e.valHooks && (e.valHooks[t] === i || !0 !== e.valHooks[t].inputmaskpatch)) {
                                                                      var n =
                                                                              e.valHooks[t] && e.valHooks[t].get
                                                                                  ? e.valHooks[t].get
                                                                                  : function (e) {
                                                                                        return e.value;
                                                                                    },
                                                                          r =
                                                                              e.valHooks[t] && e.valHooks[t].set
                                                                                  ? e.valHooks[t].set
                                                                                  : function (e, t) {
                                                                                        return (e.value = t), e;
                                                                                    };
                                                                      e.valHooks[t] = {
                                                                          get: function (e) {
                                                                              if (e.inputmask) {
                                                                                  if (e.inputmask.opts.autoUnmask) return e.inputmask.unmaskedvalue();
                                                                                  var t = n(e);
                                                                                  return -1 !== _(i, i, e.inputmask.maskset.validPositions) || !0 !== a.nullable ? t : "";
                                                                              }
                                                                              return n(e);
                                                                          },
                                                                          set: function (t, n) {
                                                                              var i,
                                                                                  a = e(t);
                                                                              return (i = r(t, n)), t.inputmask && a.trigger("setvalue"), i;
                                                                          },
                                                                          inputmaskpatch: !0,
                                                                      };
                                                                  }
                                                              })(t.type),
                                                              (function (t) {
                                                                  ie.on(t, "mouseenter", function (t) {
                                                                      var n = e(this);
                                                                      this.inputmask._valueGet() !== D().join("") && n.trigger("setvalue");
                                                                  });
                                                              })(t));
                                                  }
                                              })(t)
                                            : (t.inputmask = i),
                                        o
                                    );
                                })(t, d);
                                if (
                                    !1 !== a &&
                                    ((q = e((K = t))),
                                    -1 === (V = K !== i ? K.maxLength : i) && (V = i),
                                    !0 === d.colorMask && B(K),
                                    f && (K.hasOwnProperty("inputmode") && ((K.inputmode = d.inputmode), K.setAttribute("inputmode", d.inputmode)), "rtfm" === d.androidHack && (!0 !== d.colorMask && B(K), (K.type = "password"))),
                                    !0 === a &&
                                        (ie.on(K, "submit", ae.submitEvent),
                                        ie.on(K, "reset", ae.resetEvent),
                                        ie.on(K, "mouseenter", ae.mouseenterEvent),
                                        ie.on(K, "blur", ae.blurEvent),
                                        ie.on(K, "focus", ae.focusEvent),
                                        ie.on(K, "mouseleave", ae.mouseleaveEvent),
                                        !0 !== d.colorMask && ie.on(K, "click", ae.clickEvent),
                                        ie.on(K, "dblclick", ae.dblclickEvent),
                                        ie.on(K, "paste", ae.pasteEvent),
                                        ie.on(K, "dragdrop", ae.pasteEvent),
                                        ie.on(K, "drop", ae.pasteEvent),
                                        ie.on(K, "cut", ae.cutEvent),
                                        ie.on(K, "complete", d.oncomplete),
                                        ie.on(K, "incomplete", d.onincomplete),
                                        ie.on(K, "cleared", d.oncleared),
                                        f || !0 === d.inputEventOnly ? K.removeAttribute("maxLength") : (ie.on(K, "keydown", ae.keydownEvent), ie.on(K, "keypress", ae.keypressEvent)),
                                        ie.on(K, "compositionstart", e.noop),
                                        ie.on(K, "compositionupdate", e.noop),
                                        ie.on(K, "compositionend", e.noop),
                                        ie.on(K, "keyup", e.noop),
                                        ie.on(K, "input", ae.inputFallBackEvent),
                                        ie.on(K, "beforeinput", e.noop)),
                                    ie.on(K, "setvalue", ae.setValueEvent),
                                    (U = k().join("")),
                                    "" !== K.inputmask._valueGet(!0) || !1 === d.clearMaskOnLostFocus || n.activeElement === K)
                                ) {
                                    var r = (e.isFunction(d.onBeforeMask) && d.onBeforeMask.call(X, K.inputmask._valueGet(!0), d)) || K.inputmask._valueGet(!0);
                                    "" !== r && P(K, !0, !1, Z ? r.split("").reverse() : r.split(""));
                                    var o = D().slice();
                                    (U = o.join("")), !1 === $(o) && d.clearIncomplete && g(), d.clearMaskOnLostFocus && n.activeElement !== K && (-1 === _() ? (o = []) : R(o)), F(K, o), n.activeElement === K && O(K, E(_()));
                                }
                            })(K);
                            break;
                        case "format":
                            return (
                                (J = ((e.isFunction(d.onBeforeMask) && d.onBeforeMask.call(X, r.value, d)) || r.value).split("")),
                                P(i, !0, !1, Z ? J.reverse() : J),
                                r.metadata ? { value: Z ? D().slice().reverse().join("") : D().join(""), metadata: l.call(this, { action: "getmetadata" }, o, d) } : Z ? D().slice().reverse().join("") : D().join("")
                            );
                        case "isValid":
                            r.value ? ((J = r.value.split("")), P(i, !0, !0, Z ? J.reverse() : J)) : (r.value = D().join(""));
                            for (var re = D(), se = N(), oe = re.length - 1; oe > se && !C(oe); oe--);
                            return re.splice(se, oe + 1 - se), $(re) && r.value === D().join("");
                        case "getemptymask":
                            return k().join("");
                        case "remove":
                            return (
                                K &&
                                    K.inputmask &&
                                    ((q = e(K)),
                                    K.inputmask._valueSet(d.autoUnmask ? j(K) : K.inputmask._valueGet(!0)),
                                    ie.off(K),
                                    Object.getOwnPropertyDescriptor && Object.getPrototypeOf
                                        ? Object.getOwnPropertyDescriptor(Object.getPrototypeOf(K), "value") &&
                                          K.inputmask.__valueGet &&
                                          Object.defineProperty(K, "value", { get: K.inputmask.__valueGet, set: K.inputmask.__valueSet, configurable: !0 })
                                        : n.__lookupGetter__ && K.__lookupGetter__("value") && K.inputmask.__valueGet && (K.__defineGetter__("value", K.inputmask.__valueGet), K.__defineSetter__("value", K.inputmask.__valueSet)),
                                    (K.inputmask = i)),
                                K
                            );
                        case "getmetadata":
                            if (e.isArray(o.metadata)) {
                                var le = p(!0, 0, !1).join("");
                                return (
                                    e.each(o.metadata, function (e, t) {
                                        if (t.mask === le) return (le = t), !1;
                                    }),
                                    le
                                );
                            }
                            return o.metadata;
                    }
            }
            var d = navigator.userAgent,
                u = /mobile/i.test(d),
                c = /iemobile/i.test(d),
                h = /iphone/i.test(d) && !c,
                f = /android/i.test(d) && !c;
            return (
                (a.prototype = {
                    dataAttribute: "data-inputmask",
                    defaults: {
                        placeholder: "_",
                        optionalmarker: { start: "[", end: "]" },
                        quantifiermarker: { start: "{", end: "}" },
                        groupmarker: { start: "(", end: ")" },
                        alternatormarker: "|",
                        escapeChar: "\\",
                        mask: null,
                        regex: null,
                        oncomplete: e.noop,
                        onincomplete: e.noop,
                        oncleared: e.noop,
                        repeat: 0,
                        greedy: !0,
                        autoUnmask: !1,
                        removeMaskOnSubmit: !1,
                        clearMaskOnLostFocus: !0,
                        insertMode: !0,
                        clearIncomplete: !1,
                        alias: null,
                        onKeyDown: e.noop,
                        onBeforeMask: null,
                        onBeforePaste: function (t, n) {
                            return e.isFunction(n.onBeforeMask) ? n.onBeforeMask.call(this, t, n) : t;
                        },
                        onBeforeWrite: null,
                        onUnMask: null,
                        showMaskOnFocus: !0,
                        showMaskOnHover: !0,
                        onKeyValidation: e.noop,
                        skipOptionalPartCharacter: " ",
                        numericInput: !1,
                        rightAlign: !1,
                        undoOnEscape: !0,
                        radixPoint: "",
                        radixPointDefinitionSymbol: i,
                        groupSeparator: "",
                        keepStatic: null,
                        positionCaretOnTab: !0,
                        tabThrough: !1,
                        supportsInputType: ["text", "tel", "password"],
                        ignorables: [8, 9, 13, 19, 27, 33, 34, 35, 36, 37, 38, 39, 40, 45, 46, 93, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 0, 229],
                        isComplete: null,
                        canClearPosition: e.noop,
                        preValidation: null,
                        postValidation: null,
                        staticDefinitionSymbol: i,
                        jitMasking: !1,
                        nullable: !0,
                        inputEventOnly: !1,
                        noValuePatching: !1,
                        positionCaretOnClick: "lvp",
                        casing: null,
                        inputmode: "verbatim",
                        colorMask: !1,
                        androidHack: !1,
                        importDataAttributes: !0,
                    },
                    definitions: {
                        9: { validator: "[0-9１-９]", cardinality: 1, definitionSymbol: "*" },
                        a: { validator: "[A-Za-zА-яЁёÀ-ÿµ]", cardinality: 1, definitionSymbol: "*" },
                        "*": { validator: "[0-9１-９A-Za-zА-яЁёÀ-ÿµ]", cardinality: 1 },
                    },
                    aliases: {},
                    masksCache: {},
                    mask: function (s) {
                        function d(n, a, s, o) {
                            if (!0 === a.importDataAttributes) {
                                var l,
                                    d,
                                    u,
                                    c,
                                    h = function (e, a) {
                                        null !== (a = a !== i ? a : n.getAttribute(o + "-" + e)) && ("string" == typeof a && (0 === e.indexOf("on") ? (a = t[a]) : "false" === a ? (a = !1) : "true" === a && (a = !0)), (s[e] = a));
                                    },
                                    f = n.getAttribute(o);
                                if ((f && "" !== f && ((f = f.replace(new RegExp("'", "g"), '"')), (d = JSON.parse("{" + f + "}"))), d))
                                    for (c in ((u = i), d))
                                        if ("alias" === c.toLowerCase()) {
                                            u = d[c];
                                            break;
                                        }
                                for (l in (h("alias", u), s.alias && r(s.alias, s, a), a)) {
                                    if (d)
                                        for (c in ((u = i), d))
                                            if (c.toLowerCase() === l.toLowerCase()) {
                                                u = d[c];
                                                break;
                                            }
                                    h(l, u);
                                }
                            }
                            return e.extend(!0, a, s), ("rtl" === n.dir || a.rightAlign) && (n.style.textAlign = "right"), ("rtl" === n.dir || a.numericInput) && ((n.dir = "ltr"), n.removeAttribute("dir"), (a.isRTL = !0)), a;
                        }
                        var u = this;
                        return (
                            "string" == typeof s && (s = n.getElementById(s) || n.querySelectorAll(s)),
                            (s = s.nodeName ? [s] : s),
                            e.each(s, function (t, n) {
                                var r = e.extend(!0, {}, u.opts);
                                d(n, r, e.extend(!0, {}, u.userOptions), u.dataAttribute);
                                var s = o(r, u.noMasksCache);
                                s !== i &&
                                    (n.inputmask !== i && ((n.inputmask.opts.autoUnmask = !0), n.inputmask.remove()),
                                    (n.inputmask = new a(i, i, !0)),
                                    (n.inputmask.opts = r),
                                    (n.inputmask.noMasksCache = u.noMasksCache),
                                    (n.inputmask.userOptions = e.extend(!0, {}, u.userOptions)),
                                    (n.inputmask.isRTL = r.isRTL || r.numericInput),
                                    (n.inputmask.el = n),
                                    (n.inputmask.maskset = s),
                                    e.data(n, "_inputmask_opts", r),
                                    l.call(n.inputmask, { action: "mask" }));
                            }),
                            (s && s[0] && s[0].inputmask) || this
                        );
                    },
                    option: function (t, n) {
                        return "string" == typeof t ? this.opts[t] : "object" === (void 0 === t ? "undefined" : s(t)) ? (e.extend(this.userOptions, t), this.el && !0 !== n && this.mask(this.el), this) : void 0;
                    },
                    unmaskedvalue: function (e) {
                        return (this.maskset = this.maskset || o(this.opts, this.noMasksCache)), l.call(this, { action: "unmaskedvalue", value: e });
                    },
                    remove: function () {
                        return l.call(this, { action: "remove" });
                    },
                    getemptymask: function () {
                        return (this.maskset = this.maskset || o(this.opts, this.noMasksCache)), l.call(this, { action: "getemptymask" });
                    },
                    hasMaskedValue: function () {
                        return !this.opts.autoUnmask;
                    },
                    isComplete: function () {
                        return (this.maskset = this.maskset || o(this.opts, this.noMasksCache)), l.call(this, { action: "isComplete" });
                    },
                    getmetadata: function () {
                        return (this.maskset = this.maskset || o(this.opts, this.noMasksCache)), l.call(this, { action: "getmetadata" });
                    },
                    isValid: function (e) {
                        return (this.maskset = this.maskset || o(this.opts, this.noMasksCache)), l.call(this, { action: "isValid", value: e });
                    },
                    format: function (e, t) {
                        return (this.maskset = this.maskset || o(this.opts, this.noMasksCache)), l.call(this, { action: "format", value: e, metadata: t });
                    },
                    analyseMask: function (t, n, r) {
                        function s(e, t, n, i) {
                            (this.matches = []),
                                (this.openGroup = e || !1),
                                (this.alternatorGroup = !1),
                                (this.isGroup = e || !1),
                                (this.isOptional = t || !1),
                                (this.isQuantifier = n || !1),
                                (this.isAlternator = i || !1),
                                (this.quantifier = { min: 1, max: 1 });
                        }
                        function o(t, s, o) {
                            o = o !== i ? o : t.matches.length;
                            var l = t.matches[o - 1];
                            if (n)
                                0 === s.indexOf("[") || (y && /\\d|\\s|\\w]/i.test(s)) || "." === s
                                    ? t.matches.splice(o++, 0, {
                                          fn: new RegExp(s, r.casing ? "i" : ""),
                                          cardinality: 1,
                                          optionality: t.isOptional,
                                          newBlockMarker: l === i || l.def !== s,
                                          casing: null,
                                          def: s,
                                          placeholder: i,
                                          nativeDef: s,
                                      })
                                    : (y && (s = s[s.length - 1]),
                                      e.each(s.split(""), function (e, n) {
                                          (l = t.matches[o - 1]),
                                              t.matches.splice(o++, 0, {
                                                  fn: null,
                                                  cardinality: 0,
                                                  optionality: t.isOptional,
                                                  newBlockMarker: l === i || (l.def !== n && null !== l.fn),
                                                  casing: null,
                                                  def: r.staticDefinitionSymbol || n,
                                                  placeholder: r.staticDefinitionSymbol !== i ? n : i,
                                                  nativeDef: n,
                                              });
                                      })),
                                    (y = !1);
                            else {
                                var d = (r.definitions ? r.definitions[s] : i) || a.prototype.definitions[s];
                                if (d && !y) {
                                    for (var u = d.prevalidator, c = u ? u.length : 0, h = 1; h < d.cardinality; h++) {
                                        var f = c >= h ? u[h - 1] : [],
                                            p = f.validator,
                                            m = f.cardinality;
                                        t.matches.splice(o++, 0, {
                                            fn: p
                                                ? "string" == typeof p
                                                    ? new RegExp(p, r.casing ? "i" : "")
                                                    : new (function () {
                                                          this.test = p;
                                                      })()
                                                : new RegExp("."),
                                            cardinality: m || 1,
                                            optionality: t.isOptional,
                                            newBlockMarker: l === i || l.def !== (d.definitionSymbol || s),
                                            casing: d.casing,
                                            def: d.definitionSymbol || s,
                                            placeholder: d.placeholder,
                                            nativeDef: s,
                                        }),
                                            (l = t.matches[o - 1]);
                                    }
                                    t.matches.splice(o++, 0, {
                                        fn: d.validator
                                            ? "string" == typeof d.validator
                                                ? new RegExp(d.validator, r.casing ? "i" : "")
                                                : new (function () {
                                                      this.test = d.validator;
                                                  })()
                                            : new RegExp("."),
                                        cardinality: d.cardinality,
                                        optionality: t.isOptional,
                                        newBlockMarker: l === i || l.def !== (d.definitionSymbol || s),
                                        casing: d.casing,
                                        def: d.definitionSymbol || s,
                                        placeholder: d.placeholder,
                                        nativeDef: s,
                                    });
                                } else
                                    t.matches.splice(o++, 0, {
                                        fn: null,
                                        cardinality: 0,
                                        optionality: t.isOptional,
                                        newBlockMarker: l === i || (l.def !== s && null !== l.fn),
                                        casing: null,
                                        def: r.staticDefinitionSymbol || s,
                                        placeholder: r.staticDefinitionSymbol !== i ? s : i,
                                        nativeDef: s,
                                    }),
                                        (y = !1);
                            }
                        }
                        function l() {
                            if (b.length > 0) {
                                if ((o((h = b[b.length - 1]), u), h.isAlternator)) {
                                    f = b.pop();
                                    for (var e = 0; e < f.matches.length; e++) f.matches[e].isGroup = !1;
                                    b.length > 0 ? (h = b[b.length - 1]).matches.push(f) : v.matches.push(f);
                                }
                            } else o(v, u);
                        }
                        var d,
                            u,
                            c,
                            h,
                            f,
                            p,
                            m,
                            g = /(?:[?*+]|\{[0-9\+\*]+(?:,[0-9\+\*]*)?\})|[^.?*+^${[]()|\\]+|./g,
                            _ = /\[\^?]?(?:[^\\\]]+|\\[\S\s]?)*]?|\\(?:0(?:[0-3][0-7]{0,2}|[4-7][0-7]?)?|[1-9][0-9]*|x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4}|c[A-Za-z]|[\S\s]?)|\((?:\?[:=!]?)?|(?:[?*+]|\{[0-9]+(?:,[0-9]*)?\})\??|[^.?*+^${[()|\\]+|./g,
                            y = !1,
                            v = new s(),
                            b = [],
                            w = [];
                        for (n && ((r.optionalmarker.start = i), (r.optionalmarker.end = i)); (d = n ? _.exec(t) : g.exec(t)); ) {
                            if (((u = d[0]), n))
                                switch (u.charAt(0)) {
                                    case "?":
                                        u = "{0,1}";
                                        break;
                                    case "+":
                                    case "*":
                                        u = "{" + u + "}";
                                }
                            if (y) l();
                            else
                                switch (u.charAt(0)) {
                                    case r.escapeChar:
                                        (y = !0), n && l();
                                        break;
                                    case r.optionalmarker.end:
                                    case r.groupmarker.end:
                                        if ((((c = b.pop()).openGroup = !1), c !== i))
                                            if (b.length > 0) {
                                                if (((h = b[b.length - 1]).matches.push(c), h.isAlternator)) {
                                                    f = b.pop();
                                                    for (var M = 0; M < f.matches.length; M++) (f.matches[M].isGroup = !1), (f.matches[M].alternatorGroup = !1);
                                                    b.length > 0 ? (h = b[b.length - 1]).matches.push(f) : v.matches.push(f);
                                                }
                                            } else v.matches.push(c);
                                        else l();
                                        break;
                                    case r.optionalmarker.start:
                                        b.push(new s(!1, !0));
                                        break;
                                    case r.groupmarker.start:
                                        b.push(new s(!0));
                                        break;
                                    case r.quantifiermarker.start:
                                        var x = new s(!1, !1, !0),
                                            k = (u = u.replace(/[{}]/g, "")).split(","),
                                            D = isNaN(k[0]) ? k[0] : parseInt(k[0]),
                                            L = 1 === k.length ? D : isNaN(k[1]) ? k[1] : parseInt(k[1]);
                                        if ((("*" !== L && "+" !== L) || (D = "*" === L ? 0 : 1), (x.quantifier = { min: D, max: L }), b.length > 0)) {
                                            var S = b[b.length - 1].matches;
                                            (d = S.pop()).isGroup || ((m = new s(!0)).matches.push(d), (d = m)), S.push(d), S.push(x);
                                        } else
                                            (d = v.matches.pop()).isGroup || (n && null === d.fn && "." === d.def && (d.fn = new RegExp(d.def, r.casing ? "i" : "")), (m = new s(!0)).matches.push(d), (d = m)),
                                                v.matches.push(d),
                                                v.matches.push(x);
                                        break;
                                    case r.alternatormarker:
                                        if (b.length > 0) {
                                            var T = (h = b[b.length - 1]).matches[h.matches.length - 1];
                                            p = h.openGroup && (T.matches === i || (!1 === T.isGroup && !1 === T.isAlternator)) ? b.pop() : h.matches.pop();
                                        } else p = v.matches.pop();
                                        if (p.isAlternator) b.push(p);
                                        else if ((p.alternatorGroup ? ((f = b.pop()), (p.alternatorGroup = !1)) : (f = new s(!1, !1, !1, !0)), f.matches.push(p), b.push(f), p.openGroup)) {
                                            p.openGroup = !1;
                                            var Y = new s(!0);
                                            (Y.alternatorGroup = !0), b.push(Y);
                                        }
                                        break;
                                    default:
                                        l();
                                }
                        }
                        for (; b.length > 0; ) (c = b.pop()), v.matches.push(c);
                        return (
                            v.matches.length > 0 &&
                                ((function t(a) {
                                    a &&
                                        a.matches &&
                                        e.each(a.matches, function (e, s) {
                                            var l = a.matches[e + 1];
                                            (l === i || l.matches === i || !1 === l.isQuantifier) && s && s.isGroup && ((s.isGroup = !1), n || (o(s, r.groupmarker.start, 0), !0 !== s.openGroup && o(s, r.groupmarker.end))), t(s);
                                        });
                                })(v),
                                w.push(v)),
                            (r.numericInput || r.isRTL) &&
                                (function e(t) {
                                    for (var n in ((t.matches = t.matches.reverse()), t.matches))
                                        if (t.matches.hasOwnProperty(n)) {
                                            var a = parseInt(n);
                                            if (t.matches[n].isQuantifier && t.matches[a + 1] && t.matches[a + 1].isGroup) {
                                                var s = t.matches[n];
                                                t.matches.splice(n, 1), t.matches.splice(a + 1, 0, s);
                                            }
                                            t.matches[n].matches !== i
                                                ? (t.matches[n] = e(t.matches[n]))
                                                : (t.matches[n] = (function (e) {
                                                      return (
                                                          e === r.optionalmarker.start
                                                              ? (e = r.optionalmarker.end)
                                                              : e === r.optionalmarker.end
                                                              ? (e = r.optionalmarker.start)
                                                              : e === r.groupmarker.start
                                                              ? (e = r.groupmarker.end)
                                                              : e === r.groupmarker.end && (e = r.groupmarker.start),
                                                          e
                                                      );
                                                  })(t.matches[n]));
                                        }
                                    return t;
                                })(w[0]),
                            w
                        );
                    },
                }),
                (a.extendDefaults = function (t) {
                    e.extend(!0, a.prototype.defaults, t);
                }),
                (a.extendDefinitions = function (t) {
                    e.extend(!0, a.prototype.definitions, t);
                }),
                (a.extendAliases = function (t) {
                    e.extend(!0, a.prototype.aliases, t);
                }),
                (a.format = function (e, t, n) {
                    return a(t).format(e, n);
                }),
                (a.unmask = function (e, t) {
                    return a(t).unmaskedvalue(e);
                }),
                (a.isValid = function (e, t) {
                    return a(t).isValid(e);
                }),
                (a.remove = function (t) {
                    e.each(t, function (e, t) {
                        t.inputmask && t.inputmask.remove();
                    });
                }),
                (a.escapeRegex = function (e) {
                    return e.replace(new RegExp("(\\" + ["/", ".", "*", "+", "?", "|", "(", ")", "[", "]", "{", "}", "\\", "$", "^"].join("|\\") + ")", "gim"), "\\$1");
                }),
                (a.keyCode = {
                    ALT: 18,
                    BACKSPACE: 8,
                    BACKSPACE_SAFARI: 127,
                    CAPS_LOCK: 20,
                    COMMA: 188,
                    COMMAND: 91,
                    COMMAND_LEFT: 91,
                    COMMAND_RIGHT: 93,
                    CONTROL: 17,
                    DELETE: 46,
                    DOWN: 40,
                    END: 35,
                    ENTER: 13,
                    ESCAPE: 27,
                    HOME: 36,
                    INSERT: 45,
                    LEFT: 37,
                    MENU: 93,
                    NUMPAD_ADD: 107,
                    NUMPAD_DECIMAL: 110,
                    NUMPAD_DIVIDE: 111,
                    NUMPAD_ENTER: 108,
                    NUMPAD_MULTIPLY: 106,
                    NUMPAD_SUBTRACT: 109,
                    PAGE_DOWN: 34,
                    PAGE_UP: 33,
                    PERIOD: 190,
                    RIGHT: 39,
                    SHIFT: 16,
                    SPACE: 32,
                    TAB: 9,
                    UP: 38,
                    WINDOWS: 91,
                    X: 88,
                }),
                a
            );
        });
    },
    function (e, t) {
        e.exports = jQuery;
    },
    function (e, t, n) {
        "use strict";
        function i(e) {
            return e && e.__esModule ? e : { default: e };
        }
        n(4), n(9), n(12), n(13), n(14), n(15);
        var a = i(n(1)),
            r = i(n(0)),
            s = i(n(2));
        r.default === s.default && n(16), (window.Inputmask = a.default);
    },
    function (e, t, n) {
        var i = n(5);
        "string" == typeof i && (i = [[e.i, i, ""]]);
        n(7)(i, { hmr: !0, transform: void 0 }), i.locals && (e.exports = i.locals);
    },
    function (e, t, n) {
        (e.exports = n(6)(void 0)).push([
            e.i,
            "span.im-caret {\r\n    -webkit-animation: 1s blink step-end infinite;\r\n    animation: 1s blink step-end infinite;\r\n}\r\n\r\n@keyframes blink {\r\n    from, to {\r\n        border-right-color: black;\r\n    }\r\n    50% {\r\n        border-right-color: transparent;\r\n    }\r\n}\r\n\r\n@-webkit-keyframes blink {\r\n    from, to {\r\n        border-right-color: black;\r\n    }\r\n    50% {\r\n        border-right-color: transparent;\r\n    }\r\n}\r\n\r\nspan.im-static {\r\n    color: grey;\r\n}\r\n\r\ndiv.im-colormask {\r\n    display: inline-block;\r\n    border-style: inset;\r\n    border-width: 2px;\r\n    -webkit-appearance: textfield;\r\n    -moz-appearance: textfield;\r\n    appearance: textfield;\r\n}\r\n\r\ndiv.im-colormask > input {\r\n    position: absolute;\r\n    display: inline-block;\r\n    background-color: transparent;\r\n    color: transparent;\r\n    -webkit-appearance: caret;\r\n    -moz-appearance: caret;\r\n    appearance: caret;\r\n    border-style: none;\r\n    left: 0; /*calculated*/\r\n}\r\n\r\ndiv.im-colormask > input:focus {\r\n    outline: none;\r\n}\r\n\r\ndiv.im-colormask > input::-moz-selection{\r\n    background: none;\r\n}\r\n\r\ndiv.im-colormask > input::selection{\r\n    background: none;\r\n}\r\ndiv.im-colormask > input::-moz-selection{\r\n    background: none;\r\n}\r\n\r\ndiv.im-colormask > div {\r\n    color: black;\r\n    display: inline-block;\r\n    width: 100px; /*calculated*/\r\n}",
            "",
        ]);
    },
    function (e, t) {
        e.exports = function (e) {
            var t = [];
            return (
                (t.toString = function () {
                    return this.map(function (t) {
                        var n = (function (e, t) {
                            var n = e[1] || "",
                                i = e[3];
                            if (!i) return n;
                            if (t && "function" == typeof btoa) {
                                var a = (function (e) {
                                        return "/*# sourceMappingURL=data:application/json;charset=utf-8;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(e)))) + " */";
                                    })(i),
                                    r = i.sources.map(function (e) {
                                        return "/*# sourceURL=" + i.sourceRoot + e + " */";
                                    });
                                return [n].concat(r).concat([a]).join("\n");
                            }
                            return [n].join("\n");
                        })(t, e);
                        return t[2] ? "@media " + t[2] + "{" + n + "}" : n;
                    }).join("");
                }),
                (t.i = function (e, n) {
                    "string" == typeof e && (e = [[null, e, ""]]);
                    for (var i = {}, a = 0; a < this.length; a++) {
                        var r = this[a][0];
                        "number" == typeof r && (i[r] = !0);
                    }
                    for (a = 0; a < e.length; a++) {
                        var s = e[a];
                        ("number" == typeof s[0] && i[s[0]]) || (n && !s[2] ? (s[2] = n) : n && (s[2] = "(" + s[2] + ") and (" + n + ")"), t.push(s));
                    }
                }),
                t
            );
        };
    },
    function (e, t, n) {
        function i(e, t) {
            for (var n = 0; n < e.length; n++) {
                var i = e[n],
                    a = p[i.id];
                if (a) {
                    for (a.refs++, s = 0; s < a.parts.length; s++) a.parts[s](i.parts[s]);
                    for (; s < i.parts.length; s++) a.parts.push(u(i.parts[s], t));
                } else {
                    for (var r = [], s = 0; s < i.parts.length; s++) r.push(u(i.parts[s], t));
                    p[i.id] = { id: i.id, refs: 1, parts: r };
                }
            }
        }
        function a(e, t) {
            for (var n = [], i = {}, a = 0; a < e.length; a++) {
                var r = e[a],
                    s = t.base ? r[0] + t.base : r[0],
                    o = { css: r[1], media: r[2], sourceMap: r[3] };
                i[s] ? i[s].parts.push(o) : n.push((i[s] = { id: s, parts: [o] }));
            }
            return n;
        }
        function r(e, t) {
            var n = g(e.insertInto);
            if (!n) throw new Error("Couldn't find a style target. This probably means that the value for the 'insertInto' parameter is invalid.");
            var i = v[v.length - 1];
            if ("top" === e.insertAt) i ? (i.nextSibling ? n.insertBefore(t, i.nextSibling) : n.appendChild(t)) : n.insertBefore(t, n.firstChild), v.push(t);
            else if ("bottom" === e.insertAt) n.appendChild(t);
            else {
                if ("object" != typeof e.insertAt || !e.insertAt.before)
                    throw new Error("[Style Loader]\n\n Invalid value for parameter 'insertAt' ('options.insertAt') found.\n Must be 'top', 'bottom', or Object.\n (https://github.com/webpack-contrib/style-loader#insertat)\n");
                var a = g(e.insertInto + " " + e.insertAt.before);
                n.insertBefore(t, a);
            }
        }
        function s(e) {
            if (null === e.parentNode) return !1;
            e.parentNode.removeChild(e);
            var t = v.indexOf(e);
            t >= 0 && v.splice(t, 1);
        }
        function o(e) {
            var t = document.createElement("style");
            return (e.attrs.type = "text/css"), d(t, e.attrs), r(e, t), t;
        }
        function l(e) {
            var t = document.createElement("link");
            return (e.attrs.type = "text/css"), (e.attrs.rel = "stylesheet"), d(t, e.attrs), r(e, t), t;
        }
        function d(e, t) {
            Object.keys(t).forEach(function (n) {
                e.setAttribute(n, t[n]);
            });
        }
        function u(e, t) {
            var n, i, a, r;
            if (t.transform && e.css) {
                if (!(r = t.transform(e.css))) return function () {};
                e.css = r;
            }
            if (t.singleton) {
                var d = y++;
                (n = _ || (_ = o(t))), (i = c.bind(null, n, d, !1)), (a = c.bind(null, n, d, !0));
            } else
                e.sourceMap && "function" == typeof URL && "function" == typeof URL.createObjectURL && "function" == typeof URL.revokeObjectURL && "function" == typeof Blob && "function" == typeof btoa
                    ? ((n = l(t)),
                      (i = f.bind(null, n, t)),
                      (a = function () {
                          s(n), n.href && URL.revokeObjectURL(n.href);
                      }))
                    : ((n = o(t)),
                      (i = h.bind(null, n)),
                      (a = function () {
                          s(n);
                      }));
            return (
                i(e),
                function (t) {
                    if (t) {
                        if (t.css === e.css && t.media === e.media && t.sourceMap === e.sourceMap) return;
                        i((e = t));
                    } else a();
                }
            );
        }
        function c(e, t, n, i) {
            var a = n ? "" : i.css;
            if (e.styleSheet) e.styleSheet.cssText = w(t, a);
            else {
                var r = document.createTextNode(a),
                    s = e.childNodes;
                s[t] && e.removeChild(s[t]), s.length ? e.insertBefore(r, s[t]) : e.appendChild(r);
            }
        }
        function h(e, t) {
            var n = t.css,
                i = t.media;
            if ((i && e.setAttribute("media", i), e.styleSheet)) e.styleSheet.cssText = n;
            else {
                for (; e.firstChild; ) e.removeChild(e.firstChild);
                e.appendChild(document.createTextNode(n));
            }
        }
        function f(e, t, n) {
            var i = n.css,
                a = n.sourceMap,
                r = void 0 === t.convertToAbsoluteUrls && a;
            (t.convertToAbsoluteUrls || r) && (i = b(i)), a && (i += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(a)))) + " */");
            var s = new Blob([i], { type: "text/css" }),
                o = e.href;
            (e.href = URL.createObjectURL(s)), o && URL.revokeObjectURL(o);
        }
        var p = {},
            m = (function (e) {
                var t;
                return function () {
                    return void 0 === t && (t = e.apply(this, arguments)), t;
                };
            })(function () {
                return window && document && document.all && !window.atob;
            }),
            g = (function (e) {
                var t = {};
                return function (n) {
                    if (void 0 === t[n]) {
                        var i = e.call(this, n);
                        if (i instanceof window.HTMLIFrameElement)
                            try {
                                i = i.contentDocument.head;
                            } catch (e) {
                                i = null;
                            }
                        t[n] = i;
                    }
                    return t[n];
                };
            })(function (e) {
                return document.querySelector(e);
            }),
            _ = null,
            y = 0,
            v = [],
            b = n(8);
        e.exports = function (e, t) {
            if ("undefined" != typeof DEBUG && DEBUG && "object" != typeof document) throw new Error("The style-loader cannot be used in a non-browser environment");
            ((t = t || {}).attrs = "object" == typeof t.attrs ? t.attrs : {}), t.singleton || (t.singleton = m()), t.insertInto || (t.insertInto = "head"), t.insertAt || (t.insertAt = "bottom");
            var n = a(e, t);
            return (
                i(n, t),
                function (e) {
                    for (var r = [], s = 0; s < n.length; s++) {
                        var o = n[s];
                        (l = p[o.id]).refs--, r.push(l);
                    }
                    for (e && i(a(e, t), t), s = 0; s < r.length; s++) {
                        var l = r[s];
                        if (0 === l.refs) {
                            for (var d = 0; d < l.parts.length; d++) l.parts[d]();
                            delete p[l.id];
                        }
                    }
                }
            );
        };
        var w = (function () {
            var e = [];
            return function (t, n) {
                return (e[t] = n), e.filter(Boolean).join("\n");
            };
        })();
    },
    function (e, t) {
        e.exports = function (e) {
            var t = "undefined" != typeof window && window.location;
            if (!t) throw new Error("fixUrls requires window.location");
            if (!e || "string" != typeof e) return e;
            var n = t.protocol + "//" + t.host,
                i = n + t.pathname.replace(/\/[^\/]*$/, "/");
            return e.replace(/url\s*\(((?:[^)(]|\((?:[^)(]+|\([^)(]*\))*\))*)\)/gi, function (e, t) {
                var a,
                    r = t
                        .trim()
                        .replace(/^"(.*)"$/, function (e, t) {
                            return t;
                        })
                        .replace(/^'(.*)'$/, function (e, t) {
                            return t;
                        });
                return /^(#|data:|http:\/\/|https:\/\/|file:\/\/\/)/i.test(r) ? e : ((a = 0 === r.indexOf("//") ? r : 0 === r.indexOf("/") ? n + r : i + r.replace(/^\.\//, "")), "url(" + JSON.stringify(a) + ")");
            });
        };
    },
    function (e, t, n) {
        "use strict";
        var i, a, r;
        "function" == typeof Symbol && Symbol.iterator,
            (a = [n(0), n(1)]),
            void 0 !==
                (r =
                    "function" ==
                    typeof (i = function (e, t) {
                        return (
                            t.extendAliases({
                                "dd/mm/yyyy": {
                                    mask: "1/2/y",
                                    placeholder: "dd/mm/yyyy",
                                    regex: {
                                        val1pre: new RegExp("[0-3]"),
                                        val1: new RegExp("0[1-9]|[12][0-9]|3[01]"),
                                        val2pre: function (e) {
                                            var n = t.escapeRegex.call(this, e);
                                            return new RegExp("((0[1-9]|[12][0-9]|3[01])" + n + "[01])");
                                        },
                                        val2: function (e) {
                                            var n = t.escapeRegex.call(this, e);
                                            return new RegExp("((0[1-9]|[12][0-9])" + n + "(0[1-9]|1[012]))|(30" + n + "(0[13-9]|1[012]))|(31" + n + "(0[13578]|1[02]))");
                                        },
                                    },
                                    leapday: "29/02/",
                                    separator: "/",
                                    yearrange: { minyear: 1900, maxyear: 2099 },
                                    isInYearRange: function (e, t, n) {
                                        if (isNaN(e)) return !1;
                                        var i = parseInt(e.concat(t.toString().slice(e.length))),
                                            a = parseInt(e.concat(n.toString().slice(e.length)));
                                        return (!isNaN(i) && t <= i && i <= n) || (!isNaN(a) && t <= a && a <= n);
                                    },
                                    determinebaseyear: function (e, t, n) {
                                        var i = new Date().getFullYear();
                                        if (e > i) return e;
                                        if (t < i) {
                                            for (var a = t.toString().slice(0, 2), r = t.toString().slice(2, 4); t < a + n; ) a--;
                                            var s = a + r;
                                            return e > s ? e : s;
                                        }
                                        if (e <= i && i <= t) {
                                            for (var o = i.toString().slice(0, 2); t < o + n; ) o--;
                                            var l = o + n;
                                            return l < e ? e : l;
                                        }
                                        return i;
                                    },
                                    onKeyDown: function (n, i, a, r) {
                                        var s = e(this);
                                        if (n.ctrlKey && n.keyCode === t.keyCode.RIGHT) {
                                            var o = new Date();
                                            s.val(o.getDate().toString() + (o.getMonth() + 1).toString() + o.getFullYear().toString()), s.trigger("setvalue");
                                        }
                                    },
                                    getFrontValue: function (e, t, n) {
                                        for (var i = 0, a = 0, r = 0; r < e.length && "2" !== e.charAt(r); r++) {
                                            var s = n.definitions[e.charAt(r)];
                                            s ? ((i += a), (a = s.cardinality)) : a++;
                                        }
                                        return t.join("").substr(i, a);
                                    },
                                    postValidation: function (e, t, n) {
                                        var i,
                                            a,
                                            r = e.join("");
                                        return (
                                            0 === n.mask.indexOf("y") ? ((a = r.substr(0, 4)), (i = r.substring(4, 10))) : ((a = r.substring(6, 10)), (i = r.substr(0, 6))),
                                            t &&
                                                (i !== n.leapday ||
                                                    (function (e) {
                                                        return isNaN(e) || 29 === new Date(e, 2, 0).getDate();
                                                    })(a))
                                        );
                                    },
                                    definitions: {
                                        1: {
                                            validator: function (e, t, n, i, a) {
                                                var r = a.regex.val1.test(e);
                                                return i || r || (e.charAt(1) !== a.separator && -1 === "-./".indexOf(e.charAt(1))) || !(r = a.regex.val1.test("0" + e.charAt(0)))
                                                    ? r
                                                    : ((t.buffer[n - 1] = "0"), { refreshFromBuffer: { start: n - 1, end: n }, pos: n, c: e.charAt(0) });
                                            },
                                            cardinality: 2,
                                            prevalidator: [
                                                {
                                                    validator: function (e, t, n, i, a) {
                                                        var r = e;
                                                        isNaN(t.buffer[n + 1]) || (r += t.buffer[n + 1]);
                                                        var s = 1 === r.length ? a.regex.val1pre.test(r) : a.regex.val1.test(r);
                                                        if ((s && t.validPositions[n] && (a.regex.val2(a.separator).test(e + t.validPositions[n].input) || (t.validPositions[n].input = "0" === e ? "1" : "0")), !i && !s)) {
                                                            if ((s = a.regex.val1.test(e + "0"))) return (t.buffer[n] = e), (t.buffer[++n] = "0"), { pos: n, c: "0" };
                                                            if ((s = a.regex.val1.test("0" + e))) return (t.buffer[n] = "0"), { pos: ++n };
                                                        }
                                                        return s;
                                                    },
                                                    cardinality: 1,
                                                },
                                            ],
                                        },
                                        2: {
                                            validator: function (e, t, n, i, a) {
                                                var r = a.getFrontValue(t.mask, t.buffer, a);
                                                -1 !== r.indexOf(a.placeholder[0]) && (r = "01" + a.separator);
                                                var s = a.regex.val2(a.separator).test(r + e);
                                                return i || s || (e.charAt(1) !== a.separator && -1 === "-./".indexOf(e.charAt(1))) || !(s = a.regex.val2(a.separator).test(r + "0" + e.charAt(0)))
                                                    ? s
                                                    : ((t.buffer[n - 1] = "0"), { refreshFromBuffer: { start: n - 1, end: n }, pos: n, c: e.charAt(0) });
                                            },
                                            cardinality: 2,
                                            prevalidator: [
                                                {
                                                    validator: function (e, t, n, i, a) {
                                                        isNaN(t.buffer[n + 1]) || (e += t.buffer[n + 1]);
                                                        var r = a.getFrontValue(t.mask, t.buffer, a);
                                                        -1 !== r.indexOf(a.placeholder[0]) && (r = "01" + a.separator);
                                                        var s = 1 === e.length ? a.regex.val2pre(a.separator).test(r + e) : a.regex.val2(a.separator).test(r + e);
                                                        return (
                                                            s && t.validPositions[n] && (a.regex.val2(a.separator).test(e + t.validPositions[n].input) || (t.validPositions[n].input = "0" === e ? "1" : "0")),
                                                            i || s || !(s = a.regex.val2(a.separator).test(r + "0" + e)) ? s : ((t.buffer[n] = "0"), { pos: ++n })
                                                        );
                                                    },
                                                    cardinality: 1,
                                                },
                                            ],
                                        },
                                        y: {
                                            validator: function (e, t, n, i, a) {
                                                return a.isInYearRange(e, a.yearrange.minyear, a.yearrange.maxyear);
                                            },
                                            cardinality: 4,
                                            prevalidator: [
                                                {
                                                    validator: function (e, t, n, i, a) {
                                                        var r = a.isInYearRange(e, a.yearrange.minyear, a.yearrange.maxyear);
                                                        if (!i && !r) {
                                                            var s = a
                                                                .determinebaseyear(a.yearrange.minyear, a.yearrange.maxyear, e + "0")
                                                                .toString()
                                                                .slice(0, 1);
                                                            if ((r = a.isInYearRange(s + e, a.yearrange.minyear, a.yearrange.maxyear))) return (t.buffer[n++] = s.charAt(0)), { pos: n };
                                                            if (
                                                                ((s = a
                                                                    .determinebaseyear(a.yearrange.minyear, a.yearrange.maxyear, e + "0")
                                                                    .toString()
                                                                    .slice(0, 2)),
                                                                (r = a.isInYearRange(s + e, a.yearrange.minyear, a.yearrange.maxyear)))
                                                            )
                                                                return (t.buffer[n++] = s.charAt(0)), (t.buffer[n++] = s.charAt(1)), { pos: n };
                                                        }
                                                        return r;
                                                    },
                                                    cardinality: 1,
                                                },
                                                {
                                                    validator: function (e, t, n, i, a) {
                                                        var r = a.isInYearRange(e, a.yearrange.minyear, a.yearrange.maxyear);
                                                        if (!i && !r) {
                                                            var s = a.determinebaseyear(a.yearrange.minyear, a.yearrange.maxyear, e).toString().slice(0, 2);
                                                            if ((r = a.isInYearRange(e[0] + s[1] + e[1], a.yearrange.minyear, a.yearrange.maxyear))) return (t.buffer[n++] = s.charAt(1)), { pos: n };
                                                            if (((s = a.determinebaseyear(a.yearrange.minyear, a.yearrange.maxyear, e).toString().slice(0, 2)), (r = a.isInYearRange(s + e, a.yearrange.minyear, a.yearrange.maxyear))))
                                                                return (t.buffer[n - 1] = s.charAt(0)), (t.buffer[n++] = s.charAt(1)), (t.buffer[n++] = e.charAt(0)), { refreshFromBuffer: { start: n - 3, end: n }, pos: n };
                                                        }
                                                        return r;
                                                    },
                                                    cardinality: 2,
                                                },
                                                {
                                                    validator: function (e, t, n, i, a) {
                                                        return a.isInYearRange(e, a.yearrange.minyear, a.yearrange.maxyear);
                                                    },
                                                    cardinality: 3,
                                                },
                                            ],
                                        },
                                    },
                                    insertMode: !1,
                                    autoUnmask: !1,
                                },
                                "mm/dd/yyyy": {
                                    placeholder: "mm/dd/yyyy",
                                    alias: "dd/mm/yyyy",
                                    regex: {
                                        val2pre: function (e) {
                                            var n = t.escapeRegex.call(this, e);
                                            return new RegExp("((0[13-9]|1[012])" + n + "[0-3])|(02" + n + "[0-2])");
                                        },
                                        val2: function (e) {
                                            var n = t.escapeRegex.call(this, e);
                                            return new RegExp("((0[1-9]|1[012])" + n + "(0[1-9]|[12][0-9]))|((0[13-9]|1[012])" + n + "30)|((0[13578]|1[02])" + n + "31)");
                                        },
                                        val1pre: new RegExp("[01]"),
                                        val1: new RegExp("0[1-9]|1[012]"),
                                    },
                                    leapday: "02/29/",
                                    onKeyDown: function (n, i, a, r) {
                                        var s = e(this);
                                        if (n.ctrlKey && n.keyCode === t.keyCode.RIGHT) {
                                            var o = new Date();
                                            s.val((o.getMonth() + 1).toString() + o.getDate().toString() + o.getFullYear().toString()), s.trigger("setvalue");
                                        }
                                    },
                                },
                                "yyyy/mm/dd": {
                                    mask: "y/1/2",
                                    placeholder: "yyyy/mm/dd",
                                    alias: "mm/dd/yyyy",
                                    leapday: "/02/29",
                                    onKeyDown: function (n, i, a, r) {
                                        var s = e(this);
                                        if (n.ctrlKey && n.keyCode === t.keyCode.RIGHT) {
                                            var o = new Date();
                                            s.val(o.getFullYear().toString() + (o.getMonth() + 1).toString() + o.getDate().toString()), s.trigger("setvalue");
                                        }
                                    },
                                },
                                "dd.mm.yyyy": { mask: "1.2.y", placeholder: "dd.mm.yyyy", leapday: "29.02.", separator: ".", alias: "dd/mm/yyyy" },
                                "dd-mm-yyyy": { mask: "1-2-y", placeholder: "dd-mm-yyyy", leapday: "29-02-", separator: "-", alias: "dd/mm/yyyy" },
                                "mm.dd.yyyy": { mask: "1.2.y", placeholder: "mm.dd.yyyy", leapday: "02.29.", separator: ".", alias: "mm/dd/yyyy" },
                                "mm-dd-yyyy": { mask: "1-2-y", placeholder: "mm-dd-yyyy", leapday: "02-29-", separator: "-", alias: "mm/dd/yyyy" },
                                "yyyy.mm.dd": { mask: "y.1.2", placeholder: "yyyy.mm.dd", leapday: ".02.29", separator: ".", alias: "yyyy/mm/dd" },
                                "yyyy-mm-dd": { mask: "y-1-2", placeholder: "yyyy-mm-dd", leapday: "-02-29", separator: "-", alias: "yyyy/mm/dd" },
                                datetime: {
                                    mask: "1/2/y h:s",
                                    placeholder: "dd/mm/yyyy hh:mm",
                                    alias: "dd/mm/yyyy",
                                    regex: {
                                        hrspre: new RegExp("[012]"),
                                        hrs24: new RegExp("2[0-4]|1[3-9]"),
                                        hrs: new RegExp("[01][0-9]|2[0-4]"),
                                        ampm: new RegExp("^[a|p|A|P][m|M]"),
                                        mspre: new RegExp("[0-5]"),
                                        ms: new RegExp("[0-5][0-9]"),
                                    },
                                    timeseparator: ":",
                                    hourFormat: "24",
                                    definitions: {
                                        h: {
                                            validator: function (e, t, n, i, a) {
                                                if ("24" === a.hourFormat && 24 === parseInt(e, 10)) return (t.buffer[n - 1] = "0"), (t.buffer[n] = "0"), { refreshFromBuffer: { start: n - 1, end: n }, c: "0" };
                                                var r = a.regex.hrs.test(e);
                                                if (!i && !r && (e.charAt(1) === a.timeseparator || -1 !== "-.:".indexOf(e.charAt(1))) && (r = a.regex.hrs.test("0" + e.charAt(0))))
                                                    return (t.buffer[n - 1] = "0"), (t.buffer[n] = e.charAt(0)), { refreshFromBuffer: { start: ++n - 2, end: n }, pos: n, c: a.timeseparator };
                                                if (r && "24" !== a.hourFormat && a.regex.hrs24.test(e)) {
                                                    var s = parseInt(e, 10);
                                                    return (
                                                        24 === s ? ((t.buffer[n + 5] = "a"), (t.buffer[n + 6] = "m")) : ((t.buffer[n + 5] = "p"), (t.buffer[n + 6] = "m")),
                                                        (s -= 12) < 10 ? ((t.buffer[n] = s.toString()), (t.buffer[n - 1] = "0")) : ((t.buffer[n] = s.toString().charAt(1)), (t.buffer[n - 1] = s.toString().charAt(0))),
                                                        { refreshFromBuffer: { start: n - 1, end: n + 6 }, c: t.buffer[n] }
                                                    );
                                                }
                                                return r;
                                            },
                                            cardinality: 2,
                                            prevalidator: [
                                                {
                                                    validator: function (e, t, n, i, a) {
                                                        var r = a.regex.hrspre.test(e);
                                                        return i || r || !(r = a.regex.hrs.test("0" + e)) ? r : ((t.buffer[n] = "0"), { pos: ++n });
                                                    },
                                                    cardinality: 1,
                                                },
                                            ],
                                        },
                                        s: {
                                            validator: "[0-5][0-9]",
                                            cardinality: 2,
                                            prevalidator: [
                                                {
                                                    validator: function (e, t, n, i, a) {
                                                        var r = a.regex.mspre.test(e);
                                                        return i || r || !(r = a.regex.ms.test("0" + e)) ? r : ((t.buffer[n] = "0"), { pos: ++n });
                                                    },
                                                    cardinality: 1,
                                                },
                                            ],
                                        },
                                        t: {
                                            validator: function (e, t, n, i, a) {
                                                return a.regex.ampm.test(e + "m");
                                            },
                                            casing: "lower",
                                            cardinality: 1,
                                        },
                                    },
                                    insertMode: !1,
                                    autoUnmask: !1,
                                },
                                datetime12: { mask: "1/2/y h:s t\\m", placeholder: "dd/mm/yyyy hh:mm xm", alias: "datetime", hourFormat: "12" },
                                "mm/dd/yyyy hh:mm xm": {
                                    mask: "1/2/y h:s t\\m",
                                    placeholder: "mm/dd/yyyy hh:mm xm",
                                    alias: "datetime12",
                                    regex: {
                                        val2pre: function (e) {
                                            var n = t.escapeRegex.call(this, e);
                                            return new RegExp("((0[13-9]|1[012])" + n + "[0-3])|(02" + n + "[0-2])");
                                        },
                                        val2: function (e) {
                                            var n = t.escapeRegex.call(this, e);
                                            return new RegExp("((0[1-9]|1[012])" + n + "(0[1-9]|[12][0-9]))|((0[13-9]|1[012])" + n + "30)|((0[13578]|1[02])" + n + "31)");
                                        },
                                        val1pre: new RegExp("[01]"),
                                        val1: new RegExp("0[1-9]|1[012]"),
                                    },
                                    leapday: "02/29/",
                                    onKeyDown: function (n, i, a, r) {
                                        var s = e(this);
                                        if (n.ctrlKey && n.keyCode === t.keyCode.RIGHT) {
                                            var o = new Date();
                                            s.val((o.getMonth() + 1).toString() + o.getDate().toString() + o.getFullYear().toString()), s.trigger("setvalue");
                                        }
                                    },
                                },
                                "hh:mm t": { mask: "h:s t\\m", placeholder: "hh:mm xm", alias: "datetime", hourFormat: "12" },
                                "h:s t": { mask: "h:s t\\m", placeholder: "hh:mm xm", alias: "datetime", hourFormat: "12" },
                                "hh:mm:ss": { mask: "h:s:s", placeholder: "hh:mm:ss", alias: "datetime", autoUnmask: !1 },
                                "hh:mm": { mask: "h:s", placeholder: "hh:mm", alias: "datetime", autoUnmask: !1 },
                                date: { alias: "dd/mm/yyyy" },
                                "mm/yyyy": { mask: "1/y", placeholder: "mm/yyyy", leapday: "donotuse", separator: "/", alias: "mm/dd/yyyy" },
                                shamsi: {
                                    regex: {
                                        val2pre: function (e) {
                                            var n = t.escapeRegex.call(this, e);
                                            return new RegExp("((0[1-9]|1[012])" + n + "[0-3])");
                                        },
                                        val2: function (e) {
                                            var n = t.escapeRegex.call(this, e);
                                            return new RegExp("((0[1-9]|1[012])" + n + "(0[1-9]|[12][0-9]))|((0[1-9]|1[012])" + n + "30)|((0[1-6])" + n + "31)");
                                        },
                                        val1pre: new RegExp("[01]"),
                                        val1: new RegExp("0[1-9]|1[012]"),
                                    },
                                    yearrange: { minyear: 1300, maxyear: 1499 },
                                    mask: "y/1/2",
                                    leapday: "/12/30",
                                    placeholder: "yyyy/mm/dd",
                                    alias: "mm/dd/yyyy",
                                    clearIncomplete: !0,
                                },
                                "yyyy-mm-dd hh:mm:ss": {
                                    mask: "y-1-2 h:s:s",
                                    placeholder: "yyyy-mm-dd hh:mm:ss",
                                    alias: "datetime",
                                    separator: "-",
                                    leapday: "-02-29",
                                    regex: {
                                        val2pre: function (e) {
                                            var n = t.escapeRegex.call(this, e);
                                            return new RegExp("((0[13-9]|1[012])" + n + "[0-3])|(02" + n + "[0-2])");
                                        },
                                        val2: function (e) {
                                            var n = t.escapeRegex.call(this, e);
                                            return new RegExp("((0[1-9]|1[012])" + n + "(0[1-9]|[12][0-9]))|((0[13-9]|1[012])" + n + "30)|((0[13578]|1[02])" + n + "31)");
                                        },
                                        val1pre: new RegExp("[01]"),
                                        val1: new RegExp("0[1-9]|1[012]"),
                                    },
                                    onKeyDown: function (e, t, n, i) {},
                                },
                            }),
                            t
                        );
                    })
                        ? i.apply(t, a)
                        : i) && (e.exports = r);
    },
    function (e, t, n) {
        "use strict";
        var i;
        "function" == typeof Symbol && Symbol.iterator,
            void 0 !==
                (i = function () {
                    return window;
                }.call(t, n, t, e)) && (e.exports = i);
    },
    function (e, t, n) {
        "use strict";
        var i;
        "function" == typeof Symbol && Symbol.iterator,
            void 0 !==
                (i = function () {
                    return document;
                }.call(t, n, t, e)) && (e.exports = i);
    },
    function (e, t, n) {
        "use strict";
        var i, a, r;
        "function" == typeof Symbol && Symbol.iterator,
            (a = [n(0), n(1)]),
            void 0 !==
                (r =
                    "function" ==
                    typeof (i = function (e, t) {
                        return (
                            t.extendDefinitions({
                                A: { validator: "[A-Za-zА-яЁёÀ-ÿµ]", cardinality: 1, casing: "upper" },
                                "&": { validator: "[0-9A-Za-zА-яЁёÀ-ÿµ]", cardinality: 1, casing: "upper" },
                                "#": { validator: "[0-9A-Fa-f]", cardinality: 1, casing: "upper" },
                            }),
                            t.extendAliases({
                                url: { definitions: { i: { validator: ".", cardinality: 1 } }, mask: "(\\http://)|(\\http\\s://)|(ftp://)|(ftp\\s://)i{+}", insertMode: !1, autoUnmask: !1, inputmode: "url" },
                                ip: {
                                    mask: "i[i[i]].i[i[i]].i[i[i]].i[i[i]]",
                                    definitions: {
                                        i: {
                                            validator: function (e, t, n, i, a) {
                                                return (
                                                    n - 1 > -1 && "." !== t.buffer[n - 1] ? ((e = t.buffer[n - 1] + e), (e = n - 2 > -1 && "." !== t.buffer[n - 2] ? t.buffer[n - 2] + e : "0" + e)) : (e = "00" + e),
                                                    new RegExp("25[0-5]|2[0-4][0-9]|[01][0-9][0-9]").test(e)
                                                );
                                            },
                                            cardinality: 1,
                                        },
                                    },
                                    onUnMask: function (e, t, n) {
                                        return e;
                                    },
                                    inputmode: "numeric",
                                },
                                email: {
                                    mask: "*{1,64}[.*{1,64}][.*{1,64}][.*{1,63}]@-{1,63}.-{1,63}[.-{1,63}][.-{1,63}]",
                                    greedy: !1,
                                    onBeforePaste: function (e, t) {
                                        return (e = e.toLowerCase()).replace("mailto:", "");
                                    },
                                    definitions: { "*": { validator: "[0-9A-Za-z!#$%&'*+/=?^_`{|}~-]", cardinality: 1, casing: "lower" }, "-": { validator: "[0-9A-Za-z-]", cardinality: 1, casing: "lower" } },
                                    onUnMask: function (e, t, n) {
                                        return e;
                                    },
                                    inputmode: "email",
                                },
                                mac: { mask: "##:##:##:##:##:##" },
                                vin: { mask: "V{13}9{4}", definitions: { V: { validator: "[A-HJ-NPR-Za-hj-npr-z\\d]", cardinality: 1, casing: "upper" } }, clearIncomplete: !0, autoUnmask: !0 },
                            }),
                            t
                        );
                    })
                        ? i.apply(t, a)
                        : i) && (e.exports = r);
    },
    function (e, t, n) {
        "use strict";
        var i, a, r;
        "function" == typeof Symbol && Symbol.iterator,
            (a = [n(0), n(1)]),
            void 0 !==
                (r =
                    "function" ==
                    typeof (i = function (e, t, n) {
                        function i(e, n) {
                            for (var i = "", a = 0; a < e.length; a++)
                                t.prototype.definitions[e.charAt(a)] ||
                                n.definitions[e.charAt(a)] ||
                                n.optionalmarker.start === e.charAt(a) ||
                                n.optionalmarker.end === e.charAt(a) ||
                                n.quantifiermarker.start === e.charAt(a) ||
                                n.quantifiermarker.end === e.charAt(a) ||
                                n.groupmarker.start === e.charAt(a) ||
                                n.groupmarker.end === e.charAt(a) ||
                                n.alternatormarker === e.charAt(a)
                                    ? (i += "\\" + e.charAt(a))
                                    : (i += e.charAt(a));
                            return i;
                        }
                        return (
                            t.extendAliases({
                                numeric: {
                                    mask: function (e) {
                                        if (
                                            (0 !== e.repeat && isNaN(e.integerDigits) && (e.integerDigits = e.repeat),
                                            (e.repeat = 0),
                                            e.groupSeparator === e.radixPoint && ("." === e.radixPoint ? (e.groupSeparator = ",") : "," === e.radixPoint ? (e.groupSeparator = ".") : (e.groupSeparator = "")),
                                            " " === e.groupSeparator && (e.skipOptionalPartCharacter = n),
                                            (e.autoGroup = e.autoGroup && "" !== e.groupSeparator),
                                            e.autoGroup && ("string" == typeof e.groupSize && isFinite(e.groupSize) && (e.groupSize = parseInt(e.groupSize)), isFinite(e.integerDigits)))
                                        ) {
                                            var t = Math.floor(e.integerDigits / e.groupSize),
                                                a = e.integerDigits % e.groupSize;
                                            (e.integerDigits = parseInt(e.integerDigits) + (0 === a ? t - 1 : t)), e.integerDigits < 1 && (e.integerDigits = "*");
                                        }
                                        e.placeholder.length > 1 && (e.placeholder = e.placeholder.charAt(0)),
                                            "radixFocus" === e.positionCaretOnClick && "" === e.placeholder && !1 === e.integerOptional && (e.positionCaretOnClick = "lvp"),
                                            (e.definitions[";"] = e.definitions["~"]),
                                            (e.definitions[";"].definitionSymbol = "~"),
                                            !0 === e.numericInput &&
                                                ((e.positionCaretOnClick = "radixFocus" === e.positionCaretOnClick ? "lvp" : e.positionCaretOnClick), (e.digitsOptional = !1), isNaN(e.digits) && (e.digits = 2), (e.decimalProtect = !1));
                                        var r = "[+]";
                                        if (((r += i(e.prefix, e)), !0 === e.integerOptional ? (r += "~{1," + e.integerDigits + "}") : (r += "~{" + e.integerDigits + "}"), e.digits !== n)) {
                                            e.radixPointDefinitionSymbol = e.decimalProtect ? ":" : e.radixPoint;
                                            var s = e.digits.toString().split(",");
                                            isFinite(s[0] && s[1] && isFinite(s[1]))
                                                ? (r += e.radixPointDefinitionSymbol + ";{" + e.digits + "}")
                                                : (isNaN(e.digits) || parseInt(e.digits) > 0) &&
                                                  (e.digitsOptional ? (r += "[" + e.radixPointDefinitionSymbol + ";{1," + e.digits + "}]") : (r += e.radixPointDefinitionSymbol + ";{" + e.digits + "}"));
                                        }
                                        return (r += i(e.suffix, e)), (r += "[-]"), (e.greedy = !1), r;
                                    },
                                    placeholder: "",
                                    greedy: !1,
                                    digits: "*",
                                    digitsOptional: !0,
                                    enforceDigitsOnBlur: !1,
                                    radixPoint: ".",
                                    positionCaretOnClick: "radixFocus",
                                    groupSize: 3,
                                    groupSeparator: "",
                                    autoGroup: !1,
                                    allowMinus: !0,
                                    negationSymbol: { front: "-", back: "" },
                                    integerDigits: "+",
                                    integerOptional: !0,
                                    prefix: "",
                                    suffix: "",
                                    rightAlign: !0,
                                    decimalProtect: !0,
                                    min: null,
                                    max: null,
                                    step: 1,
                                    insertMode: !0,
                                    autoUnmask: !1,
                                    unmaskAsNumber: !1,
                                    inputmode: "numeric",
                                    preValidation: function (t, i, a, r, s) {
                                        if ("-" === a || a === s.negationSymbol.front) return !0 === s.allowMinus && ((s.isNegative = s.isNegative === n || !s.isNegative), "" === t.join("") || { caret: i, dopost: !0 });
                                        if (!1 === r && a === s.radixPoint && s.digits !== n && (isNaN(s.digits) || parseInt(s.digits) > 0)) {
                                            var o = e.inArray(s.radixPoint, t);
                                            if (-1 !== o) return !0 === s.numericInput ? i === o : { caret: o + 1 };
                                        }
                                        return !0;
                                    },
                                    postValidation: function (i, a, r) {
                                        var s = r.suffix.split(""),
                                            o = r.prefix.split("");
                                        if (a.pos === n && a.caret !== n && !0 !== a.dopost) return a;
                                        var l = a.caret !== n ? a.caret : a.pos,
                                            d = i.slice();
                                        r.numericInput && ((l = d.length - l - 1), (d = d.reverse()));
                                        var u = d[l];
                                        if ((u === r.groupSeparator && (u = d[(l += 1)]), l === d.length - r.suffix.length - 1 && u === r.radixPoint)) return a;
                                        u !== n &&
                                            u !== r.radixPoint &&
                                            u !== r.negationSymbol.front &&
                                            u !== r.negationSymbol.back &&
                                            ((d[l] = "?"),
                                            r.prefix.length > 0 && l >= (!1 === r.isNegative ? 1 : 0) && l < r.prefix.length - 1 + (!1 === r.isNegative ? 1 : 0)
                                                ? (o[l - (!1 === r.isNegative ? 1 : 0)] = "?")
                                                : r.suffix.length > 0 && l >= d.length - r.suffix.length - (!1 === r.isNegative ? 1 : 0) && (s[l - (d.length - r.suffix.length - (!1 === r.isNegative ? 1 : 0))] = "?")),
                                            (o = o.join("")),
                                            (s = s.join(""));
                                        var c = d.join("").replace(o, "");
                                        if (
                                            ((c = (c = (c = (c = c.replace(s, "")).replace(new RegExp(t.escapeRegex(r.groupSeparator), "g"), "")).replace(new RegExp("[-" + t.escapeRegex(r.negationSymbol.front) + "]", "g"), "")).replace(
                                                new RegExp(t.escapeRegex(r.negationSymbol.back) + "$"),
                                                ""
                                            )),
                                            isNaN(r.placeholder) && (c = c.replace(new RegExp(t.escapeRegex(r.placeholder), "g"), "")),
                                            c.length > 1 && 1 !== c.indexOf(r.radixPoint) && ("0" === u && (c = c.replace(/^\?/g, "")), (c = c.replace(/^0/g, ""))),
                                            c.charAt(0) === r.radixPoint && "" !== r.radixPoint && !0 !== r.numericInput && (c = "0" + c),
                                            "" !== c)
                                        ) {
                                            if (((c = c.split("")), (!r.digitsOptional || (r.enforceDigitsOnBlur && "blur" === a.event)) && isFinite(r.digits))) {
                                                var h = e.inArray(r.radixPoint, c),
                                                    f = e.inArray(r.radixPoint, d);
                                                -1 === h && (c.push(r.radixPoint), (h = c.length - 1));
                                                for (var p = 1; p <= r.digits; p++)
                                                    (r.digitsOptional && (!r.enforceDigitsOnBlur || "blur" !== a.event)) || (c[h + p] !== n && c[h + p] !== r.placeholder.charAt(0))
                                                        ? -1 !== f && d[f + p] !== n && (c[h + p] = c[h + p] || d[f + p])
                                                        : (c[h + p] = a.placeholder || r.placeholder.charAt(0));
                                            }
                                            if (!0 !== r.autoGroup || "" === r.groupSeparator || (u === r.radixPoint && a.pos === n && !a.dopost)) c = c.join("");
                                            else {
                                                var m = c[c.length - 1] === r.radixPoint && a.c === r.radixPoint;
                                                (c = t(
                                                    (function (e, t) {
                                                        var n = "";
                                                        if (((n += "(" + t.groupSeparator + "*{" + t.groupSize + "}){*}"), "" !== t.radixPoint)) {
                                                            var i = e.join("").split(t.radixPoint);
                                                            i[1] && (n += t.radixPoint + "*{" + i[1].match(/^\d*\??\d*/)[0].length + "}");
                                                        }
                                                        return n;
                                                    })(c, r),
                                                    { numericInput: !0, jitMasking: !0, definitions: { "*": { validator: "[0-9?]", cardinality: 1 } } }
                                                ).format(c.join(""))),
                                                    m && (c += r.radixPoint),
                                                    c.charAt(0) === r.groupSeparator && c.substr(1);
                                            }
                                        }
                                        if (
                                            (r.isNegative && "blur" === a.event && (r.isNegative = "0" !== c),
                                            (c = o + c),
                                            (c += s),
                                            r.isNegative && ((c = r.negationSymbol.front + c), (c += r.negationSymbol.back)),
                                            (c = c.split("")),
                                            u !== n)
                                        )
                                            if (u !== r.radixPoint && u !== r.negationSymbol.front && u !== r.negationSymbol.back) (l = e.inArray("?", c)) > -1 ? (c[l] = u) : (l = a.caret || 0);
                                            else if (u === r.radixPoint || u === r.negationSymbol.front || u === r.negationSymbol.back) {
                                                var g = e.inArray(u, c);
                                                -1 !== g && (l = g);
                                            }
                                        r.numericInput && ((l = c.length - l - 1), (c = c.reverse()));
                                        var _ = { caret: u === n || a.pos !== n ? l + (r.numericInput ? -1 : 1) : l, buffer: c, refreshFromBuffer: a.dopost || i.join("") !== c.join("") };
                                        return _.refreshFromBuffer ? _ : a;
                                    },
                                    onBeforeWrite: function (i, a, r, s) {
                                        if (i)
                                            switch (i.type) {
                                                case "keydown":
                                                    return s.postValidation(a, { caret: r, dopost: !0 }, s);
                                                case "blur":
                                                case "checkval":
                                                    var o;
                                                    if (
                                                        ((function (e) {
                                                            e.parseMinMaxOptions === n &&
                                                                (null !== e.min &&
                                                                    ((e.min = e.min.toString().replace(new RegExp(t.escapeRegex(e.groupSeparator), "g"), "")),
                                                                    "," === e.radixPoint && (e.min = e.min.replace(e.radixPoint, ".")),
                                                                    (e.min = isFinite(e.min) ? parseFloat(e.min) : NaN),
                                                                    isNaN(e.min) && (e.min = Number.MIN_VALUE)),
                                                                null !== e.max &&
                                                                    ((e.max = e.max.toString().replace(new RegExp(t.escapeRegex(e.groupSeparator), "g"), "")),
                                                                    "," === e.radixPoint && (e.max = e.max.replace(e.radixPoint, ".")),
                                                                    (e.max = isFinite(e.max) ? parseFloat(e.max) : NaN),
                                                                    isNaN(e.max) && (e.max = Number.MAX_VALUE)),
                                                                (e.parseMinMaxOptions = "done"));
                                                        })(s),
                                                        null !== s.min || null !== s.max)
                                                    ) {
                                                        if (((o = s.onUnMask(a.join(""), n, e.extend({}, s, { unmaskAsNumber: !0 }))), null !== s.min && o < s.min))
                                                            return (s.isNegative = s.min < 0), s.postValidation(s.min.toString().replace(".", s.radixPoint).split(""), { caret: r, dopost: !0, placeholder: "0" }, s);
                                                        if (null !== s.max && o > s.max)
                                                            return (s.isNegative = s.max < 0), s.postValidation(s.max.toString().replace(".", s.radixPoint).split(""), { caret: r, dopost: !0, placeholder: "0" }, s);
                                                    }
                                                    return s.postValidation(a, { caret: r, placeholder: "0", event: "blur" }, s);
                                                case "_checkval":
                                                    return { caret: r };
                                            }
                                    },
                                    regex: {
                                        integerPart: function (e, n) {
                                            return n ? new RegExp("[" + t.escapeRegex(e.negationSymbol.front) + "+]?") : new RegExp("[" + t.escapeRegex(e.negationSymbol.front) + "+]?\\d+");
                                        },
                                        integerNPart: function (e) {
                                            return new RegExp("[\\d" + t.escapeRegex(e.groupSeparator) + t.escapeRegex(e.placeholder.charAt(0)) + "]+");
                                        },
                                    },
                                    definitions: {
                                        "~": {
                                            validator: function (e, i, a, r, s, o) {
                                                var l = r ? new RegExp("[0-9" + t.escapeRegex(s.groupSeparator) + "]").test(e) : new RegExp("[0-9]").test(e);
                                                if (!0 === l) {
                                                    if (!0 !== s.numericInput && i.validPositions[a] !== n && "~" === i.validPositions[a].match.def && !o) {
                                                        var d = i.buffer.join(""),
                                                            u = (d = (d = d.replace(new RegExp("[-" + t.escapeRegex(s.negationSymbol.front) + "]", "g"), "")).replace(new RegExp(t.escapeRegex(s.negationSymbol.back) + "$"), "")).split(
                                                                s.radixPoint
                                                            );
                                                        u.length > 1 && (u[1] = u[1].replace(/0/g, s.placeholder.charAt(0))), "0" === u[0] && (u[0] = u[0].replace(/0/g, s.placeholder.charAt(0))), (d = u[0] + s.radixPoint + u[1] || "");
                                                        var c = i._buffer.join("");
                                                        for (d === s.radixPoint && (d = c); null === d.match(t.escapeRegex(c) + "$"); ) c = c.slice(1);
                                                        l = (d = (d = d.replace(c, "")).split(""))[a] === n ? { pos: a, remove: a } : { pos: a };
                                                    }
                                                } else r || e !== s.radixPoint || i.validPositions[a - 1] !== n || ((i.buffer[a] = "0"), (l = { pos: a + 1 }));
                                                return l;
                                            },
                                            cardinality: 1,
                                        },
                                        "+": {
                                            validator: function (e, t, n, i, a) {
                                                return a.allowMinus && ("-" === e || e === a.negationSymbol.front);
                                            },
                                            cardinality: 1,
                                            placeholder: "",
                                        },
                                        "-": {
                                            validator: function (e, t, n, i, a) {
                                                return a.allowMinus && e === a.negationSymbol.back;
                                            },
                                            cardinality: 1,
                                            placeholder: "",
                                        },
                                        ":": {
                                            validator: function (e, n, i, a, r) {
                                                var s = "[" + t.escapeRegex(r.radixPoint) + "]",
                                                    o = new RegExp(s).test(e);
                                                return o && n.validPositions[i] && n.validPositions[i].match.placeholder === r.radixPoint && (o = { caret: i + 1 }), o;
                                            },
                                            cardinality: 1,
                                            placeholder: function (e) {
                                                return e.radixPoint;
                                            },
                                        },
                                    },
                                    onUnMask: function (e, n, i) {
                                        if ("" === n && !0 === i.nullable) return n;
                                        var a = e.replace(i.prefix, "");
                                        return (
                                            (a = (a = a.replace(i.suffix, "")).replace(new RegExp(t.escapeRegex(i.groupSeparator), "g"), "")),
                                            "" !== i.placeholder.charAt(0) && (a = a.replace(new RegExp(i.placeholder.charAt(0), "g"), "0")),
                                            i.unmaskAsNumber
                                                ? ("" !== i.radixPoint && -1 !== a.indexOf(i.radixPoint) && (a = a.replace(t.escapeRegex.call(this, i.radixPoint), ".")),
                                                  (a = (a = a.replace(new RegExp("^" + t.escapeRegex(i.negationSymbol.front)), "-")).replace(new RegExp(t.escapeRegex(i.negationSymbol.back) + "$"), "")),
                                                  Number(a))
                                                : a
                                        );
                                    },
                                    isComplete: function (e, n) {
                                        var i = e.join("");
                                        if (e.slice().join("") !== i) return !1;
                                        var a = i.replace(n.prefix, "");
                                        return (a = (a = a.replace(n.suffix, "")).replace(new RegExp(t.escapeRegex(n.groupSeparator), "g"), "")), "," === n.radixPoint && (a = a.replace(t.escapeRegex(n.radixPoint), ".")), isFinite(a);
                                    },
                                    onBeforeMask: function (e, i) {
                                        if (((i.isNegative = n), (e = e.toString().charAt(e.length - 1) === i.radixPoint ? e.toString().substr(0, e.length - 1) : e.toString()), "" !== i.radixPoint && isFinite(e))) {
                                            var a = e.split("."),
                                                r = "" !== i.groupSeparator ? parseInt(i.groupSize) : 0;
                                            2 === a.length && (a[0].length > r || a[1].length > r || (a[0].length <= r && a[1].length < r)) && (e = e.replace(".", i.radixPoint));
                                        }
                                        var s = e.match(/,/g),
                                            o = e.match(/\./g);
                                        if (
                                            ((e =
                                                o && s
                                                    ? o.length > s.length
                                                        ? (e = e.replace(/\./g, "")).replace(",", i.radixPoint)
                                                        : s.length > o.length
                                                        ? (e = e.replace(/,/g, "")).replace(".", i.radixPoint)
                                                        : e.indexOf(".") < e.indexOf(",")
                                                        ? e.replace(/\./g, "")
                                                        : e.replace(/,/g, "")
                                                    : e.replace(new RegExp(t.escapeRegex(i.groupSeparator), "g"), "")),
                                            0 === i.digits && (-1 !== e.indexOf(".") ? (e = e.substring(0, e.indexOf("."))) : -1 !== e.indexOf(",") && (e = e.substring(0, e.indexOf(",")))),
                                            "" !== i.radixPoint && isFinite(i.digits) && -1 !== e.indexOf(i.radixPoint))
                                        ) {
                                            var l = e.split(i.radixPoint)[1].match(new RegExp("\\d*"))[0];
                                            if (parseInt(i.digits) < l.toString().length) {
                                                var d = Math.pow(10, parseInt(i.digits));
                                                (e = e.replace(t.escapeRegex(i.radixPoint), ".")), (e = (e = Math.round(parseFloat(e) * d) / d).toString().replace(".", i.radixPoint));
                                            }
                                        }
                                        return e;
                                    },
                                    canClearPosition: function (e, t, n, i, a) {
                                        var r = e.validPositions[t],
                                            s =
                                                r.input !== a.radixPoint ||
                                                (null !== e.validPositions[t].match.fn && !1 === a.decimalProtect) ||
                                                (r.input === a.radixPoint && e.validPositions[t + 1] && null === e.validPositions[t + 1].match.fn) ||
                                                isFinite(r.input) ||
                                                t === n ||
                                                r.input === a.groupSeparator ||
                                                r.input === a.negationSymbol.front ||
                                                r.input === a.negationSymbol.back;
                                        return !s || ("+" !== r.match.nativeDef && "-" !== r.match.nativeDef) || (a.isNegative = !1), s;
                                    },
                                    onKeyDown: function (n, i, a, r) {
                                        var s = e(this);
                                        if (n.ctrlKey)
                                            switch (n.keyCode) {
                                                case t.keyCode.UP:
                                                    s.val(parseFloat(this.inputmask.unmaskedvalue()) + parseInt(r.step)), s.trigger("setvalue");
                                                    break;
                                                case t.keyCode.DOWN:
                                                    s.val(parseFloat(this.inputmask.unmaskedvalue()) - parseInt(r.step)), s.trigger("setvalue");
                                            }
                                    },
                                },
                                currency: { prefix: "$ ", groupSeparator: ",", alias: "numeric", placeholder: "0", autoGroup: !0, digits: 2, digitsOptional: !1, clearMaskOnLostFocus: !1 },
                                decimal: { alias: "numeric" },
                                integer: { alias: "numeric", digits: 0, radixPoint: "" },
                                percentage: { alias: "numeric", digits: 2, digitsOptional: !0, radixPoint: ".", placeholder: "0", autoGroup: !1, min: 0, max: 100, suffix: " %", allowMinus: !1 },
                            }),
                            t
                        );
                    })
                        ? i.apply(t, a)
                        : i) && (e.exports = r);
    },
    function (e, t, n) {
        "use strict";
        var i, a, r;
        "function" == typeof Symbol && Symbol.iterator,
            (a = [n(0), n(1)]),
            void 0 !==
                (r =
                    "function" ==
                    typeof (i = function (e, t) {
                        function n(e, t) {
                            var n = (e.mask || e)
                                    .replace(/#/g, "9")
                                    .replace(/\)/, "9")
                                    .replace(/[+()#-]/g, ""),
                                i = (t.mask || t)
                                    .replace(/#/g, "9")
                                    .replace(/\)/, "9")
                                    .replace(/[+()#-]/g, ""),
                                a = (e.mask || e).split("#")[0],
                                r = (t.mask || t).split("#")[0];
                            return 0 === r.indexOf(a) ? -1 : 0 === a.indexOf(r) ? 1 : n.localeCompare(i);
                        }
                        var i = t.prototype.analyseMask;
                        return (
                            (t.prototype.analyseMask = function (t, n, a) {
                                var r = {};
                                return (
                                    a.phoneCodes &&
                                        (a.phoneCodes &&
                                            a.phoneCodes.length > 1e3 &&
                                            ((function e(n, i, a) {
                                                (a = a || r), "" !== (i = i || "") && (a[i] = {});
                                                for (var s = "", o = a[i] || a, l = n.length - 1; l >= 0; l--) (o[(s = (t = n[l].mask || n[l]).substr(0, 1))] = o[s] || []), o[s].unshift(t.substr(1)), n.splice(l, 1);
                                                for (var d in o) o[d].length > 500 && e(o[d].slice(), d, o);
                                            })((t = t.substr(1, t.length - 2)).split(a.groupmarker.end + a.alternatormarker + a.groupmarker.start)),
                                            (t = (function t(n) {
                                                var i = "",
                                                    r = [];
                                                for (var s in n)
                                                    e.isArray(n[s])
                                                        ? 1 === n[s].length
                                                            ? r.push(s + n[s])
                                                            : r.push(s + a.groupmarker.start + n[s].join(a.groupmarker.end + a.alternatormarker + a.groupmarker.start) + a.groupmarker.end)
                                                        : r.push(s + t(n[s]));
                                                return 1 === r.length ? (i += r[0]) : (i += a.groupmarker.start + r.join(a.groupmarker.end + a.alternatormarker + a.groupmarker.start) + a.groupmarker.end), i;
                                            })(r))),
                                        (t = t.replace(/9/g, "\\9"))),
                                    i.call(this, t, n, a)
                                );
                            }),
                            t.extendAliases({
                                abstractphone: {
                                    groupmarker: { start: "<", end: ">" },
                                    countrycode: "",
                                    phoneCodes: [],
                                    mask: function (e) {
                                        return (e.definitions = { "#": t.prototype.definitions[9] }), e.phoneCodes.sort(n);
                                    },
                                    keepStatic: !0,
                                    onBeforeMask: function (e, t) {
                                        var n = e.replace(/^0{1,2}/, "").replace(/[\s]/g, "");
                                        return (n.indexOf(t.countrycode) > 1 || -1 === n.indexOf(t.countrycode)) && (n = "+" + t.countrycode + n), n;
                                    },
                                    onUnMask: function (e, t, n) {
                                        return e.replace(/[()#-]/g, "");
                                    },
                                    inputmode: "tel",
                                },
                            }),
                            t
                        );
                    })
                        ? i.apply(t, a)
                        : i) && (e.exports = r);
    },
    function (e, t, n) {
        "use strict";
        var i, a, r;
        "function" == typeof Symbol && Symbol.iterator,
            (a = [n(0), n(1)]),
            void 0 !==
                (r =
                    "function" ==
                    typeof (i = function (e, t) {
                        return (
                            t.extendAliases({
                                Regex: {
                                    mask: "r",
                                    greedy: !1,
                                    repeat: "*",
                                    regex: null,
                                    regexTokens: null,
                                    tokenizer: /\[\^?]?(?:[^\\\]]+|\\[\S\s]?)*]?|\\(?:0(?:[0-3][0-7]{0,2}|[4-7][0-7]?)?|[1-9][0-9]*|x[0-9A-Fa-f]{2}|u[0-9A-Fa-f]{4}|c[A-Za-z]|[\S\s]?)|\((?:\?[:=!]?)?|(?:[?*+]|\{[0-9]+(?:,[0-9]*)?\})\??|[^.?*+^${[()|\\]+|./g,
                                    quantifierFilter: /[0-9]+[^,]/,
                                    isComplete: function (e, t) {
                                        return new RegExp(t.regex, t.casing ? "i" : "").test(e.join(""));
                                    },
                                    definitions: {
                                        r: {
                                            validator: function (t, n, i, a, r) {
                                                function s(e, t) {
                                                    (this.matches = []), (this.isGroup = e || !1), (this.isQuantifier = t || !1), (this.quantifier = { min: 1, max: 1 }), (this.repeaterPart = void 0);
                                                }
                                                function o(t, n) {
                                                    var i = !1;
                                                    n && ((c += "("), f++);
                                                    for (var a = 0; a < t.matches.length; a++) {
                                                        var s = t.matches[a];
                                                        if (!0 === s.isGroup) i = o(s, !0);
                                                        else if (!0 === s.isQuantifier) {
                                                            var d = e.inArray(s, t.matches),
                                                                u = t.matches[d - 1],
                                                                h = c;
                                                            if (isNaN(s.quantifier.max)) {
                                                                for (; s.repeaterPart && s.repeaterPart !== c && s.repeaterPart.length > c.length && !(i = o(u, !0)); );
                                                                (i = i || o(u, !0)) && (s.repeaterPart = c), (c = h + s.quantifier.max);
                                                            } else {
                                                                for (var p = 0, m = s.quantifier.max - 1; p < m && !(i = o(u, !0)); p++);
                                                                c = h + "{" + s.quantifier.min + "," + s.quantifier.max + "}";
                                                            }
                                                        } else if (void 0 !== s.matches) for (var g = 0; g < s.length && !(i = o(s[g], n)); g++);
                                                        else {
                                                            var _;
                                                            if ("[" == s.charAt(0)) {
                                                                for (_ = c, _ += s, b = 0; b < f; b++) _ += ")";
                                                                i = (w = new RegExp("^(" + _ + ")$", r.casing ? "i" : "")).test(l);
                                                            } else
                                                                for (var y = 0, v = s.length; y < v; y++)
                                                                    if ("\\" !== s.charAt(y)) {
                                                                        (_ = c), (_ = (_ += s.substr(0, y + 1)).replace(/\|$/, ""));
                                                                        for (var b = 0; b < f; b++) _ += ")";
                                                                        var w = new RegExp("^(" + _ + ")$", r.casing ? "i" : "");
                                                                        if ((i = w.test(l))) break;
                                                                    }
                                                            c += s;
                                                        }
                                                        if (i) break;
                                                    }
                                                    return n && ((c += ")"), f--), i;
                                                }
                                                var l,
                                                    d,
                                                    u = n.buffer.slice(),
                                                    c = "",
                                                    h = !1,
                                                    f = 0;
                                                null === r.regexTokens &&
                                                    (function () {
                                                        var e,
                                                            t,
                                                            n = new s(),
                                                            i = [];
                                                        for (r.regexTokens = []; (e = r.tokenizer.exec(r.regex)); )
                                                            switch ((t = e[0]).charAt(0)) {
                                                                case "(":
                                                                    i.push(new s(!0));
                                                                    break;
                                                                case ")":
                                                                    (d = i.pop()), i.length > 0 ? i[i.length - 1].matches.push(d) : n.matches.push(d);
                                                                    break;
                                                                case "{":
                                                                case "+":
                                                                case "*":
                                                                    var a = new s(!1, !0),
                                                                        o = (t = t.replace(/[{}]/g, "")).split(","),
                                                                        l = isNaN(o[0]) ? o[0] : parseInt(o[0]),
                                                                        u = 1 === o.length ? l : isNaN(o[1]) ? o[1] : parseInt(o[1]);
                                                                    if (((a.quantifier = { min: l, max: u }), i.length > 0)) {
                                                                        var c = i[i.length - 1].matches;
                                                                        (e = c.pop()).isGroup || ((d = new s(!0)).matches.push(e), (e = d)), c.push(e), c.push(a);
                                                                    } else (e = n.matches.pop()).isGroup || ((d = new s(!0)).matches.push(e), (e = d)), n.matches.push(e), n.matches.push(a);
                                                                    break;
                                                                default:
                                                                    i.length > 0 ? i[i.length - 1].matches.push(t) : n.matches.push(t);
                                                            }
                                                        n.matches.length > 0 && r.regexTokens.push(n);
                                                    })(),
                                                    u.splice(i, 0, t),
                                                    (l = u.join(""));
                                                for (var p = 0; p < r.regexTokens.length; p++) {
                                                    var m = r.regexTokens[p];
                                                    if ((h = o(m, m.isGroup))) break;
                                                }
                                                return h;
                                            },
                                            cardinality: 1,
                                        },
                                    },
                                },
                            }),
                            t
                        );
                    })
                        ? i.apply(t, a)
                        : i) && (e.exports = r);
    },
    function (e, t, n) {
        "use strict";
        var i,
            a,
            r,
            s =
                "function" == typeof Symbol && "symbol" == typeof Symbol.iterator
                    ? function (e) {
                          return typeof e;
                      }
                    : function (e) {
                          return e && "function" == typeof Symbol && e.constructor === Symbol && e !== Symbol.prototype ? "symbol" : typeof e;
                      };
        !(function (s) {
            (a = [n(2), n(1)]), void 0 !== (r = "function" == typeof (i = s) ? i.apply(t, a) : i) && (e.exports = r);
        })(function (e, t) {
            return (
                void 0 === e.fn.inputmask &&
                    (e.fn.inputmask = function (n, i) {
                        var a,
                            r = this[0];
                        if ((void 0 === i && (i = {}), "string" == typeof n))
                            switch (n) {
                                case "unmaskedvalue":
                                    return r && r.inputmask ? r.inputmask.unmaskedvalue() : e(r).val();
                                case "remove":
                                    return this.each(function () {
                                        this.inputmask && this.inputmask.remove();
                                    });
                                case "getemptymask":
                                    return r && r.inputmask ? r.inputmask.getemptymask() : "";
                                case "hasMaskedValue":
                                    return !(!r || !r.inputmask) && r.inputmask.hasMaskedValue();
                                case "isComplete":
                                    return !r || !r.inputmask || r.inputmask.isComplete();
                                case "getmetadata":
                                    return r && r.inputmask ? r.inputmask.getmetadata() : void 0;
                                case "setvalue":
                                    e(r).val(i), r && void 0 === r.inputmask && e(r).triggerHandler("setvalue");
                                    break;
                                case "option":
                                    if ("string" != typeof i)
                                        return this.each(function () {
                                            if (void 0 !== this.inputmask) return this.inputmask.option(i);
                                        });
                                    if (r && void 0 !== r.inputmask) return r.inputmask.option(i);
                                    break;
                                default:
                                    return (
                                        (i.alias = n),
                                        (a = new t(i)),
                                        this.each(function () {
                                            a.mask(this);
                                        })
                                    );
                            }
                        else {
                            if ("object" == (void 0 === n ? "undefined" : s(n)))
                                return (
                                    (a = new t(n)),
                                    void 0 === n.mask && void 0 === n.alias
                                        ? this.each(function () {
                                              if (void 0 !== this.inputmask) return this.inputmask.option(n);
                                              a.mask(this);
                                          })
                                        : this.each(function () {
                                              a.mask(this);
                                          })
                                );
                            if (void 0 === n)
                                return this.each(function () {
                                    (a = new t(i)).mask(this);
                                });
                        }
                    }),
                e.fn.inputmask
            );
        });
    },
]),
(function (e, t) {
    "use strict";
    "undefined" != typeof module && module.exports
        ? (module.exports = t(require("jquery")))
        : "function" == typeof define && define.amd
        ? define(["jquery"], function (e) {
              return t(e);
          })
        : t(e.jQuery);
})(this, function (e) {
    "use strict";
    var t = function (n, i) {
        (this.$element = e(n)),
            (this.options = e.extend({}, t.defaults, i)),
            (this.matcher = this.options.matcher || this.matcher),
            (this.sorter = this.options.sorter || this.sorter),
            (this.select = this.options.select || this.select),
            (this.autoSelect = "boolean" != typeof this.options.autoSelect || this.options.autoSelect),
            (this.highlighter = this.options.highlighter || this.highlighter),
            (this.render = this.options.render || this.render),
            (this.updater = this.options.updater || this.updater),
            (this.displayText = this.options.displayText || this.displayText),
            (this.itemLink = this.options.itemLink || this.itemLink),
            (this.itemTitle = this.options.itemTitle || this.itemTitle),
            (this.followLinkOnSelect = this.options.followLinkOnSelect || this.followLinkOnSelect),
            (this.source = this.options.source),
            (this.delay = this.options.delay),
            (this.theme = (this.options.theme && this.options.themes && this.options.themes[this.options.theme]) || t.defaults.themes[t.defaults.theme]),
            (this.$menu = e(this.options.menu || this.theme.menu)),
            (this.$appendTo = this.options.appendTo ? e(this.options.appendTo) : null),
            (this.fitToElement = "boolean" == typeof this.options.fitToElement && this.options.fitToElement),
            (this.shown = !1),
            this.listen(),
            (this.showHintOnFocus = ("boolean" == typeof this.options.showHintOnFocus || "all" === this.options.showHintOnFocus) && this.options.showHintOnFocus),
            (this.afterSelect = this.options.afterSelect),
            (this.afterEmptySelect = this.options.afterEmptySelect),
            (this.addItem = !1),
            (this.value = this.$element.val() || this.$element.text()),
            (this.keyPressed = !1),
            (this.focused = this.$element.is(":focus"));
    };
    t.prototype = {
        constructor: t,
        setDefault: function (e) {
            if ((this.$element.data("active", e), this.autoSelect || e)) {
                var t = this.updater(e);
                t || (t = ""),
                    this.$element
                        .val(this.displayText(t) || t)
                        .text(this.displayText(t) || t)
                        .change(),
                    this.afterSelect(t);
            }
            return this.hide();
        },
        select: function () {
            var e = this.$menu.find(".active").data("value");
            if ((this.$element.data("active", e), this.autoSelect || e)) {
                var t = this.updater(e);
                t || (t = ""),
                    this.$element
                        .val(this.displayText(t) || t)
                        .text(this.displayText(t) || t)
                        .change(),
                    this.afterSelect(t),
                    this.followLinkOnSelect && this.itemLink(e) ? ((document.location = this.itemLink(e)), this.afterSelect(t)) : this.followLinkOnSelect && !this.itemLink(e) ? this.afterEmptySelect(t) : this.afterSelect(t);
            } else this.afterEmptySelect(t);
            return this.hide();
        },
        updater: function (e) {
            return e;
        },
        setSource: function (e) {
            this.source = e;
        },
        show: function () {
            var t,
                n = e.extend({}, this.$element.position(), { height: this.$element[0].offsetHeight }),
                i = "function" == typeof this.options.scrollHeight ? this.options.scrollHeight.call() : this.options.scrollHeight;
            if (
                (this.shown
                    ? (t = this.$menu)
                    : this.$appendTo
                    ? ((t = this.$menu.appendTo(this.$appendTo)), (this.hasSameParent = this.$appendTo.is(this.$element.parent())))
                    : ((t = this.$menu.insertAfter(this.$element)), (this.hasSameParent = !0)),
                !this.hasSameParent)
            ) {
                t.css("position", "fixed");
                var a = this.$element.offset();
                (n.top = a.top), (n.left = a.left);
            }
            var r = e(t).parent().hasClass("dropup") ? "auto" : n.top + n.height + i,
                s = e(t).hasClass("dropdown-menu-right") ? "auto" : n.left;
            return t.css({ top: r, left: s }).show(), !0 === this.options.fitToElement && t.css("width", this.$element.outerWidth() + "px"), (this.shown = !0), this;
        },
        hide: function () {
            return this.$menu.hide(), (this.shown = !1), this;
        },
        lookup: function (t) {
            if (((this.query = null != t ? t : this.$element.val()), this.query.length < this.options.minLength && !this.options.showHintOnFocus)) return this.shown ? this.hide() : this;
            var n = e.proxy(function () {
                e.isFunction(this.source) && 3 === this.source.length
                    ? this.source(this.query, e.proxy(this.process, this), e.proxy(this.process, this))
                    : e.isFunction(this.source)
                    ? this.source(this.query, e.proxy(this.process, this))
                    : this.source && this.process(this.source);
            }, this);
            clearTimeout(this.lookupWorker), (this.lookupWorker = setTimeout(n, this.delay));
        },
        process: function (t) {
            var n = this;
            return (
                (t = e.grep(t, function (e) {
                    return n.matcher(e);
                })),
                (t = this.sorter(t)).length || this.options.addItem
                    ? (t.length > 0 ? this.$element.data("active", t[0]) : this.$element.data("active", null),
                      "all" != this.options.items && (t = t.slice(0, this.options.items)),
                      this.options.addItem && t.push(this.options.addItem),
                      this.render(t).show())
                    : this.shown
                    ? this.hide()
                    : this
            );
        },
        matcher: function (e) {
            return ~this.displayText(e).toLowerCase().indexOf(this.query.toLowerCase());
        },
        sorter: function (e) {
            for (var t, n = [], i = [], a = []; (t = e.shift()); ) {
                var r = this.displayText(t);
                r.toLowerCase().indexOf(this.query.toLowerCase()) ? (~r.indexOf(this.query) ? i.push(t) : a.push(t)) : n.push(t);
            }
            return n.concat(i, a);
        },
        highlighter: function (e) {
            var t = this.query;
            if ("" === t) return e;
            var n,
                i = e.match(/(>)([^<]*)(<)/g),
                a = [],
                r = [];
            if (i && i.length) for (n = 0; n < i.length; ++n) i[n].length > 2 && a.push(i[n]);
            else (a = []).push(e);
            t = t.replace(/[\(\)\/\.\*\+\?\[\]]/g, function (e) {
                return "\\" + e;
            });
            var s,
                o = new RegExp(t, "g");
            for (n = 0; n < a.length; ++n) (s = a[n].match(o)) && s.length > 0 && r.push(a[n]);
            for (n = 0; n < r.length; ++n) e = e.replace(r[n], r[n].replace(o, "<strong>$&</strong>"));
            return e;
        },
        render: function (t) {
            var n = this,
                i = this,
                a = !1,
                r = [],
                s = n.options.separator;
            return (
                e.each(t, function (e, n) {
                    e > 0 && n[s] !== t[e - 1][s] && r.push({ __type: "divider" }), !n[s] || (0 !== e && n[s] === t[e - 1][s]) || r.push({ __type: "category", name: n[s] }), r.push(n);
                }),
                (t = e(r).map(function (t, r) {
                    if ("category" == (r.__type || !1)) return e(n.options.headerHtml || n.theme.headerHtml).text(r.name)[0];
                    if ("divider" == (r.__type || !1)) return e(n.options.headerDivider || n.theme.headerDivider)[0];
                    var s = i.displayText(r);
                    return (
                        (t = e(n.options.item || n.theme.item).data("value", r))
                            .find(n.options.itemContentSelector || n.theme.itemContentSelector)
                            .addBack(n.options.itemContentSelector || n.theme.itemContentSelector)
                            .html(n.highlighter(s, r)),
                        n.options.followLinkOnSelect && t.find("a").attr("href", i.itemLink(r)),
                        t.find("a").attr("title", i.itemTitle(r)),
                        s == i.$element.val() && (t.addClass("active"), i.$element.data("active", r), (a = !0)),
                        t[0]
                    );
                })),
                this.autoSelect && !a && (t.filter(":not(.dropdown-header)").first().addClass("active"), this.$element.data("active", t.first().data("value"))),
                this.$menu.html(t),
                this
            );
        },
        displayText: function (e) {
            return void 0 !== e && void 0 !== e.name ? e.name : e;
        },
        itemLink: function (e) {
            return null;
        },
        itemTitle: function (e) {
            return null;
        },
        next: function (t) {
            var n = this.$menu.find(".active").removeClass("active").next();
            n.length || (n = e(this.$menu.find(e(this.options.item || this.theme.item).prop("tagName"))[0])), n.addClass("active");
            var i = this.updater(n.data("value"));
            this.$element.val(this.displayText(i) || i);
        },
        prev: function (t) {
            var n = this.$menu.find(".active").removeClass("active").prev();
            n.length || (n = this.$menu.find(e(this.options.item || this.theme.item).prop("tagName")).last()), n.addClass("active");
            var i = this.updater(n.data("value"));
            this.$element.val(this.displayText(i) || i);
        },
        listen: function () {
            this.$element
                .on("focus.bootstrap3Typeahead", e.proxy(this.focus, this))
                .on("blur.bootstrap3Typeahead", e.proxy(this.blur, this))
                .on("keypress.bootstrap3Typeahead", e.proxy(this.keypress, this))
                .on("propertychange.bootstrap3Typeahead input.bootstrap3Typeahead", e.proxy(this.input, this))
                .on("keyup.bootstrap3Typeahead", e.proxy(this.keyup, this)),
                this.eventSupported("keydown") && this.$element.on("keydown.bootstrap3Typeahead", e.proxy(this.keydown, this));
            var t = e(this.options.item || this.theme.item).prop("tagName");
            "ontouchstart" in document.documentElement
                ? this.$menu.on("touchstart", t, e.proxy(this.touchstart, this)).on("touchend", t, e.proxy(this.click, this))
                : this.$menu.on("click", e.proxy(this.click, this)).on("mouseenter", t, e.proxy(this.mouseenter, this)).on("mouseleave", t, e.proxy(this.mouseleave, this)).on("mousedown", e.proxy(this.mousedown, this));
        },
        destroy: function () {
            this.$element.data("typeahead", null),
                this.$element.data("active", null),
                this.$element
                    .unbind("focus.bootstrap3Typeahead")
                    .unbind("blur.bootstrap3Typeahead")
                    .unbind("keypress.bootstrap3Typeahead")
                    .unbind("propertychange.bootstrap3Typeahead input.bootstrap3Typeahead")
                    .unbind("keyup.bootstrap3Typeahead"),
                this.eventSupported("keydown") && this.$element.unbind("keydown.bootstrap3-typeahead"),
                this.$menu.remove(),
                (this.destroyed = !0);
        },
        eventSupported: function (e) {
            var t = e in this.$element;
            return t || (this.$element.setAttribute(e, "return;"), (t = "function" == typeof this.$element[e])), t;
        },
        move: function (e) {
            if (this.shown)
                switch (e.keyCode) {
                    case 9:
                    case 13:
                    case 27:
                        e.preventDefault();
                        break;
                    case 38:
                        if (e.shiftKey) return;
                        e.preventDefault(), this.prev();
                        break;
                    case 40:
                        if (e.shiftKey) return;
                        e.preventDefault(), this.next();
                }
        },
        keydown: function (t) {
            17 !== t.keyCode && ((this.keyPressed = !0), (this.suppressKeyPressRepeat = ~e.inArray(t.keyCode, [40, 38, 9, 13, 27])), this.shown || 40 != t.keyCode ? this.move(t) : this.lookup());
        },
        keypress: function (e) {
            this.suppressKeyPressRepeat || this.move(e);
        },
        input: function (e) {
            var t = this.$element.val() || this.$element.text();
            this.value !== t && ((this.value = t), this.lookup());
        },
        keyup: function (e) {
            if (!this.destroyed)
                switch (e.keyCode) {
                    case 40:
                    case 38:
                    case 16:
                    case 17:
                    case 18:
                        break;
                    case 9:
                        if (!this.shown || (this.showHintOnFocus && !this.keyPressed)) return;
                        this.select();
                        break;
                    case 13:
                        if (!this.shown) return;
                        this.select();
                        break;
                    case 27:
                        if (!this.shown) return;
                        this.hide();
                }
        },
        focus: function (e) {
            this.focused || ((this.focused = !0), (this.keyPressed = !1), this.options.showHintOnFocus && !0 !== this.skipShowHintOnFocus && ("all" === this.options.showHintOnFocus ? this.lookup("") : this.lookup())),
                this.skipShowHintOnFocus && (this.skipShowHintOnFocus = !1);
        },
        blur: function (e) {
            this.mousedover || this.mouseddown || !this.shown
                ? this.mouseddown && ((this.skipShowHintOnFocus = !0), this.$element.focus(), (this.mouseddown = !1))
                : (this.select(), this.hide(), (this.focused = !1), (this.keyPressed = !1));
        },
        click: function (e) {
            e.preventDefault(), (this.skipShowHintOnFocus = !0), this.select(), this.$element.focus(), this.hide();
        },
        mouseenter: function (t) {
            (this.mousedover = !0), this.$menu.find(".active").removeClass("active"), e(t.currentTarget).addClass("active");
        },
        mouseleave: function (e) {
            (this.mousedover = !1), !this.focused && this.shown && this.hide();
        },
        mousedown: function (e) {
            (this.mouseddown = !0),
                this.$menu.one(
                    "mouseup",
                    function (e) {
                        this.mouseddown = !1;
                    }.bind(this)
                );
        },
        touchstart: function (t) {
            t.preventDefault(), this.$menu.find(".active").removeClass("active"), e(t.currentTarget).addClass("active");
        },
        touchend: function (e) {
            e.preventDefault(), this.select(), this.$element.focus();
        },
    };
    var n = e.fn.typeahead;
    (e.fn.typeahead = function (n) {
        var i = arguments;
        return "string" == typeof n && "getActive" == n
            ? this.data("active")
            : this.each(function () {
                  var a = e(this),
                      r = a.data("typeahead"),
                      s = "object" == typeof n && n;
                  r || a.data("typeahead", (r = new t(this, s))), "string" == typeof n && r[n] && (i.length > 1 ? r[n].apply(r, Array.prototype.slice.call(i, 1)) : r[n]());
              });
    }),
        (t.defaults = {
            source: [],
            items: 8,
            minLength: 1,
            scrollHeight: 0,
            autoSelect: !0,
            afterSelect: e.noop,
            afterEmptySelect: e.noop,
            addItem: !1,
            followLinkOnSelect: !1,
            delay: 0,
            separator: "category",
            theme: "bootstrap3",
            themes: {
                bootstrap3: {
                    menu: '<ul class="typeahead dropdown-menu" role="listbox"></ul>',
                    item: '<li><a class="dropdown-item" href="#" role="option"></a></li>',
                    itemContentSelector: "a",
                    headerHtml: '<li class="dropdown-header"></li>',
                    headerDivider: '<li class="divider" role="separator"></li>',
                },
                bootstrap4: {
                    menu: '<div class="typeahead dropdown-menu" role="listbox"></div>',
                    item: '<button class="dropdown-item" role="option"></button>',
                    itemContentSelector: ".dropdown-item",
                    headerHtml: '<h6 class="dropdown-header"></h6>',
                    headerDivider: '<div class="dropdown-divider"></div>',
                },
            },
        }),
        (e.fn.typeahead.Constructor = t),
        (e.fn.typeahead.noConflict = function () {
            return (e.fn.typeahead = n), this;
        }),
        e(document).on("focus.typeahead.data-api", '[data-provide="typeahead"]', function (t) {
            var n = e(this);
            n.data("typeahead") || n.typeahead(n.data());
        });
}),
(function ($, moment)
{
   var pluginName = "bootstrapMaterialDatePicker";
   var pluginDataName = "plugin_" + pluginName;

   moment.locale('en');

   function Plugin(element, options)
   {
      this.currentView = 0;

      this.minDate;
      this.maxDate;

      this._attachedEvents = [];

      this.element = element;
      this.$element = $(element);


      this.params = {date: true, time: true, format: 'YYYY-MM-DD', minDate: null, maxDate: null, currentDate: null, lang: 'en', weekStart: 0, disabledDays: [], shortTime: false, clearButton: false, nowButton: false, cancelText: 'Cancel', okText: 'OK', clearText: 'Clear', nowText: 'Now', switchOnClick: false, triggerEvent: 'focus', monthPicker: false, year:true};
      this.params = $.fn.extend(this.params, options);

      this.name = "dtp_" + this.setName();
      this.$element.attr("data-dtp", this.name);

      moment.locale(this.params.lang);

      this.init();
   }

   $.fn[pluginName] = function (options, p)
   {
      this.each(function ()
      {
         if (!$.data(this, pluginDataName))
         {
            $.data(this, pluginDataName, new Plugin(this, options));
         } else
         {
            if (typeof ($.data(this, pluginDataName)[options]) === 'function')
            {
               $.data(this, pluginDataName)[options](p);
            }
            if (options === 'destroy')
            {
               delete $.data(this, pluginDataName);
            }
         }
      });
      return this;
   };

   Plugin.prototype =
           {
              init: function ()
              {
                 this.initDays();
                 this.initDates();

                 this.initTemplate();

                 this.initButtons();

                 this._attachEvent($(window), 'resize', this._centerBox.bind(this));
                 this._attachEvent(this.$dtpElement.find('.dtp-content'), 'click', this._onElementClick.bind(this));
                 this._attachEvent(this.$dtpElement, 'click', this._onBackgroundClick.bind(this));
                 this._attachEvent(this.$dtpElement.find('.dtp-close > a'), 'click', this._onCloseClick.bind(this));
                 this._attachEvent(this.$element, this.params.triggerEvent, this._fireCalendar.bind(this));
              },
              initDays: function ()
              {
                 this.days = [];
                 for (var i = this.params.weekStart; this.days.length < 7; i++)
                 {
                    if (i > 6)
                    {
                       i = 0;
                    }
                    this.days.push(i.toString());
                 }
              },
              initDates: function ()
              {
                 if (this.$element.val().length > 0)
                 {
                    if (typeof (this.params.format) !== 'undefined' && this.params.format !== null)
                    {
                       this.currentDate = moment(this.$element.val(), this.params.format).locale(this.params.lang);
                    } else
                    {
                       this.currentDate = moment(this.$element.val()).locale(this.params.lang);
                    }
                 } else
                 {
                    if (typeof (this.$element.attr('value')) !== 'undefined' && this.$element.attr('value') !== null && this.$element.attr('value') !== "")
                    {
                       if (typeof (this.$element.attr('value')) === 'string')
                       {
                          if (typeof (this.params.format) !== 'undefined' && this.params.format !== null)
                          {
                             this.currentDate = moment(this.$element.attr('value'), this.params.format).locale(this.params.lang);
                          } else
                          {
                             this.currentDate = moment(this.$element.attr('value')).locale(this.params.lang);
                          }
                       }
                    } else
                    {
                       if (typeof (this.params.currentDate) !== 'undefined' && this.params.currentDate !== null)
                       {
                          if (typeof (this.params.currentDate) === 'string')
                          {
                             if (typeof (this.params.format) !== 'undefined' && this.params.format !== null)
                             {
                                this.currentDate = moment(this.params.currentDate, this.params.format).locale(this.params.lang);
                             } else
                             {
                                this.currentDate = moment(this.params.currentDate).locale(this.params.lang);
                             }
                          } else
                          {
                             if (typeof (this.params.currentDate.isValid) === 'undefined' || typeof (this.params.currentDate.isValid) !== 'function')
                             {
                                var x = this.params.currentDate.getTime();
                                this.currentDate = moment(x, "x").locale(this.params.lang);
                             } else
                             {
                                this.currentDate = this.params.currentDate;
                             }
                          }
                          this.$element.val(this.currentDate.format(this.params.format));
                       } else
                          this.currentDate = moment();
                    }
                 }

                 if (typeof (this.params.minDate) !== 'undefined' && this.params.minDate !== null)
                 {
                    if (typeof (this.params.minDate) === 'string')
                    {
                       if (typeof (this.params.format) !== 'undefined' && this.params.format !== null)
                       {
                          this.minDate = moment(this.params.minDate, this.params.format).locale(this.params.lang);
                       } else
                       {
                          this.minDate = moment(this.params.minDate).locale(this.params.lang);
                       }
                    } else
                    {
                       if (typeof (this.params.minDate.isValid) === 'undefined' || typeof (this.params.minDate.isValid) !== 'function')
                       {
                          var x = this.params.minDate.getTime();
                          this.minDate = moment(x, "x").locale(this.params.lang);
                       } else
                       {
                          this.minDate = this.params.minDate;
                       }
                    }
                 } else if (this.params.minDate === null)
                 {
                    this.minDate = null;
                 }

                 if (typeof (this.params.maxDate) !== 'undefined' && this.params.maxDate !== null)
                 {
                    if (typeof (this.params.maxDate) === 'string')
                    {
                       if (typeof (this.params.format) !== 'undefined' && this.params.format !== null)
                       {
                          this.maxDate = moment(this.params.maxDate, this.params.format).locale(this.params.lang);
                       } else
                       {
                          this.maxDate = moment(this.params.maxDate).locale(this.params.lang);
                       }
                    } else
                    {
                       if (typeof (this.params.maxDate.isValid) === 'undefined' || typeof (this.params.maxDate.isValid) !== 'function')
                       {
                          var x = this.params.maxDate.getTime();
                          this.maxDate = moment(x, "x").locale(this.params.lang);
                       } else
                       {
                          this.maxDate = this.params.maxDate;
                       }
                    }
                 } else if (this.params.maxDate === null)
                 {
                    this.maxDate = null;
                 }

                 if (!this.isAfterMinDate(this.currentDate))
                 {
                    this.currentDate = moment(this.minDate);
                 }
                 if (!this.isBeforeMaxDate(this.currentDate))
                 {
                    this.currentDate = moment(this.maxDate);
                 }
              },
              initTemplate: function ()
              {
                  var yearPicker = "";
                  var y =this.currentDate.year();
                  for (var i = y-3; i < y + 4; i++) {
                      yearPicker += '<div class="year-picker-item" data-year="' + i + '">' + i + '</div>';
                  }
                  this.midYear=y;
                  var yearHtml =
                      '<div class="dtp-picker-year hidden" >' +
                      '<div><a href="javascript:void(0);" class="btn btn-default dtp-select-year-range before" style="margin: 0;"><i class="material-icons">keyboard_arrow_up</i></a></div>' +
                      yearPicker +
                      '<div><a href="javascript:void(0);" class="btn btn-default dtp-select-year-range after" style="margin: 0;"><i class="material-icons">keyboard_arrow_down</i></a></div>' +
                      '</div>';

                 this.template = '<div class="dtp hidden" id="' + this.name + '">' +
                         '<div class="dtp-content">' +
                         '<div class="dtp-date-view">' +
                         '<header class="dtp-header">' +
                         '<div class="dtp-actual-day">Lundi</div>' +
                         '<div class="dtp-close"><a href="javascript:void(0);"><i class="material-icons">clear</i></a></div>' +
                         '</header>' +
                         '<div class="dtp-date hidden">' +
                         '<div>' +
                         '<div class="left center p10">' +
                         '<a href="javascript:void(0);" class="dtp-select-month-before"><i class="material-icons">chevron_left</i></a>' +
                         '</div>' +
                         '<div class="dtp-actual-month p80">MAR</div>' +
                         '<div class="right center p10">' +
                         '<a href="javascript:void(0);" class="dtp-select-month-after"><i class="material-icons">chevron_right</i></a>' +
                         '</div>' +
                         '<div class="clearfix"></div>' +
                         '</div>' +
                         '<div class="dtp-actual-num">13</div>' +
                         '<div>' +
                         '<div class="left center p10">' +
                         '<a href="javascript:void(0);" class="dtp-select-year-before"><i class="material-icons">chevron_left</i></a>' +
                         '</div>' +
                         '<div class="dtp-actual-year p80'+(this.params.year?"":" disabled")+'">2014</div>' +
                         '<div class="right center p10">' +
                         '<a href="javascript:void(0);" class="dtp-select-year-after"><i class="material-icons">chevron_right</i></a>' +
                         '</div>' +
                         '<div class="clearfix"></div>' +
                         '</div>' +
                         '</div>' +
                         '<div class="dtp-time hidden">' +
                         '<div class="dtp-actual-maxtime">23:55</div>' +
                         '</div>' +
                         '<div class="dtp-picker">' +
                         '<div class="dtp-picker-calendar"></div>' +
                         '<div class="dtp-picker-datetime hidden">' +
                         '<div class="dtp-actual-meridien">' +
                         '<div class="left p20">' +
                         '<a class="dtp-meridien-am" href="javascript:void(0);">AM</a>' +
                         '</div>' +
                         '<div class="dtp-actual-time p60"></div>' +
                         '<div class="right p20">' +
                         '<a class="dtp-meridien-pm" href="javascript:void(0);">PM</a>' +
                         '</div>' +
                         '<div class="clearfix"></div>' +
                         '</div>' +
                         '<div id="dtp-svg-clock">' +
                         '</div>' +
                         '</div>' +
                         yearHtml+
                         '</div>' +
                         '</div>' +
                         '<div class="dtp-buttons">' +
                         '<button class="dtp-btn-now btn btn-flat hidden">' + this.params.nowText + '</button>' +
                         '<button class="dtp-btn-clear btn btn-flat hidden">' + this.params.clearText + '</button>' +
                         '<button class="dtp-btn-cancel btn btn-flat">' + this.params.cancelText + '</button>' +
                         '<button class="dtp-btn-ok btn btn-flat">' + this.params.okText + '</button>' +
                         '<div class="clearfix"></div>' +
                         '</div>' +
                         '</div>' +
                         '</div>';

                 if ($('body').find("#" + this.name).length <= 0)
                 {
                    $('body').append(this.template);

                    if (this)
                       this.dtpElement = $('body').find("#" + this.name);
                    this.$dtpElement = $(this.dtpElement);
                 }
              },
              initButtons: function ()
              {
                 this._attachEvent(this.$dtpElement.find('.dtp-btn-cancel'), 'click', this._onCancelClick.bind(this));
                 this._attachEvent(this.$dtpElement.find('.dtp-btn-ok'), 'click', this._onOKClick.bind(this));
                 this._attachEvent(this.$dtpElement.find('a.dtp-select-month-before'), 'click', this._onMonthBeforeClick.bind(this));
                 this._attachEvent(this.$dtpElement.find('a.dtp-select-month-after'), 'click', this._onMonthAfterClick.bind(this));
                 this._attachEvent(this.$dtpElement.find('a.dtp-select-year-before'), 'click', this._onYearBeforeClick.bind(this));
                 this._attachEvent(this.$dtpElement.find('a.dtp-select-year-after'), 'click', this._onYearAfterClick.bind(this));
                 this._attachEvent(this.$dtpElement.find('.dtp-actual-year'), 'click', this._onActualYearClick.bind(this));
                 this._attachEvent(this.$dtpElement.find('a.dtp-select-year-range.before'), 'click', this._onYearRangeBeforeClick.bind(this));
                 this._attachEvent(this.$dtpElement.find('a.dtp-select-year-range.after'), 'click', this._onYearRangeAfterClick.bind(this));
                 this._attachEvent(this.$dtpElement.find('div.year-picker-item'), 'click', this._onYearItemClick.bind(this));

                 if (this.params.clearButton === true)
                 {
                    this._attachEvent(this.$dtpElement.find('.dtp-btn-clear'), 'click', this._onClearClick.bind(this));
                    this.$dtpElement.find('.dtp-btn-clear').removeClass('hidden');
                 }

                 if (this.params.nowButton === true)
                 {
                    this._attachEvent(this.$dtpElement.find('.dtp-btn-now'), 'click', this._onNowClick.bind(this));
                    this.$dtpElement.find('.dtp-btn-now').removeClass('hidden');
                 }

                 if ((this.params.nowButton === true) && (this.params.clearButton === true))
                 {
                    this.$dtpElement.find('.dtp-btn-clear, .dtp-btn-now, .dtp-btn-cancel, .dtp-btn-ok').addClass('btn-xs');
                 } else if ((this.params.nowButton === true) || (this.params.clearButton === true))
                 {
                    this.$dtpElement.find('.dtp-btn-clear, .dtp-btn-now, .dtp-btn-cancel, .dtp-btn-ok').addClass('btn-sm');
                 }
              },
              initMeridienButtons: function ()
              {
                 this.$dtpElement.find('a.dtp-meridien-am').off('click').on('click', this._onSelectAM.bind(this));
                 this.$dtpElement.find('a.dtp-meridien-pm').off('click').on('click', this._onSelectPM.bind(this));
              },
              initDate: function (d)
              {
                 this.currentView = 0;

                 if (this.params.monthPicker === false)
                 {
                    this.$dtpElement.find('.dtp-picker-calendar').removeClass('hidden');
                 }
                 this.$dtpElement.find('.dtp-picker-datetime').addClass('hidden');
                 this.$dtpElement.find('.dtp-picker-year').addClass('hidden');

                 var _date = ((typeof (this.currentDate) !== 'undefined' && this.currentDate !== null) ? this.currentDate : null);
                 var _calendar = this.generateCalendar(this.currentDate);

                 if (typeof (_calendar.week) !== 'undefined' && typeof (_calendar.days) !== 'undefined')
                 {
                    var _template = this.constructHTMLCalendar(_date, _calendar);

                    this.$dtpElement.find('a.dtp-select-day').off('click');
                    this.$dtpElement.find('.dtp-picker-calendar').html(_template);

                    this.$dtpElement.find('a.dtp-select-day').on('click', this._onSelectDate.bind(this));

                    this.toggleButtons(_date);
                 }

                 this._centerBox();
                 this.showDate(_date);
              },
              initHours: function ()
              {
                 this.currentView = 1;

                 this.showTime(this.currentDate);
                 this.initMeridienButtons();

                 if (this.currentDate.hour() < 12)
                 {
                    this.$dtpElement.find('a.dtp-meridien-am').click();
                 } else
                 {
                    this.$dtpElement.find('a.dtp-meridien-pm').click();
                 }

                 var hFormat = ((this.params.shortTime) ? 'h' : 'H');

                 this.$dtpElement.find('.dtp-picker-datetime').removeClass('hidden');
                 this.$dtpElement.find('.dtp-picker-calendar').addClass('hidden');
                 this.$dtpElement.find('.dtp-picker-year').addClass('hidden');

                 var svgClockElement = this.createSVGClock(true);

                 for (var i = 0; i < 12; i++)
                 {
                    var x = -(162 * (Math.sin(-Math.PI * 2 * (i / 12))));
                    var y = -(162 * (Math.cos(-Math.PI * 2 * (i / 12))));

                    var fill = ((this.currentDate.format(hFormat) == i) ? "#8BC34A" : 'transparent');
                    var color = ((this.currentDate.format(hFormat) == i) ? "#fff" : '#000');

                    var svgHourCircle = this.createSVGElement("circle", {'id': 'h-' + i, 'class': 'dtp-select-hour', 'style': 'cursor:pointer', r: '30', cx: x, cy: y, fill: fill, 'data-hour': i});

                    var svgHourText = this.createSVGElement("text", {'id': 'th-' + i, 'class': 'dtp-select-hour-text', 'text-anchor': 'middle', 'style': 'cursor:pointer', 'font-weight': 'bold', 'font-size': '20', x: x, y: y + 7, fill: color, 'data-hour': i});
                    svgHourText.textContent = ((i === 0) ? ((this.params.shortTime) ? 12 : i) : i);

                    if (!this.toggleTime(i, true))
                    {
                       svgHourCircle.className += " disabled";
                       svgHourText.className += " disabled";
                       svgHourText.setAttribute('fill', '#bdbdbd');
                    } else
                    {
                       svgHourCircle.addEventListener('click', this._onSelectHour.bind(this));
                       svgHourText.addEventListener('click', this._onSelectHour.bind(this));
                    }

                    svgClockElement.appendChild(svgHourCircle)
                    svgClockElement.appendChild(svgHourText)
                 }

                 if (!this.params.shortTime)
                 {
                    for (var i = 0; i < 12; i++)
                    {
                       var x = -(110 * (Math.sin(-Math.PI * 2 * (i / 12))));
                       var y = -(110 * (Math.cos(-Math.PI * 2 * (i / 12))));

                       var fill = ((this.currentDate.format(hFormat) == (i + 12)) ? "#8BC34A" : 'transparent');
                       var color = ((this.currentDate.format(hFormat) == (i + 12)) ? "#fff" : '#000');

                       var svgHourCircle = this.createSVGElement("circle", {'id': 'h-' + (i + 12), 'class': 'dtp-select-hour', 'style': 'cursor:pointer', r: '30', cx: x, cy: y, fill: fill, 'data-hour': (i + 12)});

                       var svgHourText = this.createSVGElement("text", {'id': 'th-' + (i + 12), 'class': 'dtp-select-hour-text', 'text-anchor': 'middle', 'style': 'cursor:pointer', 'font-weight': 'bold', 'font-size': '22', x: x, y: y + 7, fill: color, 'data-hour': (i + 12)});
                       svgHourText.textContent = i + 12;

                       if (!this.toggleTime(i + 12, true))
                       {
                          svgHourCircle.className += " disabled";
                          svgHourText.className += " disabled";
                          svgHourText.setAttribute('fill', '#bdbdbd');
                       } else
                       {
                          svgHourCircle.addEventListener('click', this._onSelectHour.bind(this));
                          svgHourText.addEventListener('click', this._onSelectHour.bind(this));
                       }

                       svgClockElement.appendChild(svgHourCircle)
                       svgClockElement.appendChild(svgHourText)
                    }

                    this.$dtpElement.find('a.dtp-meridien-am').addClass('hidden');
                    this.$dtpElement.find('a.dtp-meridien-pm').addClass('hidden');
                 }

                 this._centerBox();
              },
              initMinutes: function ()
              {
                 this.currentView = 2;

                 this.showTime(this.currentDate);

                 this.initMeridienButtons();

                 if (this.currentDate.hour() < 12)
                 {
                    this.$dtpElement.find('a.dtp-meridien-am').click();
                 } else
                 {
                    this.$dtpElement.find('a.dtp-meridien-pm').click();
                 }

                 this.$dtpElement.find('.dtp-picker-year').addClass('hidden');
                 this.$dtpElement.find('.dtp-picker-calendar').addClass('hidden');
                 this.$dtpElement.find('.dtp-picker-datetime').removeClass('hidden');

                 var svgClockElement = this.createSVGClock(false);

                 for (var i = 0; i < 60; i++)
                 {
                    var s = ((i % 5 === 0) ? 162 : 158);
                    var r = ((i % 5 === 0) ? 30 : 20);

                    var x = -(s * (Math.sin(-Math.PI * 2 * (i / 60))));
                    var y = -(s * (Math.cos(-Math.PI * 2 * (i / 60))));

                    var color = ((this.currentDate.format("m") == i) ? "#8BC34A" : 'transparent');

                    var svgMinuteCircle = this.createSVGElement("circle", {'id': 'm-' + i, 'class': 'dtp-select-minute', 'style': 'cursor:pointer', r: r, cx: x, cy: y, fill: color, 'data-minute': i});

                    if (!this.toggleTime(i, false))
                    {
                       svgMinuteCircle.className += " disabled";
                    } else
                    {
                       svgMinuteCircle.addEventListener('click', this._onSelectMinute.bind(this));
                    }

                    svgClockElement.appendChild(svgMinuteCircle)
                 }

                 for (var i = 0; i < 60; i++)
                 {
                    if ((i % 5) === 0)
                    {
                       var x = -(162 * (Math.sin(-Math.PI * 2 * (i / 60))));
                       var y = -(162 * (Math.cos(-Math.PI * 2 * (i / 60))));

                       var color = ((this.currentDate.format("m") == i) ? "#fff" : '#000');

                       var svgMinuteText = this.createSVGElement("text", {'id': 'tm-' + i, 'class': 'dtp-select-minute-text', 'text-anchor': 'middle', 'style': 'cursor:pointer', 'font-weight': 'bold', 'font-size': '20', x: x, y: y + 7, fill: color, 'data-minute': i});
                       svgMinuteText.textContent = i;

                       if (!this.toggleTime(i, false))
                       {
                          svgMinuteText.className += " disabled";
                          svgMinuteText.setAttribute('fill', '#bdbdbd');
                       } else
                       {
                          svgMinuteText.addEventListener('click', this._onSelectMinute.bind(this));
                       }

                       svgClockElement.appendChild(svgMinuteText)
                    }
                 }

                 this._centerBox();
              },
              animateHands: function ()
              {
                 var H = this.currentDate.hour();
                 var M = this.currentDate.minute();

                 var hh = this.$dtpElement.find('.hour-hand');
                 hh[0].setAttribute('transform', "rotate(" + 360 * H / 12 + ")");

                 var mh = this.$dtpElement.find('.minute-hand');
                 mh[0].setAttribute('transform', "rotate(" + 360 * M / 60 + ")");
              },
              createSVGClock: function (isHour)
              {
                 var hl = ((this.params.shortTime) ? -120 : -90);

                 var svgElement = this.createSVGElement("svg", {class: 'svg-clock', viewBox: '0,0,400,400'});
                 var svgGElement = this.createSVGElement("g", {transform: 'translate(200,200) '});
                 var svgClockFace = this.createSVGElement("circle", {r: '192', fill: '#eee', stroke: '#bdbdbd', 'stroke-width': 2});
                 var svgClockCenter = this.createSVGElement("circle", {r: '15', fill: '#757575'});

                 svgGElement.appendChild(svgClockFace)

                 if (isHour)
                 {
                    var svgMinuteHand = this.createSVGElement("line", {class: 'minute-hand', x1: 0, y1: 0, x2: 0, y2: -150, stroke: '#bdbdbd', 'stroke-width': 2});
                    var svgHourHand = this.createSVGElement("line", {class: 'hour-hand', x1: 0, y1: 0, x2: 0, y2: hl, stroke: '#8BC34A', 'stroke-width': 8});

                    svgGElement.appendChild(svgMinuteHand);
                    svgGElement.appendChild(svgHourHand);
                 } else
                 {
                    var svgMinuteHand = this.createSVGElement("line", {class: 'minute-hand', x1: 0, y1: 0, x2: 0, y2: -150, stroke: '#8BC34A', 'stroke-width': 2});
                    var svgHourHand = this.createSVGElement("line", {class: 'hour-hand', x1: 0, y1: 0, x2: 0, y2: hl, stroke: '#bdbdbd', 'stroke-width': 8});

                    svgGElement.appendChild(svgHourHand);
                    svgGElement.appendChild(svgMinuteHand);
                 }

                 svgGElement.appendChild(svgClockCenter)

                 svgElement.appendChild(svgGElement)

                 this.$dtpElement.find("#dtp-svg-clock").empty();
                 this.$dtpElement.find("#dtp-svg-clock")[0].appendChild(svgElement);

                 this.animateHands();

                 return svgGElement;
              },
              createSVGElement: function (tag, attrs)
              {
                 var el = document.createElementNS('http://www.w3.org/2000/svg', tag);
                 for (var k in attrs)
                 {
                    el.setAttribute(k, attrs[k]);
                 }
                 return el;
              },
              isAfterMinDate: function (date, checkHour, checkMinute)
              {
                 var _return = true;

                 if (typeof (this.minDate) !== 'undefined' && this.minDate !== null)
                 {
                    var _minDate = moment(this.minDate);
                    var _date = moment(date);

                    if (!checkHour && !checkMinute)
                    {
                       _minDate.hour(0);
                       _minDate.minute(0);

                       _date.hour(0);
                       _date.minute(0);
                    }

                    _minDate.second(0);
                    _date.second(0);
                    _minDate.millisecond(0);
                    _date.millisecond(0);

                    if (!checkMinute)
                    {
                       _date.minute(0);
                       _minDate.minute(0);

                       _return = (parseInt(_date.format("X")) >= parseInt(_minDate.format("X")));
                    } else
                    {
                       _return = (parseInt(_date.format("X")) >= parseInt(_minDate.format("X")));
                    }
                 }

                 return _return;
              },
              isBeforeMaxDate: function (date, checkTime, checkMinute)
              {
                 var _return = true;

                 if (typeof (this.maxDate) !== 'undefined' && this.maxDate !== null)
                 {
                    var _maxDate = moment(this.maxDate);
                    var _date = moment(date);

                    if (!checkTime && !checkMinute)
                    {
                       _maxDate.hour(0);
                       _maxDate.minute(0);

                       _date.hour(0);
                       _date.minute(0);
                    }

                    _maxDate.second(0);
                    _date.second(0);
                    _maxDate.millisecond(0);
                    _date.millisecond(0);

                    if (!checkMinute)
                    {
                       _date.minute(0);
                       _maxDate.minute(0);

                       _return = (parseInt(_date.format("X")) <= parseInt(_maxDate.format("X")));
                    } else
                    {
                       _return = (parseInt(_date.format("X")) <= parseInt(_maxDate.format("X")));
                    }
                 }

                 return _return;
              },
              rotateElement: function (el, deg)
              {
                 $(el).css
                         ({
                            WebkitTransform: 'rotate(' + deg + 'deg)',
                            '-moz-transform': 'rotate(' + deg + 'deg)'
                         });
              },
              showDate: function (date)
              {
                 if (date)
                 {
                    this.$dtpElement.find('.dtp-actual-day').html(date.locale(this.params.lang).format('dddd'));
                    this.$dtpElement.find('.dtp-actual-month').html(date.locale(this.params.lang).format('MMM').toUpperCase());
                    this.$dtpElement.find('.dtp-actual-num').html(date.locale(this.params.lang).format('DD'));
                    this.$dtpElement.find('.dtp-actual-year').html(date.locale(this.params.lang).format('YYYY'));
                 }
              },
              showTime: function (date)
              {
                 if (date)
                 {
                    var minutes = date.minute();
                    var content = ((this.params.shortTime) ? date.format('hh') : date.format('HH')) + ':' + ((minutes.toString().length == 2) ? minutes : '0' + minutes) + ((this.params.shortTime) ? ' ' + date.format('A') : '');

                    if (this.params.date)
                       this.$dtpElement.find('.dtp-actual-time').html(content);
                    else
                    {
                       if (this.params.shortTime)
                          this.$dtpElement.find('.dtp-actual-day').html(date.format('A'));
                       else
                          this.$dtpElement.find('.dtp-actual-day').html('&nbsp;');

                       this.$dtpElement.find('.dtp-actual-maxtime').html(content);
                    }
                 }
              },
              selectDate: function (date)
              {
                 if (date)
                 {
                    this.currentDate.date(date);

                    this.showDate(this.currentDate);
                    this.$element.trigger('dateSelected', this.currentDate);
                 }
              },
              generateCalendar: function (date)
              {
                 var _calendar = {};

                 if (date !== null)
                 {
                    var startOfMonth = moment(date).locale(this.params.lang).startOf('month');
                    var endOfMonth = moment(date).locale(this.params.lang).endOf('month');

                    var iNumDay = startOfMonth.format('d');

                    _calendar.week = this.days;
                    _calendar.days = [];

                    for (var i = startOfMonth.date(); i <= endOfMonth.date(); i++)
                    {
                       if (i === startOfMonth.date())
                       {
                          var iWeek = _calendar.week.indexOf(iNumDay.toString());
                          if (iWeek > 0)
                          {
                             for (var x = 0; x < iWeek; x++)
                             {
                                _calendar.days.push(0);
                             }
                          }
                       }
                       _calendar.days.push(moment(startOfMonth).locale(this.params.lang).date(i));
                    }
                 }

                 return _calendar;
              },
              constructHTMLCalendar: function (date, calendar)
              {
                 var _template = "";

                 _template += '<div class="dtp-picker-month">' + date.locale(this.params.lang).format('MMMM YYYY') + '</div>';
                 _template += '<table class="table dtp-picker-days"><thead>';
                 for (var i = 0; i < calendar.week.length; i++)
                 {
                    _template += '<th>' + moment(parseInt(calendar.week[i]), "d").locale(this.params.lang).format("dd").substring(0, 1) + '</th>';
                 }

                 _template += '</thead>';
                 _template += '<tbody><tr>';

                 for (var i = 0; i < calendar.days.length; i++)
                 {
                    if (i % 7 == 0)
                       _template += '</tr><tr>';
                    _template += '<td data-date="' + moment(calendar.days[i]).locale(this.params.lang).format("D") + '">';
                    if (calendar.days[i] != 0)
                    {
                        if (this.isBeforeMaxDate(moment(calendar.days[i]), false, false) === false
                            || this.isAfterMinDate(moment(calendar.days[i]), false, false) === false
                            || this.params.disabledDays.indexOf(calendar.days[i].isoWeekday()) !== -1)
                        {
                            _template += '<span class="dtp-select-day">' + moment(calendar.days[i]).locale(this.params.lang).format("DD") + '</span>';
                        } else
                        {
                            if (moment(calendar.days[i]).locale(this.params.lang).format("DD") === moment(this.currentDate).locale(this.params.lang).format("DD"))
                            {
                                _template += '<a href="javascript:void(0);" class="dtp-select-day selected">' + moment(calendar.days[i]).locale(this.params.lang).format("DD") + '</a>';
                            } else
                            {
                                _template += '<a href="javascript:void(0);" class="dtp-select-day">' + moment(calendar.days[i]).locale(this.params.lang).format("DD") + '</a>';
                            }
                        }

                        _template += '</td>';
                    }
                 }
                 _template += '</tr></tbody></table>';

                 return _template;
              },
              setName: function ()
              {
                 var text = "";
                 var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

                 for (var i = 0; i < 5; i++)
                 {
                    text += possible.charAt(Math.floor(Math.random() * possible.length));
                 }

                 return text;
              },
              isPM: function ()
              {
                 return this.$dtpElement.find('a.dtp-meridien-pm').hasClass('selected');
              },
              setElementValue: function ()
              {
                 this.$element.trigger('beforeChange', this.currentDate);
                 if (typeof ($.material) !== 'undefined')
                 {
                    this.$element.removeClass('empty');
                 }
                 this.$element.val(moment(this.currentDate).locale(this.params.lang).format(this.params.format));
                 this.$element.trigger('change', this.currentDate);
              },
              toggleButtons: function (date)
              {
                 if (date && date.isValid())
                 {
                    var startOfMonth = moment(date).locale(this.params.lang).startOf('month');
                    var endOfMonth = moment(date).locale(this.params.lang).endOf('month');

                    if (!this.isAfterMinDate(startOfMonth, false, false))
                    {
                       this.$dtpElement.find('a.dtp-select-month-before').addClass('invisible');
                    } else
                    {
                       this.$dtpElement.find('a.dtp-select-month-before').removeClass('invisible');
                    }

                    if (!this.isBeforeMaxDate(endOfMonth, false, false))
                    {
                       this.$dtpElement.find('a.dtp-select-month-after').addClass('invisible');
                    } else
                    {
                       this.$dtpElement.find('a.dtp-select-month-after').removeClass('invisible');
                    }

                    var startOfYear = moment(date).locale(this.params.lang).startOf('year');
                    var endOfYear = moment(date).locale(this.params.lang).endOf('year');

                    if (!this.isAfterMinDate(startOfYear, false, false))
                    {
                       this.$dtpElement.find('a.dtp-select-year-before').addClass('invisible');
                    } else
                    {
                       this.$dtpElement.find('a.dtp-select-year-before').removeClass('invisible');
                    }

                    if (!this.isBeforeMaxDate(endOfYear, false, false))
                    {
                       this.$dtpElement.find('a.dtp-select-year-after').addClass('invisible');
                    } else
                    {
                       this.$dtpElement.find('a.dtp-select-year-after').removeClass('invisible');
                    }
                 }
              },
              toggleTime: function (value, isHours)
              {
                 var result = false;

                 if (isHours)
                 {
                    var _date = moment(this.currentDate);
                    _date.hour(this.convertHours(value)).minute(0).second(0);

                    result = !(this.isAfterMinDate(_date, true, false) === false || this.isBeforeMaxDate(_date, true, false) === false);
                 } else
                 {
                    var _date = moment(this.currentDate);
                    _date.minute(value).second(0);

                    result = !(this.isAfterMinDate(_date, true, true) === false || this.isBeforeMaxDate(_date, true, true) === false);
                 }

                 return result;
              },
              _attachEvent: function (el, ev, fn)
              {
                 el.on(ev, null, null, fn);
                 this._attachedEvents.push([el, ev, fn]);
              },
              _detachEvents: function ()
              {
                 for (var i = this._attachedEvents.length - 1; i >= 0; i--)
                 {
                    this._attachedEvents[i][0].off(this._attachedEvents[i][1], this._attachedEvents[i][2]);
                    this._attachedEvents.splice(i, 1);
                 }
              },
              _fireCalendar: function ()
              {
                 this.currentView = 0;
                 this.$element.blur();

                 this.initDates();

                 this.show();

                 if (this.params.date)
                 {
                    this.$dtpElement.find('.dtp-date').removeClass('hidden');
                    this.initDate();
                 } else
                 {
                    if (this.params.time)
                    {
                       this.$dtpElement.find('.dtp-time').removeClass('hidden');
                       this.initHours();
                    }
                 }
              },
              _onBackgroundClick: function (e)
              {
                 e.stopPropagation();
                 this.hide();
              },
              _onElementClick: function (e)
              {
                 e.stopPropagation();
              },
              _onKeydown: function (e)
              {
                 if (e.which === 27)
                 {
                    this.hide();
                 }
              },
              _onCloseClick: function ()
              {
                 this.hide();
              },
              _onClearClick: function ()
              {
                 this.currentDate = null;
                 this.$element.trigger('beforeChange', this.currentDate);
                 this.hide();
                 if (typeof ($.material) !== 'undefined')
                 {
                    this.$element.addClass('empty');
                 }
                 this.$element.val('');
                 this.$element.trigger('change', this.currentDate);
              },
              _onNowClick: function ()
              {
                 this.currentDate = moment();

                 if (this.params.date === true)
                 {
                    this.showDate(this.currentDate);

                    if (this.currentView === 0)
                    {
                       this.initDate();
                    }
                 }

                 if (this.params.time === true)
                 {
                    this.showTime(this.currentDate);

                    switch (this.currentView)
                    {
                       case 1 :
                          this.initHours();
                          break;
                       case 2 :
                          this.initMinutes();
                          break;
                    }

                    this.animateHands();
                 }
              },
              _onOKClick: function ()
              {
                 switch (this.currentView)
                 {
                    case 0:
                       if (this.params.time === true)
                       {
                          this.initHours();
                       } else
                       {
                          this.setElementValue();
                          this.hide();
                       }
                       break;
                    case 1:
                       this.initMinutes();
                       break;
                    case 2:
                       this.setElementValue();
                       this.hide();
                       break;
                 }
              },
              _onCancelClick: function ()
              {
                 if (this.params.time)
                 {
                    switch (this.currentView)
                    {
                       case 0:
                          this.hide();
                          break;
                       case 1:
                          if (this.params.date)
                          {
                             this.initDate();
                          } else
                          {
                             this.hide();
                          }
                          break;
                       case 2:
                          this.initHours();
                          break;
                    }
                 } else
                 {
                    this.hide();
                 }
              },
              _onMonthBeforeClick: function ()
              {
                 this.currentDate.subtract(1, 'months');
                 this.initDate(this.currentDate);
                  this._closeYearPicker();
              },
              _onMonthAfterClick: function ()
              {
                 this.currentDate.add(1, 'months');
                 this.initDate(this.currentDate);
                  this._closeYearPicker();
              },
              _onYearBeforeClick: function ()
              {
                 this.currentDate.subtract(1, 'years');
                 this.initDate(this.currentDate);
                  this._closeYearPicker();
              },
              _onYearAfterClick: function ()
              {
                 this.currentDate.add(1, 'years');
                 this.initDate(this.currentDate);
                  this._closeYearPicker();
              },
               refreshYearItems:function () {
                  var curYear=this.currentDate.year(),midYear=this.midYear;
                   var minYear=1850;
                   if (typeof (this.minDate) !== 'undefined' && this.minDate !== null){
                       minYear=moment(this.minDate).year();
                   }

                   var maxYear=2200;
                   if (typeof (this.maxDate) !== 'undefined' && this.maxDate !== null){
                       maxYear=moment(this.maxDate).year();
                   }

                   this.$dtpElement.find(".dtp-picker-year .invisible").removeClass("invisible");
                   this.$dtpElement.find(".year-picker-item").each(function (i, el) {
                       var newYear = midYear - 3 + i;
                       $(el).attr("data-year", newYear).text(newYear).data("year", newYear);
                       if (curYear == newYear) {
                           $(el).addClass("active");
                       } else {
                           $(el).removeClass("active");
                       }
                       if(newYear<minYear || newYear>maxYear){
                           $(el).addClass("invisible")
                       }
                   });
                   if(minYear>=midYear-3){
                       this.$dtpElement.find(".dtp-select-year-range.before").addClass('invisible');
                   }
                   if(maxYear<=midYear+3){
                       this.$dtpElement.find(".dtp-select-year-range.after").addClass('invisible');
                   }

                   this.$dtpElement.find(".dtp-select-year-range").data("mid", midYear);
               },
               _onActualYearClick:function(){
                  if(this.params.year){
                      if(this.$dtpElement.find('.dtp-picker-year.hidden').length>0) {
                          this.$dtpElement.find('.dtp-picker-datetime').addClass("hidden");
                          this.$dtpElement.find('.dtp-picker-calendar').addClass("hidden");
                          this.$dtpElement.find('.dtp-picker-year').removeClass("hidden");
                          this.midYear = this.currentDate.year();
                          this.refreshYearItems();
                      }else{
                          this._closeYearPicker();
                      }
                  }
               },
               _onYearRangeBeforeClick:function(){
                   this.midYear-=7;
                   this.refreshYearItems();
               },
               _onYearRangeAfterClick:function(){
                   this.midYear+=7;
                   this.refreshYearItems();
               },
               _onYearItemClick:function (e) {
                   var newYear = $(e.currentTarget).data('year');
                   var oldYear = this.currentDate.year();
                   var diff = newYear - oldYear;
                   this.currentDate.add(diff, 'years');
                   this.initDate(this.currentDate);

                   this._closeYearPicker();
                   this.$element.trigger("yearSelected",this.currentDate);
               },
               _closeYearPicker:function(){
                   this.$dtpElement.find('.dtp-picker-calendar').removeClass("hidden");
                   this.$dtpElement.find('.dtp-picker-year').addClass("hidden");
               },
               enableYearPicker:function () {
                    this.params.year=true;
                    this.$dtpElement.find(".dtp-actual-year").reomveClass("disabled");
               },
               disableYearPicker:function () {
                   this.params.year=false;
                   this.$dtpElement.find(".dtp-actual-year").addClass("disabled");
                   this._closeYearPicker();
               },
              _onSelectDate: function (e)
              {
                 this.$dtpElement.find('a.dtp-select-day').removeClass('selected');
                 $(e.currentTarget).addClass('selected');

                 this.selectDate($(e.currentTarget).parent().data("date"));

                 if (this.params.switchOnClick === true && this.params.time === true)
                    setTimeout(this.initHours.bind(this), 200);

                 if(this.params.switchOnClick === true && this.params.time === false) {
                    setTimeout(this._onOKClick.bind(this), 200);
                 }

              },
              _onSelectHour: function (e)
              {
                 if (!$(e.target).hasClass('disabled'))
                 {
                    var value = $(e.target).data('hour');
                    var parent = $(e.target).parent();

                    var h = parent.find('.dtp-select-hour');
                    for (var i = 0; i < h.length; i++)
                    {
                       $(h[i]).attr('fill', 'transparent');
                    }
                    var th = parent.find('.dtp-select-hour-text');
                    for (var i = 0; i < th.length; i++)
                    {
                       $(th[i]).attr('fill', '#000');
                    }

                    $(parent.find('#h-' + value)).attr('fill', '#8BC34A');
                    $(parent.find('#th-' + value)).attr('fill', '#fff');

                    this.currentDate.hour(parseInt(value));

                    if (this.params.shortTime === true && this.isPM())
                    {
                       this.currentDate.add(12, 'hours');
                    }

                    this.showTime(this.currentDate);

                    this.animateHands();

                    if (this.params.switchOnClick === true)
                       setTimeout(this.initMinutes.bind(this), 200);
                 }
              },
              _onSelectMinute: function (e)
              {
                 if (!$(e.target).hasClass('disabled'))
                 {
                    var value = $(e.target).data('minute');
                    var parent = $(e.target).parent();

                    var m = parent.find('.dtp-select-minute');
                    for (var i = 0; i < m.length; i++)
                    {
                       $(m[i]).attr('fill', 'transparent');
                    }
                    var tm = parent.find('.dtp-select-minute-text');
                    for (var i = 0; i < tm.length; i++)
                    {
                       $(tm[i]).attr('fill', '#000');
                    }

                    $(parent.find('#m-' + value)).attr('fill', '#8BC34A');
                    $(parent.find('#tm-' + value)).attr('fill', '#fff');

                    this.currentDate.minute(parseInt(value));
                    this.showTime(this.currentDate);

                    this.animateHands();

                    if (this.params.switchOnClick === true)
                       setTimeout(function ()
                       {
                          this.setElementValue();
                          this.hide();
                       }.bind(this), 200);
                 }
              },
              _onSelectAM: function (e)
              {
                 $('.dtp-actual-meridien').find('a').removeClass('selected');
                 $(e.currentTarget).addClass('selected');

                 if (this.currentDate.hour() >= 12)
                 {
                    if (this.currentDate.subtract(12, 'hours'))
                       this.showTime(this.currentDate);
                 }
                 this.toggleTime((this.currentView === 1));
              },
              _onSelectPM: function (e)
              {
                 $('.dtp-actual-meridien').find('a').removeClass('selected');
                 $(e.currentTarget).addClass('selected');

                 if (this.currentDate.hour() < 12)
                 {
                    if (this.currentDate.add(12, 'hours'))
                       this.showTime(this.currentDate);
                 }
                 this.toggleTime((this.currentView === 1));
              },
              _hideCalendar: function() {
                 this.$dtpElement.find('.dtp-picker-calendar').addClass('hidden');
              },
              convertHours: function (h)
              {
                 var _return = h;

                 if (this.params.shortTime === true)
                 {
                    if ((h < 12) && this.isPM())
                    {
                       _return += 12;
                    }
                 }

                 return _return;
              },
              setDate: function (date)
              {
                 this.params.currentDate = date;
                 this.initDates();
              },
              setMinDate: function (date)
              {
                 this.params.minDate = date;
                 this.initDates();
              },
              setMaxDate: function (date)
              {
                 this.params.maxDate = date;
                 this.initDates();
              },
              destroy: function ()
              {
                 this._detachEvents();
                 this.$dtpElement.remove();
              },
              show: function ()
              {
                 this.$dtpElement.removeClass('hidden');
                 this._attachEvent($(window), 'keydown', this._onKeydown.bind(this));
                 this._centerBox();
                 this.$element.trigger('open');
                 if (this.params.monthPicker === true)
                 {
                    this._hideCalendar();
                 }
              },
              hide: function ()
              {
                 $(window).off('keydown', null, null, this._onKeydown.bind(this));
                 this.$dtpElement.addClass('hidden');
                 this.$element.trigger('close');
              },
              _centerBox: function ()
              {
                 var h = (this.$dtpElement.height() - this.$dtpElement.find('.dtp-content').height()) / 2;
                 this.$dtpElement.find('.dtp-content').css('marginLeft', -(this.$dtpElement.find('.dtp-content').width() / 2) + 'px');
                 this.$dtpElement.find('.dtp-content').css('top', h + 'px');
              },
              enableDays: function ()
              {
                 var enableDays = this.params.enableDays;
                 if (enableDays) {
                    $(".dtp-picker-days tbody tr td").each(function () {
                       if (!(($.inArray($(this).index(), enableDays)) >= 0)) {
                          $(this).find('a').css({
                             "background": "#e3e3e3",
                             "cursor": "no-drop",
                             "opacity": "0.5"
                          }).off("click");
                       }
                    });
                 }
              }

           };
})(jQuery, moment),
(function (e, t) {
    "function" == typeof define && define.amd
        ? define("Chartist", [], function () {
              return (e.Chartist = t());
          })
        : "object" == typeof module && module.exports
        ? (module.exports = t())
        : (e.Chartist = t());
})(this, function () {
    var e = { version: "0.11.0" };
    return (
        (function (e, t, n) {
            "use strict";
            (n.namespaces = { svg: "http://www.w3.org/2000/svg", xmlns: "http://www.w3.org/2000/xmlns/", xhtml: "http://www.w3.org/1999/xhtml", xlink: "http://www.w3.org/1999/xlink", ct: "http://gionkunz.github.com/chartist-js/ct" }),
                (n.noop = function (e) {
                    return e;
                }),
                (n.alphaNumerate = function (e) {
                    return String.fromCharCode(97 + (e % 26));
                }),
                (n.extend = function (e) {
                    var t, i, a;
                    for (e = e || {}, t = 1; t < arguments.length; t++) for (var r in (i = arguments[t])) (a = i[r]), (e[r] = "object" != typeof a || null === a || a instanceof Array ? a : n.extend(e[r], a));
                    return e;
                }),
                (n.replaceAll = function (e, t, n) {
                    return e.replace(new RegExp(t, "g"), n);
                }),
                (n.ensureUnit = function (e, t) {
                    return "number" == typeof e && (e += t), e;
                }),
                (n.quantity = function (e) {
                    if ("string" == typeof e) {
                        var t = /^(\d+)\s*(.*)$/g.exec(e);
                        return { value: +t[1], unit: t[2] || void 0 };
                    }
                    return { value: e };
                }),
                (n.querySelector = function (e) {
                    return e instanceof Node ? e : t.querySelector(e);
                }),
                (n.times = function (e) {
                    return Array.apply(null, new Array(e));
                }),
                (n.sum = function (e, t) {
                    return e + (t || 0);
                }),
                (n.mapMultiply = function (e) {
                    return function (t) {
                        return t * e;
                    };
                }),
                (n.mapAdd = function (e) {
                    return function (t) {
                        return t + e;
                    };
                }),
                (n.serialMap = function (e, t) {
                    var i = [],
                        a = Math.max.apply(
                            null,
                            e.map(function (e) {
                                return e.length;
                            })
                        );
                    return (
                        n.times(a).forEach(function (n, a) {
                            var r = e.map(function (e) {
                                return e[a];
                            });
                            i[a] = t.apply(null, r);
                        }),
                        i
                    );
                }),
                (n.roundWithPrecision = function (e, t) {
                    var i = Math.pow(10, t || n.precision);
                    return Math.round(e * i) / i;
                }),
                (n.precision = 8),
                (n.escapingMap = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }),
                (n.serialize = function (e) {
                    return null == e
                        ? e
                        : ("number" == typeof e ? (e = "" + e) : "object" == typeof e && (e = JSON.stringify({ data: e })),
                          Object.keys(n.escapingMap).reduce(function (e, t) {
                              return n.replaceAll(e, t, n.escapingMap[t]);
                          }, e));
                }),
                (n.deserialize = function (e) {
                    if ("string" != typeof e) return e;
                    e = Object.keys(n.escapingMap).reduce(function (e, t) {
                        return n.replaceAll(e, n.escapingMap[t], t);
                    }, e);
                    try {
                        e = void 0 !== (e = JSON.parse(e)).data ? e.data : e;
                    } catch (e) {}
                    return e;
                }),
                (n.createSvg = function (e, t, i, a) {
                    var r;
                    return (
                        (t = t || "100%"),
                        (i = i || "100%"),
                        Array.prototype.slice
                            .call(e.querySelectorAll("svg"))
                            .filter(function (e) {
                                return e.getAttributeNS(n.namespaces.xmlns, "ct");
                            })
                            .forEach(function (t) {
                                e.removeChild(t);
                            }),
                        ((r = new n.Svg("svg").attr({ width: t, height: i }).addClass(a))._node.style.width = t),
                        (r._node.style.height = i),
                        e.appendChild(r._node),
                        r
                    );
                }),
                (n.normalizeData = function (e, t, i) {
                    var a,
                        r = { raw: e, normalized: {} };
                    return (
                        (r.normalized.series = n.getDataArray({ series: e.series || [] }, t, i)),
                        (a = r.normalized.series.every(function (e) {
                            return e instanceof Array;
                        })
                            ? Math.max.apply(
                                  null,
                                  r.normalized.series.map(function (e) {
                                      return e.length;
                                  })
                              )
                            : r.normalized.series.length),
                        (r.normalized.labels = (e.labels || []).slice()),
                        Array.prototype.push.apply(
                            r.normalized.labels,
                            n.times(Math.max(0, a - r.normalized.labels.length)).map(function () {
                                return "";
                            })
                        ),
                        t && n.reverseData(r.normalized),
                        r
                    );
                }),
                (n.safeHasProperty = function (e, t) {
                    return null !== e && "object" == typeof e && e.hasOwnProperty(t);
                }),
                (n.isDataHoleValue = function (e) {
                    return null == e || ("number" == typeof e && isNaN(e));
                }),
                (n.reverseData = function (e) {
                    e.labels.reverse(), e.series.reverse();
                    for (var t = 0; t < e.series.length; t++) "object" == typeof e.series[t] && void 0 !== e.series[t].data ? e.series[t].data.reverse() : e.series[t] instanceof Array && e.series[t].reverse();
                }),
                (n.getDataArray = function (e, t, i) {
                    return e.series.map(function e(t) {
                        if (n.safeHasProperty(t, "value")) return e(t.value);
                        if (n.safeHasProperty(t, "data")) return e(t.data);
                        if (t instanceof Array) return t.map(e);
                        if (!n.isDataHoleValue(t)) {
                            if (i) {
                                var a = {};
                                return (
                                    "string" == typeof i ? (a[i] = n.getNumberOrUndefined(t)) : (a.y = n.getNumberOrUndefined(t)),
                                    (a.x = t.hasOwnProperty("x") ? n.getNumberOrUndefined(t.x) : a.x),
                                    (a.y = t.hasOwnProperty("y") ? n.getNumberOrUndefined(t.y) : a.y),
                                    a
                                );
                            }
                            return n.getNumberOrUndefined(t);
                        }
                    });
                }),
                (n.normalizePadding = function (e, t) {
                    return (
                        (t = t || 0),
                        "number" == typeof e
                            ? { top: e, right: e, bottom: e, left: e }
                            : { top: "number" == typeof e.top ? e.top : t, right: "number" == typeof e.right ? e.right : t, bottom: "number" == typeof e.bottom ? e.bottom : t, left: "number" == typeof e.left ? e.left : t }
                    );
                }),
                (n.getMetaData = function (e, t) {
                    var n = e.data ? e.data[t] : e[t];
                    return n ? n.meta : void 0;
                }),
                (n.orderOfMagnitude = function (e) {
                    return Math.floor(Math.log(Math.abs(e)) / Math.LN10);
                }),
                (n.projectLength = function (e, t, n) {
                    return (t / n.range) * e;
                }),
                (n.getAvailableHeight = function (e, t) {
                    return Math.max((n.quantity(t.height).value || e.height()) - (t.chartPadding.top + t.chartPadding.bottom) - t.axisX.offset, 0);
                }),
                (n.getHighLow = function (e, t, i) {
                    var a = { high: void 0 === (t = n.extend({}, t, i ? t["axis" + i.toUpperCase()] : {})).high ? -Number.MAX_VALUE : +t.high, low: void 0 === t.low ? Number.MAX_VALUE : +t.low },
                        r = void 0 === t.high,
                        s = void 0 === t.low;
                    return (
                        (r || s) &&
                            (function e(t) {
                                if (void 0 !== t)
                                    if (t instanceof Array) for (var n = 0; n < t.length; n++) e(t[n]);
                                    else {
                                        var o = i ? +t[i] : +t;
                                        r && o > a.high && (a.high = o), s && o < a.low && (a.low = o);
                                    }
                            })(e),
                        (t.referenceValue || 0 === t.referenceValue) && ((a.high = Math.max(t.referenceValue, a.high)), (a.low = Math.min(t.referenceValue, a.low))),
                        a.high <= a.low && (0 === a.low ? (a.high = 1) : a.low < 0 ? (a.high = 0) : (a.high > 0 || (a.high = 1), (a.low = 0))),
                        a
                    );
                }),
                (n.isNumeric = function (e) {
                    return null !== e && isFinite(e);
                }),
                (n.isFalseyButZero = function (e) {
                    return !e && 0 !== e;
                }),
                (n.getNumberOrUndefined = function (e) {
                    return n.isNumeric(e) ? +e : void 0;
                }),
                (n.isMultiValue = function (e) {
                    return "object" == typeof e && ("x" in e || "y" in e);
                }),
                (n.getMultiValue = function (e, t) {
                    return n.isMultiValue(e) ? n.getNumberOrUndefined(e[t || "y"]) : n.getNumberOrUndefined(e);
                }),
                (n.rho = function (e) {
                    function t(e, n) {
                        return e % n == 0 ? n : t(n, e % n);
                    }
                    function n(e) {
                        return e * e + 1;
                    }
                    if (1 === e) return e;
                    var i,
                        a = 2,
                        r = 2;
                    if (e % 2 == 0) return 2;
                    do {
                        (a = n(a) % e), (r = n(n(r)) % e), (i = t(Math.abs(a - r), e));
                    } while (1 === i);
                    return i;
                }),
                (n.getBounds = function (e, t, i, a) {
                    function r(e, t) {
                        return e === (e += t) && (e *= 1 + (t > 0 ? f : -f)), e;
                    }
                    var s,
                        o,
                        l,
                        d = 0,
                        u = { high: t.high, low: t.low };
                    (u.valueRange = u.high - u.low),
                        (u.oom = n.orderOfMagnitude(u.valueRange)),
                        (u.step = Math.pow(10, u.oom)),
                        (u.min = Math.floor(u.low / u.step) * u.step),
                        (u.max = Math.ceil(u.high / u.step) * u.step),
                        (u.range = u.max - u.min),
                        (u.numberOfSteps = Math.round(u.range / u.step));
                    var c = n.projectLength(e, u.step, u) < i,
                        h = a ? n.rho(u.range) : 0;
                    if (a && n.projectLength(e, 1, u) >= i) u.step = 1;
                    else if (a && h < u.step && n.projectLength(e, h, u) >= i) u.step = h;
                    else
                        for (;;) {
                            if (c && n.projectLength(e, u.step, u) <= i) u.step *= 2;
                            else {
                                if (c || !(n.projectLength(e, u.step / 2, u) >= i)) break;
                                if (((u.step /= 2), a && u.step % 1 != 0)) {
                                    u.step *= 2;
                                    break;
                                }
                            }
                            if (d++ > 1e3) throw new Error("Exceeded maximum number of iterations while optimizing scale step!");
                        }
                    var f = 2221e-19;
                    for (u.step = Math.max(u.step, f), o = u.min, l = u.max; o + u.step <= u.low; ) o = r(o, u.step);
                    for (; l - u.step >= u.high; ) l = r(l, -u.step);
                    (u.min = o), (u.max = l), (u.range = u.max - u.min);
                    var p = [];
                    for (s = u.min; s <= u.max; s = r(s, u.step)) {
                        var m = n.roundWithPrecision(s);
                        m !== p[p.length - 1] && p.push(m);
                    }
                    return (u.values = p), u;
                }),
                (n.polarToCartesian = function (e, t, n, i) {
                    var a = ((i - 90) * Math.PI) / 180;
                    return { x: e + n * Math.cos(a), y: t + n * Math.sin(a) };
                }),
                (n.createChartRect = function (e, t, i) {
                    var a = !(!t.axisX && !t.axisY),
                        r = a ? t.axisY.offset : 0,
                        s = a ? t.axisX.offset : 0,
                        o = e.width() || n.quantity(t.width).value || 0,
                        l = e.height() || n.quantity(t.height).value || 0,
                        d = n.normalizePadding(t.chartPadding, i);
                    (o = Math.max(o, r + d.left + d.right)), (l = Math.max(l, s + d.top + d.bottom));
                    var u = {
                        padding: d,
                        width: function () {
                            return this.x2 - this.x1;
                        },
                        height: function () {
                            return this.y1 - this.y2;
                        },
                    };
                    return (
                        a
                            ? ("start" === t.axisX.position ? ((u.y2 = d.top + s), (u.y1 = Math.max(l - d.bottom, u.y2 + 1))) : ((u.y2 = d.top), (u.y1 = Math.max(l - d.bottom - s, u.y2 + 1))),
                              "start" === t.axisY.position ? ((u.x1 = d.left + r), (u.x2 = Math.max(o - d.right, u.x1 + 1))) : ((u.x1 = d.left), (u.x2 = Math.max(o - d.right - r, u.x1 + 1))))
                            : ((u.x1 = d.left), (u.x2 = Math.max(o - d.right, u.x1 + 1)), (u.y2 = d.top), (u.y1 = Math.max(l - d.bottom, u.y2 + 1))),
                        u
                    );
                }),
                (n.createGrid = function (e, t, i, a, r, s, o, l) {
                    var d = {};
                    (d[i.units.pos + "1"] = e), (d[i.units.pos + "2"] = e), (d[i.counterUnits.pos + "1"] = a), (d[i.counterUnits.pos + "2"] = a + r);
                    var u = s.elem("line", d, o.join(" "));
                    l.emit("draw", n.extend({ type: "grid", axis: i, index: t, group: s, element: u }, d));
                }),
                (n.createGridBackground = function (e, t, n, i) {
                    var a = e.elem("rect", { x: t.x1, y: t.y2, width: t.width(), height: t.height() }, n, !0);
                    i.emit("draw", { type: "gridBackground", group: e, element: a });
                }),
                (n.createLabel = function (e, i, a, r, s, o, l, d, u, c, h) {
                    var f,
                        p = {};
                    if (((p[s.units.pos] = e + l[s.units.pos]), (p[s.counterUnits.pos] = l[s.counterUnits.pos]), (p[s.units.len] = i), (p[s.counterUnits.len] = Math.max(0, o - 10)), c)) {
                        var m = t.createElement("span");
                        (m.className = u.join(" ")),
                            m.setAttribute("xmlns", n.namespaces.xhtml),
                            (m.innerText = r[a]),
                            (m.style[s.units.len] = Math.round(p[s.units.len]) + "px"),
                            (m.style[s.counterUnits.len] = Math.round(p[s.counterUnits.len]) + "px"),
                            (f = d.foreignObject(m, n.extend({ style: "overflow: visible;" }, p)));
                    } else f = d.elem("text", p, u.join(" ")).text(r[a]);
                    h.emit("draw", n.extend({ type: "label", axis: s, index: a, group: d, element: f, text: r[a] }, p));
                }),
                (n.getSeriesOption = function (e, t, n) {
                    if (e.name && t.series && t.series[e.name]) {
                        var i = t.series[e.name];
                        return i.hasOwnProperty(n) ? i[n] : t[n];
                    }
                    return t[n];
                }),
                (n.optionsProvider = function (t, i, a) {
                    function r(t) {
                        var r = s;
                        if (((s = n.extend({}, l)), i)) for (o = 0; o < i.length; o++) e.matchMedia(i[o][0]).matches && (s = n.extend(s, i[o][1]));
                        a && t && a.emit("optionsChanged", { previousOptions: r, currentOptions: s });
                    }
                    var s,
                        o,
                        l = n.extend({}, t),
                        d = [];
                    if (!e.matchMedia) throw "window.matchMedia not found! Make sure you're using a polyfill.";
                    if (i)
                        for (o = 0; o < i.length; o++) {
                            var u = e.matchMedia(i[o][0]);
                            u.addListener(r), d.push(u);
                        }
                    return (
                        r(),
                        {
                            removeMediaQueryListeners: function () {
                                d.forEach(function (e) {
                                    e.removeListener(r);
                                });
                            },
                            getCurrentOptions: function () {
                                return n.extend({}, s);
                            },
                        }
                    );
                }),
                (n.splitIntoSegments = function (e, t, i) {
                    i = n.extend({}, { increasingX: !1, fillHoles: !1 }, i);
                    for (var a = [], r = !0, s = 0; s < e.length; s += 2)
                        void 0 === n.getMultiValue(t[s / 2].value)
                            ? i.fillHoles || (r = !0)
                            : (i.increasingX && s >= 2 && e[s] <= e[s - 2] && (r = !0),
                              r && (a.push({ pathCoordinates: [], valueData: [] }), (r = !1)),
                              a[a.length - 1].pathCoordinates.push(e[s], e[s + 1]),
                              a[a.length - 1].valueData.push(t[s / 2]));
                    return a;
                });
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            (n.Interpolation = {}),
                (n.Interpolation.none = function (e) {
                    return (
                        (e = n.extend({}, { fillHoles: !1 }, e)),
                        function (t, i) {
                            for (var a = new n.Svg.Path(), r = !0, s = 0; s < t.length; s += 2) {
                                var o = t[s],
                                    l = t[s + 1],
                                    d = i[s / 2];
                                void 0 !== n.getMultiValue(d.value) ? (r ? a.move(o, l, !1, d) : a.line(o, l, !1, d), (r = !1)) : e.fillHoles || (r = !0);
                            }
                            return a;
                        }
                    );
                }),
                (n.Interpolation.simple = function (e) {
                    e = n.extend({}, { divisor: 2, fillHoles: !1 }, e);
                    var t = 1 / Math.max(1, e.divisor);
                    return function (i, a) {
                        for (var r, s, o, l = new n.Svg.Path(), d = 0; d < i.length; d += 2) {
                            var u = i[d],
                                c = i[d + 1],
                                h = (u - r) * t,
                                f = a[d / 2];
                            void 0 !== f.value ? (void 0 === o ? l.move(u, c, !1, f) : l.curve(r + h, s, u - h, c, u, c, !1, f), (r = u), (s = c), (o = f)) : e.fillHoles || (r = u = o = void 0);
                        }
                        return l;
                    };
                }),
                (n.Interpolation.cardinal = function (e) {
                    e = n.extend({}, { tension: 1, fillHoles: !1 }, e);
                    var t = Math.min(1, Math.max(0, e.tension)),
                        i = 1 - t;
                    return function a(r, s) {
                        var o = n.splitIntoSegments(r, s, { fillHoles: e.fillHoles });
                        if (o.length) {
                            if (o.length > 1) {
                                var l = [];
                                return (
                                    o.forEach(function (e) {
                                        l.push(a(e.pathCoordinates, e.valueData));
                                    }),
                                    n.Svg.Path.join(l)
                                );
                            }
                            if (((r = o[0].pathCoordinates), (s = o[0].valueData), r.length <= 4)) return n.Interpolation.none()(r, s);
                            for (var d = new n.Svg.Path().move(r[0], r[1], !1, s[0]), u = 0, c = r.length; c - 2 > u; u += 2) {
                                var h = [
                                    { x: +r[u - 2], y: +r[u - 1] },
                                    { x: +r[u], y: +r[u + 1] },
                                    { x: +r[u + 2], y: +r[u + 3] },
                                    { x: +r[u + 4], y: +r[u + 5] },
                                ];
                                c - 4 === u ? (h[3] = h[2]) : u || (h[0] = { x: +r[u], y: +r[u + 1] }),
                                    d.curve(
                                        (t * (-h[0].x + 6 * h[1].x + h[2].x)) / 6 + i * h[2].x,
                                        (t * (-h[0].y + 6 * h[1].y + h[2].y)) / 6 + i * h[2].y,
                                        (t * (h[1].x + 6 * h[2].x - h[3].x)) / 6 + i * h[2].x,
                                        (t * (h[1].y + 6 * h[2].y - h[3].y)) / 6 + i * h[2].y,
                                        h[2].x,
                                        h[2].y,
                                        !1,
                                        s[(u + 2) / 2]
                                    );
                            }
                            return d;
                        }
                        return n.Interpolation.none()([]);
                    };
                }),
                (n.Interpolation.monotoneCubic = function (e) {
                    return (
                        (e = n.extend({}, { fillHoles: !1 }, e)),
                        function t(i, a) {
                            var r = n.splitIntoSegments(i, a, { fillHoles: e.fillHoles, increasingX: !0 });
                            if (r.length) {
                                if (r.length > 1) {
                                    var s = [];
                                    return (
                                        r.forEach(function (e) {
                                            s.push(t(e.pathCoordinates, e.valueData));
                                        }),
                                        n.Svg.Path.join(s)
                                    );
                                }
                                if (((i = r[0].pathCoordinates), (a = r[0].valueData), i.length <= 4)) return n.Interpolation.none()(i, a);
                                var o,
                                    l,
                                    d = [],
                                    u = [],
                                    c = i.length / 2,
                                    h = [],
                                    f = [],
                                    p = [],
                                    m = [];
                                for (o = 0; o < c; o++) (d[o] = i[2 * o]), (u[o] = i[2 * o + 1]);
                                for (o = 0; o < c - 1; o++) (p[o] = u[o + 1] - u[o]), (m[o] = d[o + 1] - d[o]), (f[o] = p[o] / m[o]);
                                for (h[0] = f[0], h[c - 1] = f[c - 2], o = 1; o < c - 1; o++)
                                    0 === f[o] || 0 === f[o - 1] || f[o - 1] > 0 != f[o] > 0
                                        ? (h[o] = 0)
                                        : ((h[o] = (3 * (m[o - 1] + m[o])) / ((2 * m[o] + m[o - 1]) / f[o - 1] + (m[o] + 2 * m[o - 1]) / f[o])), isFinite(h[o]) || (h[o] = 0));
                                for (l = new n.Svg.Path().move(d[0], u[0], !1, a[0]), o = 0; o < c - 1; o++)
                                    l.curve(d[o] + m[o] / 3, u[o] + (h[o] * m[o]) / 3, d[o + 1] - m[o] / 3, u[o + 1] - (h[o + 1] * m[o]) / 3, d[o + 1], u[o + 1], !1, a[o + 1]);
                                return l;
                            }
                            return n.Interpolation.none()([]);
                        }
                    );
                }),
                (n.Interpolation.step = function (e) {
                    return (
                        (e = n.extend({}, { postpone: !0, fillHoles: !1 }, e)),
                        function (t, i) {
                            for (var a, r, s, o = new n.Svg.Path(), l = 0; l < t.length; l += 2) {
                                var d = t[l],
                                    u = t[l + 1],
                                    c = i[l / 2];
                                void 0 !== c.value ? (void 0 === s ? o.move(d, u, !1, c) : (e.postpone ? o.line(d, r, !1, s) : o.line(a, u, !1, c), o.line(d, u, !1, c)), (a = d), (r = u), (s = c)) : e.fillHoles || (a = r = s = void 0);
                            }
                            return o;
                        }
                    );
                });
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            n.EventEmitter = function () {
                var e = [];
                return {
                    addEventHandler: function (t, n) {
                        (e[t] = e[t] || []), e[t].push(n);
                    },
                    removeEventHandler: function (t, n) {
                        e[t] && (n ? (e[t].splice(e[t].indexOf(n), 1), 0 === e[t].length && delete e[t]) : delete e[t]);
                    },
                    emit: function (t, n) {
                        e[t] &&
                            e[t].forEach(function (e) {
                                e(n);
                            }),
                            e["*"] &&
                                e["*"].forEach(function (e) {
                                    e(t, n);
                                });
                    },
                };
            };
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            n.Class = {
                extend: function (e, t) {
                    var i = t || this.prototype || n.Class,
                        a = Object.create(i);
                    n.Class.cloneDefinitions(a, e);
                    var r = function () {
                        var e,
                            t = a.constructor || function () {};
                        return (e = this === n ? Object.create(a) : this), t.apply(e, Array.prototype.slice.call(arguments, 0)), e;
                    };
                    return (r.prototype = a), (r.super = i), (r.extend = this.extend), r;
                },
                cloneDefinitions: function () {
                    var e = (function (e) {
                            var t = [];
                            if (e.length) for (var n = 0; n < e.length; n++) t.push(e[n]);
                            return t;
                        })(arguments),
                        t = e[0];
                    return (
                        e.splice(1, e.length - 1).forEach(function (e) {
                            Object.getOwnPropertyNames(e).forEach(function (n) {
                                delete t[n], Object.defineProperty(t, n, Object.getOwnPropertyDescriptor(e, n));
                            });
                        }),
                        t
                    );
                },
            };
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            function i() {
                e.addEventListener("resize", this.resizeListener),
                    (this.optionsProvider = n.optionsProvider(this.options, this.responsiveOptions, this.eventEmitter)),
                    this.eventEmitter.addEventHandler(
                        "optionsChanged",
                        function () {
                            this.update();
                        }.bind(this)
                    ),
                    this.options.plugins &&
                        this.options.plugins.forEach(
                            function (e) {
                                e instanceof Array ? e[0](this, e[1]) : e(this);
                            }.bind(this)
                        ),
                    this.eventEmitter.emit("data", { type: "initial", data: this.data }),
                    this.createChart(this.optionsProvider.getCurrentOptions()),
                    (this.initializeTimeoutId = void 0);
            }
            n.Base = n.Class.extend({
                constructor: function (e, t, a, r, s) {
                    (this.container = n.querySelector(e)),
                        (this.data = t || {}),
                        (this.data.labels = this.data.labels || []),
                        (this.data.series = this.data.series || []),
                        (this.defaultOptions = a),
                        (this.options = r),
                        (this.responsiveOptions = s),
                        (this.eventEmitter = n.EventEmitter()),
                        (this.supportsForeignObject = n.Svg.isSupported("Extensibility")),
                        (this.supportsAnimations = n.Svg.isSupported("AnimationEventsAttribute")),
                        (this.resizeListener = function () {
                            this.update();
                        }.bind(this)),
                        this.container && (this.container.__chartist__ && this.container.__chartist__.detach(), (this.container.__chartist__ = this)),
                        (this.initializeTimeoutId = setTimeout(i.bind(this), 0));
                },
                optionsProvider: void 0,
                container: void 0,
                svg: void 0,
                eventEmitter: void 0,
                createChart: function () {
                    throw new Error("Base chart type can't be instantiated!");
                },
                update: function (e, t, i) {
                    return (
                        e && ((this.data = e || {}), (this.data.labels = this.data.labels || []), (this.data.series = this.data.series || []), this.eventEmitter.emit("data", { type: "update", data: this.data })),
                        t &&
                            ((this.options = n.extend({}, i ? this.options : this.defaultOptions, t)),
                            this.initializeTimeoutId || (this.optionsProvider.removeMediaQueryListeners(), (this.optionsProvider = n.optionsProvider(this.options, this.responsiveOptions, this.eventEmitter)))),
                        this.initializeTimeoutId || this.createChart(this.optionsProvider.getCurrentOptions()),
                        this
                    );
                },
                detach: function () {
                    return this.initializeTimeoutId ? e.clearTimeout(this.initializeTimeoutId) : (e.removeEventListener("resize", this.resizeListener), this.optionsProvider.removeMediaQueryListeners()), this;
                },
                on: function (e, t) {
                    return this.eventEmitter.addEventHandler(e, t), this;
                },
                off: function (e, t) {
                    return this.eventEmitter.removeEventHandler(e, t), this;
                },
                version: n.version,
                supportsForeignObject: !1,
            });
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            (n.Svg = n.Class.extend({
                constructor: function (e, i, a, r, s) {
                    e instanceof Element ? (this._node = e) : ((this._node = t.createElementNS(n.namespaces.svg, e)), "svg" === e && this.attr({ "xmlns:ct": n.namespaces.ct })),
                        i && this.attr(i),
                        a && this.addClass(a),
                        r && (s && r._node.firstChild ? r._node.insertBefore(this._node, r._node.firstChild) : r._node.appendChild(this._node));
                },
                attr: function (e, t) {
                    return "string" == typeof e
                        ? t
                            ? this._node.getAttributeNS(t, e)
                            : this._node.getAttribute(e)
                        : (Object.keys(e).forEach(
                              function (t) {
                                  if (void 0 !== e[t])
                                      if (-1 !== t.indexOf(":")) {
                                          var i = t.split(":");
                                          this._node.setAttributeNS(n.namespaces[i[0]], t, e[t]);
                                      } else this._node.setAttribute(t, e[t]);
                              }.bind(this)
                          ),
                          this);
                },
                elem: function (e, t, i, a) {
                    return new n.Svg(e, t, i, this, a);
                },
                parent: function () {
                    return this._node.parentNode instanceof SVGElement ? new n.Svg(this._node.parentNode) : null;
                },
                root: function () {
                    for (var e = this._node; "svg" !== e.nodeName; ) e = e.parentNode;
                    return new n.Svg(e);
                },
                querySelector: function (e) {
                    var t = this._node.querySelector(e);
                    return t ? new n.Svg(t) : null;
                },
                querySelectorAll: function (e) {
                    var t = this._node.querySelectorAll(e);
                    return t.length ? new n.Svg.List(t) : null;
                },
                getNode: function () {
                    return this._node;
                },
                foreignObject: function (e, i, a, r) {
                    if ("string" == typeof e) {
                        var s = t.createElement("div");
                        (s.innerHTML = e), (e = s.firstChild);
                    }
                    e.setAttribute("xmlns", n.namespaces.xmlns);
                    var o = this.elem("foreignObject", i, a, r);
                    return o._node.appendChild(e), o;
                },
                text: function (e) {
                    return this._node.appendChild(t.createTextNode(e)), this;
                },
                empty: function () {
                    for (; this._node.firstChild; ) this._node.removeChild(this._node.firstChild);
                    return this;
                },
                remove: function () {
                    return this._node.parentNode.removeChild(this._node), this.parent();
                },
                replace: function (e) {
                    return this._node.parentNode.replaceChild(e._node, this._node), e;
                },
                append: function (e, t) {
                    return t && this._node.firstChild ? this._node.insertBefore(e._node, this._node.firstChild) : this._node.appendChild(e._node), this;
                },
                classes: function () {
                    return this._node.getAttribute("class") ? this._node.getAttribute("class").trim().split(/\s+/) : [];
                },
                addClass: function (e) {
                    return (
                        this._node.setAttribute(
                            "class",
                            this.classes(this._node)
                                .concat(e.trim().split(/\s+/))
                                .filter(function (e, t, n) {
                                    return n.indexOf(e) === t;
                                })
                                .join(" ")
                        ),
                        this
                    );
                },
                removeClass: function (e) {
                    var t = e.trim().split(/\s+/);
                    return (
                        this._node.setAttribute(
                            "class",
                            this.classes(this._node)
                                .filter(function (e) {
                                    return -1 === t.indexOf(e);
                                })
                                .join(" ")
                        ),
                        this
                    );
                },
                removeAllClasses: function () {
                    return this._node.setAttribute("class", ""), this;
                },
                height: function () {
                    return this._node.getBoundingClientRect().height;
                },
                width: function () {
                    return this._node.getBoundingClientRect().width;
                },
                animate: function (e, t, i) {
                    return (
                        void 0 === t && (t = !0),
                        Object.keys(e).forEach(
                            function (a) {
                                function r(e, t) {
                                    var r,
                                        s,
                                        o,
                                        l = {};
                                    e.easing && ((o = e.easing instanceof Array ? e.easing : n.Svg.Easing[e.easing]), delete e.easing),
                                        (e.begin = n.ensureUnit(e.begin, "ms")),
                                        (e.dur = n.ensureUnit(e.dur, "ms")),
                                        o && ((e.calcMode = "spline"), (e.keySplines = o.join(" ")), (e.keyTimes = "0;1")),
                                        t && ((e.fill = "freeze"), (l[a] = e.from), this.attr(l), (s = n.quantity(e.begin || 0).value), (e.begin = "indefinite")),
                                        (r = this.elem("animate", n.extend({ attributeName: a }, e))),
                                        t &&
                                            setTimeout(
                                                function () {
                                                    try {
                                                        r._node.beginElement();
                                                    } catch (t) {
                                                        (l[a] = e.to), this.attr(l), r.remove();
                                                    }
                                                }.bind(this),
                                                s
                                            ),
                                        i &&
                                            r._node.addEventListener(
                                                "beginEvent",
                                                function () {
                                                    i.emit("animationBegin", { element: this, animate: r._node, params: e });
                                                }.bind(this)
                                            ),
                                        r._node.addEventListener(
                                            "endEvent",
                                            function () {
                                                i && i.emit("animationEnd", { element: this, animate: r._node, params: e }), t && ((l[a] = e.to), this.attr(l), r.remove());
                                            }.bind(this)
                                        );
                                }
                                e[a] instanceof Array
                                    ? e[a].forEach(
                                          function (e) {
                                              r.bind(this)(e, !1);
                                          }.bind(this)
                                      )
                                    : r.bind(this)(e[a], t);
                            }.bind(this)
                        ),
                        this
                    );
                },
            })),
                (n.Svg.isSupported = function (e) {
                    return t.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#" + e, "1.1");
                }),
                (n.Svg.Easing = {
                    easeInSine: [0.47, 0, 0.745, 0.715],
                    easeOutSine: [0.39, 0.575, 0.565, 1],
                    easeInOutSine: [0.445, 0.05, 0.55, 0.95],
                    easeInQuad: [0.55, 0.085, 0.68, 0.53],
                    easeOutQuad: [0.25, 0.46, 0.45, 0.94],
                    easeInOutQuad: [0.455, 0.03, 0.515, 0.955],
                    easeInCubic: [0.55, 0.055, 0.675, 0.19],
                    easeOutCubic: [0.215, 0.61, 0.355, 1],
                    easeInOutCubic: [0.645, 0.045, 0.355, 1],
                    easeInQuart: [0.895, 0.03, 0.685, 0.22],
                    easeOutQuart: [0.165, 0.84, 0.44, 1],
                    easeInOutQuart: [0.77, 0, 0.175, 1],
                    easeInQuint: [0.755, 0.05, 0.855, 0.06],
                    easeOutQuint: [0.23, 1, 0.32, 1],
                    easeInOutQuint: [0.86, 0, 0.07, 1],
                    easeInExpo: [0.95, 0.05, 0.795, 0.035],
                    easeOutExpo: [0.19, 1, 0.22, 1],
                    easeInOutExpo: [1, 0, 0, 1],
                    easeInCirc: [0.6, 0.04, 0.98, 0.335],
                    easeOutCirc: [0.075, 0.82, 0.165, 1],
                    easeInOutCirc: [0.785, 0.135, 0.15, 0.86],
                    easeInBack: [0.6, -0.28, 0.735, 0.045],
                    easeOutBack: [0.175, 0.885, 0.32, 1.275],
                    easeInOutBack: [0.68, -0.55, 0.265, 1.55],
                }),
                (n.Svg.List = n.Class.extend({
                    constructor: function (e) {
                        var t = this;
                        this.svgElements = [];
                        for (var i = 0; i < e.length; i++) this.svgElements.push(new n.Svg(e[i]));
                        Object.keys(n.Svg.prototype)
                            .filter(function (e) {
                                return -1 === ["constructor", "parent", "querySelector", "querySelectorAll", "replace", "append", "classes", "height", "width"].indexOf(e);
                            })
                            .forEach(function (e) {
                                t[e] = function () {
                                    var i = Array.prototype.slice.call(arguments, 0);
                                    return (
                                        t.svgElements.forEach(function (t) {
                                            n.Svg.prototype[e].apply(t, i);
                                        }),
                                        t
                                    );
                                };
                            });
                    },
                }));
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            function i(e, t, i, a, r, s) {
                var o = n.extend({ command: r ? e.toLowerCase() : e.toUpperCase() }, t, s ? { data: s } : {});
                i.splice(a, 0, o);
            }
            function a(e, t) {
                e.forEach(function (n, i) {
                    r[n.command.toLowerCase()].forEach(function (a, r) {
                        t(n, a, i, r, e);
                    });
                });
            }
            var r = { m: ["x", "y"], l: ["x", "y"], c: ["x1", "y1", "x2", "y2", "x", "y"], a: ["rx", "ry", "xAr", "lAf", "sf", "x", "y"] },
                s = { accuracy: 3 };
            (n.Svg.Path = n.Class.extend({
                constructor: function (e, t) {
                    (this.pathElements = []), (this.pos = 0), (this.close = e), (this.options = n.extend({}, s, t));
                },
                position: function (e) {
                    return void 0 !== e ? ((this.pos = Math.max(0, Math.min(this.pathElements.length, e))), this) : this.pos;
                },
                remove: function (e) {
                    return this.pathElements.splice(this.pos, e), this;
                },
                move: function (e, t, n, a) {
                    return i("M", { x: +e, y: +t }, this.pathElements, this.pos++, n, a), this;
                },
                line: function (e, t, n, a) {
                    return i("L", { x: +e, y: +t }, this.pathElements, this.pos++, n, a), this;
                },
                curve: function (e, t, n, a, r, s, o, l) {
                    return i("C", { x1: +e, y1: +t, x2: +n, y2: +a, x: +r, y: +s }, this.pathElements, this.pos++, o, l), this;
                },
                arc: function (e, t, n, a, r, s, o, l, d) {
                    return i("A", { rx: +e, ry: +t, xAr: +n, lAf: +a, sf: +r, x: +s, y: +o }, this.pathElements, this.pos++, l, d), this;
                },
                scale: function (e, t) {
                    return (
                        a(this.pathElements, function (n, i) {
                            n[i] *= "x" === i[0] ? e : t;
                        }),
                        this
                    );
                },
                translate: function (e, t) {
                    return (
                        a(this.pathElements, function (n, i) {
                            n[i] += "x" === i[0] ? e : t;
                        }),
                        this
                    );
                },
                transform: function (e) {
                    return (
                        a(this.pathElements, function (t, n, i, a, r) {
                            var s = e(t, n, i, a, r);
                            (s || 0 === s) && (t[n] = s);
                        }),
                        this
                    );
                },
                parse: function (e) {
                    var t = e
                        .replace(/([A-Za-z])([0-9])/g, "$1 $2")
                        .replace(/([0-9])([A-Za-z])/g, "$1 $2")
                        .split(/[\s,]+/)
                        .reduce(function (e, t) {
                            return t.match(/[A-Za-z]/) && e.push([]), e[e.length - 1].push(t), e;
                        }, []);
                    "Z" === t[t.length - 1][0].toUpperCase() && t.pop();
                    var i = t.map(function (e) {
                            var t = e.shift(),
                                i = r[t.toLowerCase()];
                            return n.extend(
                                { command: t },
                                i.reduce(function (t, n, i) {
                                    return (t[n] = +e[i]), t;
                                }, {})
                            );
                        }),
                        a = [this.pos, 0];
                    return Array.prototype.push.apply(a, i), Array.prototype.splice.apply(this.pathElements, a), (this.pos += i.length), this;
                },
                stringify: function () {
                    var e = Math.pow(10, this.options.accuracy);
                    return (
                        this.pathElements.reduce(
                            function (t, n) {
                                var i = r[n.command.toLowerCase()].map(
                                    function (t) {
                                        return this.options.accuracy ? Math.round(n[t] * e) / e : n[t];
                                    }.bind(this)
                                );
                                return t + n.command + i.join(",");
                            }.bind(this),
                            ""
                        ) + (this.close ? "Z" : "")
                    );
                },
                clone: function (e) {
                    var t = new n.Svg.Path(e || this.close);
                    return (
                        (t.pos = this.pos),
                        (t.pathElements = this.pathElements.slice().map(function (e) {
                            return n.extend({}, e);
                        })),
                        (t.options = n.extend({}, this.options)),
                        t
                    );
                },
                splitByCommand: function (e) {
                    var t = [new n.Svg.Path()];
                    return (
                        this.pathElements.forEach(function (i) {
                            i.command === e.toUpperCase() && 0 !== t[t.length - 1].pathElements.length && t.push(new n.Svg.Path()), t[t.length - 1].pathElements.push(i);
                        }),
                        t
                    );
                },
            })),
                (n.Svg.Path.elementDescriptions = r),
                (n.Svg.Path.join = function (e, t, i) {
                    for (var a = new n.Svg.Path(t, i), r = 0; r < e.length; r++) for (var s = e[r], o = 0; o < s.pathElements.length; o++) a.pathElements.push(s.pathElements[o]);
                    return a;
                });
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            var i = { x: { pos: "x", len: "width", dir: "horizontal", rectStart: "x1", rectEnd: "x2", rectOffset: "y2" }, y: { pos: "y", len: "height", dir: "vertical", rectStart: "y2", rectEnd: "y1", rectOffset: "x1" } };
            (n.Axis = n.Class.extend({
                constructor: function (e, t, n, a) {
                    (this.units = e), (this.counterUnits = e === i.x ? i.y : i.x), (this.chartRect = t), (this.axisLength = t[e.rectEnd] - t[e.rectStart]), (this.gridOffset = t[e.rectOffset]), (this.ticks = n), (this.options = a);
                },
                createGridAndLabels: function (e, t, i, a, r) {
                    var s = a["axis" + this.units.pos.toUpperCase()],
                        o = this.ticks.map(this.projectValue.bind(this)),
                        l = this.ticks.map(s.labelInterpolationFnc);
                    o.forEach(
                        function (d, u) {
                            var c,
                                h = { x: 0, y: 0 };
                            (c = o[u + 1] ? o[u + 1] - d : Math.max(this.axisLength - d, 30)),
                                (n.isFalseyButZero(l[u]) && "" !== l[u]) ||
                                    ("x" === this.units.pos
                                        ? ((d = this.chartRect.x1 + d),
                                          (h.x = a.axisX.labelOffset.x),
                                          "start" === a.axisX.position ? (h.y = this.chartRect.padding.top + a.axisX.labelOffset.y + (i ? 5 : 20)) : (h.y = this.chartRect.y1 + a.axisX.labelOffset.y + (i ? 5 : 20)))
                                        : ((d = this.chartRect.y1 - d),
                                          (h.y = a.axisY.labelOffset.y - (i ? c : 0)),
                                          "start" === a.axisY.position ? (h.x = i ? this.chartRect.padding.left + a.axisY.labelOffset.x : this.chartRect.x1 - 10) : (h.x = this.chartRect.x2 + a.axisY.labelOffset.x + 10)),
                                    s.showGrid && n.createGrid(d, u, this, this.gridOffset, this.chartRect[this.counterUnits.len](), e, [a.classNames.grid, a.classNames[this.units.dir]], r),
                                    s.showLabel && n.createLabel(d, c, u, l, this, s.offset, h, t, [a.classNames.label, a.classNames[this.units.dir], "start" === s.position ? a.classNames[s.position] : a.classNames.end], i, r));
                        }.bind(this)
                    );
                },
                projectValue: function (e, t, n) {
                    throw new Error("Base axis can't be instantiated!");
                },
            })),
                (n.Axis.units = i);
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            n.AutoScaleAxis = n.Axis.extend({
                constructor: function (e, t, i, a) {
                    var r = a.highLow || n.getHighLow(t, a, e.pos);
                    (this.bounds = n.getBounds(i[e.rectEnd] - i[e.rectStart], r, a.scaleMinSpace || 20, a.onlyInteger)),
                        (this.range = { min: this.bounds.min, max: this.bounds.max }),
                        n.AutoScaleAxis.super.constructor.call(this, e, i, this.bounds.values, a);
                },
                projectValue: function (e) {
                    return (this.axisLength * (+n.getMultiValue(e, this.units.pos) - this.bounds.min)) / this.bounds.range;
                },
            });
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            n.FixedScaleAxis = n.Axis.extend({
                constructor: function (e, t, i, a) {
                    var r = a.highLow || n.getHighLow(t, a, e.pos);
                    (this.divisor = a.divisor || 1),
                        (this.ticks =
                            a.ticks ||
                            n.times(this.divisor).map(
                                function (e, t) {
                                    return r.low + ((r.high - r.low) / this.divisor) * t;
                                }.bind(this)
                            )),
                        this.ticks.sort(function (e, t) {
                            return e - t;
                        }),
                        (this.range = { min: r.low, max: r.high }),
                        n.FixedScaleAxis.super.constructor.call(this, e, i, this.ticks, a),
                        (this.stepLength = this.axisLength / this.divisor);
                },
                projectValue: function (e) {
                    return (this.axisLength * (+n.getMultiValue(e, this.units.pos) - this.range.min)) / (this.range.max - this.range.min);
                },
            });
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            n.StepAxis = n.Axis.extend({
                constructor: function (e, t, i, a) {
                    n.StepAxis.super.constructor.call(this, e, i, a.ticks, a);
                    var r = Math.max(1, a.ticks.length - (a.stretch ? 1 : 0));
                    this.stepLength = this.axisLength / r;
                },
                projectValue: function (e, t) {
                    return this.stepLength * t;
                },
            });
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            var i = {
                axisX: { offset: 30, position: "end", labelOffset: { x: 0, y: 0 }, showLabel: !0, showGrid: !0, labelInterpolationFnc: n.noop, type: void 0 },
                axisY: { offset: 40, position: "start", labelOffset: { x: 0, y: 0 }, showLabel: !0, showGrid: !0, labelInterpolationFnc: n.noop, type: void 0, scaleMinSpace: 20, onlyInteger: !1 },
                width: void 0,
                height: void 0,
                showLine: !0,
                showPoint: !0,
                showArea: !1,
                areaBase: 0,
                lineSmooth: !0,
                showGridBackground: !1,
                low: void 0,
                high: void 0,
                chartPadding: { top: 15, right: 15, bottom: 5, left: 10 },
                fullWidth: !1,
                reverseData: !1,
                classNames: {
                    chart: "ct-chart-line",
                    label: "ct-label",
                    labelGroup: "ct-labels",
                    series: "ct-series",
                    line: "ct-line",
                    point: "ct-point",
                    area: "ct-area",
                    grid: "ct-grid",
                    gridGroup: "ct-grids",
                    gridBackground: "ct-grid-background",
                    vertical: "ct-vertical",
                    horizontal: "ct-horizontal",
                    start: "ct-start",
                    end: "ct-end",
                },
            };
            n.Line = n.Base.extend({
                constructor: function (e, t, a, r) {
                    n.Line.super.constructor.call(this, e, t, i, n.extend({}, i, a), r);
                },
                createChart: function (e) {
                    var t = n.normalizeData(this.data, e.reverseData, !0);
                    this.svg = n.createSvg(this.container, e.width, e.height, e.classNames.chart);
                    var a,
                        r,
                        s = this.svg.elem("g").addClass(e.classNames.gridGroup),
                        o = this.svg.elem("g"),
                        l = this.svg.elem("g").addClass(e.classNames.labelGroup),
                        d = n.createChartRect(this.svg, e, i.padding);
                    (a =
                        void 0 === e.axisX.type
                            ? new n.StepAxis(n.Axis.units.x, t.normalized.series, d, n.extend({}, e.axisX, { ticks: t.normalized.labels, stretch: e.fullWidth }))
                            : e.axisX.type.call(n, n.Axis.units.x, t.normalized.series, d, e.axisX)),
                        (r =
                            void 0 === e.axisY.type
                                ? new n.AutoScaleAxis(n.Axis.units.y, t.normalized.series, d, n.extend({}, e.axisY, { high: n.isNumeric(e.high) ? e.high : e.axisY.high, low: n.isNumeric(e.low) ? e.low : e.axisY.low }))
                                : e.axisY.type.call(n, n.Axis.units.y, t.normalized.series, d, e.axisY)),
                        a.createGridAndLabels(s, l, this.supportsForeignObject, e, this.eventEmitter),
                        r.createGridAndLabels(s, l, this.supportsForeignObject, e, this.eventEmitter),
                        e.showGridBackground && n.createGridBackground(s, d, e.classNames.gridBackground, this.eventEmitter),
                        t.raw.series.forEach(
                            function (i, s) {
                                var l = o.elem("g");
                                l.attr({ "ct:series-name": i.name, "ct:meta": n.serialize(i.meta) }), l.addClass([e.classNames.series, i.className || e.classNames.series + "-" + n.alphaNumerate(s)].join(" "));
                                var u = [],
                                    c = [];
                                t.normalized.series[s].forEach(
                                    function (e, o) {
                                        var l = { x: d.x1 + a.projectValue(e, o, t.normalized.series[s]), y: d.y1 - r.projectValue(e, o, t.normalized.series[s]) };
                                        u.push(l.x, l.y), c.push({ value: e, valueIndex: o, meta: n.getMetaData(i, o) });
                                    }.bind(this)
                                );
                                var h = {
                                        lineSmooth: n.getSeriesOption(i, e, "lineSmooth"),
                                        showPoint: n.getSeriesOption(i, e, "showPoint"),
                                        showLine: n.getSeriesOption(i, e, "showLine"),
                                        showArea: n.getSeriesOption(i, e, "showArea"),
                                        areaBase: n.getSeriesOption(i, e, "areaBase"),
                                    },
                                    f = ("function" == typeof h.lineSmooth ? h.lineSmooth : h.lineSmooth ? n.Interpolation.monotoneCubic() : n.Interpolation.none())(u, c);
                                if (
                                    (h.showPoint &&
                                        f.pathElements.forEach(
                                            function (t) {
                                                var o = l
                                                    .elem("line", { x1: t.x, y1: t.y, x2: t.x + 0.01, y2: t.y }, e.classNames.point)
                                                    .attr({ "ct:value": [t.data.value.x, t.data.value.y].filter(n.isNumeric).join(","), "ct:meta": n.serialize(t.data.meta) });
                                                this.eventEmitter.emit("draw", {
                                                    type: "point",
                                                    value: t.data.value,
                                                    index: t.data.valueIndex,
                                                    meta: t.data.meta,
                                                    series: i,
                                                    seriesIndex: s,
                                                    axisX: a,
                                                    axisY: r,
                                                    group: l,
                                                    element: o,
                                                    x: t.x,
                                                    y: t.y,
                                                });
                                            }.bind(this)
                                        ),
                                    h.showLine)
                                ) {
                                    var p = l.elem("path", { d: f.stringify() }, e.classNames.line, !0);
                                    this.eventEmitter.emit("draw", {
                                        type: "line",
                                        values: t.normalized.series[s],
                                        path: f.clone(),
                                        chartRect: d,
                                        index: s,
                                        series: i,
                                        seriesIndex: s,
                                        seriesMeta: i.meta,
                                        axisX: a,
                                        axisY: r,
                                        group: l,
                                        element: p,
                                    });
                                }
                                if (h.showArea && r.range) {
                                    var m = Math.max(Math.min(h.areaBase, r.range.max), r.range.min),
                                        g = d.y1 - r.projectValue(m);
                                    f.splitByCommand("M")
                                        .filter(function (e) {
                                            return e.pathElements.length > 1;
                                        })
                                        .map(function (e) {
                                            var t = e.pathElements[0],
                                                n = e.pathElements[e.pathElements.length - 1];
                                            return e
                                                .clone(!0)
                                                .position(0)
                                                .remove(1)
                                                .move(t.x, g)
                                                .line(t.x, t.y)
                                                .position(e.pathElements.length + 1)
                                                .line(n.x, g);
                                        })
                                        .forEach(
                                            function (n) {
                                                var o = l.elem("path", { d: n.stringify() }, e.classNames.area, !0);
                                                this.eventEmitter.emit("draw", {
                                                    type: "area",
                                                    values: t.normalized.series[s],
                                                    path: n.clone(),
                                                    series: i,
                                                    seriesIndex: s,
                                                    axisX: a,
                                                    axisY: r,
                                                    chartRect: d,
                                                    index: s,
                                                    group: l,
                                                    element: o,
                                                });
                                            }.bind(this)
                                        );
                                }
                            }.bind(this)
                        ),
                        this.eventEmitter.emit("created", { bounds: r.bounds, chartRect: d, axisX: a, axisY: r, svg: this.svg, options: e });
                },
            });
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            var i = {
                axisX: { offset: 30, position: "end", labelOffset: { x: 0, y: 0 }, showLabel: !0, showGrid: !0, labelInterpolationFnc: n.noop, scaleMinSpace: 30, onlyInteger: !1 },
                axisY: { offset: 40, position: "start", labelOffset: { x: 0, y: 0 }, showLabel: !0, showGrid: !0, labelInterpolationFnc: n.noop, scaleMinSpace: 20, onlyInteger: !1 },
                width: void 0,
                height: void 0,
                high: void 0,
                low: void 0,
                referenceValue: 0,
                chartPadding: { top: 15, right: 15, bottom: 5, left: 10 },
                seriesBarDistance: 15,
                stackBars: !1,
                stackMode: "accumulate",
                horizontalBars: !1,
                distributeSeries: !1,
                reverseData: !1,
                showGridBackground: !1,
                classNames: {
                    chart: "ct-chart-bar",
                    horizontalBars: "ct-horizontal-bars",
                    label: "ct-label",
                    labelGroup: "ct-labels",
                    series: "ct-series",
                    bar: "ct-bar",
                    grid: "ct-grid",
                    gridGroup: "ct-grids",
                    gridBackground: "ct-grid-background",
                    vertical: "ct-vertical",
                    horizontal: "ct-horizontal",
                    start: "ct-start",
                    end: "ct-end",
                },
            };
            n.Bar = n.Base.extend({
                constructor: function (e, t, a, r) {
                    n.Bar.super.constructor.call(this, e, t, i, n.extend({}, i, a), r);
                },
                createChart: function (e) {
                    var t, a;
                    e.distributeSeries
                        ? ((t = n.normalizeData(this.data, e.reverseData, e.horizontalBars ? "x" : "y")).normalized.series = t.normalized.series.map(function (e) {
                              return [e];
                          }))
                        : (t = n.normalizeData(this.data, e.reverseData, e.horizontalBars ? "x" : "y")),
                        (this.svg = n.createSvg(this.container, e.width, e.height, e.classNames.chart + (e.horizontalBars ? " " + e.classNames.horizontalBars : "")));
                    var r = this.svg.elem("g").addClass(e.classNames.gridGroup),
                        s = this.svg.elem("g"),
                        o = this.svg.elem("g").addClass(e.classNames.labelGroup);
                    if (e.stackBars && 0 !== t.normalized.series.length) {
                        var l = n.serialMap(t.normalized.series, function () {
                            return Array.prototype.slice
                                .call(arguments)
                                .map(function (e) {
                                    return e;
                                })
                                .reduce(
                                    function (e, t) {
                                        return { x: e.x + (t && t.x) || 0, y: e.y + (t && t.y) || 0 };
                                    },
                                    { x: 0, y: 0 }
                                );
                        });
                        a = n.getHighLow([l], e, e.horizontalBars ? "x" : "y");
                    } else a = n.getHighLow(t.normalized.series, e, e.horizontalBars ? "x" : "y");
                    (a.high = +e.high || (0 === e.high ? 0 : a.high)), (a.low = +e.low || (0 === e.low ? 0 : a.low));
                    var d,
                        u,
                        c,
                        h,
                        f,
                        p = n.createChartRect(this.svg, e, i.padding);
                    (u = e.distributeSeries && e.stackBars ? t.normalized.labels.slice(0, 1) : t.normalized.labels),
                        e.horizontalBars
                            ? ((d = h =
                                  void 0 === e.axisX.type
                                      ? new n.AutoScaleAxis(n.Axis.units.x, t.normalized.series, p, n.extend({}, e.axisX, { highLow: a, referenceValue: 0 }))
                                      : e.axisX.type.call(n, n.Axis.units.x, t.normalized.series, p, n.extend({}, e.axisX, { highLow: a, referenceValue: 0 }))),
                              (c = f = void 0 === e.axisY.type ? new n.StepAxis(n.Axis.units.y, t.normalized.series, p, { ticks: u }) : e.axisY.type.call(n, n.Axis.units.y, t.normalized.series, p, e.axisY)))
                            : ((c = h = void 0 === e.axisX.type ? new n.StepAxis(n.Axis.units.x, t.normalized.series, p, { ticks: u }) : e.axisX.type.call(n, n.Axis.units.x, t.normalized.series, p, e.axisX)),
                              (d = f =
                                  void 0 === e.axisY.type
                                      ? new n.AutoScaleAxis(n.Axis.units.y, t.normalized.series, p, n.extend({}, e.axisY, { highLow: a, referenceValue: 0 }))
                                      : e.axisY.type.call(n, n.Axis.units.y, t.normalized.series, p, n.extend({}, e.axisY, { highLow: a, referenceValue: 0 }))));
                    var m = e.horizontalBars ? p.x1 + d.projectValue(0) : p.y1 - d.projectValue(0),
                        g = [];
                    c.createGridAndLabels(r, o, this.supportsForeignObject, e, this.eventEmitter),
                        d.createGridAndLabels(r, o, this.supportsForeignObject, e, this.eventEmitter),
                        e.showGridBackground && n.createGridBackground(r, p, e.classNames.gridBackground, this.eventEmitter),
                        t.raw.series.forEach(
                            function (i, a) {
                                var r,
                                    o,
                                    l = a - (t.raw.series.length - 1) / 2;
                                (r = e.distributeSeries && !e.stackBars ? c.axisLength / t.normalized.series.length / 2 : e.distributeSeries && e.stackBars ? c.axisLength / 2 : c.axisLength / t.normalized.series[a].length / 2),
                                    (o = s.elem("g")).attr({ "ct:series-name": i.name, "ct:meta": n.serialize(i.meta) }),
                                    o.addClass([e.classNames.series, i.className || e.classNames.series + "-" + n.alphaNumerate(a)].join(" ")),
                                    t.normalized.series[a].forEach(
                                        function (s, u) {
                                            var _, y, v, b;
                                            if (
                                                ((b = e.distributeSeries && !e.stackBars ? a : e.distributeSeries && e.stackBars ? 0 : u),
                                                (_ = e.horizontalBars
                                                    ? { x: p.x1 + d.projectValue(s && s.x ? s.x : 0, u, t.normalized.series[a]), y: p.y1 - c.projectValue(s && s.y ? s.y : 0, b, t.normalized.series[a]) }
                                                    : { x: p.x1 + c.projectValue(s && s.x ? s.x : 0, b, t.normalized.series[a]), y: p.y1 - d.projectValue(s && s.y ? s.y : 0, u, t.normalized.series[a]) }),
                                                c instanceof n.StepAxis &&
                                                    (c.options.stretch || (_[c.units.pos] += r * (e.horizontalBars ? -1 : 1)),
                                                    (_[c.units.pos] += e.stackBars || e.distributeSeries ? 0 : l * e.seriesBarDistance * (e.horizontalBars ? -1 : 1))),
                                                (v = g[u] || m),
                                                (g[u] = v - (m - _[c.counterUnits.pos])),
                                                void 0 !== s)
                                            ) {
                                                var w = {};
                                                (w[c.units.pos + "1"] = _[c.units.pos]),
                                                    (w[c.units.pos + "2"] = _[c.units.pos]),
                                                    !e.stackBars || ("accumulate" !== e.stackMode && e.stackMode)
                                                        ? ((w[c.counterUnits.pos + "1"] = m), (w[c.counterUnits.pos + "2"] = _[c.counterUnits.pos]))
                                                        : ((w[c.counterUnits.pos + "1"] = v), (w[c.counterUnits.pos + "2"] = g[u])),
                                                    (w.x1 = Math.min(Math.max(w.x1, p.x1), p.x2)),
                                                    (w.x2 = Math.min(Math.max(w.x2, p.x1), p.x2)),
                                                    (w.y1 = Math.min(Math.max(w.y1, p.y2), p.y1)),
                                                    (w.y2 = Math.min(Math.max(w.y2, p.y2), p.y1));
                                                var M = n.getMetaData(i, u);
                                                (y = o.elem("line", w, e.classNames.bar).attr({ "ct:value": [s.x, s.y].filter(n.isNumeric).join(","), "ct:meta": n.serialize(M) })),
                                                    this.eventEmitter.emit("draw", n.extend({ type: "bar", value: s, index: u, meta: M, series: i, seriesIndex: a, axisX: h, axisY: f, chartRect: p, group: o, element: y }, w));
                                            }
                                        }.bind(this)
                                    );
                            }.bind(this)
                        ),
                        this.eventEmitter.emit("created", { bounds: d.bounds, chartRect: p, axisX: h, axisY: f, svg: this.svg, options: e });
                },
            });
        })(window, document, e),
        (function (e, t, n) {
            "use strict";
            function i(e, t, n) {
                var i = t.x > e.x;
                return (i && "explode" === n) || (!i && "implode" === n) ? "start" : (i && "implode" === n) || (!i && "explode" === n) ? "end" : "middle";
            }
            var a = {
                width: void 0,
                height: void 0,
                chartPadding: 5,
                classNames: { chartPie: "ct-chart-pie", chartDonut: "ct-chart-donut", series: "ct-series", slicePie: "ct-slice-pie", sliceDonut: "ct-slice-donut", sliceDonutSolid: "ct-slice-donut-solid", label: "ct-label" },
                startAngle: 0,
                total: void 0,
                donut: !1,
                donutSolid: !1,
                donutWidth: 60,
                showLabel: !0,
                labelOffset: 0,
                labelPosition: "inside",
                labelInterpolationFnc: n.noop,
                labelDirection: "neutral",
                reverseData: !1,
                ignoreEmptyValues: !1,
            };
            n.Pie = n.Base.extend({
                constructor: function (e, t, i, r) {
                    n.Pie.super.constructor.call(this, e, t, a, n.extend({}, a, i), r);
                },
                createChart: function (e) {
                    var t,
                        r,
                        s,
                        o,
                        l,
                        d = n.normalizeData(this.data),
                        u = [],
                        c = e.startAngle;
                    (this.svg = n.createSvg(this.container, e.width, e.height, e.donut ? e.classNames.chartDonut : e.classNames.chartPie)),
                        (r = n.createChartRect(this.svg, e, a.padding)),
                        (s = Math.min(r.width() / 2, r.height() / 2)),
                        (l =
                            e.total ||
                            d.normalized.series.reduce(function (e, t) {
                                return e + t;
                            }, 0));
                    var h = n.quantity(e.donutWidth);
                    "%" === h.unit && (h.value *= s / 100),
                        (s -= e.donut && !e.donutSolid ? h.value / 2 : 0),
                        (o = "outside" === e.labelPosition || (e.donut && !e.donutSolid) ? s : "center" === e.labelPosition ? 0 : e.donutSolid ? s - h.value / 2 : s / 2),
                        (o += e.labelOffset);
                    var f = { x: r.x1 + r.width() / 2, y: r.y2 + r.height() / 2 },
                        p =
                            1 ===
                            d.raw.series.filter(function (e) {
                                return e.hasOwnProperty("value") ? 0 !== e.value : 0 !== e;
                            }).length;
                    d.raw.series.forEach(
                        function (e, t) {
                            u[t] = this.svg.elem("g", null, null);
                        }.bind(this)
                    ),
                        e.showLabel && (t = this.svg.elem("g", null, null)),
                        d.raw.series.forEach(
                            function (a, r) {
                                if (0 !== d.normalized.series[r] || !e.ignoreEmptyValues) {
                                    u[r].attr({ "ct:series-name": a.name }), u[r].addClass([e.classNames.series, a.className || e.classNames.series + "-" + n.alphaNumerate(r)].join(" "));
                                    var m = l > 0 ? c + (d.normalized.series[r] / l) * 360 : 0,
                                        g = Math.max(0, c - (0 === r || p ? 0 : 0.2));
                                    m - g >= 359.99 && (m = g + 359.99);
                                    var _,
                                        y,
                                        v,
                                        b = n.polarToCartesian(f.x, f.y, s, g),
                                        w = n.polarToCartesian(f.x, f.y, s, m),
                                        M = new n.Svg.Path(!e.donut || e.donutSolid).move(w.x, w.y).arc(s, s, 0, m - c > 180, 0, b.x, b.y);
                                    e.donut
                                        ? e.donutSolid &&
                                          ((v = s - h.value), (_ = n.polarToCartesian(f.x, f.y, v, c - (0 === r || p ? 0 : 0.2))), (y = n.polarToCartesian(f.x, f.y, v, m)), M.line(_.x, _.y), M.arc(v, v, 0, m - c > 180, 1, y.x, y.y))
                                        : M.line(f.x, f.y);
                                    var x = e.classNames.slicePie;
                                    e.donut && ((x = e.classNames.sliceDonut), e.donutSolid && (x = e.classNames.sliceDonutSolid));
                                    var k = u[r].elem("path", { d: M.stringify() }, x);
                                    if (
                                        (k.attr({ "ct:value": d.normalized.series[r], "ct:meta": n.serialize(a.meta) }),
                                        e.donut && !e.donutSolid && (k._node.style.strokeWidth = h.value + "px"),
                                        this.eventEmitter.emit("draw", {
                                            type: "slice",
                                            value: d.normalized.series[r],
                                            totalDataSum: l,
                                            index: r,
                                            meta: a.meta,
                                            series: a,
                                            group: u[r],
                                            element: k,
                                            path: M.clone(),
                                            center: f,
                                            radius: s,
                                            startAngle: c,
                                            endAngle: m,
                                        }),
                                        e.showLabel)
                                    ) {
                                        var D, L;
                                        (D = 1 === d.raw.series.length ? { x: f.x, y: f.y } : n.polarToCartesian(f.x, f.y, o, c + (m - c) / 2)),
                                            (L = d.normalized.labels && !n.isFalseyButZero(d.normalized.labels[r]) ? d.normalized.labels[r] : d.normalized.series[r]);
                                        var S = e.labelInterpolationFnc(L, r);
                                        if (S || 0 === S) {
                                            var T = t.elem("text", { dx: D.x, dy: D.y, "text-anchor": i(f, D, e.labelDirection) }, e.classNames.label).text("" + S);
                                            this.eventEmitter.emit("draw", { type: "label", index: r, group: t, element: T, text: "" + S, x: D.x, y: D.y });
                                        }
                                    }
                                    c = m;
                                }
                            }.bind(this)
                        ),
                        this.eventEmitter.emit("created", { chartRect: r, svg: this.svg, options: e });
                },
                determineAnchorPosition: i,
            });
        })(window, document, e),
        e
    );
}),
(function (e, t) {
    "function" == typeof define && define.amd
        ? define(["chartist"], function (n) {
              return (e.returnExportsGlobal = t(n));
          })
        : "object" == typeof exports
        ? (module.exports = t(require("chartist")))
        : (e["Chartist.plugins.tooltips"] = t(Chartist));
})(this, function (e) {
    return (
        (function (e, t, n) {
            "use strict";
            var i = { currency: void 0, currencyFormatCallback: void 0, tooltipOffset: { x: 0, y: -20 }, anchorToPoint: !1, appendToBody: !1, class: void 0, pointClass: "ct-point" };
            function a(e) {
                var t = new RegExp("tooltip-show\\s*", "gi");
                e.className = e.className.replace(t, "").trim();
            }
            function r(e, t) {
                return (" " + e.getAttribute("class") + " ").indexOf(" " + t + " ") > -1;
            }
            (n.plugins = n.plugins || {}),
                (n.plugins.tooltip = function (s) {
                    return (
                        (s = n.extend({}, i, s)),
                        function (i) {
                            var o = s.pointClass;
                            i instanceof n.Bar ? (o = "ct-bar") : i instanceof n.Pie && (o = i.options.donut ? "ct-slice-donut" : "ct-slice-pie");
                            var l = i.container,
                                d = l.querySelector(".chartist-tooltip");
                            d || (((d = t.createElement("div")).className = s.class ? "chartist-tooltip " + s.class : "chartist-tooltip"), s.appendToBody ? t.body.appendChild(d) : l.appendChild(d));
                            var u = d.offsetHeight,
                                c = d.offsetWidth;
                            function h(e, t, n) {
                                l.addEventListener(e, function (e) {
                                    (t && !r(e.target, t)) || n(e);
                                });
                            }
                            function f(t) {
                                u = u || d.offsetHeight;
                                var n,
                                    i,
                                    a = -(c = c || d.offsetWidth) / 2 + s.tooltipOffset.x,
                                    r = -u + s.tooltipOffset.y;
                                if (s.appendToBody) (d.style.top = t.pageY + r + "px"), (d.style.left = t.pageX + a + "px");
                                else {
                                    var o = l.getBoundingClientRect(),
                                        h = t.pageX - o.left - e.pageXOffset,
                                        f = t.pageY - o.top - e.pageYOffset;
                                    !0 === s.anchorToPoint && t.target.x2 && t.target.y2 && ((n = parseInt(t.target.x2.baseVal.value)), (i = parseInt(t.target.y2.baseVal.value))),
                                        (d.style.top = (i || f) + r + "px"),
                                        (d.style.left = (n || h) + a + "px");
                                }
                            }
                            a(d),
                                h("mouseover", o, function (e) {
                                    var a,
                                        o = e.target,
                                        l = "",
                                        h = (i instanceof n.Pie ? o : o.parentNode) ? o.parentNode.getAttribute("ct:meta") || o.parentNode.getAttribute("ct:series-name") : "",
                                        p = o.getAttribute("ct:meta") || h || "",
                                        m = !!p,
                                        g = o.getAttribute("ct:value");
                                    if ((s.transformTooltipTextFnc && "function" == typeof s.transformTooltipTextFnc && (g = s.transformTooltipTextFnc(g)), s.tooltipFnc && "function" == typeof s.tooltipFnc)) l = s.tooltipFnc(p, g);
                                    else {
                                        if (s.metaIsHTML) {
                                            var _ = t.createElement("textarea");
                                            (_.innerHTML = p), (p = _.value);
                                        }
                                        if (((p = '<span class="chartist-tooltip-meta">' + p + "</span>"), m)) l += p + "<br>";
                                        else if (i instanceof n.Pie) {
                                            var y = (function (e, t) {
                                                do {
                                                    e = e.nextSibling;
                                                } while (e && !r(e, "ct-label"));
                                                return e;
                                            })(o);
                                            y && (l += ((a = y).innerText || a.textContent) + "<br>");
                                        }
                                        g &&
                                            (s.currency && (g = null != s.currencyFormatCallback ? s.currencyFormatCallback(g, s) : s.currency + g.replace(/(\d)(?=(\d{3})+(?:\.\d+)?$)/g, "$1,")),
                                            (l += g = '<span class="chartist-tooltip-value">' + g + "</span>"));
                                    }
                                    l &&
                                        ((d.innerHTML = l),
                                        f(e),
                                        (function (e) {
                                            r(e, "tooltip-show") || (e.className = e.className + " tooltip-show");
                                        })(d),
                                        (u = d.offsetHeight),
                                        (c = d.offsetWidth));
                                }),
                                h("mouseout", o, function () {
                                    a(d);
                                }),
                                h("mousemove", null, function (e) {
                                    !1 === s.anchorToPoint && f(e);
                                });
                        }
                    );
                });
        })(window, document, e),
        e.plugins.tooltips
    );
}),(function (e) {
    "use strict";
    var t = function (t, n) {
        (this.options = n),
            (this.$element = e(t)),
            (this.$container = e("<div/>", { class: "ms-container" })),
            (this.$selectableContainer = e("<div/>", { class: "ms-selectable" })),
            (this.$selectionContainer = e("<div/>", { class: "ms-selection" })),
            (this.$selectableUl = e("<ul/>", { class: "ms-list", tabindex: "-1" })),
            (this.$selectionUl = e("<ul/>", { class: "ms-list", tabindex: "-1" })),
            (this.scrollTo = 0),
            (this.elemsSelector = "li:visible:not(.ms-optgroup-label,.ms-optgroup-container,." + n.disabledClass + ")");
    };
    (t.prototype = {
        constructor: t,
        init: function () {
            var t = this,
                n = this.$element;
            if (0 === n.next(".ms-container").length) {
                n.css({ position: "absolute", left: "-9999px" }),
                    n.attr("id", n.attr("id") ? n.attr("id") : Math.ceil(1e3 * Math.random()) + "multiselect"),
                    this.$container.attr("id", "ms-" + n.attr("id")),
                    this.$container.addClass(t.options.cssClass),
                    n.find("option").each(function () {
                        t.generateLisFromOption(this);
                    }),
                    this.$selectionUl.find(".ms-optgroup-label").hide(),
                    t.options.selectableHeader && t.$selectableContainer.append(t.options.selectableHeader),
                    t.$selectableContainer.append(t.$selectableUl),
                    t.options.selectableFooter && t.$selectableContainer.append(t.options.selectableFooter),
                    t.options.selectionHeader && t.$selectionContainer.append(t.options.selectionHeader),
                    t.$selectionContainer.append(t.$selectionUl),
                    t.options.selectionFooter && t.$selectionContainer.append(t.options.selectionFooter),
                    t.$container.append(t.$selectableContainer),
                    t.$container.append(t.$selectionContainer),
                    n.after(t.$container),
                    t.activeMouse(t.$selectableUl),
                    t.activeKeyboard(t.$selectableUl);
                var i = t.options.dblClick ? "dblclick" : "click";
                t.$selectableUl.on(i, ".ms-elem-selectable", function () {
                    t.select(e(this).data("ms-value"));
                }),
                    t.$selectionUl.on(i, ".ms-elem-selection", function () {
                        t.deselect(e(this).data("ms-value"));
                    }),
                    t.activeMouse(t.$selectionUl),
                    t.activeKeyboard(t.$selectionUl),
                    n.on("focus", function () {
                        t.$selectableUl.focus();
                    });
            }
            var a = n
                .find("option:selected")
                .map(function () {
                    return e(this).val();
                })
                .get();
            t.select(a, "init"), "function" == typeof t.options.afterInit && t.options.afterInit.call(this, this.$container);
        },
        generateLisFromOption: function (t, n, i) {
            for (var a = this, r = a.$element, s = "", o = e(t), l = 0; l < t.attributes.length; l++) {
                var d = t.attributes[l];
                "value" !== d.name && "disabled" !== d.name && (s += d.name + '="' + d.value + '" ');
            }
            var u = e("<li " + s + "><span>" + a.escapeHTML(o.text()) + "</span></li>"),
                c = u.clone(),
                h = o.val(),
                f = a.sanitize(h);
            u
                .data("ms-value", h)
                .addClass("ms-elem-selectable")
                .attr("id", f + "-selectable"),
                c
                    .data("ms-value", h)
                    .addClass("ms-elem-selection")
                    .attr("id", f + "-selection")
                    .hide(),
                (o.prop("disabled") || r.prop("disabled")) && (c.addClass(a.options.disabledClass), u.addClass(a.options.disabledClass));
            var p = o.parent("optgroup");
            if (p.length > 0) {
                var m = p.attr("label"),
                    g = a.sanitize(m),
                    _ = a.$selectableUl.find("#optgroup-selectable-" + g),
                    y = a.$selectionUl.find("#optgroup-selection-" + g);
                if (0 === _.length) {
                    var v = '<li class="ms-optgroup-container"></li>',
                        b = '<ul class="ms-optgroup"><li class="ms-optgroup-label"><span>' + m + "</span></li></ul>";
                    (_ = e(v)),
                        (y = e(v)),
                        _.attr("id", "optgroup-selectable-" + g),
                        y.attr("id", "optgroup-selection-" + g),
                        _.append(e(b)),
                        y.append(e(b)),
                        a.options.selectableOptgroup &&
                            (_.find(".ms-optgroup-label").on("click", function () {
                                var t = p
                                    .children(":not(:selected, :disabled)")
                                    .map(function () {
                                        return e(this).val();
                                    })
                                    .get();
                                a.select(t);
                            }),
                            y.find(".ms-optgroup-label").on("click", function () {
                                var t = p
                                    .children(":selected:not(:disabled)")
                                    .map(function () {
                                        return e(this).val();
                                    })
                                    .get();
                                a.deselect(t);
                            })),
                        a.$selectableUl.append(_),
                        a.$selectionUl.append(y);
                }
                (n = null == n ? _.find("ul").children().length : n + 1), u.insertAt(n, _.children()), c.insertAt(n, y.children());
            } else (n = null == n ? a.$selectableUl.children().length : n), u.insertAt(n, a.$selectableUl), c.insertAt(n, a.$selectionUl);
        },
        addOption: function (t) {
            var n = this;
            t.value && (t = [t]),
                e.each(t, function (t, i) {
                    if (i.value && 0 === n.$element.find("option[value='" + i.value + "']").length) {
                        var a = e('<option value="' + i.value + '">' + i.text + "</option>"),
                            r = ((t = parseInt(void 0 === i.index ? n.$element.children().length : i.index)), null == i.nested ? n.$element : e("optgroup[label='" + i.nested + "']"));
                        a.insertAt(t, r), n.generateLisFromOption(a.get(0), t, i.nested);
                    }
                });
        },
        escapeHTML: function (t) {
            return e("<div>").text(t).html();
        },
        activeKeyboard: function (t) {
            var n = this;
            t.on("focus", function () {
                e(this).addClass("ms-focus");
            })
                .on("blur", function () {
                    e(this).removeClass("ms-focus");
                })
                .on("keydown", function (i) {
                    switch (i.which) {
                        case 40:
                        case 38:
                            return i.preventDefault(), i.stopPropagation(), void n.moveHighlight(e(this), 38 === i.which ? -1 : 1);
                        case 37:
                        case 39:
                            return i.preventDefault(), i.stopPropagation(), void n.switchList(t);
                        case 9:
                            if (n.$element.is("[tabindex]")) {
                                i.preventDefault();
                                var a = parseInt(n.$element.attr("tabindex"), 10);
                                return (a = i.shiftKey ? a - 1 : a + 1), void e('[tabindex="' + a + '"]').focus();
                            }
                            i.shiftKey && n.$element.trigger("focus");
                    }
                    if (e.inArray(i.which, n.options.keySelect) > -1) return i.preventDefault(), i.stopPropagation(), void n.selectHighlighted(t);
                });
        },
        moveHighlight: function (e, t) {
            var n = e.find(this.elemsSelector),
                i = n.filter(".ms-hover"),
                a = null,
                r = n.first().outerHeight(),
                s = e.height();
            if ((this.$container.prop("id"), n.removeClass("ms-hover"), 1 === t)) {
                if (0 === (a = i.nextAll(this.elemsSelector).first()).length)
                    if ((l = i.parent()).hasClass("ms-optgroup")) {
                        var o = l.parent().next(":visible");
                        a = o.length > 0 ? o.find(this.elemsSelector).first() : n.first();
                    } else a = n.first();
            } else if (-1 === t) {
                var l;
                if (0 === (a = i.prevAll(this.elemsSelector).first()).length)
                    if ((l = i.parent()).hasClass("ms-optgroup")) {
                        var d = l.parent().prev(":visible");
                        a = d.length > 0 ? d.find(this.elemsSelector).last() : n.last();
                    } else a = n.last();
            }
            if (a.length > 0) {
                a.addClass("ms-hover");
                var u = e.scrollTop() + a.position().top - s / 2 + r / 2;
                e.scrollTop(u);
            }
        },
        selectHighlighted: function (e) {
            var t = e.find(this.elemsSelector),
                n = t.filter(".ms-hover").first();
            n.length > 0 && (e.parent().hasClass("ms-selectable") ? this.select(n.data("ms-value")) : this.deselect(n.data("ms-value")), t.removeClass("ms-hover"));
        },
        switchList: function (e) {
            e.blur(), this.$container.find(this.elemsSelector).removeClass("ms-hover"), e.parent().hasClass("ms-selectable") ? this.$selectionUl.focus() : this.$selectableUl.focus();
        },
        activeMouse: function (t) {
            var n = this;
            e("body").on("mouseenter", n.elemsSelector, function () {
                e(this).parents(".ms-container").find(n.elemsSelector).removeClass("ms-hover"), e(this).addClass("ms-hover");
            });
        },
        refresh: function () {
            this.destroy(), this.$element.multiSelect(this.options);
        },
        destroy: function () {
            e("#ms-" + this.$element.attr("id")).remove(), this.$element.css("position", "").css("left", ""), this.$element.removeData("multiselect");
        },
        select: function (t, n) {
            "string" == typeof t && (t = [t]);
            var i = this,
                a = this.$element,
                r = e.map(t, function (e) {
                    return i.sanitize(e);
                }),
                s = this.$selectableUl.find("#" + r.join("-selectable, #") + "-selectable").filter(":not(." + i.options.disabledClass + ")"),
                o = this.$selectionUl.find("#" + r.join("-selection, #") + "-selection").filter(":not(." + i.options.disabledClass + ")"),
                l = a.find("option:not(:disabled)").filter(function () {
                    return e.inArray(this.value, t) > -1;
                });
            if (("init" === n && ((s = this.$selectableUl.find("#" + r.join("-selectable, #") + "-selectable")), (o = this.$selectionUl.find("#" + r.join("-selection, #") + "-selection"))), s.length > 0)) {
                s.addClass("ms-selected").hide(), o.addClass("ms-selected").show(), l.prop("selected", !0), i.$container.find(i.elemsSelector).removeClass("ms-hover");
                var d = i.$selectableUl.children(".ms-optgroup-container");
                if (d.length > 0)
                    d.each(function () {
                        var t = e(this).find(".ms-elem-selectable");
                        t.length === t.filter(".ms-selected").length && e(this).find(".ms-optgroup-label").hide();
                    }),
                        i.$selectionUl.children(".ms-optgroup-container").each(function () {
                            e(this).find(".ms-elem-selection").filter(".ms-selected").length > 0 && e(this).find(".ms-optgroup-label").show();
                        });
                else if (i.options.keepOrder && "init" !== n) {
                    var u = i.$selectionUl.find(".ms-selected");
                    u.length > 1 && u.last().get(0) != o.get(0) && o.insertAfter(u.last());
                }
                "init" !== n && (a.trigger("change"), "function" == typeof i.options.afterSelect && i.options.afterSelect.call(this, t));
            }
        },
        deselect: function (t) {
            "string" == typeof t && (t = [t]);
            var n = this,
                i = this.$element,
                a = e.map(t, function (e) {
                    return n.sanitize(e);
                }),
                r = this.$selectableUl.find("#" + a.join("-selectable, #") + "-selectable"),
                s = this.$selectionUl
                    .find("#" + a.join("-selection, #") + "-selection")
                    .filter(".ms-selected")
                    .filter(":not(." + n.options.disabledClass + ")"),
                o = i.find("option").filter(function () {
                    return e.inArray(this.value, t) > -1;
                });
            if (s.length > 0) {
                r.removeClass("ms-selected").show(), s.removeClass("ms-selected").hide(), o.prop("selected", !1), n.$container.find(n.elemsSelector).removeClass("ms-hover");
                var l = n.$selectableUl.children(".ms-optgroup-container");
                l.length > 0 &&
                    (l.each(function () {
                        e(this).find(".ms-elem-selectable").filter(":not(.ms-selected)").length > 0 && e(this).find(".ms-optgroup-label").show();
                    }),
                    n.$selectionUl.children(".ms-optgroup-container").each(function () {
                        0 === e(this).find(".ms-elem-selection").filter(".ms-selected").length && e(this).find(".ms-optgroup-label").hide();
                    })),
                    i.trigger("change"),
                    "function" == typeof n.options.afterDeselect && n.options.afterDeselect.call(this, t);
            }
        },
        select_all: function () {
            var t = this.$element,
                n = t.val();
            if (
                (t.find('option:not(":disabled")').prop("selected", !0),
                this.$selectableUl
                    .find(".ms-elem-selectable")
                    .filter(":not(." + this.options.disabledClass + ")")
                    .addClass("ms-selected")
                    .hide(),
                this.$selectionUl.find(".ms-optgroup-label").show(),
                this.$selectableUl.find(".ms-optgroup-label").hide(),
                this.$selectionUl
                    .find(".ms-elem-selection")
                    .filter(":not(." + this.options.disabledClass + ")")
                    .addClass("ms-selected")
                    .show(),
                this.$selectionUl.focus(),
                t.trigger("change"),
                "function" == typeof this.options.afterSelect)
            ) {
                var i = e.grep(t.val(), function (t) {
                    return e.inArray(t, n) < 0;
                });
                this.options.afterSelect.call(this, i);
            }
        },
        deselect_all: function () {
            var e = this.$element,
                t = e.val();
            e.find("option").prop("selected", !1),
                this.$selectableUl.find(".ms-elem-selectable").removeClass("ms-selected").show(),
                this.$selectionUl.find(".ms-optgroup-label").hide(),
                this.$selectableUl.find(".ms-optgroup-label").show(),
                this.$selectionUl.find(".ms-elem-selection").removeClass("ms-selected").hide(),
                this.$selectableUl.focus(),
                e.trigger("change"),
                "function" == typeof this.options.afterDeselect && this.options.afterDeselect.call(this, t);
        },
        sanitize: function (e) {
            var t,
                n,
                i = 0;
            if (0 == e.length) return i;
            for (t = 0, n = e.length; t < n; t++) (i = (i << 5) - i + e.charCodeAt(t)), (i |= 0);
            return i;
        },
    }),
        (e.fn.multiSelect = function () {
            var n = arguments[0],
                i = arguments;
            return this.each(function () {
                var a = e(this),
                    r = a.data("multiselect"),
                    s = e.extend({}, e.fn.multiSelect.defaults, a.data(), "object" == typeof n && n);
                r || a.data("multiselect", (r = new t(this, s))), "string" == typeof n ? r[n](i[1]) : r.init();
            });
        }),
        (e.fn.multiSelect.defaults = { keySelect: [32], selectableOptgroup: !1, disabledClass: "disabled", dblClick: !1, keepOrder: !1, cssClass: "" }),
        (e.fn.multiSelect.Constructor = t),
        (e.fn.insertAt = function (e, t) {
            return this.each(function () {
                0 === e
                    ? t.prepend(this)
                    : t
                          .children()
                          .eq(e - 1)
                          .after(this);
            });
        });
})(window.jQuery),
(function (e) {
    "object" == typeof exports && "undefined" != typeof module
        ? (module.exports = e())
        : "function" == typeof define && define.amd
        ? define([], e)
        : (("undefined" != typeof window ? window : "undefined" != typeof global ? global : "undefined" != typeof self ? self : this).dragula = e());
})(function () {
    return (function e(t, n, i) {
        function a(s, o) {
            if (!n[s]) {
                if (!t[s]) {
                    var l = "function" == typeof require && require;
                    if (!o && l) return l(s, !0);
                    if (r) return r(s, !0);
                    var d = new Error("Cannot find module '" + s + "'");
                    throw ((d.code = "MODULE_NOT_FOUND"), d);
                }
                var u = (n[s] = { exports: {} });
                t[s][0].call(
                    u.exports,
                    function (e) {
                        return a(t[s][1][e] || e);
                    },
                    u,
                    u.exports,
                    e,
                    t,
                    n,
                    i
                );
            }
            return n[s].exports;
        }
        for (var r = "function" == typeof require && require, s = 0; s < i.length; s++) a(i[s]);
        return a;
    })(
        {
            1: [
                function (e, t, n) {
                    "use strict";
                    function i(e) {
                        var t = a[e];
                        return t ? (t.lastIndex = 0) : (a[e] = t = new RegExp(r + e + s, "g")), t;
                    }
                    var a = {},
                        r = "(?:^|\\s)",
                        s = "(?:\\s|$)";
                    t.exports = {
                        add: function (e, t) {
                            var n = e.className;
                            n.length ? i(t).test(n) || (e.className += " " + t) : (e.className = t);
                        },
                        rm: function (e, t) {
                            e.className = e.className.replace(i(t), " ").trim();
                        },
                    };
                },
                {},
            ],
            2: [
                function (e, t, n) {
                    (function (n) {
                        "use strict";
                        function i(e, t, i, a) {
                            n.navigator.pointerEnabled
                                ? y[t](e, { mouseup: "pointerup", mousedown: "pointerdown", mousemove: "pointermove" }[i], a)
                                : n.navigator.msPointerEnabled
                                ? y[t](e, { mouseup: "MSPointerUp", mousedown: "MSPointerDown", mousemove: "MSPointerMove" }[i], a)
                                : (y[t](e, { mouseup: "touchend", mousedown: "touchstart", mousemove: "touchmove" }[i], a), y[t](e, i, a));
                        }
                        function a(e) {
                            if (void 0 !== e.touches) return e.touches.length;
                            if (void 0 !== e.which && 0 !== e.which) return e.which;
                            if (void 0 !== e.buttons) return e.buttons;
                            var t = e.button;
                            return void 0 !== t ? (1 & t ? 1 : 2 & t ? 3 : 4 & t ? 2 : 0) : void 0;
                        }
                        function r(e) {
                            var t = e.getBoundingClientRect();
                            return { left: t.left + s("scrollLeft", "pageXOffset"), top: t.top + s("scrollTop", "pageYOffset") };
                        }
                        function s(e, t) {
                            return void 0 !== n[t] ? n[t] : w.clientHeight ? w[e] : b.body[e];
                        }
                        function o(e, t, n) {
                            var i,
                                a = e || {},
                                r = a.className;
                            return (a.className += " gu-hide"), (i = b.elementFromPoint(t, n)), (a.className = r), i;
                        }
                        function l() {
                            return !1;
                        }
                        function d() {
                            return !0;
                        }
                        function u(e) {
                            return e.width || e.right - e.left;
                        }
                        function c(e) {
                            return e.height || e.bottom - e.top;
                        }
                        function h(e) {
                            return e.parentNode === b ? null : e.parentNode;
                        }
                        function f(e) {
                            return "INPUT" === e.tagName || "TEXTAREA" === e.tagName || "SELECT" === e.tagName || p(e);
                        }
                        function p(e) {
                            return !!e && "false" !== e.contentEditable && ("true" === e.contentEditable || p(h(e)));
                        }
                        function m(e) {
                            return (
                                e.nextElementSibling ||
                                (function () {
                                    var t = e;
                                    do {
                                        t = t.nextSibling;
                                    } while (t && 1 !== t.nodeType);
                                    return t;
                                })()
                            );
                        }
                        function g(e, t) {
                            var n = (function (e) {
                                    return e.targetTouches && e.targetTouches.length ? e.targetTouches[0] : e.changedTouches && e.changedTouches.length ? e.changedTouches[0] : e;
                                })(t),
                                i = { pageX: "clientX", pageY: "clientY" };
                            return e in i && !(e in n) && i[e] in n && (e = i[e]), n[e];
                        }
                        var _ = e("contra/emitter"),
                            y = e("crossvent"),
                            v = e("./classes"),
                            b = document,
                            w = b.documentElement;
                        t.exports = function (e, t) {
                            function n(e) {
                                return -1 !== te.containers.indexOf(e) || ee.isContainer(e);
                            }
                            function s(e) {
                                var t = e ? "remove" : "add";
                                i(w, t, "mousedown", k), i(w, t, "mouseup", C);
                            }
                            function p(e) {
                                i(w, e ? "remove" : "add", "mousemove", D);
                            }
                            function M(e) {
                                var t = e ? "remove" : "add";
                                y[t](w, "selectstart", x), y[t](w, "click", x);
                            }
                            function x(e) {
                                Z && e.preventDefault();
                            }
                            function k(e) {
                                if (((q = e.clientX), (V = e.clientY), 1 === a(e) && !e.metaKey && !e.ctrlKey)) {
                                    var t = e.target,
                                        n = L(t);
                                    n && ((Z = n), p(), "mousedown" === e.type && (f(t) ? t.focus() : e.preventDefault()));
                                }
                            }
                            function D(e) {
                                if (Z) {
                                    if (0 === a(e)) return void C({});
                                    if (void 0 === e.clientX || e.clientX !== q || void 0 === e.clientY || e.clientY !== V) {
                                        if (ee.ignoreInputTextSelection) {
                                            var t = g("clientX", e),
                                                n = g("clientY", e);
                                            if (f(b.elementFromPoint(t, n))) return;
                                        }
                                        var i = Z;
                                        p(!0), M(), T(), S(i);
                                        var s = r(B);
                                        (z = g("pageX", e) - s.left), (U = g("pageY", e) - s.top), v.add(X || B, "gu-transit"), O(), j(e);
                                    }
                                }
                            }
                            function L(e) {
                                if (!((te.dragging && $) || n(e))) {
                                    for (var t = e; h(e) && !1 === n(h(e)); ) {
                                        if (ee.invalid(e, t)) return;
                                        if (!(e = h(e))) return;
                                    }
                                    var i = h(e);
                                    if (i && !ee.invalid(e, t) && ee.moves(e, i, t, m(e))) return { item: e, source: i };
                                }
                            }
                            function S(e) {
                                (function (e, t) {
                                    return "boolean" == typeof ee.copy ? ee.copy : ee.copy(e, t);
                                })(e.item, e.source) && ((X = e.item.cloneNode(!0)), te.emit("cloned", X, e.item, "copy")),
                                    (W = e.source),
                                    (B = e.item),
                                    (G = J = m(e.item)),
                                    (te.dragging = !0),
                                    te.emit("drag", B, W);
                            }
                            function T() {
                                if (te.dragging) {
                                    var e = X || B;
                                    E(e, h(e));
                                }
                            }
                            function Y() {
                                (Z = !1), p(!0), M(!0);
                            }
                            function C(e) {
                                if ((Y(), te.dragging)) {
                                    var t = X || B,
                                        n = g("clientX", e),
                                        i = g("clientY", e),
                                        a = P(o($, n, i), n, i);
                                    a && ((X && ee.copySortSource) || !X || a !== W) ? E(t, a) : ee.removeOnSpill ? A() : I();
                                }
                            }
                            function E(e, t) {
                                var n = h(e);
                                X && ee.copySortSource && t === W && n.removeChild(B), H(t) ? te.emit("cancel", e, W, W) : te.emit("drop", e, t, W, J), F();
                            }
                            function A() {
                                if (te.dragging) {
                                    var e = X || B,
                                        t = h(e);
                                    t && t.removeChild(e), te.emit(X ? "cancel" : "remove", e, t, W), F();
                                }
                            }
                            function I(e) {
                                if (te.dragging) {
                                    var t = arguments.length > 0 ? e : ee.revertOnSpill,
                                        n = X || B,
                                        i = h(n),
                                        a = H(i);
                                    !1 === a && t && (X ? i && i.removeChild(X) : W.insertBefore(n, G)), a || t ? te.emit("cancel", n, W, W) : te.emit("drop", n, i, W, J), F();
                                }
                            }
                            function F() {
                                var e = X || B;
                                Y(),
                                    $ && (v.rm(ee.mirrorContainer, "gu-unselectable"), i(w, "remove", "mousemove", j), h($).removeChild($), ($ = null)),
                                    e && v.rm(e, "gu-transit"),
                                    K && clearTimeout(K),
                                    (te.dragging = !1),
                                    Q && te.emit("out", e, Q, W),
                                    te.emit("dragend", e),
                                    (W = B = X = G = J = K = Q = null);
                            }
                            function H(e, t) {
                                var n;
                                return (n = void 0 !== t ? t : $ ? J : m(X || B)), e === W && n === G;
                            }
                            function P(e, t, i) {
                                function a() {
                                    if (!1 === n(r)) return !1;
                                    var a = N(r, e),
                                        s = R(r, a, t, i);
                                    return !!H(r, s) || ee.accepts(B, r, W, s);
                                }
                                for (var r = e; r && !a(); ) r = h(r);
                                return r;
                            }
                            function j(e) {
                                function t(e) {
                                    te.emit(e, s, Q, W);
                                }
                                if ($) {
                                    e.preventDefault();
                                    var n = g("clientX", e),
                                        i = g("clientY", e),
                                        a = n - z,
                                        r = i - U;
                                    ($.style.left = a + "px"), ($.style.top = r + "px");
                                    var s = X || B,
                                        l = o($, n, i),
                                        d = P(l, n, i),
                                        u = null !== d && d !== Q;
                                    (u || null === d) && (Q && t("out"), (Q = d), u && t("over"));
                                    var c = h(s);
                                    if (d === W && X && !ee.copySortSource) return void (c && c.removeChild(s));
                                    var f,
                                        p = N(d, l);
                                    if (null !== p) f = R(d, p, n, i);
                                    else {
                                        if (!0 !== ee.revertOnSpill || X) return void (X && c && c.removeChild(s));
                                        (f = G), (d = W);
                                    }
                                    ((null === f && u) || (f !== s && f !== m(s))) && ((J = f), d.insertBefore(s, f), te.emit("shadow", s, d, W));
                                }
                            }
                            function O() {
                                if (!$) {
                                    var e = B.getBoundingClientRect();
                                    (($ = B.cloneNode(!0)).style.width = u(e) + "px"),
                                        ($.style.height = c(e) + "px"),
                                        v.rm($, "gu-transit"),
                                        v.add($, "gu-mirror"),
                                        ee.mirrorContainer.appendChild($),
                                        i(w, "add", "mousemove", j),
                                        v.add(ee.mirrorContainer, "gu-unselectable"),
                                        te.emit("cloned", $, B, "mirror");
                                }
                            }
                            function N(e, t) {
                                for (var n = t; n !== e && h(n) !== e; ) n = h(n);
                                return n === w ? null : n;
                            }
                            function R(e, t, n, i) {
                                var a = "horizontal" === ee.direction,
                                    r =
                                        t !== e
                                            ? (function () {
                                                  var e = t.getBoundingClientRect();
                                                  return (function (e) {
                                                      return e ? m(t) : t;
                                                  })(a ? n > e.left + u(e) / 2 : i > e.top + c(e) / 2);
                                              })()
                                            : (function () {
                                                  var t,
                                                      r,
                                                      s,
                                                      o = e.children.length;
                                                  for (t = 0; o > t; t++) {
                                                      if (((s = (r = e.children[t]).getBoundingClientRect()), a && s.left + s.width / 2 > n)) return r;
                                                      if (!a && s.top + s.height / 2 > i) return r;
                                                  }
                                                  return null;
                                              })();
                                return r;
                            }
                            1 === arguments.length && !1 === Array.isArray(e) && ((t = e), (e = []));
                            var $,
                                W,
                                B,
                                z,
                                U,
                                q,
                                V,
                                G,
                                J,
                                X,
                                K,
                                Z,
                                Q = null,
                                ee = t || {};
                            void 0 === ee.moves && (ee.moves = d),
                                void 0 === ee.accepts && (ee.accepts = d),
                                void 0 === ee.invalid &&
                                    (ee.invalid = function () {
                                        return !1;
                                    }),
                                void 0 === ee.containers && (ee.containers = e || []),
                                void 0 === ee.isContainer && (ee.isContainer = l),
                                void 0 === ee.copy && (ee.copy = !1),
                                void 0 === ee.copySortSource && (ee.copySortSource = !1),
                                void 0 === ee.revertOnSpill && (ee.revertOnSpill = !1),
                                void 0 === ee.removeOnSpill && (ee.removeOnSpill = !1),
                                void 0 === ee.direction && (ee.direction = "vertical"),
                                void 0 === ee.ignoreInputTextSelection && (ee.ignoreInputTextSelection = !0),
                                void 0 === ee.mirrorContainer && (ee.mirrorContainer = b.body);
                            var te = _({
                                containers: ee.containers,
                                start: function (e) {
                                    var t = L(e);
                                    t && S(t);
                                },
                                end: T,
                                cancel: I,
                                remove: A,
                                destroy: function () {
                                    s(!0), C({});
                                },
                                canMove: function (e) {
                                    return !!L(e);
                                },
                                dragging: !1,
                            });
                            return (
                                !0 === ee.removeOnSpill &&
                                    te
                                        .on("over", function (e) {
                                            v.rm(e, "gu-hide");
                                        })
                                        .on("out", function (e) {
                                            te.dragging && v.add(e, "gu-hide");
                                        }),
                                s(),
                                te
                            );
                        };
                    }.call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}));
                },
                { "./classes": 1, "contra/emitter": 5, crossvent: 6 },
            ],
            3: [
                function (e, t, n) {
                    t.exports = function (e, t) {
                        return Array.prototype.slice.call(e, t);
                    };
                },
                {},
            ],
            4: [
                function (e, t, n) {
                    "use strict";
                    var i = e("ticky");
                    t.exports = function (e, t, n) {
                        e &&
                            i(function () {
                                e.apply(n || null, t || []);
                            });
                    };
                },
                { ticky: 9 },
            ],
            5: [
                function (e, t, n) {
                    "use strict";
                    var i = e("atoa"),
                        a = e("./debounce");
                    t.exports = function (e, t) {
                        var n = t || {},
                            r = {};
                        return (
                            void 0 === e && (e = {}),
                            (e.on = function (t, n) {
                                return r[t] ? r[t].push(n) : (r[t] = [n]), e;
                            }),
                            (e.once = function (t, n) {
                                return (n._once = !0), e.on(t, n), e;
                            }),
                            (e.off = function (t, n) {
                                var i = arguments.length;
                                if (1 === i) delete r[t];
                                else if (0 === i) r = {};
                                else {
                                    var a = r[t];
                                    if (!a) return e;
                                    a.splice(a.indexOf(n), 1);
                                }
                                return e;
                            }),
                            (e.emit = function () {
                                var t = i(arguments);
                                return e.emitterSnapshot(t.shift()).apply(this, t);
                            }),
                            (e.emitterSnapshot = function (t) {
                                var s = (r[t] || []).slice(0);
                                return function () {
                                    var r = i(arguments),
                                        o = this || e;
                                    if ("error" === t && !1 !== n.throws && !s.length) throw 1 === r.length ? r[0] : r;
                                    return (
                                        s.forEach(function (i) {
                                            n.async ? a(i, r, o) : i.apply(o, r), i._once && e.off(t, i);
                                        }),
                                        e
                                    );
                                };
                            }),
                            e
                        );
                    };
                },
                { "./debounce": 4, atoa: 3 },
            ],
            6: [
                function (e, t, n) {
                    (function (n) {
                        "use strict";
                        function i(e, t, i) {
                            return function (t) {
                                var a = t || n.event;
                                (a.target = a.target || a.srcElement),
                                    (a.preventDefault =
                                        a.preventDefault ||
                                        function () {
                                            a.returnValue = !1;
                                        }),
                                    (a.stopPropagation =
                                        a.stopPropagation ||
                                        function () {
                                            a.cancelBubble = !0;
                                        }),
                                    (a.which = a.which || a.keyCode),
                                    i.call(e, a);
                            };
                        }
                        function a(e, t, n) {
                            var i = (function (e, t, n) {
                                var i, a;
                                for (i = 0; i < u.length; i++) if ((a = u[i]).element === e && a.type === t && a.fn === n) return i;
                            })(e, t, n);
                            if (i) {
                                var a = u[i].wrapper;
                                return u.splice(i, 1), a;
                            }
                        }
                        var r = e("custom-event"),
                            s = e("./eventmap"),
                            o = n.document,
                            l = function (e, t, n, i) {
                                return e.addEventListener(t, n, i);
                            },
                            d = function (e, t, n, i) {
                                return e.removeEventListener(t, n, i);
                            },
                            u = [];
                        n.addEventListener ||
                            ((l = function (e, t, n) {
                                return e.attachEvent(
                                    "on" + t,
                                    (function (e, t, n) {
                                        var r = a(e, t, n) || i(e, 0, n);
                                        return u.push({ wrapper: r, element: e, type: t, fn: n }), r;
                                    })(e, t, n)
                                );
                            }),
                            (d = function (e, t, n) {
                                var i = a(e, t, n);
                                return i ? e.detachEvent("on" + t, i) : void 0;
                            })),
                            (t.exports = {
                                add: l,
                                remove: d,
                                fabricate: function (e, t, n) {
                                    var i =
                                        -1 === s.indexOf(t)
                                            ? new r(t, { detail: n })
                                            : (function () {
                                                  var e;
                                                  return o.createEvent ? (e = o.createEvent("Event")).initEvent(t, !0, !0) : o.createEventObject && (e = o.createEventObject()), e;
                                              })();
                                    e.dispatchEvent ? e.dispatchEvent(i) : e.fireEvent("on" + t, i);
                                },
                            });
                    }.call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}));
                },
                { "./eventmap": 7, "custom-event": 8 },
            ],
            7: [
                function (e, t, n) {
                    (function (e) {
                        "use strict";
                        var n = [],
                            i = "",
                            a = /^on/;
                        for (i in e) a.test(i) && n.push(i.slice(2));
                        t.exports = n;
                    }.call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}));
                },
                {},
            ],
            8: [
                function (e, t, n) {
                    (function (e) {
                        var n = e.CustomEvent;
                        t.exports = (function () {
                            try {
                                var e = new n("cat", { detail: { foo: "bar" } });
                                return "cat" === e.type && "bar" === e.detail.foo;
                            } catch (e) {}
                            return !1;
                        })()
                            ? n
                            : "function" == typeof document.createEvent
                            ? function (e, t) {
                                  var n = document.createEvent("CustomEvent");
                                  return t ? n.initCustomEvent(e, t.bubbles, t.cancelable, t.detail) : n.initCustomEvent(e, !1, !1, void 0), n;
                              }
                            : function (e, t) {
                                  var n = document.createEventObject();
                                  return (n.type = e), t ? ((n.bubbles = Boolean(t.bubbles)), (n.cancelable = Boolean(t.cancelable)), (n.detail = t.detail)) : ((n.bubbles = !1), (n.cancelable = !1), (n.detail = void 0)), n;
                              };
                    }.call(this, "undefined" != typeof global ? global : "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}));
                },
                {},
            ],
            9: [
                function (e, t, n) {
                    var i;
                    (i =
                        "function" == typeof setImmediate
                            ? function (e) {
                                  setImmediate(e);
                              }
                            : function (e) {
                                  setTimeout(e, 0);
                              }),
                        (t.exports = i);
                },
                {},
            ],
        },
        {},
        [2]
    )(2);
}),    (function (e, t) {
    function n(n, i, a) {
        var r = n.children(i.headerTag),
            s = n.children(i.bodyTag);
        r.length > s.length ? C(O, "contents") : r.length < s.length && C(O, "titles");
        var o = i.startIndex;
        if (((a.stepCount = r.length), i.saveState && e.cookie)) {
            var l = e.cookie(I + c(n)),
                u = parseInt(l, 0);
            !isNaN(u) && u < a.stepCount && (o = u);
        }
        (a.currentIndex = o),
            r.each(function (i) {
                var a = e(this),
                    r = s.eq(i),
                    o = r.data("mode"),
                    l = null == o ? N.html : h(N, /^\s*$/.test(o) || isNaN(o) ? o : parseInt(o, 0)),
                    u = l === N.html || r.data("url") === t ? "" : r.data("url"),
                    c = l !== N.html && "1" === r.data("loaded"),
                    f = e.extend({}, W, { title: a.html(), content: l === N.html ? r.html() : "", contentUrl: u, contentMode: l, contentLoaded: c });
                !(function (e, t) {
                    d(e).push(t);
                })(n, f);
            });
    }
    function i(e, t) {
        var n = e.find(".steps li").eq(t.currentIndex);
        e.triggerHandler("finishing", [t.currentIndex]) ? (n.addClass("done").removeClass("error"), e.triggerHandler("finished", [t.currentIndex])) : n.addClass("error");
    }
    function a(e) {
        var t = e.data("eventNamespace");
        return null == t && ((t = "." + c(e)), e.data("eventNamespace", t)), t;
    }
    function r(e, t) {
        var n = c(e);
        return e.find("#" + n + F + t);
    }
    function s(e, t) {
        var n = c(e);
        return e.find("#" + n + H + t);
    }
    function o(e) {
        return e.data("options");
    }
    function l(e) {
        return e.data("state");
    }
    function d(e) {
        return e.data("steps");
    }
    function u(e, t) {
        var n = d(e);
        return (0 > t || t >= n.length) && C(j), n[t];
    }
    function c(e) {
        var t = e.data("uid");
        return null == t && (null == (t = e._id()) && ((t = "steps-uid-".concat(A)), e._id(t)), A++, e.data("uid", t)), t;
    }
    function h(e, n) {
        if ((E("enumType", e), E("keyOrValue", n), "string" == typeof n)) {
            var i = e[n];
            return i === t && C("The enum key '{0}' does not exist.", n), i;
        }
        if ("number" == typeof n) {
            for (var a in e) if (e[a] === n) return n;
            C("Invalid enum value '{0}'.", n);
        } else C("Invalid key or value type.");
    }
    function f(e, t, n) {
        return v(
            e,
            t,
            n,
            (function (e, t) {
                return e.currentIndex + 1;
            })(n)
        );
    }
    function p(e, t, n) {
        return v(
            e,
            t,
            n,
            (function (e, t) {
                return e.currentIndex - 1;
            })(n)
        );
    }
    function m(t) {
        var i = e.extend(!0, {}, B, t);
        return this.each(function () {
            var t = e(this),
                s = { currentIndex: i.startIndex, currentStep: null, stepCount: 0, transitionElement: null };
            t.data("options", i),
                t.data("state", s),
                t.data("steps", []),
                n(t, i, s),
                (function (t, n, i) {
                    var a = '<{0} class="{1}">{2}</{0}>',
                        r = h(R, n.stepsOrientation) === R.vertical ? " vertical" : "",
                        s = e(a.format(n.contentContainerTag, "content " + n.clearFixCssClass, t.html())),
                        o = e(a.format(n.stepsContainerTag, "steps " + n.clearFixCssClass, '<ul role="tablist"></ul>')),
                        l = s.children(n.headerTag),
                        d = s.children(n.bodyTag);
                    t
                        .attr("role", "application")
                        .empty()
                        .append(o)
                        .append(s)
                        .addClass(n.cssClass + " " + n.clearFixCssClass + r),
                        d.each(function (n) {
                            k(t, i, e(this), n);
                        }),
                        l.each(function (a) {
                            L(t, n, i, e(this), a);
                        }),
                        M(t, n, i),
                        (function (e, t, n) {
                            if (t.enablePagination) {
                                var i = '<li><a href="#{0}" role="menuitem">{1}</a></li>',
                                    a = "";
                                t.forceMoveForward || (a += i.format("previous", t.labels.previous)),
                                    (a += i.format("next", t.labels.next)),
                                    t.enableFinishButton && (a += i.format("finish", t.labels.finish)),
                                    t.enableCancelButton && (a += i.format("cancel", t.labels.cancel)),
                                    e.append('<{0} class="actions {1}"><ul role="menu" aria-label="{2}">{3}</ul></{0}>'.format(t.actionContainerTag, t.clearFixCssClass, t.labels.pagination, a)),
                                    w(e, t, n),
                                    y(e, t, n);
                            }
                        })(t, n, i);
                })(t, i, s),
                (function (e, t) {
                    var n = a(e);
                    e.bind("canceled" + n, t.onCanceled),
                        e.bind("contentLoaded" + n, t.onContentLoaded),
                        e.bind("finishing" + n, t.onFinishing),
                        e.bind("finished" + n, t.onFinished),
                        e.bind("init" + n, t.onInit),
                        e.bind("stepChanging" + n, t.onStepChanging),
                        e.bind("stepChanged" + n, t.onStepChanged),
                        t.enableKeyNavigation && e.bind("keyup" + n, _),
                        e.find(".actions a").bind("click" + n, b);
                })(t, i),
                i.autoFocus && 0 === A && r(t, i.startIndex).focus(),
                t.triggerHandler("init", [i.startIndex]);
        });
    }
    function g(t, n, i, a, r) {
        (0 > a || a > i.stepCount) && C(j),
            (function (e, t, n) {
                d(e).splice(t, 0, n);
            })(t, a, (r = e.extend({}, W, r))),
            i.currentIndex !== i.stepCount && i.currentIndex >= a && (i.currentIndex++, S(t, n, i)),
            i.stepCount++;
        var o = t.find(".content"),
            l = e("<{0}>{1}</{0}>".format(n.headerTag, r.title)),
            u = e("<{0}></{0}>".format(n.bodyTag));
        return (
            (null == r.contentMode || r.contentMode === N.html) && u.html(r.content),
            0 === a
                ? o.prepend(u).prepend(l)
                : s(t, a - 1)
                      .after(u)
                      .after(l),
            k(t, i, u, a),
            L(t, n, i, l, a),
            x(t, n, i, a),
            a === i.currentIndex && M(t, n, i),
            w(t, n, i),
            t
        );
    }
    function _(t) {
        var n = e(this),
            i = o(n),
            a = l(n);
        if (i.suppressPaginationOnFocus && n.find(":focus").is(":input")) return t.preventDefault(), !1;
        37 === t.keyCode ? (t.preventDefault(), p(n, i, a)) : 39 === t.keyCode && (t.preventDefault(), f(n, i, a));
    }
    function y(t, n, i) {
        if (i.stepCount > 0) {
            var a = i.currentIndex,
                r = u(t, a);
            if (!n.enableContentCache || !r.contentLoaded)
                switch (h(N, r.contentMode)) {
                    case N.iframe:
                        t.find(".content > .body")
                            .eq(i.currentIndex)
                            .empty()
                            .html('<iframe src="' + r.contentUrl + '" frameborder="0" scrolling="no" />')
                            .data("loaded", "1");
                        break;
                    case N.async:
                        var o = s(t, a)
                            ._aria("busy", "true")
                            .empty()
                            .append(D(n.loadingTemplate, { text: n.labels.loading }));
                        e.ajax({ url: r.contentUrl, cache: !1 }).done(function (e) {
                            o.empty().html(e)._aria("busy", "false").data("loaded", "1"), t.triggerHandler("contentLoaded", [a]);
                        });
                }
        }
    }
    function v(e, t, n, i) {
        var a = n.currentIndex;
        if (i >= 0 && i < n.stepCount && !(t.forceMoveForward && i < n.currentIndex)) {
            var s = r(e, i),
                o = s.parent(),
                l = o.hasClass("disabled");
            return o._enableAria(), s.click(), a !== n.currentIndex || !l || (o._enableAria(!1), !1);
        }
        return !1;
    }
    function b(t) {
        t.preventDefault();
        var n = e(this),
            a = n.parent().parent().parent().parent(),
            r = o(a),
            s = l(a),
            d = n.attr("href");
        switch (d.substring(d.lastIndexOf("#") + 1)) {
            case "cancel":
                !(function (e) {
                    e.triggerHandler("canceled");
                })(a);
                break;
            case "finish":
                i(a, s);
                break;
            case "next":
                f(a, r, s);
                break;
            case "previous":
                p(a, r, s);
        }
    }
    function w(e, t, n) {
        if (t.enablePagination) {
            var i = e.find(".actions a[href$='#finish']").parent(),
                a = e.find(".actions a[href$='#next']").parent();
            t.forceMoveForward ||
                e
                    .find(".actions a[href$='#previous']")
                    .parent()
                    ._enableAria(n.currentIndex > 0),
                t.enableFinishButton && t.showFinishButtonAlways
                    ? (i._enableAria(n.stepCount > 0), a._enableAria(n.stepCount > 1 && n.stepCount > n.currentIndex + 1))
                    : (i._showAria(t.enableFinishButton && n.stepCount === n.currentIndex + 1), a._showAria(0 === n.stepCount || n.stepCount > n.currentIndex + 1)._enableAria(n.stepCount > n.currentIndex + 1 || !t.enableFinishButton));
        }
    }
    function M(t, n, i, a) {
        var s = r(t, i.currentIndex),
            o = e('<span class="current-info audible">' + n.labels.current + " </span>"),
            l = t.find(".content > .title");
        if (null != a) {
            var d = r(t, a);
            d.parent().addClass("done").removeClass("error")._selectAria(!1), l.eq(a).removeClass("current").next(".body").removeClass("current"), (o = d.find(".current-info")), s.focus();
        }
        s.prepend(o).parent()._selectAria().removeClass("done")._enableAria(), l.eq(i.currentIndex).addClass("current").next(".body").addClass("current");
    }
    function x(e, t, n, i) {
        for (var a = c(e), r = i; r < n.stepCount; r++) {
            var s = a + F + r,
                o = a + H + r,
                l = a + P + r,
                d = e.find(".title").eq(r)._id(l);
            e
                .find(".steps a")
                .eq(r)
                ._id(s)
                ._aria("controls", o)
                .attr("href", "#" + l)
                .html(D(t.titleTemplate, { index: r + 1, title: d.html() })),
                e.find(".body").eq(r)._id(o)._aria("labelledby", l);
        }
    }
    function k(e, t, n, i) {
        var a = c(e),
            r = a + H + i,
            s = a + P + i;
        n._id(r)
            .attr("role", "tabpanel")
            ._aria("labelledby", s)
            .addClass("body")
            ._showAria(t.currentIndex === i);
    }
    function D(e, n) {
        for (var i = e.match(/#([a-z]*)#/gi), a = 0; a < i.length; a++) {
            var r = i[a],
                s = r.substring(1, r.length - 1);
            n[s] === t && C("The key '{0}' does not exist in the substitute collection!", s), (e = e.replace(r, n[s]));
        }
        return e;
    }
    function L(t, n, i, r, s) {
        var o = c(t),
            l = o + F + s,
            d = o + H + s,
            u = o + P + s,
            h = t.find(".steps > ul"),
            f = D(n.titleTemplate, { index: s + 1, title: r.html() }),
            p = e('<li role="tab"><a id="' + l + '" href="#' + u + '" aria-controls="' + d + '">' + f + "</a></li>");
        p._enableAria(n.enableAllSteps || i.currentIndex > s),
            i.currentIndex > s && p.addClass("done"),
            r._id(u).attr("tabindex", "-1").addClass("title"),
            0 === s
                ? h.prepend(p)
                : h
                      .find("li")
                      .eq(s - 1)
                      .after(p),
            0 === s && h.find("li").removeClass("first").eq(s).addClass("first"),
            s === i.stepCount - 1 && h.find("li").removeClass("last").eq(s).addClass("last"),
            p.children("a").bind("click" + a(t), Y);
    }
    function S(t, n, i) {
        n.saveState && e.cookie && e.cookie(I + c(t), i.currentIndex);
    }
    function T(t, n, i, a, r, s) {
        var o = t.find(".content > .body"),
            d = h($, n.transitionEffect),
            u = n.transitionEffectSpeed,
            c = o.eq(a),
            f = o.eq(r);
        switch (d) {
            case $.fade:
            case $.slide:
                var p = d === $.fade ? "fadeOut" : "slideUp",
                    m = d === $.fade ? "fadeIn" : "slideDown";
                (i.transitionElement = c),
                    f[p](u, function () {
                        var t = l(e(this)._showAria(!1).parent().parent());
                        t.transitionElement &&
                            (t.transitionElement[m](u, function () {
                                e(this)._showAria();
                            })
                                .promise()
                                .done(s),
                            (t.transitionElement = null));
                    });
                break;
            case $.slideLeft:
                var g = f.outerWidth(!0),
                    _ = a > r ? -g : g,
                    y = a > r ? g : -g;
                e.when(
                    f.animate({ left: _ }, u, function () {
                        e(this)._showAria(!1);
                    }),
                    c
                        .css("left", y + "px")
                        ._showAria()
                        .animate({ left: 0 }, u)
                ).done(s);
                break;
            default:
                e.when(f._showAria(!1), c._showAria()).done(s);
        }
    }
    function Y(t) {
        t.preventDefault();
        var n = e(this),
            i = n.parent().parent().parent().parent(),
            a = o(i),
            s = l(i),
            d = s.currentIndex;
        if (n.parent().is(":not(.disabled):not(.current)")) {
            var u = n.attr("href");
            !(function (e, t, n, i) {
                if (((0 > i || i >= n.stepCount) && C(j), !(t.forceMoveForward && i < n.currentIndex))) {
                    var a = n.currentIndex;
                    e.triggerHandler("stepChanging", [n.currentIndex, i])
                        ? ((n.currentIndex = i),
                          S(e, t, n),
                          M(e, t, n, a),
                          w(e, t, n),
                          y(e, t, n),
                          T(e, t, n, i, a, function () {
                              e.triggerHandler("stepChanged", [i, a]);
                          }))
                        : e.find(".steps li").eq(a).addClass("error");
                }
            })(i, a, s, parseInt(u.substring(u.lastIndexOf("-") + 1), 0));
        }
        return d === s.currentIndex ? (r(i, d).focus(), !1) : void 0;
    }
    function C(e) {
        throw (arguments.length > 1 && (e = e.format(Array.prototype.slice.call(arguments, 1))), new Error(e));
    }
    function E(e, t) {
        null == t && C("The argument '{0}' is null or undefined.", e);
    }
    e.fn.extend({
        _aria: function (e, t) {
            return this.attr("aria-" + e, t);
        },
        _removeAria: function (e) {
            return this.removeAttr("aria-" + e);
        },
        _enableAria: function (e) {
            return null == e || e ? this.removeClass("disabled")._aria("disabled", "false") : this.addClass("disabled")._aria("disabled", "true");
        },
        _showAria: function (e) {
            return null == e || e ? this.show()._aria("hidden", "false") : this.hide()._aria("hidden", "true");
        },
        _selectAria: function (e) {
            return null == e || e ? this.addClass("current")._aria("selected", "true") : this.removeClass("current")._aria("selected", "false");
        },
        _id: function (e) {
            return e ? this.attr("id", e) : this.attr("id");
        },
    }),
        String.prototype.format ||
            (String.prototype.format = function () {
                for (var t = 1 === arguments.length && e.isArray(arguments[0]) ? arguments[0] : arguments, n = this, i = 0; i < t.length; i++) {
                    var a = new RegExp("\\{" + i + "\\}", "gm");
                    n = n.replace(a, t[i]);
                }
                return n;
            });
    var A = 0,
        I = "jQu3ry_5teps_St@te_",
        F = "-t-",
        H = "-p-",
        P = "-h-",
        j = "Index out of range.",
        O = "One or more corresponding step {0} are missing.";
    (e.fn.steps = function (t) {
        return e.fn.steps[t] ? e.fn.steps[t].apply(this, Array.prototype.slice.call(arguments, 1)) : "object" != typeof t && t ? void e.error("Method " + t + " does not exist on jQuery.steps") : m.apply(this, arguments);
    }),
        (e.fn.steps.add = function (e) {
            var t = l(this);
            return g(this, o(this), t, t.stepCount, e);
        }),
        (e.fn.steps.destroy = function () {
            return (function (t, n) {
                var i = a(t);
                t.unbind(i).removeData("uid").removeData("options").removeData("state").removeData("steps").removeData("eventNamespace").find(".actions a").unbind(i), t.removeClass(n.clearFixCssClass + " vertical");
                var r = t.find(".content > *");
                r.removeData("loaded").removeData("mode").removeData("url"),
                    r.removeAttr("id").removeAttr("role").removeAttr("tabindex").removeAttr("class").removeAttr("style")._removeAria("labelledby")._removeAria("hidden"),
                    t.find(".content > [data-mode='async'],.content > [data-mode='iframe']").empty();
                var s = e('<{0} class="{1}"></{0}>'.format(t.get(0).tagName, t.attr("class"))),
                    o = t._id();
                return null != o && "" !== o && s._id(o), s.html(t.find(".content").html()), t.after(s), t.remove(), s;
            })(this, o(this));
        }),
        (e.fn.steps.finish = function () {
            i(this, l(this));
        }),
        (e.fn.steps.getCurrentIndex = function () {
            return l(this).currentIndex;
        }),
        (e.fn.steps.getCurrentStep = function () {
            return u(this, l(this).currentIndex);
        }),
        (e.fn.steps.getStep = function (e) {
            return u(this, e);
        }),
        (e.fn.steps.insert = function (e, t) {
            return g(this, o(this), l(this), e, t);
        }),
        (e.fn.steps.next = function () {
            return f(this, o(this), l(this));
        }),
        (e.fn.steps.previous = function () {
            return p(this, o(this), l(this));
        }),
        (e.fn.steps.remove = function (e) {
            return (function (e, t, n, i) {
                return !(
                    0 > i ||
                    i >= n.stepCount ||
                    n.currentIndex === i ||
                    ((function (e, t) {
                        d(e).splice(t, 1);
                    })(e, i),
                    n.currentIndex > i && (n.currentIndex--, S(e, t, n)),
                    n.stepCount--,
                    (function (e, t) {
                        var n = c(e);
                        return e.find("#" + n + P + t);
                    })(e, i).remove(),
                    s(e, i).remove(),
                    r(e, i).parent().remove(),
                    0 === i && e.find(".steps li").first().addClass("first"),
                    i === n.stepCount && e.find(".steps li").eq(i).addClass("last"),
                    x(e, t, n, i),
                    w(e, t, n),
                    0)
                );
            })(this, o(this), l(this), e);
        }),
        (e.fn.steps.setStep = function () {
            throw new Error("Not yet implemented!");
        }),
        (e.fn.steps.skip = function () {
            throw new Error("Not yet implemented!");
        });
    var N = (e.fn.steps.contentMode = { html: 0, iframe: 1, async: 2 }),
        R = (e.fn.steps.stepsOrientation = { horizontal: 0, vertical: 1 }),
        $ = (e.fn.steps.transitionEffect = { none: 0, fade: 1, slide: 2, slideLeft: 3 }),
        W = (e.fn.steps.stepModel = { title: "", content: "", contentUrl: "", contentMode: N.html, contentLoaded: !1 }),
        B = (e.fn.steps.defaults = {
            headerTag: "h1",
            bodyTag: "div",
            contentContainerTag: "div",
            actionContainerTag: "div",
            stepsContainerTag: "div",
            cssClass: "wizard",
            clearFixCssClass: "clearfix",
            stepsOrientation: R.horizontal,
            titleTemplate: '<span class="number">#index#.</span> #title#',
            loadingTemplate: '<span class="spinner"></span> #text#',
            autoFocus: !1,
            enableAllSteps: !1,
            enableKeyNavigation: !0,
            enablePagination: !0,
            suppressPaginationOnFocus: !0,
            enableContentCache: !0,
            enableCancelButton: !1,
            enableFinishButton: !0,
            preloadContent: !1,
            showFinishButtonAlways: !1,
            forceMoveForward: !1,
            saveState: !1,
            startIndex: 0,
            transitionEffect: $.none,
            transitionEffectSpeed: 200,
            onStepChanging: function () {
                return !0;
            },
            onStepChanged: function () {},
            onCanceled: function () {},
            onFinishing: function () {
                return !0;
            },
            onFinished: function () {},
            onContentLoaded: function () {},
            onInit: function () {},
            labels: { cancel: "Cancel", current: "current step:", pagination: "Pagination", finish: "Finish", next: "Next", previous: "Previous", loading: "Loading ..." },
        });
})(jQuery),(function (e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["jquery"], e) : e("undefined" != typeof jQuery ? jQuery : window.Zepto);
})(function (e) {
    "use strict";
    var t = {};
    (t.fileapi = void 0 !== e("<input type='file'/>").get(0).files), (t.formdata = void 0 !== window.FormData);
    var n = !!e.fn.prop;
    function i(t) {
        var n = t.data;
        t.isDefaultPrevented() || (t.preventDefault(), e(t.target).ajaxSubmit(n));
    }
    function a(t) {
        var n = t.target,
            i = e(n);
        if (!i.is("[type=submit],[type=image]")) {
            var a = i.closest("[type=submit]");
            if (0 === a.length) return;
            n = a[0];
        }
        var r = this;
        if (((r.clk = n), "image" == n.type))
            if (void 0 !== t.offsetX) (r.clk_x = t.offsetX), (r.clk_y = t.offsetY);
            else if ("function" == typeof e.fn.offset) {
                var s = i.offset();
                (r.clk_x = t.pageX - s.left), (r.clk_y = t.pageY - s.top);
            } else (r.clk_x = t.pageX - n.offsetLeft), (r.clk_y = t.pageY - n.offsetTop);
        setTimeout(function () {
            r.clk = r.clk_x = r.clk_y = null;
        }, 100);
    }
    function r() {
        if (e.fn.ajaxSubmit.debug) {
            var t = "[jquery.form] " + Array.prototype.join.call(arguments, "");
            window.console && window.console.log ? window.console.log(t) : window.opera && window.opera.postError && window.opera.postError(t);
        }
    }
    (e.fn.attr2 = function () {
        if (!n) return this.attr.apply(this, arguments);
        var e = this.prop.apply(this, arguments);
        return (e && e.jquery) || "string" == typeof e ? e : this.attr.apply(this, arguments);
    }),
        (e.fn.ajaxSubmit = function (i) {
            if (!this.length) return r("ajaxSubmit: skipping submit process - no element selected"), this;
            var a,
                s,
                o,
                l = this;
            "function" == typeof i ? (i = { success: i }) : void 0 === i && (i = {}),
                (a = i.type || this.attr2("method")),
                (o = (o = "string" == typeof (s = i.url || this.attr2("action")) ? e.trim(s) : "") || window.location.href || "") && (o = (o.match(/^([^#]+)/) || [])[1]),
                (i = e.extend(!0, { url: o, success: e.ajaxSettings.success, type: a || e.ajaxSettings.type, iframeSrc: /^https/i.test(window.location.href || "") ? "javascript:false" : "about:blank" }, i));
            var d = {};
            if ((this.trigger("form-pre-serialize", [this, i, d]), d.veto)) return r("ajaxSubmit: submit vetoed via form-pre-serialize trigger"), this;
            if (i.beforeSerialize && !1 === i.beforeSerialize(this, i)) return r("ajaxSubmit: submit aborted via beforeSerialize callback"), this;
            var u = i.traditional;
            void 0 === u && (u = e.ajaxSettings.traditional);
            var c,
                h = [],
                f = this.formToArray(i.semantic, h);
            if ((i.data && ((i.extraData = i.data), (c = e.param(i.data, u))), i.beforeSubmit && !1 === i.beforeSubmit(f, this, i))) return r("ajaxSubmit: submit aborted via beforeSubmit callback"), this;
            if ((this.trigger("form-submit-validate", [f, this, i, d]), d.veto)) return r("ajaxSubmit: submit vetoed via form-submit-validate trigger"), this;
            var p = e.param(f, u);
            c && (p = p ? p + "&" + c : c), "GET" == i.type.toUpperCase() ? ((i.url += (i.url.indexOf("?") >= 0 ? "&" : "?") + p), (i.data = null)) : (i.data = p);
            var m = [];
            if (
                (i.resetForm &&
                    m.push(function () {
                        l.resetForm();
                    }),
                i.clearForm &&
                    m.push(function () {
                        l.clearForm(i.includeHidden);
                    }),
                !i.dataType && i.target)
            ) {
                var g = i.success || function () {};
                m.push(function (t) {
                    var n = i.replaceTarget ? "replaceWith" : "html";
                    e(i.target)[n](t).each(g, arguments);
                });
            } else i.success && m.push(i.success);
            if (
                ((i.success = function (e, t, n) {
                    for (var a = i.context || this, r = 0, s = m.length; r < s; r++) m[r].apply(a, [e, t, n || l, l]);
                }),
                i.error)
            ) {
                var _ = i.error;
                i.error = function (e, t, n) {
                    var a = i.context || this;
                    _.apply(a, [e, t, n, l]);
                };
            }
            if (i.complete) {
                var y = i.complete;
                i.complete = function (e, t) {
                    var n = i.context || this;
                    y.apply(n, [e, t, l]);
                };
            }
            var v =
                    e("input[type=file]:enabled", this).filter(function () {
                        return "" !== e(this).val();
                    }).length > 0,
                b = "multipart/form-data",
                w = l.attr("enctype") == b || l.attr("encoding") == b,
                M = t.fileapi && t.formdata;
            r("fileAPI :" + M);
            var x,
                k = (v || w) && !M;
            !1 !== i.iframe && (i.iframe || k)
                ? i.closeKeepAlive
                    ? e.get(i.closeKeepAlive, function () {
                          x = L(f);
                      })
                    : (x = L(f))
                : (x =
                      (v || w) && M
                          ? (function (t) {
                                for (var n = new FormData(), r = 0; r < t.length; r++) n.append(t[r].name, t[r].value);
                                if (i.extraData) {
                                    var s = (function (t) {
                                        var n,
                                            a,
                                            r = e.param(t, i.traditional).split("&"),
                                            s = r.length,
                                            o = [];
                                        for (n = 0; n < s; n++) (r[n] = r[n].replace(/\+/g, " ")), (a = r[n].split("=")), o.push([decodeURIComponent(a[0]), decodeURIComponent(a[1])]);
                                        return o;
                                    })(i.extraData);
                                    for (r = 0; r < s.length; r++) s[r] && n.append(s[r][0], s[r][1]);
                                }
                                i.data = null;
                                var o = e.extend(!0, {}, e.ajaxSettings, i, { contentType: !1, processData: !1, cache: !1, type: a || "POST" });
                                i.uploadProgress &&
                                    (o.xhr = function () {
                                        var t = e.ajaxSettings.xhr();
                                        return (
                                            t.upload &&
                                                t.upload.addEventListener(
                                                    "progress",
                                                    function (e) {
                                                        var t = 0,
                                                            n = e.loaded || e.position,
                                                            a = e.total;
                                                        e.lengthComputable && (t = Math.ceil((n / a) * 100)), i.uploadProgress(e, n, a, t);
                                                    },
                                                    !1
                                                ),
                                            t
                                        );
                                    }),
                                    (o.data = null);
                                var l = o.beforeSend;
                                return (
                                    (o.beforeSend = function (e, t) {
                                        i.formData ? (t.data = i.formData) : (t.data = n), l && l.call(this, e, t);
                                    }),
                                    e.ajax(o)
                                );
                            })(f)
                          : e.ajax(i)),
                l.removeData("jqxhr").data("jqxhr", x);
            for (var D = 0; D < h.length; D++) h[D] = null;
            return this.trigger("form-submit-notify", [this, i]), this;
            function L(t) {
                var s,
                    o,
                    d,
                    u,
                    c,
                    f,
                    p,
                    m,
                    g,
                    _,
                    y,
                    v,
                    b = l[0],
                    w = e.Deferred();
                if (
                    ((w.abort = function (e) {
                        m.abort(e);
                    }),
                    t)
                )
                    for (o = 0; o < h.length; o++) (s = e(h[o])), n ? s.prop("disabled", !1) : s.removeAttr("disabled");
                if (
                    (((d = e.extend(!0, {}, e.ajaxSettings, i)).context = d.context || d),
                    (c = "jqFormIO" + new Date().getTime()),
                    d.iframeTarget
                        ? (_ = (f = e(d.iframeTarget)).attr2("name"))
                            ? (c = _)
                            : f.attr2("name", c)
                        : (f = e('<iframe name="' + c + '" src="' + d.iframeSrc + '" />')).css({ position: "absolute", top: "-1000px", left: "-1000px" }),
                    (p = f[0]),
                    (m = {
                        aborted: 0,
                        responseText: null,
                        responseXML: null,
                        status: 0,
                        statusText: "n/a",
                        getAllResponseHeaders: function () {},
                        getResponseHeader: function () {},
                        setRequestHeader: function () {},
                        abort: function (t) {
                            var n = "timeout" === t ? "timeout" : "aborted";
                            r("aborting upload... " + n), (this.aborted = 1);
                            try {
                                p.contentWindow.document.execCommand && p.contentWindow.document.execCommand("Stop");
                            } catch (e) {}
                            f.attr("src", d.iframeSrc), (m.error = n), d.error && d.error.call(d.context, m, n, t), u && e.event.trigger("ajaxError", [m, d, n]), d.complete && d.complete.call(d.context, m, n);
                        },
                    }),
                    (u = d.global) && 0 == e.active++ && e.event.trigger("ajaxStart"),
                    u && e.event.trigger("ajaxSend", [m, d]),
                    d.beforeSend && !1 === d.beforeSend.call(d.context, m, d))
                )
                    return d.global && e.active--, w.reject(), w;
                if (m.aborted) return w.reject(), w;
                function M(e) {
                    var t = null;
                    try {
                        e.contentWindow && (t = e.contentWindow.document);
                    } catch (e) {
                        r("cannot get iframe.contentWindow document: " + e);
                    }
                    if (t) return t;
                    try {
                        t = e.contentDocument ? e.contentDocument : e.document;
                    } catch (n) {
                        r("cannot get iframe.contentDocument: " + n), (t = e.document);
                    }
                    return t;
                }
                (g = b.clk) && (_ = g.name) && !g.disabled && ((d.extraData = d.extraData || {}), (d.extraData[_] = g.value), "image" == g.type && ((d.extraData[_ + ".x"] = b.clk_x), (d.extraData[_ + ".y"] = b.clk_y)));
                var x = e("meta[name=csrf-token]").attr("content"),
                    k = e("meta[name=csrf-param]").attr("content");
                function D() {
                    var t = l.attr2("target"),
                        n = l.attr2("action"),
                        i = l.attr("enctype") || l.attr("encoding") || "multipart/form-data";
                    b.setAttribute("target", c),
                        (a && !/post/i.test(a)) || b.setAttribute("method", "POST"),
                        n != d.url && b.setAttribute("action", d.url),
                        d.skipEncodingOverride || (a && !/post/i.test(a)) || l.attr({ encoding: "multipart/form-data", enctype: "multipart/form-data" }),
                        d.timeout &&
                            (v = setTimeout(function () {
                                (y = !0), C(1);
                            }, d.timeout));
                    var s = [];
                    try {
                        if (d.extraData)
                            for (var o in d.extraData)
                                d.extraData.hasOwnProperty(o) &&
                                    (e.isPlainObject(d.extraData[o]) && d.extraData[o].hasOwnProperty("name") && d.extraData[o].hasOwnProperty("value")
                                        ? s.push(
                                              e('<input type="hidden" name="' + d.extraData[o].name + '">')
                                                  .val(d.extraData[o].value)
                                                  .appendTo(b)[0]
                                          )
                                        : s.push(
                                              e('<input type="hidden" name="' + o + '">')
                                                  .val(d.extraData[o])
                                                  .appendTo(b)[0]
                                          ));
                        d.iframeTarget || f.appendTo("body"),
                            p.attachEvent ? p.attachEvent("onload", C) : p.addEventListener("load", C, !1),
                            setTimeout(function e() {
                                try {
                                    var t = M(p).readyState;
                                    r("state = " + t), t && "uninitialized" == t.toLowerCase() && setTimeout(e, 50);
                                } catch (e) {
                                    r("Server abort: ", e, " (", e.name, ")"), C(2), v && clearTimeout(v), (v = void 0);
                                }
                            }, 15);
                        try {
                            b.submit();
                        } catch (e) {
                            document.createElement("form").submit.apply(b);
                        }
                    } finally {
                        b.setAttribute("action", n), b.setAttribute("enctype", i), t ? b.setAttribute("target", t) : l.removeAttr("target"), e(s).remove();
                    }
                }
                k && x && ((d.extraData = d.extraData || {}), (d.extraData[k] = x)), d.forceSync ? D() : setTimeout(D, 10);
                var L,
                    S,
                    T,
                    Y = 50;
                function C(t) {
                    if (!m.aborted && !T) {
                        if (((S = M(p)) || (r("cannot access response document"), (t = 2)), 1 === t && m)) return m.abort("timeout"), void w.reject(m, "timeout");
                        if (2 == t && m) return m.abort("server abort"), void w.reject(m, "error", "server abort");
                        if ((S && S.location.href != d.iframeSrc) || y) {
                            p.detachEvent ? p.detachEvent("onload", C) : p.removeEventListener("load", C, !1);
                            var n,
                                i = "success";
                            try {
                                if (y) throw "timeout";
                                var a = "xml" == d.dataType || S.XMLDocument || e.isXMLDoc(S);
                                if ((r("isXml=" + a), !a && window.opera && (null === S.body || !S.body.innerHTML) && --Y)) return r("requeing onLoad callback, DOM not available"), void setTimeout(C, 250);
                                var s = S.body ? S.body : S.documentElement;
                                (m.responseText = s ? s.innerHTML : null),
                                    (m.responseXML = S.XMLDocument ? S.XMLDocument : S),
                                    a && (d.dataType = "xml"),
                                    (m.getResponseHeader = function (e) {
                                        return { "content-type": d.dataType }[e.toLowerCase()];
                                    }),
                                    s && ((m.status = Number(s.getAttribute("status")) || m.status), (m.statusText = s.getAttribute("statusText") || m.statusText));
                                var o = (d.dataType || "").toLowerCase(),
                                    l = /(json|script|text)/.test(o);
                                if (l || d.textarea) {
                                    var c = S.getElementsByTagName("textarea")[0];
                                    if (c) (m.responseText = c.value), (m.status = Number(c.getAttribute("status")) || m.status), (m.statusText = c.getAttribute("statusText") || m.statusText);
                                    else if (l) {
                                        var h = S.getElementsByTagName("pre")[0],
                                            g = S.getElementsByTagName("body")[0];
                                        h ? (m.responseText = h.textContent ? h.textContent : h.innerText) : g && (m.responseText = g.textContent ? g.textContent : g.innerText);
                                    }
                                } else "xml" == o && !m.responseXML && m.responseText && (m.responseXML = E(m.responseText));
                                try {
                                    L = I(m, o, d);
                                } catch (e) {
                                    (i = "parsererror"), (m.error = n = e || i);
                                }
                            } catch (e) {
                                r("error caught: ", e), (i = "error"), (m.error = n = e || i);
                            }
                            m.aborted && (r("upload aborted"), (i = null)),
                                m.status && (i = (m.status >= 200 && m.status < 300) || 304 === m.status ? "success" : "error"),
                                "success" === i
                                    ? (d.success && d.success.call(d.context, L, "success", m), w.resolve(m.responseText, "success", m), u && e.event.trigger("ajaxSuccess", [m, d]))
                                    : i && (void 0 === n && (n = m.statusText), d.error && d.error.call(d.context, m, i, n), w.reject(m, "error", n), u && e.event.trigger("ajaxError", [m, d, n])),
                                u && e.event.trigger("ajaxComplete", [m, d]),
                                u && !--e.active && e.event.trigger("ajaxStop"),
                                d.complete && d.complete.call(d.context, m, i),
                                (T = !0),
                                d.timeout && clearTimeout(v),
                                setTimeout(function () {
                                    d.iframeTarget ? f.attr("src", d.iframeSrc) : f.remove(), (m.responseXML = null);
                                }, 100);
                        }
                    }
                }
                var E =
                        e.parseXML ||
                        function (e, t) {
                            return (
                                window.ActiveXObject ? (((t = new ActiveXObject("Microsoft.XMLDOM")).async = "false"), t.loadXML(e)) : (t = new DOMParser().parseFromString(e, "text/xml")),
                                t && t.documentElement && "parsererror" != t.documentElement.nodeName ? t : null
                            );
                        },
                    A =
                        e.parseJSON ||
                        function (e) {
                            return window.eval("(" + e + ")");
                        },
                    I = function (t, n, i) {
                        var a = t.getResponseHeader("content-type") || "",
                            r = "xml" === n || (!n && a.indexOf("xml") >= 0),
                            s = r ? t.responseXML : t.responseText;
                        return (
                            r && "parsererror" === s.documentElement.nodeName && e.error && e.error("parsererror"),
                            i && i.dataFilter && (s = i.dataFilter(s, n)),
                            "string" == typeof s && ("json" === n || (!n && a.indexOf("json") >= 0) ? (s = A(s)) : ("script" === n || (!n && a.indexOf("javascript") >= 0)) && e.globalEval(s)),
                            s
                        );
                    };
                return w;
            }
        }),
        (e.fn.ajaxForm = function (t) {
            if ((((t = t || {}).delegation = t.delegation && e.isFunction(e.fn.on)), !t.delegation && 0 === this.length)) {
                var n = { s: this.selector, c: this.context };
                return !e.isReady && n.s
                    ? (r("DOM not ready, queuing ajaxForm"),
                      e(function () {
                          e(n.s, n.c).ajaxForm(t);
                      }),
                      this)
                    : (r("terminating; zero elements found by selector" + (e.isReady ? "" : " (DOM not ready)")), this);
            }
            return t.delegation
                ? (e(document).off("submit.form-plugin", this.selector, i).off("click.form-plugin", this.selector, a).on("submit.form-plugin", this.selector, t, i).on("click.form-plugin", this.selector, t, a), this)
                : this.ajaxFormUnbind().bind("submit.form-plugin", t, i).bind("click.form-plugin", t, a);
        }),
        (e.fn.ajaxFormUnbind = function () {
            return this.unbind("submit.form-plugin click.form-plugin");
        }),
        (e.fn.formToArray = function (n, i) {
            var a = [];
            if (0 === this.length) return a;
            var r,
                s,
                o,
                l,
                d,
                u,
                c,
                h,
                f = this[0],
                p = this.attr("id"),
                m = n ? f.getElementsByTagName("*") : f.elements;
            if ((m && !/MSIE [678]/.test(navigator.userAgent) && (m = e(m).get()), p && (r = e(':input[form="' + p + '"]').get()).length && (m = (m || []).concat(r)), !m || !m.length)) return a;
            for (s = 0, c = m.length; s < c; s++)
                if ((l = (u = m[s]).name) && !u.disabled)
                    if (n && f.clk && "image" == u.type) f.clk == u && (a.push({ name: l, value: e(u).val(), type: u.type }), a.push({ name: l + ".x", value: f.clk_x }, { name: l + ".y", value: f.clk_y }));
                    else if ((d = e.fieldValue(u, !0)) && d.constructor == Array) for (i && i.push(u), o = 0, h = d.length; o < h; o++) a.push({ name: l, value: d[o] });
                    else if (t.fileapi && "file" == u.type) {
                        i && i.push(u);
                        var g = u.files;
                        if (g.length) for (o = 0; o < g.length; o++) a.push({ name: l, value: g[o], type: u.type });
                        else a.push({ name: l, value: "", type: u.type });
                    } else null != d && (i && i.push(u), a.push({ name: l, value: d, type: u.type, required: u.required }));
            if (!n && f.clk) {
                var _ = e(f.clk),
                    y = _[0];
                (l = y.name) && !y.disabled && "image" == y.type && (a.push({ name: l, value: _.val() }), a.push({ name: l + ".x", value: f.clk_x }, { name: l + ".y", value: f.clk_y }));
            }
            return a;
        }),
        (e.fn.formSerialize = function (t) {
            return e.param(this.formToArray(t));
        }),
        (e.fn.fieldSerialize = function (t) {
            var n = [];
            return (
                this.each(function () {
                    var i = this.name;
                    if (i) {
                        var a = e.fieldValue(this, t);
                        if (a && a.constructor == Array) for (var r = 0, s = a.length; r < s; r++) n.push({ name: i, value: a[r] });
                        else null != a && n.push({ name: this.name, value: a });
                    }
                }),
                e.param(n)
            );
        }),
        (e.fn.fieldValue = function (t) {
            for (var n = [], i = 0, a = this.length; i < a; i++) {
                var r = this[i],
                    s = e.fieldValue(r, t);
                null == s || (s.constructor == Array && !s.length) || (s.constructor == Array ? e.merge(n, s) : n.push(s));
            }
            return n;
        }),
        (e.fieldValue = function (t, n) {
            var i = t.name,
                a = t.type,
                r = t.tagName.toLowerCase();
            if (
                (void 0 === n && (n = !0),
                n && (!i || t.disabled || "reset" == a || "button" == a || (("checkbox" == a || "radio" == a) && !t.checked) || (("submit" == a || "image" == a) && t.form && t.form.clk != t) || ("select" == r && -1 == t.selectedIndex)))
            )
                return null;
            if ("select" == r) {
                var s = t.selectedIndex;
                if (s < 0) return null;
                for (var o = [], l = t.options, d = "select-one" == a, u = d ? s + 1 : l.length, c = d ? s : 0; c < u; c++) {
                    var h = l[c];
                    if (h.selected) {
                        var f = h.value;
                        if ((f || (f = h.attributes && h.attributes.value && !h.attributes.value.specified ? h.text : h.value), d)) return f;
                        o.push(f);
                    }
                }
                return o;
            }
            return e(t).val();
        }),
        (e.fn.clearForm = function (t) {
            return this.each(function () {
                e("input,select,textarea", this).clearFields(t);
            });
        }),
        (e.fn.clearFields = e.fn.clearInputs = function (t) {
            var n = /^(?:color|date|datetime|email|month|number|password|range|search|tel|text|time|url|week)$/i;
            return this.each(function () {
                var i = this.type,
                    a = this.tagName.toLowerCase();
                n.test(i) || "textarea" == a
                    ? (this.value = "")
                    : "checkbox" == i || "radio" == i
                    ? (this.checked = !1)
                    : "select" == a
                    ? (this.selectedIndex = -1)
                    : "file" == i
                    ? /MSIE/.test(navigator.userAgent)
                        ? e(this).replaceWith(e(this).clone(!0))
                        : e(this).val("")
                    : t && ((!0 === t && /hidden/.test(i)) || ("string" == typeof t && e(this).is(t))) && (this.value = "");
            });
        }),
        (e.fn.resetForm = function () {
            return this.each(function () {
                ("function" == typeof this.reset || ("object" == typeof this.reset && !this.reset.nodeType)) && this.reset();
            });
        }),
        (e.fn.enable = function (e) {
            return (
                void 0 === e && (e = !0),
                this.each(function () {
                    this.disabled = !e;
                })
            );
        }),
        (e.fn.selected = function (t) {
            return (
                void 0 === t && (t = !0),
                this.each(function () {
                    var n = this.type;
                    if ("checkbox" == n || "radio" == n) this.checked = t;
                    else if ("option" == this.tagName.toLowerCase()) {
                        var i = e(this).parent("select");
                        t && i[0] && "select-one" == i[0].type && i.find("option").selected(!1), (this.selected = t);
                    }
                })
            );
        }),
        (e.fn.ajaxSubmit.debug = !1);
})