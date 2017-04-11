function get_short(res) {
    switch (res) {
        case "Compile Error":
            return "ce";
            break;
        case "Accepted":
            return "ac";
            break;
        case "Wrong Answer":
            return "wa";
            break;
        case "Runtime Error":
            return "re";
            break;
        case "Time Limit Exceed":
            return "tle";
            break;
        case "Memory Limit Exceed":
            return "mle";
            break;
        case "Output Limit Exceed":
            return "ole";
            break;
        case "Presentation Error":
            return "pe";
            break;
        case "Challenged":
            return "wa";
            break;
        case "Pretest Passed":
            return "ac";
            break;
        case "Restricted Function":
            return "rf";
            break;
        default:
            return "";
    }
}

function striptags(a) {
    return a.replace(/(<([^>]+)>)/ig, "")
}

function getURLPara(a) {
    return decodeURIComponent((RegExp("[?|&|#]" + a + "=" + "(.*?)(&|#|;|$)").exec(location.href) || [, ""])[1].replace(/\+/g, "%20")) || null
}

/******** jquery form plugin *********/
(function (e) {
    "use strict";

    function n(t) {
        var n = t.data;
        if (!t.isDefaultPrevented()) {
            t.preventDefault();
            e(this).ajaxSubmit(n)
        }
    }

    function r(t) {
        var n = t.target;
        var r = e(n);
        if (!r.is("[type=submit],[type=image]")) {
            var i = r.closest("[type=submit]");
            if (i.length === 0) {
                return
            }
            n = i[0]
        }
        var s = this;
        s.clk = n;
        if (n.type == "image") {
            if (t.offsetX !== undefined) {
                s.clk_x = t.offsetX;
                s.clk_y = t.offsetY
            } else if (typeof e.fn.offset == "function") {
                var o = r.offset();
                s.clk_x = t.pageX - o.left;
                s.clk_y = t.pageY - o.top
            } else {
                s.clk_x = t.pageX - n.offsetLeft;
                s.clk_y = t.pageY - n.offsetTop
            }
        }
        setTimeout(function () {
            s.clk = s.clk_x = s.clk_y = null
        }, 100)
    }

    function i() {
        if (!e.fn.ajaxSubmit.debug) return;
        var t = "[jquery.form] " + Array.prototype.join.call(arguments, "");
        if (window.console && window.console.log) {
            window.console.log(t)
        } else if (window.opera && window.opera.postError) {
            window.opera.postError(t)
        }
    }

    var t = {};
    t.fileapi = e("<input type='file'/>").get(0).files !== undefined;
    t.formdata = window.FormData !== undefined;
    e.fn.ajaxSubmit = function (n) {
        function T(t) {
            var n = e.param(t).split("&");
            var r = n.length;
            var i = {};
            var s, o;
            for (s = 0; s < r; s++) {
                n[s] = n[s].replace(/\+/g, " ");
                o = n[s].split("=");
                i[decodeURIComponent(o[0])] = decodeURIComponent(o[1])
            }
            return i
        }

        function N(t) {
            var i = new FormData;
            for (var s = 0; s < t.length; s++) {
                i.append(t[s].name, t[s].value)
            }
            if (n.extraData) {
                var o = T(n.extraData);
                for (var u in o) if (o.hasOwnProperty(u)) i.append(u, o[u])
            }
            n.data = null;
            var a = e.extend(true, {}, e.ajaxSettings, n, {
                contentType: false,
                processData: false,
                cache: false,
                type: r || "POST"
            });
            if (n.uploadProgress) {
                a.xhr = function () {
                    var e = jQuery.ajaxSettings.xhr();
                    if (e.upload) {
                        e.upload.onprogress = function (e) {
                            var t = 0;
                            var r = e.loaded || e.position;
                            var i = e.total;
                            if (e.lengthComputable) {
                                t = Math.ceil(r / i * 100)
                            }
                            n.uploadProgress(e, r, i, t)
                        }
                    }
                    return e
                }
            }
            a.data = null;
            var f = a.beforeSend;
            a.beforeSend = function (e, t) {
                t.data = i;
                if (f) f.call(this, e, t)
            };
            return e.ajax(a)
        }

        function C(t) {
            function T(e) {
                var t = e.contentWindow ? e.contentWindow.document : e.contentDocument ? e.contentDocument : e.document;
                return t
            }

            function k() {
                function o() {
                    try {
                        var e = T(d).readyState;
                        i("state = " + e);
                        if (e && e.toLowerCase() == "uninitialized") setTimeout(o, 50)
                    } catch (t) {
                        i("Server abort: ", t, " (", t.name, ")");
                        _(x);
                        if (b) clearTimeout(b);
                        b = undefined
                    }
                }

                var t = u.attr("target"),
                    n = u.attr("action");
                s.setAttribute("target", h);
                if (!r) {
                    s.setAttribute("method", "POST")
                }
                if (n != f.url) {
                    s.setAttribute("action", f.url)
                }
                if (!f.skipEncodingOverride && (!r || /post/i.test(r))) {
                    u.attr({
                        encoding: "multipart/form-data",
                        enctype: "multipart/form-data"
                    })
                }
                if (f.timeout) {
                    b = setTimeout(function () {
                        y = true;
                        _(S)
                    }, f.timeout)
                }
                var a = [];
                try {
                    if (f.extraData) {
                        for (var l in f.extraData) {
                            if (f.extraData.hasOwnProperty(l)) {
                                if (e.isPlainObject(f.extraData[l]) && f.extraData[l].hasOwnProperty("name") && f.extraData[l].hasOwnProperty("value")) {
                                    a.push(e('<input type="hidden" name="' + f.extraData[l].name + '">').val(f.extraData[l].value).appendTo(s)[0])
                                } else {
                                    a.push(e('<input type="hidden" name="' + l + '">').val(f.extraData[l]).appendTo(s)[0])
                                }
                            }
                        }
                    }
                    if (!f.iframeTarget) {
                        p.appendTo("body");
                        if (d.attachEvent) d.attachEvent("onload", _);
                        else d.addEventListener("load", _, false)
                    }
                    setTimeout(o, 15);
                    s.submit()
                } finally {
                    s.setAttribute("action", n);
                    if (t) {
                        s.setAttribute("target", t)
                    } else {
                        u.removeAttr("target")
                    }
                    e(a).remove()
                }
            }

            function _(t) {
                if (v.aborted || M) {
                    return
                }
                try {
                    A = T(d)
                } catch (n) {
                    i("cannot access response document: ", n);
                    t = x
                }
                if (t === S && v) {
                    v.abort("timeout");
                    E.reject(v, "timeout");
                    return
                } else if (t == x && v) {
                    v.abort("server abort");
                    E.reject(v, "error", "server abort");
                    return
                }
                if (!A || A.location.href == f.iframeSrc) {
                    if (!y) return
                }
                if (d.detachEvent) d.detachEvent("onload", _);
                else d.removeEventListener("load", _, false);
                var r = "success",
                    s;
                try {
                    if (y) {
                        throw "timeout"
                    }
                    var o = f.dataType == "xml" || A.XMLDocument || e.isXMLDoc(A);
                    i("isXml=" + o);
                    if (!o && window.opera && (A.body === null || !A.body.innerHTML)) {
                        if (--O) {
                            i("requeing onLoad callback, DOM not available");
                            setTimeout(_, 250);
                            return
                        }
                    }
                    var u = A.body ? A.body : A.documentElement;
                    v.responseText = u ? u.innerHTML : null;
                    v.responseXML = A.XMLDocument ? A.XMLDocument : A;
                    if (o) f.dataType = "xml";
                    v.getResponseHeader = function (e) {
                        var t = {
                            "content-type": f.dataType
                        };
                        return t[e]
                    };
                    if (u) {
                        v.status = Number(u.getAttribute("status")) || v.status;
                        v.statusText = u.getAttribute("statusText") || v.statusText
                    }
                    var a = (f.dataType || "").toLowerCase();
                    var l = /(json|script|text)/.test(a);
                    if (l || f.textarea) {
                        var h = A.getElementsByTagName("textarea")[0];
                        if (h) {
                            v.responseText = h.value;
                            v.status = Number(h.getAttribute("status")) || v.status;
                            v.statusText = h.getAttribute("statusText") || v.statusText
                        } else if (l) {
                            var m = A.getElementsByTagName("pre")[0];
                            var g = A.getElementsByTagName("body")[0];
                            if (m) {
                                v.responseText = m.textContent ? m.textContent : m.innerText
                            } else if (g) {
                                v.responseText = g.textContent ? g.textContent : g.innerText
                            }
                        }
                    } else if (a == "xml" && !v.responseXML && v.responseText) {
                        v.responseXML = D(v.responseText)
                    }
                    try {
                        L = H(v, a, f)
                    } catch (t) {
                        r = "parsererror";
                        v.error = s = t || r
                    }
                } catch (t) {
                    i("error caught: ", t);
                    r = "error";
                    v.error = s = t || r
                }
                if (v.aborted) {
                    i("upload aborted");
                    r = null
                }
                if (v.status) {
                    r = v.status >= 200 && v.status < 300 || v.status === 304 ? "success" : "error"
                }
                if (r === "success") {
                    if (f.success) f.success.call(f.context, L, "success", v);
                    E.resolve(v.responseText, "success", v);
                    if (c) e.event.trigger("ajaxSuccess", [v, f])
                } else if (r) {
                    if (s === undefined) s = v.statusText;
                    if (f.error) f.error.call(f.context, v, r, s);
                    E.reject(v, "error", s);
                    if (c) e.event.trigger("ajaxError", [v, f, s])
                }
                if (c) e.event.trigger("ajaxComplete", [v, f]);
                if (c && !--e.active) {
                    e.event.trigger("ajaxStop")
                }
                if (f.complete) f.complete.call(f.context, v, r);
                M = true;
                if (f.timeout) clearTimeout(b);
                setTimeout(function () {
                    if (!f.iframeTarget) p.remove();
                    v.responseXML = null
                }, 100)
            }

            var s = u[0],
                o, a, f, c, h, p, d, v, m, g, y, b;
            var w = !!e.fn.prop;
            var E = e.Deferred();
            if (e("[name=submit],[id=submit]", s).length) {
                alert('Error: Form elements must not have name or id of "submit".');
                E.reject();
                return E
            }
            if (t) {
                for (a = 0; a < l.length; a++) {
                    o = e(l[a]);
                    if (w) o.prop("disabled", false);
                    else o.removeAttr("disabled")
                }
            }
            f = e.extend(true, {}, e.ajaxSettings, n);
            f.context = f.context || f;
            h = "jqFormIO" + (new Date).getTime();
            if (f.iframeTarget) {
                p = e(f.iframeTarget);
                g = p.attr("name");
                if (!g) p.attr("name", h);
                else h = g
            } else {
                p = e('<iframe name="' + h + '" src="' + f.iframeSrc + '" />');
                p.css({
                    position: "absolute",
                    top: "-1000px",
                    left: "-1000px"
                })
            }
            d = p[0];
            v = {
                aborted: 0,
                responseText: null,
                responseXML: null,
                status: 0,
                statusText: "n/a",
                getAllResponseHeaders: function () {
                },
                getResponseHeader: function () {
                },
                setRequestHeader: function () {
                },
                abort: function (t) {
                    var n = t === "timeout" ? "timeout" : "aborted";
                    i("aborting upload... " + n);
                    this.aborted = 1;
                    try {
                        if (d.contentWindow.document.execCommand) {
                            d.contentWindow.document.execCommand("Stop")
                        }
                    } catch (r) {
                    }
                    p.attr("src", f.iframeSrc);
                    v.error = n;
                    if (f.error) f.error.call(f.context, v, n, t);
                    if (c) e.event.trigger("ajaxError", [v, f, n]);
                    if (f.complete) f.complete.call(f.context, v, n)
                }
            };
            c = f.global;
            if (c && 0 === e.active++) {
                e.event.trigger("ajaxStart")
            }
            if (c) {
                e.event.trigger("ajaxSend", [v, f])
            }
            if (f.beforeSend && f.beforeSend.call(f.context, v, f) === false) {
                if (f.global) {
                    e.active--
                }
                E.reject();
                return E
            }
            if (v.aborted) {
                E.reject();
                return E
            }
            m = s.clk;
            if (m) {
                g = m.name;
                if (g && !m.disabled) {
                    f.extraData = f.extraData || {};
                    f.extraData[g] = m.value;
                    if (m.type == "image") {
                        f.extraData[g + ".x"] = s.clk_x;
                        f.extraData[g + ".y"] = s.clk_y
                    }
                }
            }
            var S = 1;
            var x = 2;
            var N = e("meta[name=csrf-token]").attr("content");
            var C = e("meta[name=csrf-param]").attr("content");
            if (C && N) {
                f.extraData = f.extraData || {};
                f.extraData[C] = N
            }
            if (f.forceSync) {
                k()
            } else {
                setTimeout(k, 10)
            }
            var L, A, O = 50,
                M;
            var D = e.parseXML || function (e, t) {
                    if (window.ActiveXObject) {
                        t = new ActiveXObject("Microsoft.XMLDOM");
                        t.async = "false";
                        t.loadXML(e)
                    } else {
                        t = (new DOMParser).parseFromString(e, "text/xml")
                    }
                    return t && t.documentElement && t.documentElement.nodeName != "parsererror" ? t : null
                };
            var P = e.parseJSON || function (e) {
                    return window["eval"]("(" + e + ")")
                };
            var H = function (t, n, r) {
                var i = t.getResponseHeader("content-type") || "",
                    s = n === "xml" || !n && i.indexOf("xml") >= 0,
                    o = s ? t.responseXML : t.responseText;
                if (s && o.documentElement.nodeName === "parsererror") {
                    if (e.error) e.error("parsererror")
                }
                if (r && r.dataFilter) {
                    o = r.dataFilter(o, n)
                }
                if (typeof o === "string") {
                    if (n === "json" || !n && i.indexOf("json") >= 0) {
                        o = P(o)
                    } else if (n === "script" || !n && i.indexOf("javascript") >= 0) {
                        e.globalEval(o)
                    }
                }
                return o
            };
            return E
        }

        if (!this.length) {
            i("ajaxSubmit: skipping submit process - no element selected");
            return this
        }
        var r, s, o, u = this;
        if (typeof n == "function") {
            n = {
                success: n
            }
        }
        r = this.attr("method");
        s = this.attr("action");
        o = typeof s === "string" ? e.trim(s) : "";
        o = o || window.location.href || "";
        if (o) {
            o = (o.match(/^([^#]+)/) || [])[1]
        }
        n = e.extend(true, {
            url: o,
            success: e.ajaxSettings.success,
            type: r || "GET",
            iframeSrc: /^https/i.test(window.location.href || "") ? "javascript:false" : "about:blank"
        }, n);
        var a = {};
        this.trigger("form-pre-serialize", [this, n, a]);
        if (a.veto) {
            i("ajaxSubmit: submit vetoed via form-pre-serialize trigger");
            return this
        }
        if (n.beforeSerialize && n.beforeSerialize(this, n) === false) {
            i("ajaxSubmit: submit aborted via beforeSerialize callback");
            return this
        }
        var f = n.traditional;
        if (f === undefined) {
            f = e.ajaxSettings.traditional
        }
        var l = [];
        var c, h = this.formToArray(n.semantic, l);
        if (n.data) {
            n.extraData = n.data;
            c = e.param(n.data, f)
        }
        if (n.beforeSubmit && n.beforeSubmit(h, this, n) === false) {
            i("ajaxSubmit: submit aborted via beforeSubmit callback");
            return this
        }
        this.trigger("form-submit-validate", [h, this, n, a]);
        if (a.veto) {
            i("ajaxSubmit: submit vetoed via form-submit-validate trigger");
            return this
        }
        var p = e.param(h, f);
        if (c) {
            p = p ? p + "&" + c : c
        }
        if (n.type.toUpperCase() == "GET") {
            n.url += (n.url.indexOf("?") >= 0 ? "&" : "?") + p;
            n.data = null
        } else {
            n.data = p
        }
        var d = [];
        if (n.resetForm) {
            d.push(function () {
                u.resetForm()
            })
        }
        if (n.clearForm) {
            d.push(function () {
                u.clearForm(n.includeHidden)
            })
        }
        if (!n.dataType && n.target) {
            var v = n.success || function () {
                };
            d.push(function (t) {
                var r = n.replaceTarget ? "replaceWith" : "html";
                e(n.target)[r](t).each(v, arguments)
            })
        } else if (n.success) {
            d.push(n.success)
        }
        n.success = function (e, t, r) {
            var i = n.context || this;
            for (var s = 0, o = d.length; s < o; s++) {
                d[s].apply(i, [e, t, r || u, u])
            }
        };
        var m = e('input[type=file]:enabled[value!=""]', this);
        var g = m.length > 0;
        var y = "multipart/form-data";
        var b = u.attr("enctype") == y || u.attr("encoding") == y;
        var w = t.fileapi && t.formdata;
        i("fileAPI :" + w);
        var E = (g || b) && !w;
        var S;
        if (n.iframe !== false && (n.iframe || E)) {
            if (n.closeKeepAlive) {
                e.get(n.closeKeepAlive, function () {
                    S = C(h)
                })
            } else {
                S = C(h)
            }
        } else if ((g || b) && w) {
            S = N(h)
        } else {
            S = e.ajax(n)
        }
        u.removeData("jqxhr").data("jqxhr", S);
        for (var x = 0; x < l.length; x++) l[x] = null;
        this.trigger("form-submit-notify", [this, n]);
        return this
    };
    e.fn.ajaxForm = function (t) {
        t = t || {};
        t.delegation = t.delegation && e.isFunction(e.fn.on);
        if (!t.delegation && this.length === 0) {
            var s = {
                s: this.selector,
                c: this.context
            };
            if (!e.isReady && s.s) {
                i("DOM not ready, queuing ajaxForm");
                e(function () {
                    e(s.s, s.c).ajaxForm(t)
                });
                return this
            }
            i("terminating; zero elements found by selector" + (e.isReady ? "" : " (DOM not ready)"));
            return this
        }
        if (t.delegation) {
            e(document).off("submit.form-plugin", this.selector, n).off("click.form-plugin", this.selector, r).on("submit.form-plugin", this.selector, t, n).on("click.form-plugin", this.selector, t, r);
            return this
        }
        return this.ajaxFormUnbind().bind("submit.form-plugin", t, n).bind("click.form-plugin", t, r)
    };
    e.fn.ajaxFormUnbind = function () {
        return this.unbind("submit.form-plugin click.form-plugin")
    };
    e.fn.formToArray = function (n, r) {
        var i = [];
        if (this.length === 0) {
            return i
        }
        var s = this[0];
        var o = n ? s.getElementsByTagName("*") : s.elements;
        if (!o) {
            return i
        }
        var u, a, f, l, c, h, p;
        for (u = 0, h = o.length; u < h; u++) {
            c = o[u];
            f = c.name;
            if (!f) {
                continue
            }
            if (n && s.clk && c.type == "image") {
                if (!c.disabled && s.clk == c) {
                    i.push({
                        name: f,
                        value: e(c).val(),
                        type: c.type
                    });
                    i.push({
                        name: f + ".x",
                        value: s.clk_x
                    }, {
                        name: f + ".y",
                        value: s.clk_y
                    })
                }
                continue
            }
            l = e.fieldValue(c, true);
            if (l && l.constructor == Array) {
                if (r) r.push(c);
                for (a = 0, p = l.length; a < p; a++) {
                    i.push({
                        name: f,
                        value: l[a]
                    })
                }
            } else if (t.fileapi && c.type == "file" && !c.disabled) {
                if (r) r.push(c);
                var d = c.files;
                if (d.length) {
                    for (a = 0; a < d.length; a++) {
                        i.push({
                            name: f,
                            value: d[a],
                            type: c.type
                        })
                    }
                } else {
                    i.push({
                        name: f,
                        value: "",
                        type: c.type
                    })
                }
            } else if (l !== null && typeof l != "undefined") {
                if (r) r.push(c);
                i.push({
                    name: f,
                    value: l,
                    type: c.type,
                    required: c.required
                })
            }
        }
        if (!n && s.clk) {
            var v = e(s.clk),
                m = v[0];
            f = m.name;
            if (f && !m.disabled && m.type == "image") {
                i.push({
                    name: f,
                    value: v.val()
                });
                i.push({
                    name: f + ".x",
                    value: s.clk_x
                }, {
                    name: f + ".y",
                    value: s.clk_y
                })
            }
        }
        return i
    };
    e.fn.formSerialize = function (t) {
        return e.param(this.formToArray(t))
    };
    e.fn.fieldSerialize = function (t) {
        var n = [];
        this.each(function () {
            var r = this.name;
            if (!r) {
                return
            }
            var i = e.fieldValue(this, t);
            if (i && i.constructor == Array) {
                for (var s = 0, o = i.length; s < o; s++) {
                    n.push({
                        name: r,
                        value: i[s]
                    })
                }
            } else if (i !== null && typeof i != "undefined") {
                n.push({
                    name: this.name,
                    value: i
                })
            }
        });
        return e.param(n)
    };
    e.fn.fieldValue = function (t) {
        for (var n = [], r = 0, i = this.length; r < i; r++) {
            var s = this[r];
            var o = e.fieldValue(s, t);
            if (o === null || typeof o == "undefined" || o.constructor == Array && !o.length) {
                continue
            }
            if (o.constructor == Array) e.merge(n, o);
            else n.push(o)
        }
        return n
    };
    e.fieldValue = function (t, n) {
        var r = t.name,
            i = t.type,
            s = t.tagName.toLowerCase();
        if (n === undefined) {
            n = true
        }
        if (n && (!r || t.disabled || i == "reset" || i == "button" || (i == "checkbox" || i == "radio") && !t.checked || (i == "submit" || i == "image") && t.form && t.form.clk != t || s == "select" && t.selectedIndex == -1)) {
            return null
        }
        if (s == "select") {
            var o = t.selectedIndex;
            if (o < 0) {
                return null
            }
            var u = [],
                a = t.options;
            var f = i == "select-one";
            var l = f ? o + 1 : a.length;
            for (var c = f ? o : 0; c < l; c++) {
                var h = a[c];
                if (h.selected) {
                    var p = h.value;
                    if (!p) {
                        p = h.attributes && h.attributes["value"] && !h.attributes["value"].specified ? h.text : h.value
                    }
                    if (f) {
                        return p
                    }
                    u.push(p)
                }
            }
            return u
        }
        return e(t).val()
    };
    e.fn.clearForm = function (t) {
        return this.each(function () {
            e("input,select,textarea", this).clearFields(t)
        })
    };
    e.fn.clearFields = e.fn.clearInputs = function (t) {
        var n = /^(?:color|date|datetime|email|month|number|password|range|search|tel|text|time|url|week)$/i;
        return this.each(function () {
            var r = this.type,
                i = this.tagName.toLowerCase();
            if (n.test(r) || i == "textarea") {
                this.value = ""
            } else if (r == "checkbox" || r == "radio") {
                this.checked = false
            } else if (i == "select") {
                this.selectedIndex = -1
            } else if (r == "file") {
                if (/MSIE/.test(navigator.userAgent)) {
                    e(this).replaceWith(e(this).clone())
                } else {
                    e(this).val("")
                }
            } else if (t) {
                if (t === true && /hidden/.test(r) || typeof t == "string" && e(this).is(t)) this.value = ""
            }
        })
    };
    e.fn.resetForm = function () {
        return this.each(function () {
            if (typeof this.reset == "function" || typeof this.reset == "object" && !this.reset.nodeType) {
                this.reset()
            }
        })
    };
    e.fn.enable = function (e) {
        if (e === undefined) {
            e = true
        }
        return this.each(function () {
            this.disabled = !e
        })
    };
    e.fn.selected = function (t) {
        if (t === undefined) {
            t = true
        }
        return this.each(function () {
            var n = this.type;
            if (n == "checkbox" || n == "radio") {
                this.checked = t
            } else if (this.tagName.toLowerCase() == "option") {
                var r = e(this).parent("select");
                if (t && r[0] && r[0].type == "select-one") {
                    r.find("option").selected(false)
                }
                this.selected = t
            }
        })
    };
    e.fn.ajaxSubmit.debug = false
})(jQuery);

/********* jquery cookie plugin *********/
(function (e) {
    if (typeof define === "function" && define.amd && define.amd.jQuery) {
        define(["jquery"], e)
    } else {
        e(jQuery)
    }
})(function (e) {
    function n(e) {
        return e
    }

    function r(e) {
        return decodeURIComponent(e.replace(t, " "))
    }

    function i(e) {
        if (e.indexOf('"') === 0) {
            e = e.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, "\\")
        }
        return s.json ? JSON.parse(e) : e
    }

    var t = /\+/g;
    var s = e.cookie = function (t, o, u) {
        if (o !== undefined) {
            u = e.extend({}, s.defaults, u);
            if (typeof u.expires === "number") {
                var a = u.expires,
                    f = u.expires = new Date;
                f.setDate(f.getDate() + a)
            }
            o = s.json ? JSON.stringify(o) : String(o);
            return document.cookie = [encodeURIComponent(t), "=", s.raw ? o : encodeURIComponent(o), u.expires ? "; expires=" + u.expires.toUTCString() : "", u.path ? "; path=" + u.path : "", u.domain ? "; domain=" + u.domain : "", u.secure ? "; secure" : ""].join("")
        }
        var l = s.raw ? n : r;
        var c = document.cookie.split("; ");
        var h = t ? undefined : {};
        for (var p = 0, d = c.length; p < d; p++) {
            var v = c[p].split("=");
            var m = l(v.shift());
            var g = l(v.join("="));
            if (t && t === m) {
                h = i(g);
                break
            }
            if (!t) {
                h[m] = i(g)
            }
        }
        return h
    };
    s.defaults = {};
    e.removeCookie = function (t, n) {
        if (e.cookie(t) !== undefined) {
            e.cookie(t, "", e.extend(n, {
                expires: -1
            }));
            return true
        }
        return false
    }
});

/********  jquery marquee plugin  ********/

(function (a) {
    a.fn.marquee = function (b) {
        function c(a, b, c) {
            var d = c.behavior,
                e = c.width,
                f = c.dir;
            var g = 0;
            if (d == "alternate") {
                g = a == 1 ? b[c.widthAxis] - e * 2 : e
            } else if (d == "slide") {
                if (a == -1) {
                    g = f == -1 ? b[c.widthAxis] : e
                } else {
                    g = f == -1 ? b[c.widthAxis] - e * 2 : 0
                }
            } else {
                g = a == -1 ? b[c.widthAxis] : 0
            }
            return g
        }

        function d() {
            var b = e.length,
                f = null,
                g = null,
                h = {}, i = [],
                j = false;
            while (b--) {
                f = e[b];
                g = a(f);
                h = g.data("marqueeState");
                if (g.data("paused") !== true) {
                    f[h.axis] += h.scrollamount * h.dir;
                    j = h.dir == -1 ? f[h.axis] <= c(h.dir * -1, f, h) : f[h.axis] >= c(h.dir * -1, f, h);
                    if (h.behavior == "scroll" && h.last == f[h.axis] || h.behavior == "alternate" && j && h.last != -1 || h.behavior == "slide" && j && h.last != -1) {
                        if (h.behavior == "alternate") {
                            h.dir *= -1
                        }
                        h.last = -1;
                        g.trigger("stop");
                        h.loops--;
                        if (h.loops === 0) {
                            if (h.behavior != "slide") {
                                f[h.axis] = c(h.dir, f, h)
                            } else {
                                f[h.axis] = c(h.dir * -1, f, h)
                            }
                            g.trigger("end")
                        } else {
                            i.push(f);
                            g.trigger("start");
                            f[h.axis] = c(h.dir, f, h)
                        }
                    } else {
                        i.push(f)
                    }
                    h.last = f[h.axis];
                    g.data("marqueeState", h)
                } else {
                    i.push(f)
                }
            }
            e = i;
            if (e.length) {
                setTimeout(d, 25)
            }
        }

        var e = [],
            f = this.length;
        this.each(function (g) {
            var h = a(this),
                i = h.attr("width") || h.width(),
                j = h.attr("height") || h.height(),
                k = striptags(h.html()) == "" ? 0 : -12;
            $marqueeRedux = h.after("<div " + (b ? 'class="' + b + '" ' : "") + 'style="display: block-inline; width: ' + i + "px; height: " + j + "px; overflow: hidden; margin-bottom: " + k + 'px; margin-left: auto; margin-right:auto;"><div style="float: left; white-space: nowrap;">' + h.html() + "</div></div>").next(), marqueeRedux = $marqueeRedux.get(0), hitedge = 0, direction = (h.attr("direction") || "left").toLowerCase(), marqueeState = {
                dir: /down|right/.test(direction) ? -1 : 1,
                axis: /left|right/.test(direction) ? "scrollLeft" : "scrollTop",
                widthAxis: /left|right/.test(direction) ? "scrollWidth" : "scrollHeight",
                last: -1,
                loops: h.attr("loop") || -1,
                scrollamount: h.attr("scrollamount") || this.scrollAmount || 2,
                behavior: (h.attr("behavior") || "scroll").toLowerCase(),
                width: /left|right/.test(direction) ? i : j
            };
            if (h.attr("loop") == -1 && marqueeState.behavior == "slide") {
                marqueeState.loops = 1
            }
            h.remove();
            if (/left|right/.test(direction)) {
                $marqueeRedux.find("> div").css("padding", "0 " + i + "px")
            } else {
                $marqueeRedux.find("> div").css("padding", j + "px 0")
            }
            $marqueeRedux.bind("stop", function () {
                $marqueeRedux.data("paused", true)
            }).bind("pause", function () {
                $marqueeRedux.data("paused", true)
            }).bind("start", function () {
                $marqueeRedux.data("paused", false)
            }).bind("unpause", function () {
                $marqueeRedux.data("paused", false)
            }).data("marqueeState", marqueeState);
            e.push(marqueeRedux);
            marqueeRedux[marqueeState.axis] = c(marqueeState.dir, marqueeRedux, marqueeState);
            $marqueeRedux.trigger("start");
            if (g + 1 == f) {
                d()
            }
        });
        return a(e)
    }
})(jQuery);

/***** jQuery populate *****/
jQuery.fn.populate = function (a, b) {
    function c(a, d) {
        d = d || "";
        if (a == undefined) {
        } else if (a.constructor == Object) {
            for (var e in a) {
                var f = d + (d == "" ? e : "[" + e + "]");
                c(a[e], f)
            }
        } else if (a.constructor == Array) {
            for (var g = 0; g < a.length; g++) {
                var i = b.useIndices ? g : "";
                i = b.phpNaming ? "[" + i + "]" : i;
                var f = d + i;
                c(a[g], f)
            }
        } else {
            if (h[d] == undefined) {
                h[d] = a
            } else if (h[d].constructor != Array) {
                h[d] = [h[d], a]
            } else {
                h[d].push(a)
            }
        }
    }

    function d(a) {
        if (window.console && console.log) {
            console.log(a)
        }
    }

    function e(a) {
        if (!b.phpNaming) {
            a = a.replace(/\[\]$/, "")
        }
        return a
    }

    function f(a, c, d) {
        var e = b.identifier == "id" ? "#" + c : "[" + b.identifier + '="' + c + '"]';
        var f = jQuery(e, a);
        d = d.toString();
        d = d == "null" ? "" : d;
        f.html(d)
    }

    function g(a, c, f) {
        var c = e(c);
        var g = a[c];
        if (g == undefined) {
            g = jQuery("#" + c, a);
            if (g) {
                g.html(f);
                return true
            }
            if (b.debug) {
                d("No such element as " + c)
            }
            return false
        }
        if (b.debug) {
            _populate.elements.push(g)
        }
        elements = g.type == undefined && g.length ? g : [g];
        for (var h = 0; h < elements.length; h++) {
            var g = elements[h];
            if (!g || typeof g == "undefined" || typeof g == "function") {
                continue
            }
            switch (g.type || g.tagName) {
                case "radio":
                    g.checked = g.value != "" && f.toString() == g.value;
                case "checkbox":
                    var i = f.constructor == Array ? f : [f];
                    for (var j = 0; j < i.length; j++) {
                        g.checked |= g.value == i[j]
                    }
                    break;
                case "select-multiple":
                    var i = f.constructor == Array ? f : [f];
                    for (var k = 0; k < g.options.length; k++) {
                        for (var j = 0; j < i.length; j++) {
                            g.options[k].selected |= g.options[k].value == i[j]
                        }
                    }
                    break;
                case "select":
                case "select-one":
                    g.value = f.toString() || f;
                    break;
                case "text":
                case "button":
                case "textarea":
                case "submit":
                default:
                    f = f == null ? "" : f;
                    g.value = f
            }
        }
    }

    if (a === undefined) {
        return this
    }
    var b = jQuery.extend({
        phpNaming: true,
        phpIndices: false,
        resetForm: true,
        identifier: "id",
        debug: false
    }, b);
    if (b.phpIndices) {
        b.phpNaming = true
    }
    var h = [];
    c(a);
    if (b.debug) {
        _populate = {
            arr: h,
            obj: a,
            elements: []
        }
    }
    this.each(function () {
        var a = this.tagName.toLowerCase();
        var c = a == "form" ? g : f;
        if (a == "form" && b.resetForm) {
            this.reset()
        }
        for (var d in h) {
            c(this, d, h[d])
        }
    });
    return this
};

/***** jQuery deserialize *****/

(function (a) {
    var b = Array.prototype.push,
        c = /^(radio|checkbox)$/i,
        d = /^(option|select-one|select-multiple)$/i,
        e = /^(hidden|text|search|tel|url|email|password|datetime|date|month|week|time|datetime-local|number|range|color|submit|image|reset|button|textarea)$/i;
    a.fn.extend({
        deserialize: function (f, g) {
            if (!this.length || !f) {
                return this
            }
            var h, i, j = this[0].elements || this.find(":input").get(),
                k = [];
            if (!j) {
                return this
            }
            if (a.isArray(f)) {
                k = f
            } else if (a.isPlainObject(f)) {
                var l, m;
                for (l in f) {
                    a.isArray(m = f[l]) ? b.apply(k, a.map(m, function (a) {
                        return {
                            name: l,
                            value: a
                        }
                    })) : b.call(k, {
                        name: l,
                        value: m
                    })
                }
            } else if (typeof f === "string") {
                var n;
                f = decodeURIComponent(f).split("&");
                for (h = 0, i = f.length; h < i; h++) {
                    n = f[h].split("=");
                    b.call(k, {
                        name: n[0],
                        value: n[1]
                    })
                }
            }
            if (!(i = k.length)) {
                return this
            }
            var o, p, q, r, s, t, u;
            for (h = 0; h < i; h++) {
                o = k[h];
                if (!(p = j[o.name])) {
                    continue
                }
                u = (s = p.length) ? p[0] : p;
                u = u.type || u.nodeName;
                t = null;
                if (e.test(u)) {
                    t = "value"
                } else if (c.test(u)) {
                    t = "checked"
                } else if (d.test(u)) {
                    t = "selected"
                }
                if (s) {
                    for (r = 0; r < s; r++) {
                        q = p[r];
                        if (q.value == o.value) {
                            q[t] = true
                        }
                    }
                } else {
                    p[t] = o.value
                }
            }
            if (a.isFunction(g)) {
                g.call(this)
            }
            return this
        }
    })
})(jQuery);


/***********   jQuery HashChange plugin   ************/
(function (a, b, c) {
    function d(a) {
        a = a || location.href;
        return "#" + a.replace(/^[^#]*#?(.*)$/, "$1")
    }

    "$:nomunge";
    var e = "hashchange",
        f = document,
        g, h = a.event.special,
        i = f.documentMode,
        j = "on" + e in b && (i === c || i > 7);
    a.fn[e] = function (a) {
        return a ? this.bind(e, a) : this.trigger(e)
    };
    a.fn[e].delay = 50;
    h[e] = a.extend(h[e], {
        setup: function () {
            if (j) {
                return false
            }
            a(g.start)
        },
        teardown: function () {
            if (j) {
                return false
            }
            a(g.stop)
        }
    });
    g = function () {
        function g() {
            var c = d(),
                f = n(k);
            if (c !== k) {
                m(k = c, f);
                a(b).trigger(e)
            } else if (f !== k) {
                location.href = location.href.replace(/#.*/, "") + f
            }
            i = setTimeout(g, a.fn[e].delay)
        }

        var h = {}, i, k = d(),
            l = function (a) {
                return a
            }, m = l,
            n = l;
        h.start = function () {
            i || g()
        };
        h.stop = function () {
            i && clearTimeout(i);
            i = c
        };
        /msie/.test(navigator.userAgent.toLowerCase()) && !j && function () {
            var b, c;
            h.start = function () {
                if (!b) {
                    c = a.fn[e].src;
                    c = c && c + d();
                    b = a('<iframe tabindex="-1" title="empty"/>').hide().one("load", function () {
                        c || m(d());
                        g()
                    }).attr("src", c || "javascript:0").insertAfter("body")[0].contentWindow;
                    f.onpropertychange = function () {
                        try {
                            if (event.propertyName === "title") {
                                b.document.title = f.title
                            }
                        } catch (a) {
                        }
                    }
                }
            };
            h.stop = l;
            n = function () {
                return d(b.location.href)
            };
            m = function (c, d) {
                var g = b.document,
                    h = a.fn[e].domain;
                if (c !== d) {
                    g.title = f.title;
                    g.open();
                    h && g.write('<script>document.domain="' + h + '"</script>');
                    g.close();
                    b.location.hash = c
                }
            }
        }();
        return h
    }()
})(jQuery, this)



/*
 * Project: Twitter Bootstrap Hover Dropdown
 * Author: Cameron Spear
 * Contributors: Mattia Larentis
 *
 * Dependencies?: Twitter Bootstrap's Dropdown plugin
 *
 * A simple plugin to enable twitter bootstrap dropdowns to active on hover and provide a nice user experience.
 *
 * No license, do what you want. I'd love credit or a shoutout, though.
 *
 * http://cameronspear.com/blog/twitter-bootstrap-dropdown-on-hover-plugin/
 */
;
(function ($, window, undefined) {
    // outside the scope of the jQuery plugin to
    // keep track of all dropdowns
    var $allDropdowns = $();

    // if instantlyCloseOthers is true, then it will instantly
    // shut other nav items when a new one is hovered over
    $.fn.dropdownHover = function (options) {

        // the element we really care about
        // is the dropdown-toggle's parent
        $allDropdowns = $allDropdowns.add(this.parent());

        return this.each(function () {
            var $this = $(this).parent(),
                defaults = {
                    delay: 100,
                    instantlyCloseOthers: true
                },
                data = {
                    delay: $(this).data('delay'),
                    instantlyCloseOthers: $(this).data('close-others')
                },
                options = $.extend(true, {}, defaults, options, data),
                timeout;

            $this.hover(function () {
                if (options.instantlyCloseOthers === true)
                    $allDropdowns.removeClass('open');

                window.clearTimeout(timeout);
                $(this).addClass('open');
            }, function () {
                timeout = window.setTimeout(function () {
                    $this.removeClass('open');
                }, options.delay);
            });
        });
    };

    $(document).ready(function () {
        $('[data-hover="dropdown"]').dropdownHover();
    });
})(jQuery, this);


/**
 ** noUislider 2.0
 ** No copyrights or licenses. Do what you like. Feel free to share this code, or build upon it.
 ** @author:        @leongersen
 ** @repository:    https://github.com/leongersen/noUiSlider
 **
 **/

(function ($) {

    $.fn.left = function () {
        return parseInt(this.css('left'));
    };

    /**
     ** Touch support
     **
     ** Implementation from:
     ** http://ross.posterous.com/2008/08/19/iphone-touch-events-in-javascript/
     **/

    function touchHandler(event) {

        var touches = event.changedTouches, first = touches[0], type = "";

        switch (event.type) {
            case "touchstart":
                type = "mousedown";
                break;
            case "touchmove":
                type = "mousemove";
                break;
            case "touchend":
                type = "mouseup";
                break;
            default:
                return;
        }

        var simulatedEvent = document.createEvent("MouseEvent");
        simulatedEvent.initMouseEvent(type, true, true, window, 1, first.screenX, first.screenY, first.clientX, first.clientY, false, false, false, false, 0, null);

        first.target.dispatchEvent(simulatedEvent);
        event.preventDefault();

    }

    $.fn.noUiSlider = function (method, options) {

        var settings = {

            /**
             ** {knobs}             Specifies the number of knobs. (init)
             ** [INT]               1, 2
             **/
            'knobs': 2,
            /**
             ** {connect}           Whether to connect the middle bar to the knobs. (init)
             ** [MIXED]             "upper", "lower", false, true
             **/
            'connect': true,
            /**
             ** {scale};            The values represented by the slider knobs. (init,move,value)
             ** [ARRAY]             [-+x,>x]
             **/
            'scale': [0, 100],
            /**
             ** {start}             The starting positions for the knobs, mapped to {scale}. (init)
             ** [ARRAY]             [y>={scale[0]}, y=<{scale[1]}]
             **/
            'start': [25, 75],
            /**
             ** {to}                The position to move a knob to. (move)
             ** [INT]               Any, but will be corrected to match z > {scale[0]} || _l, z < {scale[1]} || _u
             **/
            'to': 0,
            /**
             ** {knob}              The knob to move. (move)
             ** [MIXED]             0,1,"lower","upper"
             **/
            'knob': 0,
            /**
             ** {change}            The function to be called on every change. (init)
             ** [FUNCTION]          param [STRING]'move type'
             **/
            'change': '',
            /**
             ** {end}               The function when a knob is no longer being changed. (init)
             ** [FUNCTION]          param [STRING]'move type'
             **/
            'end': '',
            /**
             ** {step}              Whether, and at what intervals, the slider should snap to a new position. Adheres to {scale} (init)
             ** [MIXED]             <x, FALSE
             **/
            'step': false,
            /**
             ** {save}              Whether a scale give to a function should become the default for the slider it is called on. (move,value)
             ** [BOOLEAN]           true, false
             **/
            'save': false

        };

        /**
         ** [FUNCTION]  attach
         ** param       [noUiSliderObject]
         ** returns     [NULL]
         **/
        function attach(o) {

            var s = o.data('settings');

            var _l = o.children('.noUi-lowerHandle');
            var _u = o.children('.noUi-upperHandle');
            var _b = o.children('.noUi-midBar');

            if (s.connect !== false) {

                if (_l) {
                    if (_u) {
                        _b.css('left', _l.left());
                    } else {
                        if (s.connect == 'lower') {
                            _b.css('right', ( o.innerWidth() - _l.left() ));
                        } else {
                            _b.css({'left': _l.left(), 'right': 0});
                        }
                    }
                }
                if (_u) {
                    if (_l) {
                        _b.css('right', ( o.innerWidth() - _u.left() ));
                    } else {
                        if (s.connect == 'lower') {
                            _b.css('right', ( o.innerWidth() - _u.left() ));
                        } else {
                            _b.css({'left': _u.left(), 'right': 0});
                        }
                    }
                }

            }

            var values = new Array();

            if (_l) {
                values[0] = reverse(s.scale[0], s.scale[1], _l.left(), o.innerWidth());
            } else {
                values[0] = false;
            }

            if (_u) {
                values[1] = reverse(s.scale[0], s.scale[1], _u.left(), o.innerWidth());
            } else {
                values[1] = false;
            }

            o.data('values', values);

        }

        /**
         ** [FUNCTION]  isNeg
         ** param       [INT]
         ** returns     true, false
         **/
        function isNeg(test) {
            return test < 0;
        }

        /**
         ** [FUNCTION]  inv
         ** param       [INT]
         ** returns     inverted [INT]
         **/
        function inv(subject) {
            return subject * -1;
        }

        /**
         ** [FUNCTION]  translate
         ** param       [INT][INT][INT][INT]
         ** returns     [INT]
         **/
        function translate(low, high, val, ref) {

            if (isNeg(low)) {

                val = val + inv(low);
                high = high + inv(low);

            } else {

                val = val - low;
                high = high - low;

            }

            return ( ( val * ref ) / high );

        }

        /**
         ** [FUNCTION]  reverse
         ** param       [INT][INT][INT][INT]
         ** returns     [INT]
         **/
        function reverse(low, high, val, ref) {

            if (isNeg(low)) {

                high = high + inv(low);

            } else {

                high = high - low;

            }

            return ( ( ( val * high ) / ref ) + low );

        }

        var methods = {

            /**
             ** [FUNCTION]
             ** Initialises slider, places DOM elements, binds events
             **/
            init: function () {

                return this.each(function () {

                    var o = $(this);
                    var s = settings;

                    o.data('settings', s);

                    var _l = $('<div class="noUi-handle noUi-lowerHandle"><div></div></div>');
                    var _u = $('<div class="noUi-handle noUi-upperHandle"><div></div></div>');
                    var _b = $('<div class="noUi-midBar"></div>');
                    var knobs = false;

                    if (s.knobs === 1) {

                        if (s.connect === true || s.connect === 'lower') {

                            _l = false;
                            _u = _u.appendTo(o);
                            _b = _b.insertBefore(_u);

                            knobs = _u;

                        } else {

                            if (s.connect === 'upper') {

                                _l = _l.appendTo(o);
                                _b = _b.insertAfter(_l);
                                _u = false;

                                knobs = _l;

                            } else {

                                _l = _l.appendTo(o);
                                _b = false;
                                _u = false;

                                knobs = _l;

                            }

                        }

                    } else {

                        knobs = _l.add(_u).appendTo(o);

                        _l = knobs.filter('.noUi-lowerHandle');
                        _u = knobs.filter('.noUi-upperHandle');

                        if (s.connect === true) {

                            _b = _b.insertAfter(_l);

                        } else {

                            _b = false;

                        }

                    }

                    o.data('knobs', knobs).css('position', 'relative').children().css('position', 'absolute');

                    if (_b) {
                        _b.css({'left': 0, 'right': 0});
                    }

                    /** Setting all knobs to their initial position **/
                    knobs.each(function (index) {

                        $(this).css({
                            'left': translate(s.scale[0], s.scale[1], s.start[index], o.innerWidth()),
                            'zIndex': index + 1
                        });

                        if (document.addEventListener) {
                            this.addEventListener("touchstart", touchHandler, true);
                            this.addEventListener("touchmove", touchHandler, true);
                            this.addEventListener("touchend", touchHandler, true);
                            this.addEventListener("touchcancel", touchHandler, true);
                        }

                    });

                    /** Trigger midbar build **/
                    attach(o);

                    /** Bind mousedown event on all knobs. **/
                    knobs.children().bind('mousedown.noUiSlider', function (e) {
                        if ($(this).parent().parent().hasClass("disabled")) return;

                        var k = $(this).parent();
                        $(this).addClass('noUi-activeHandle');
                        e.preventDefault();

                        /** Prevent accidental selecting **/
                        $('body').bind('selectstart.noUiSlider', function () {
                            return false;
                        });

                        /** Respond to the mouse moving trough the document. **/
                        $(document).bind('mousemove.noUiSlider', function (f) {

                            var newPosition = ( f.pageX - ( Math.round(o.offset().left)) );
                            var currentPosition = k.left();

                            var greenLight = false;

                            /** Correcting the new value for overlap with either the other knob or the sliders edges. Any correction vouches for step. **/
                            if (k.hasClass('noUi-upperHandle')) {

                                if (_l && newPosition < _l.left()) {

                                    newPosition = _l.left();
                                    greenLight = true;

                                }

                            }

                            if (k.hasClass('noUi-lowerHandle')) {

                                if (_u && newPosition > _u.left()) {

                                    newPosition = _u.left();
                                    greenLight = true;

                                }

                            }

                            if (newPosition > o.innerWidth()) {

                                newPosition = o.innerWidth();
                                greenLight = true;

                            } else if (newPosition < 0) {

                                newPosition = 0;
                                greenLight = true;

                            }

                            if (s.step && !greenLight) {

                                if (Math.abs(currentPosition - newPosition) >= translate(s.scale[0], s.scale[1], s.step, o.innerWidth())) {

                                    greenLight = true;

                                }

                            } else {

                                greenLight = true;

                            }

                            /** Even if all checks worked fine, there is still no need what-so-ever to fire any functions without change. **/
                            if (currentPosition == newPosition) {

                                greenLight = false;

                            }

                            /** Move the knob to it's new position, and call all callbacks **/
                            if (greenLight) {

                                k.css('left', newPosition);

                                /** Safety patch to prevent knobs getting stuck **/
                                if ((k.hasClass('noUi-upperHandle') && k.left() == 0) || (k.hasClass('noUi-lowerHandle') && k.left() == o.innerWidth())) {
                                    k.css('zIndex', parseInt(k.css('zIndex')) + 2);
                                }

                                attach(o);
                                if (typeof(s.change) == "function") {
                                    s.change.call(o, 'slide');
                                }

                            }

                        });

                        /** Unbind events on mouseup **/
                        $(document).bind('mouseup.noUiSlider', function () {

                            $('.noUi-activeHandle').removeClass('noUi-activeHandle');
                            $(document).unbind('mousemove.noUiSlider').unbind('mouseup.noUiSlider');
                            $('body').unbind('selectstart.noUiSlider');

                            if (typeof(s.end) == "function") {
                                s.end.call(o, 'slide');
                            }

                        });

                    });

                    /** clickMove functionality **/
                    o.click(function (e) {
                        if ($(this).hasClass("disabled")) return;

                        if (_l && _u) {

                            var calc = e.pageX - o.offset().left;

                            if (calc < ( ( _l.left() + _u.left() ) / 2 )) {

                                _l.css("left", calc);

                            } else {

                                _u.css("left", calc);

                            }

                        } else {

                            knobs.css('left', (e.pageX - o.offset().left));

                        }

                        attach(o);

                        if (typeof(s.change) == "function") {
                            s.change.call(o, 'click');
                        }
                        if (typeof(s.end) == "function") {
                            s.end.call(o, 'click');
                        }

                    }).find('*:not(.noUi-midBar)').click(function () {

                        return false;

                    });

                });

            },

            /**
             ** [FUNCTION]
             ** Moves slider to given point
             **/

            move: function () {

                var o = $(this);

                var s = o.data('settings');
                var knobs = o.data('knobs');

                var _l = knobs.filter('.noUi-lowerHandle');
                var _u = knobs.filter('.noUi-upperHandle');

                var n = settings;

                if (n.scale) {

                    s.scale = n.scale;

                    if (n.save) {
                        o.data('settings', s)
                    }

                }

                var newPosition = translate(s.scale[0], s.scale[1], n.to, o.innerWidth());

                var k;

                if (n.knob === 'upper' || n.knob == 1) {

                    if (_l && newPosition < _l.left()) {

                        newPosition = _l.left();

                    }

                    k = _u;

                } else if (n.knob === 'lower' || n.knob == 0) {

                    if (_u && newPosition > _u.left()) {

                        newPosition = _u.left();

                    }

                    k = _l;

                }

                if (newPosition > o.innerWidth()) {

                    newPosition = o.innerWidth();

                } else if (newPosition < 0) {

                    newPosition = 0;

                }

                k.css('left', newPosition);

                /** Safety patch to prevent knobs getting stuck **/
                if ((k.hasClass('noUi-upperHandle') && k.left() == 0) || (k.hasClass('noUi-lowerHandle') && k.left() == o.innerWidth())) {
                    k.css('zIndex', parseInt(k.css('zIndex')) + 2);
                }

                attach(o);

                if (typeof(s.change) == "function") {
                    s.change.call(o, 'move');
                }
                if (typeof(s.end) == "function") {
                    s.end.call(o, 'move');
                }

            },

            value: function () {

                var o = $(this);
                var s = o.data('settings');

                var n = optns;

                var values = o.data('values');

                if (typeof n != "undefined" && typeof n.scale != "undefined" && !( ( n.scale[0] == s.scale[0] ) && ( n.scale[1] == s.scale[1] ) )) {

                    /** crazy wizard magic! **/

                    if (values[0]) {
                        values[0] = translate(n.scale[0], n.scale[1], reverse(s.scale[0], s.scale[1], values[0], o.innerWidth()), o.innerWidth());
                    }
                    if (values[1]) {
                        values[1] = translate(n.scale[0], n.scale[1], reverse(s.scale[0], s.scale[1], values[1], o.innerWidth()), o.innerWidth());
                    }

                    if (n.save) {

                        s.scale = n.scale;
                        o.data('settings', s);
                        $(this).data('values', values)

                    }

                }

                /** For usability. There is no real use for floats here. **/
                if (values[0]) {
                    values[0] = Math.round(values[0]);
                }
                if (values[1]) {
                    values[1] = Math.round(values[1]);
                }

                return values;

            }

        };

        var optns = options;
        var options = $.extend(settings, options);

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('No such method: ' + method);
        }

    };

})(jQuery);

/*
 * 
 * TableSorter 2.0 - Client-side table sorting with ease!
 * Version 2.0.5b
 * @requires jQuery v1.2.3
 * 
 * Copyright (c) 2007 Christian Bach
 * Examples and docs at: http://tablesorter.com
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * 
 */
/**
 *
 * @description Create a sortable table with multi-column sorting capabilitys
 *
 * @example $('table').tablesorter();
 * @desc Create a simple tablesorter interface.
 *
 * @example $('table').tablesorter({ sortList:[[0,0],[1,0]] });
 * @desc Create a tablesorter interface and sort on the first and secound column column headers.
 *
 * @example $('table').tablesorter({ headers: { 0: { sorter: false}, 1: {sorter: false} } });
 *
 * @desc Create a tablesorter interface and disableing the first and second  column headers.
 *
 *
 * @example $('table').tablesorter({ headers: { 0: {sorter:"integer"}, 1: {sorter:"currency"} } });
 *
 * @desc Create a tablesorter interface and set a column parser for the first
 *       and second column.
 *
 *
 * @param Object
 *            settings An object literal containing key/value pairs to provide
 *            optional settings.
 *
 *
 * @option String cssHeader (optional) A string of the class name to be appended
 *         to sortable tr elements in the thead of the table. Default value:
 *         "header"
 *
 * @option String cssAsc (optional) A string of the class name to be appended to
 *         sortable tr elements in the thead on a ascending sort. Default value:
 *         "headerSortUp"
 *
 * @option String cssDesc (optional) A string of the class name to be appended
 *         to sortable tr elements in the thead on a descending sort. Default
 *         value: "headerSortDown"
 *
 * @option String sortInitialOrder (optional) A string of the inital sorting
 *         order can be asc or desc. Default value: "asc"
 *
 * @option String sortMultisortKey (optional) A string of the multi-column sort
 *         key. Default value: "shiftKey"
 *
 * @option String textExtraction (optional) A string of the text-extraction
 *         method to use. For complex html structures inside td cell set this
 *         option to "complex", on large tables the complex option can be slow.
 *         Default value: "simple"
 *
 * @option Object headers (optional) An array containing the forces sorting
 *         rules. This option let's you specify a default sorting rule. Default
 *         value: null
 *
 * @option Array sortList (optional) An array containing the forces sorting
 *         rules. This option let's you specify a default sorting rule. Default
 *         value: null
 *
 * @option Array sortForce (optional) An array containing forced sorting rules.
 *         This option let's you specify a default sorting rule, which is
 *         prepended to user-selected rules. Default value: null
 *
 * @option Boolean sortLocaleCompare (optional) Boolean flag indicating whatever
 *         to use String.localeCampare method or not. Default set to true.
 *
 *
 * @option Array sortAppend (optional) An array containing forced sorting rules.
 *         This option let's you specify a default sorting rule, which is
 *         appended to user-selected rules. Default value: null
 *
 * @option Boolean widthFixed (optional) Boolean flag indicating if tablesorter
 *         should apply fixed widths to the table columns. This is usefull when
 *         using the pager companion plugin. This options requires the dimension
 *         jquery plugin. Default value: false
 *
 * @option Boolean cancelSelection (optional) Boolean flag indicating if
 *         tablesorter should cancel selection of the table headers text.
 *         Default value: true
 *
 * @option Boolean debug (optional) Boolean flag indicating if tablesorter
 *         should display debuging information usefull for development.
 *
 * @type jQuery
 *
 * @name tablesorter
 *
 * @cat Plugins/Tablesorter
 *
 * @author Christian Bach/christian.bach@polyester.se
 */

(function ($) {
    $.extend({
        tablesorter: new
            function () {

                var parsers = [],
                    widgets = [];

                this.defaults = {
                    cssHeader: "header",
                    cssAsc: "headerSortUp",
                    cssDesc: "headerSortDown",
                    cssChildRow: "expand-child",
                    sortInitialOrder: "asc",
                    sortMultiSortKey: "shiftKey",
                    sortForce: null,
                    sortAppend: null,
                    sortLocaleCompare: true,
                    textExtraction: "simple",
                    parsers: {}, widgets: [],
                    widgetZebra: {
                        css: ["even", "odd"]
                    }, headers: {}, widthFixed: false,
                    cancelSelection: true,
                    sortList: [],
                    headerList: [],
                    dateFormat: "us",
                    decimal: '/\.|\,/g',
                    onRenderHeader: null,
                    selectorHeaders: 'thead th',
                    debug: false
                };

                /* debuging utils */

                function benchmark(s, d) {
                    log(s + "," + (new Date().getTime() - d.getTime()) + "ms");
                }

                this.benchmark = benchmark;

                function log(s) {
                    if (typeof console != "undefined" && typeof console.debug != "undefined") {
                        console.log(s);
                    } else {
                        alert(s);
                    }
                }

                /* parsers utils */

                function buildParserCache(table, $headers) {

                    if (table.config.debug) {
                        var parsersDebug = "";
                    }

                    if (table.tBodies.length == 0) return; // In the case of empty tables
                    var rows = table.tBodies[0].rows;

                    if (rows[0]) {

                        var list = [],
                            cells = rows[0].cells,
                            l = cells.length;

                        for (var i = 0; i < l; i++) {

                            var p = false;

                            if ($.metadata && ($($headers[i]).metadata() && $($headers[i]).metadata().sorter)) {

                                p = getParserById($($headers[i]).metadata().sorter);

                            } else if ((table.config.headers[i] && table.config.headers[i].sorter)) {

                                p = getParserById(table.config.headers[i].sorter);
                            }
                            if (!p) {

                                p = detectParserForColumn(table, rows, -1, i);
                            }

                            if (table.config.debug) {
                                parsersDebug += "column:" + i + " parser:" + p.id + "\n";
                            }

                            list.push(p);
                        }
                    }

                    if (table.config.debug) {
                        log(parsersDebug);
                    }

                    return list;
                };

                function detectParserForColumn(table, rows, rowIndex, cellIndex) {
                    var l = parsers.length,
                        node = false,
                        nodeValue = false,
                        keepLooking = true;
                    while (nodeValue == '' && keepLooking) {
                        rowIndex++;
                        if (rows[rowIndex]) {
                            node = getNodeFromRowAndCellIndex(rows, rowIndex, cellIndex);
                            nodeValue = trimAndGetNodeText(table.config, node);
                            if (table.config.debug) {
                                log('Checking if value was empty on row:' + rowIndex);
                            }
                        } else {
                            keepLooking = false;
                        }
                    }
                    for (var i = 1; i < l; i++) {
                        if (parsers[i].is(nodeValue, table, node)) {
                            return parsers[i];
                        }
                    }
                    // 0 is always the generic parser (text)
                    return parsers[0];
                }

                function getNodeFromRowAndCellIndex(rows, rowIndex, cellIndex) {
                    return rows[rowIndex].cells[cellIndex];
                }

                function trimAndGetNodeText(config, node) {
                    return $.trim(getElementText(config, node));
                }

                function getParserById(name) {
                    var l = parsers.length;
                    for (var i = 0; i < l; i++) {
                        if (parsers[i].id.toLowerCase() == name.toLowerCase()) {
                            return parsers[i];
                        }
                    }
                    return false;
                }

                /* utils */

                function buildCache(table) {

                    if (table.config.debug) {
                        var cacheTime = new Date();
                    }

                    var totalRows = (table.tBodies[0] && table.tBodies[0].rows.length) || 0,
                        totalCells = (table.tBodies[0].rows[0] && table.tBodies[0].rows[0].cells.length) || 0,
                        parsers = table.config.parsers,
                        cache = {
                            row: [],
                            normalized: []
                        };

                    for (var i = 0; i < totalRows; ++i) {

                        /** Add the table data to main data array */
                        var c = $(table.tBodies[0].rows[i]),
                            cols = [];

                        // if this is a child row, add it to the last row's children and
                        // continue to the next row
                        if (c.hasClass(table.config.cssChildRow)) {
                            cache.row[cache.row.length - 1] = cache.row[cache.row.length - 1].add(c);
                            // go to the next for loop
                            continue;
                        }

                        cache.row.push(c);

                        for (var j = 0; j < totalCells; ++j) {
                            cols.push(parsers[j].format(getElementText(table.config, c[0].cells[j]), table, c[0].cells[j]));
                        }

                        cols.push(cache.normalized.length); // add position for rowCache
                        cache.normalized.push(cols);
                        cols = null;
                    }
                    ;

                    if (table.config.debug) {
                        benchmark("Building cache for " + totalRows + " rows:", cacheTime);
                    }

                    return cache;
                };

                function getElementText(config, node) {

                    var text = "";

                    if (!node) return "";

                    if (!config.supportsTextContent) config.supportsTextContent = node.textContent || false;

                    if (config.textExtraction == "simple") {
                        if (config.supportsTextContent) {
                            text = node.textContent;
                        } else {
                            if (node.childNodes[0] && node.childNodes[0].hasChildNodes()) {
                                text = node.childNodes[0].innerHTML;
                            } else {
                                text = node.innerHTML;
                            }
                        }
                    } else {
                        if (typeof(config.textExtraction) == "function") {
                            text = config.textExtraction(node);
                        } else {
                            text = $(node).text();
                        }
                    }
                    return text;
                }

                function appendToTable(table, cache) {

                    if (table.config.debug) {
                        var appendTime = new Date()
                    }

                    var c = cache,
                        r = c.row,
                        n = c.normalized,
                        totalRows = n.length,
                        checkCell = (n[0].length - 1),
                        tableBody = $(table.tBodies[0]),
                        rows = [];


                    for (var i = 0; i < totalRows; i++) {
                        var pos = n[i][checkCell];

                        rows.push(r[pos]);

                        if (!table.config.appender) {

                            //var o = ;
                            var l = r[pos].length;
                            for (var j = 0; j < l; j++) {
                                tableBody[0].appendChild(r[pos][j]);
                            }

                            //
                        }
                    }


                    if (table.config.appender) {

                        table.config.appender(table, rows);
                    }

                    rows = null;

                    if (table.config.debug) {
                        benchmark("Rebuilt table:", appendTime);
                    }

                    // apply table widgets
                    applyWidget(table);

                    // trigger sortend
                    setTimeout(function () {
                        $(table).trigger("sortEnd");
                    }, 0);

                };

                function buildHeaders(table) {

                    if (table.config.debug) {
                        var time = new Date();
                    }

                    var meta = ($.metadata) ? true : false;

                    var header_index = computeTableHeaderCellIndexes(table);

                    $tableHeaders = $(table.config.selectorHeaders, table).each(function (index) {

                        this.column = header_index[this.parentNode.rowIndex + "-" + this.cellIndex];
                        // this.column = index;
                        this.order = formatSortingOrder(table.config.sortInitialOrder);


                        this.count = this.order;

                        if (checkHeaderMetadata(this) || checkHeaderOptions(table, index)) this.sortDisabled = true;
                        if (checkHeaderOptionsSortingLocked(table, index)) this.order = this.lockedOrder = checkHeaderOptionsSortingLocked(table, index);

                        if (!this.sortDisabled) {
                            var $th = $(this).addClass(table.config.cssHeader);
                            if (table.config.onRenderHeader) table.config.onRenderHeader.apply($th);
                        }

                        // add cell to headerList
                        table.config.headerList[index] = this;
                    });

                    if (table.config.debug) {
                        benchmark("Built headers:", time);
                        log($tableHeaders);
                    }

                    return $tableHeaders;

                };

                // from:
                // http://www.javascripttoolbox.com/lib/table/examples.php
                // http://www.javascripttoolbox.com/temp/table_cellindex.html


                function computeTableHeaderCellIndexes(t) {
                    var matrix = [];
                    var lookup = {};
                    var thead = t.getElementsByTagName('THEAD')[0];
                    var trs = thead.getElementsByTagName('TR');

                    for (var i = 0; i < trs.length; i++) {
                        var cells = trs[i].cells;
                        for (var j = 0; j < cells.length; j++) {
                            var c = cells[j];

                            var rowIndex = c.parentNode.rowIndex;
                            var cellId = rowIndex + "-" + c.cellIndex;
                            var rowSpan = c.rowSpan || 1;
                            var colSpan = c.colSpan || 1
                            var firstAvailCol;
                            if (typeof(matrix[rowIndex]) == "undefined") {
                                matrix[rowIndex] = [];
                            }
                            // Find first available column in the first row
                            for (var k = 0; k < matrix[rowIndex].length + 1; k++) {
                                if (typeof(matrix[rowIndex][k]) == "undefined") {
                                    firstAvailCol = k;
                                    break;
                                }
                            }
                            lookup[cellId] = firstAvailCol;
                            for (var k = rowIndex; k < rowIndex + rowSpan; k++) {
                                if (typeof(matrix[k]) == "undefined") {
                                    matrix[k] = [];
                                }
                                var matrixrow = matrix[k];
                                for (var l = firstAvailCol; l < firstAvailCol + colSpan; l++) {
                                    matrixrow[l] = "x";
                                }
                            }
                        }
                    }
                    return lookup;
                }

                function checkCellColSpan(table, rows, row) {
                    var arr = [],
                        r = table.tHead.rows,
                        c = r[row].cells;

                    for (var i = 0; i < c.length; i++) {
                        var cell = c[i];

                        if (cell.colSpan > 1) {
                            arr = arr.concat(checkCellColSpan(table, headerArr, row++));
                        } else {
                            if (table.tHead.length == 1 || (cell.rowSpan > 1 || !r[row + 1])) {
                                arr.push(cell);
                            }
                            // headerArr[row] = (i+row);
                        }
                    }
                    return arr;
                };

                function checkHeaderMetadata(cell) {
                    if (($.metadata) && ($(cell).metadata().sorter === false)) {
                        return true;
                    }
                    ;
                    return false;
                }

                function checkHeaderOptions(table, i) {
                    if ((table.config.headers[i]) && (table.config.headers[i].sorter === false)) {
                        return true;
                    }
                    ;
                    return false;
                }

                function checkHeaderOptionsSortingLocked(table, i) {
                    if ((table.config.headers[i]) && (table.config.headers[i].lockedOrder)) return table.config.headers[i].lockedOrder;
                    return false;
                }

                function applyWidget(table) {
                    var c = table.config.widgets;
                    var l = c.length;
                    for (var i = 0; i < l; i++) {

                        getWidgetById(c[i]).format(table);
                    }

                }

                function getWidgetById(name) {
                    var l = widgets.length;
                    for (var i = 0; i < l; i++) {
                        if (widgets[i].id.toLowerCase() == name.toLowerCase()) {
                            return widgets[i];
                        }
                    }
                };

                function formatSortingOrder(v) {
                    if (typeof(v) != "Number") {
                        return (v.toLowerCase() == "desc") ? 1 : 0;
                    } else {
                        return (v == 1) ? 1 : 0;
                    }
                }

                function isValueInArray(v, a) {
                    var l = a.length;
                    for (var i = 0; i < l; i++) {
                        if (a[i][0] == v) {
                            return true;
                        }
                    }
                    return false;
                }

                function setHeadersCss(table, $headers, list, css) {
                    // remove all header information
                    $headers.removeClass(css[0]).removeClass(css[1]);

                    var h = [];
                    $headers.each(function (offset) {
                        if (!this.sortDisabled) {
                            h[this.column] = $(this);
                        }
                    });

                    var l = list.length;
                    for (var i = 0; i < l; i++) {
                        h[list[i][0]].addClass(css[list[i][1]]);
                    }
                }

                function fixColumnWidth(table, $headers) {
                    var c = table.config;
                    if (c.widthFixed) {
                        var colgroup = $('<colgroup>');
                        $("tr:first td", table.tBodies[0]).each(function () {
                            colgroup.append($('<col>').css('width', $(this).width()));
                        });
                        $(table).prepend(colgroup);
                    }
                    ;
                }

                function updateHeaderSortCount(table, sortList) {
                    var c = table.config,
                        l = sortList.length;
                    for (var i = 0; i < l; i++) {
                        var s = sortList[i],
                            o = c.headerList[s[0]];
                        o.count = s[1];
                        o.count++;
                    }
                }

                /* sorting methods */

                function multisort(table, sortList, cache) {

                    if (table.config.debug) {
                        var sortTime = new Date();
                    }

                    var dynamicExp = "var sortWrapper = function(a,b) {",
                        l = sortList.length;

                    // TODO: inline functions.
                    for (var i = 0; i < l; i++) {

                        var c = sortList[i][0];
                        var order = sortList[i][1];
                        // var s = (getCachedSortType(table.config.parsers,c) == "text") ?
                        // ((order == 0) ? "sortText" : "sortTextDesc") : ((order == 0) ?
                        // "sortNumeric" : "sortNumericDesc");
                        // var s = (table.config.parsers[c].type == "text") ? ((order == 0)
                        // ? makeSortText(c) : makeSortTextDesc(c)) : ((order == 0) ?
                        // makeSortNumeric(c) : makeSortNumericDesc(c));
                        var s = (table.config.parsers[c].type == "text") ? ((order == 0) ? makeSortFunction("text", "asc", c) : makeSortFunction("text", "desc", c)) : ((order == 0) ? makeSortFunction("numeric", "asc", c) : makeSortFunction("numeric", "desc", c));
                        var e = "e" + i;

                        dynamicExp += "var " + e + " = " + s; // + "(a[" + c + "],b[" + c
                        // + "]); ";
                        dynamicExp += "if(" + e + ") { return " + e + "; } ";
                        dynamicExp += "else { ";

                    }

                    // if value is the same keep orignal order
                    var orgOrderCol = cache.normalized[0].length - 1;
                    dynamicExp += "return a[" + orgOrderCol + "]-b[" + orgOrderCol + "];";

                    for (var i = 0; i < l; i++) {
                        dynamicExp += "}; ";
                    }

                    dynamicExp += "return 0; ";
                    dynamicExp += "}; ";

                    if (table.config.debug) {
                        benchmark("Evaling expression:" + dynamicExp, new Date());
                    }

                    eval(dynamicExp);

                    cache.normalized.sort(sortWrapper);

                    if (table.config.debug) {
                        benchmark("Sorting on " + sortList.toString() + " and dir " + order + " time:", sortTime);
                    }

                    return cache;
                };

                function makeSortFunction(type, direction, index) {
                    var a = "a[" + index + "]",
                        b = "b[" + index + "]";
                    if (type == 'text' && direction == 'asc') {
                        return "(" + a + " == " + b + " ? 0 : (" + a + " === null ? Number.POSITIVE_INFINITY : (" + b + " === null ? Number.NEGATIVE_INFINITY : (" + a + " < " + b + ") ? -1 : 1 )));";
                    } else if (type == 'text' && direction == 'desc') {
                        return "(" + a + " == " + b + " ? 0 : (" + a + " === null ? Number.POSITIVE_INFINITY : (" + b + " === null ? Number.NEGATIVE_INFINITY : (" + b + " < " + a + ") ? -1 : 1 )));";
                    } else if (type == 'numeric' && direction == 'asc') {
                        return "(" + a + " === null && " + b + " === null) ? 0 :(" + a + " === null ? Number.POSITIVE_INFINITY : (" + b + " === null ? Number.NEGATIVE_INFINITY : " + a + " - " + b + "));";
                    } else if (type == 'numeric' && direction == 'desc') {
                        return "(" + a + " === null && " + b + " === null) ? 0 :(" + a + " === null ? Number.POSITIVE_INFINITY : (" + b + " === null ? Number.NEGATIVE_INFINITY : " + b + " - " + a + "));";
                    }
                };

                function makeSortText(i) {
                    return "((a[" + i + "] < b[" + i + "]) ? -1 : ((a[" + i + "] > b[" + i + "]) ? 1 : 0));";
                };

                function makeSortTextDesc(i) {
                    return "((b[" + i + "] < a[" + i + "]) ? -1 : ((b[" + i + "] > a[" + i + "]) ? 1 : 0));";
                };

                function makeSortNumeric(i) {
                    return "a[" + i + "]-b[" + i + "];";
                };

                function makeSortNumericDesc(i) {
                    return "b[" + i + "]-a[" + i + "];";
                };

                function sortText(a, b) {
                    if (table.config.sortLocaleCompare) return a.localeCompare(b);
                    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
                };

                function sortTextDesc(a, b) {
                    if (table.config.sortLocaleCompare) return b.localeCompare(a);
                    return ((b < a) ? -1 : ((b > a) ? 1 : 0));
                };

                function sortNumeric(a, b) {
                    return a - b;
                };

                function sortNumericDesc(a, b) {
                    return b - a;
                };

                function getCachedSortType(parsers, i) {
                    return parsers[i].type;
                };
                /* public methods */
                this.construct = function (settings) {
                    return this.each(function () {
                        // if no thead or tbody quit.
                        if (!this.tHead || !this.tBodies) return;
                        // declare
                        var $this, $document, $headers, cache, config, shiftDown = 0,
                            sortOrder;
                        // new blank config object
                        this.config = {};
                        // merge and extend.
                        config = $.extend(this.config, $.tablesorter.defaults, settings);
                        // store common expression for speed
                        $this = $(this);
                        // save the settings where they read
                        $.data(this, "tablesorter", config);
                        // build headers
                        $headers = buildHeaders(this);
                        // try to auto detect column type, and store in tables config
                        this.config.parsers = buildParserCache(this, $headers);
                        // build the cache for the tbody cells
                        cache = buildCache(this);
                        // get the css class names, could be done else where.
                        var sortCSS = [config.cssDesc, config.cssAsc];
                        // fixate columns if the users supplies the fixedWidth option
                        fixColumnWidth(this);
                        // apply event handling to headers
                        // this is to big, perhaps break it out?
                        $headers.click(
                            function (e) {
                                var totalRows = ($this[0].tBodies[0] && $this[0].tBodies[0].rows.length) || 0;
                                if (!this.sortDisabled && totalRows > 0) {
                                    // Only call sortStart if sorting is
                                    // enabled.
                                    $this.trigger("sortStart");
                                    // store exp, for speed
                                    var $cell = $(this);
                                    // get current column index
                                    var i = this.column;
                                    // get current column sort order
                                    this.order = this.count++ % 2;
                                    // always sort on the locked order.
                                    if (this.lockedOrder) this.order = this.lockedOrder;

                                    // user only whants to sort on one
                                    // column
                                    if (!e[config.sortMultiSortKey]) {
                                        // flush the sort list
                                        config.sortList = [];
                                        if (config.sortForce != null) {
                                            var a = config.sortForce;
                                            for (var j = 0; j < a.length; j++) {
                                                if (a[j][0] != i) {
                                                    config.sortList.push(a[j]);
                                                }
                                            }
                                        }
                                        // add column to sort list
                                        config.sortList.push([i, this.order]);
                                        // multi column sorting
                                    } else {
                                        // the user has clicked on an all
                                        // ready sortet column.
                                        if (isValueInArray(i, config.sortList)) {
                                            // revers the sorting direction
                                            // for all tables.
                                            for (var j = 0; j < config.sortList.length; j++) {
                                                var s = config.sortList[j],
                                                    o = config.headerList[s[0]];
                                                if (s[0] == i) {
                                                    o.count = s[1];
                                                    o.count++;
                                                    s[1] = o.count % 2;
                                                }
                                            }
                                        } else {
                                            // add column to sort list array
                                            config.sortList.push([i, this.order]);
                                        }
                                    }
                                    ;
                                    setTimeout(function () {
                                        // set css for headers
                                        setHeadersCss($this[0], $headers, config.sortList, sortCSS);
                                        appendToTable(
                                            $this[0], multisort(
                                                $this[0], config.sortList, cache)
                                        );
                                    }, 1);
                                    // stop normal event by returning false
                                    return false;
                                }
                                // cancel selection
                            }).mousedown(function () {
                            if (config.cancelSelection) {
                                this.onselectstart = function () {
                                    return false
                                };
                                return false;
                            }
                        });
                        // apply easy methods that trigger binded events
                        $this.bind("update", function () {
                            var me = this;
                            setTimeout(function () {
                                // rebuild parsers.
                                me.config.parsers = buildParserCache(
                                    me, $headers);
                                // rebuild the cache map
                                cache = buildCache(me);
                            }, 1);
                        }).bind("updateCell", function (e, cell) {
                            var config = this.config;
                            // get position from the dom.
                            var pos = [(cell.parentNode.rowIndex - 1), cell.cellIndex];
                            // update cache
                            cache.normalized[pos[0]][pos[1]] = config.parsers[pos[1]].format(
                                getElementText(config, cell), cell);
                        }).bind("sorton", function (e, list) {
                            $(this).trigger("sortStart");
                            config.sortList = list;
                            // update and store the sortlist
                            var sortList = config.sortList;
                            // update header count index
                            updateHeaderSortCount(this, sortList);
                            // set css for headers
                            setHeadersCss(this, $headers, sortList, sortCSS);
                            // sort the table and append it to the dom
                            appendToTable(this, multisort(this, sortList, cache));
                        }).bind("appendCache", function () {
                            appendToTable(this, cache);
                        }).bind("applyWidgetId", function (e, id) {
                            getWidgetById(id).format(this);
                        }).bind("applyWidgets", function () {
                            // apply widgets
                            applyWidget(this);
                        });
                        if ($.metadata && ($(this).metadata() && $(this).metadata().sortlist)) {
                            config.sortList = $(this).metadata().sortlist;
                        }
                        // if user has supplied a sort list to constructor.
                        if (config.sortList.length > 0) {
                            $this.trigger("sorton", [config.sortList]);
                        }
                        // apply widgets
                        applyWidget(this);
                    });
                };
                this.addParser = function (parser) {
                    var l = parsers.length,
                        a = true;
                    for (var i = 0; i < l; i++) {
                        if (parsers[i].id.toLowerCase() == parser.id.toLowerCase()) {
                            a = false;
                        }
                    }
                    if (a) {
                        parsers.push(parser);
                    }
                    ;
                };
                this.addWidget = function (widget) {
                    widgets.push(widget);
                };
                this.formatFloat = function (s) {
                    var i = parseFloat(s);
                    return (isNaN(i)) ? 0 : i;
                };
                this.formatInt = function (s) {
                    var i = parseInt(s);
                    return (isNaN(i)) ? 0 : i;
                };
                this.isDigit = function (s, config) {
                    // replace all an wanted chars and match.
                    return /^[-+]?\d*$/.test($.trim(s.replace(/[,.']/g, '')));
                };
                this.clearTableBody = function (table) {
                    if ($.browser.msie) {
                        function empty() {
                            while (this.firstChild)
                                this.removeChild(this.firstChild);
                        }

                        empty.apply(table.tBodies[0]);
                    } else {
                        table.tBodies[0].innerHTML = "";
                    }
                };
            }
    });

    // extend plugin scope
    $.fn.extend({
        tablesorter: $.tablesorter.construct
    });

    // make shortcut
    var ts = $.tablesorter;

    // add default parsers
    ts.addParser({
        id: "text",
        is: function (s) {
            return true;
        }, format: function (s) {
            return $.trim(s.toLocaleLowerCase());
        }, type: "text"
    });

    ts.addParser({
        id: "digit",
        is: function (s, table) {
            var c = table.config;
            return $.tablesorter.isDigit(s, c);
        }, format: function (s) {
            return $.tablesorter.formatFloat(s);
        }, type: "numeric"
    });

    ts.addParser({
        id: "currency",
        is: function (s) {
            return /^[£$€?.]/.test(s);
        }, format: function (s) {
            return $.tablesorter.formatFloat(s.replace(new RegExp(/[£$€]/g), ""));
        }, type: "numeric"
    });

    ts.addParser({
        id: "ipAddress",
        is: function (s) {
            return /^\d{2,3}[\.]\d{2,3}[\.]\d{2,3}[\.]\d{2,3}$/.test(s);
        }, format: function (s) {
            var a = s.split("."),
                r = "",
                l = a.length;
            for (var i = 0; i < l; i++) {
                var item = a[i];
                if (item.length == 2) {
                    r += "0" + item;
                } else {
                    r += item;
                }
            }
            return $.tablesorter.formatFloat(r);
        }, type: "numeric"
    });

    ts.addParser({
        id: "url",
        is: function (s) {
            return /^(https?|ftp|file):\/\/$/.test(s);
        }, format: function (s) {
            return jQuery.trim(s.replace(new RegExp(/(https?|ftp|file):\/\//), ''));
        }, type: "text"
    });

    ts.addParser({
        id: "isoDate",
        is: function (s) {
            return /^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(s);
        }, format: function (s) {
            return $.tablesorter.formatFloat((s != "") ? new Date(s.replace(
                new RegExp(/-/g), "/")).getTime() : "0");
        }, type: "numeric"
    });

    ts.addParser({
        id: "percent",
        is: function (s) {
            return /\%$/.test($.trim(s));
        }, format: function (s) {
            return $.tablesorter.formatFloat(s.replace(new RegExp(/%/g), ""));
        }, type: "numeric"
    });

    ts.addParser({
        id: "usLongDate",
        is: function (s) {
            return s.match(new RegExp(/^[A-Za-z]{3,10}\.? [0-9]{1,2}, ([0-9]{4}|'?[0-9]{2}) (([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(AM|PM)))$/));
        }, format: function (s) {
            return $.tablesorter.formatFloat(new Date(s).getTime());
        }, type: "numeric"
    });

    ts.addParser({
        id: "shortDate",
        is: function (s) {
            return /\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/.test(s);
        }, format: function (s, table) {
            var c = table.config;
            s = s.replace(/\-/g, "/");
            if (c.dateFormat == "us") {
                // reformat the string in ISO format
                s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$1/$2");
            } else if (c.dateFormat == "uk") {
                // reformat the string in ISO format
                s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/, "$3/$2/$1");
            } else if (c.dateFormat == "dd/mm/yy" || c.dateFormat == "dd-mm-yy") {
                s = s.replace(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2})/, "$1/$2/$3");
            }
            return $.tablesorter.formatFloat(new Date(s).getTime());
        }, type: "numeric"
    });
    ts.addParser({
        id: "time",
        is: function (s) {
            return /^(([0-2]?[0-9]:[0-5][0-9])|([0-1]?[0-9]:[0-5][0-9]\s(am|pm)))$/.test(s);
        }, format: function (s) {
            return $.tablesorter.formatFloat(new Date("2000/01/01 " + s).getTime());
        }, type: "numeric"
    });
    ts.addParser({
        id: "metadata",
        is: function (s) {
            return false;
        }, format: function (s, table, cell) {
            var c = table.config,
                p = (!c.parserMetadataName) ? 'sortValue' : c.parserMetadataName;
            return $(cell).metadata()[p];
        }, type: "numeric"
    });
    // add default widgets
    ts.addWidget({
        id: "zebra",
        format: function (table) {
            if (table.config.debug) {
                var time = new Date();
            }
            var $tr, row = -1,
                odd;
            // loop through the visible rows
            $("tr:visible", table.tBodies[0]).each(function (i) {
                $tr = $(this);
                // style children rows the same way the parent
                // row was styled
                if (!$tr.hasClass(table.config.cssChildRow)) row++;
                odd = (row % 2 == 0);
                $tr.removeClass(
                    table.config.widgetZebra.css[odd ? 0 : 1]).addClass(
                    table.config.widgetZebra.css[odd ? 1 : 0])
            });
            if (table.config.debug) {
                $.tablesorter.benchmark("Applying Zebra widget", time);
            }
        }
    });
})(jQuery);

(function ($) {

    $.fn.problemlist = function (method) {
        var template = "\
            <fieldset>\
                <label class=\"input-prepend\"><span class=\"add-on\">Problem</span><input type=\"text\" class=\"input-mini plable\" name=\"lable\" value=\"\" /></label>\
                OJ: <select class=\"vpname input-small\">" + $.fn.problemlist.ojoptions + "</select>\
                <input class=\"vpid input-medium\" type=\"text\" name=\"vpid\" value=\"\" placeholder=\"Problem ID\" />\
                <input class=\"vpid\" type=\"hidden\" name=\"pid\" value=\"\" />\
                <br /><span></span>\
                <div class=\"selptype hide\">\
                    <label class=\"radio inline\"><input type=\"radio\" class=\"ptype\" name=\"ptype\" value=\"1\" checked=\"checked\" /> CF</label>\
                    <label class=\"radio inline\"><input type=\"radio\" class=\"ptype\" name=\"ptype\" value=\"2\" /> TC</label>\
                    <label class=\"radio inline\"><input type=\"radio\" class=\"ptype\" name=\"ptype\" value=\"3\" /> CF Dynamic</label>\
                </div>\
                <div class=\"well selpara hide\">\
                    <div class=\"cf tc\"><label class=\"input\">Base Value (MP) : <input type=\"text\" class=\"pbase input-small\" value=\"\" name=\"base\" /></label></div>\
                    <div class=\"cf tc\"><label class=\"input\">Min Value: <input type=\"text\" class=\"minp input-small\" value=\"\" name=\"minp\" /></label></div>\
                    <div class=\"cf tc\"><label class=\"input\">Parameter A: <input type=\"text\" class=\"para_a input-small\" value=\"\" name=\"para_a\" /></label></div>\
                    <div class=\"cf tc\"><label class=\"input\">Parameter B: <input type=\"text\" class=\"para_b input-small\" value=\"\" name=\"para_b\" /></label></div>\
                    <div class=\"tc\" style=\"display:none\"><label class=\"input\">Parameter C: <input class=\"para_c input-small\" type=\"text\" name=\"para_c\" /></label></div>\
                    <div class=\"tc\" style=\"display:none\"><label class=\"input\">Parameter D: <input class=\"para_d input-small\" type=\"text\" name=\"para_d\" /></label></div>\
                    <div class=\"tc\" style=\"display:none\"><label class=\"input\">Parameter E: <input class=\"para_e input-small\" type=\"text\" name=\"para_e\" /></label></div>\
                </div>\
            </fieldset>\
        ";

        function deal(id, oj, $target) {
            $.get("ajax/get_problem_basic.php?vid=" + id + "&vname=" + oj + "&randomid=" + Math.random(), function (data) {
                var p = eval('(' + data + ')');
                if (p.code != 0) {
                    if (id == $target.prev().val()) {
                        $target.val("");
                        $target.next().next().html("Error!");
                    }
                }
                else {
                    var p = eval('(' + data + ')');
                    if (id == $target.prev().val()) {
                        $target.val(p.pid);
                        $target.next().next().html("<a href='problem_show.php?pid=" + p.pid + "' target='_blank'>" + p.title + "</a>");
                    }
                }
            });
        }

        function fill($target, options) {
            $target.find("input[type!=radio]").each(function () {
                var name = $(this).attr("name");
                if (options[name]) $(this).val(options[name]);
            });
            $target.find("input[type=radio]").each(function () {
                var name = $(this).attr("name");
                if (options[name] && $(this).val() == options[name]) $(this).attr("checked", "checked");
            });
        }

        function reset(target) {
            target.pl_next_id = 0;
            target.pl_type = 0;
            $(target).children("fieldset").remove();
        }

        var methods = {
            spawn: function (options) {
                return this.each(function () {
                    var next_id = this.pl_next_id;
                    $(this).append(template);
                    $target = $(this).children("fieldset:last");

                    if (options && options.type == 2) {
                        options = $.extend({}, $.fn.problemlist.tcdefaults, options);
                    } else {
                        options = $.extend({}, $.fn.problemlist.cfdefaults, options);
                    }
                    if (!options.type) options.type = this.pl_type;
                    if (!options.lable && next_id < 26) options.lable = String.fromCharCode(65 + next_id);
                    fill($target, options);
                    if (options.type > 0) {
                        $target.find(".selptype,.selpara").show();
                        if (options.type == 2) $target.find(".tc").show();
                    }
                    $target.find("input").each(function () {
                        var name = $(this).attr("name");
                        if (name) $(this).attr("name", "prob[" + next_id + "][" + name + "]");
                    });
                    this.pl_next_id++;
                });
            },
            load: function (url) {
                return this.each(function () {
                    var root = this;
                    reset(this);
                    $.get(url + "&random=" + Math.random(), function (data) {
                        var p = eval('(' + data + ')');
                        if (p.prob) {
                            $.each(p.prob, function (ind, prob) {
                                prob.vpid = prob.pid;
                                prob.ptype = prob.type;
                                $(root).problemlist('spawn', prob);
                                $(root).find("span:last").html("<a href='problem_show.php?pid=" + prob.pid + "' target='_blank'>" + prob.title + "</a>");
                            });
                        }
                        $(root).problemlist('spawn');
                    });
                });
            },
            loadcontest: function (cid) {
                return $(this).problemlist("load", "ajax/get_contest_problems.php?cid=" + cid);
            },
            loadsource: function (src) {
                return $(this).problemlist("load", "ajax/get_src_problems.php?src=" + src);
            },
            settype: function (type) {
                return this.each(function () {
                    this.pl_type = type;
                    if (this.pl_type == 1) {
                        $(this).find(".selptype,.selpara,.cf").show();
                    } else {
                        $(this).find(".selptype,.selpara").hide();
                    }
                });
            },
            reset: function () {
                return this.each(function () {
                    reset(this);
                });
            },
            init: function () {
                return this.each(function () {
                    var root = this;
                    reset(this);
                    $(this).delegate(".vpid", "keyup", function () {
                        var vid = $(this).val();
                        var vname = $(this).prev().val();
                        var $target = $(this).next();
                        deal(vid, vname, $target);
                    });
                    $(this).delegate(".vpname", "change", function () {
                        var vid = $(this).next().val();
                        var vname = $(this).val();
                        var $target = $(this).next().next();
                        deal(vid, vname, $target);
                    });
                    $(this).delegate(".ptype", "change", function () {
                        var ptp = $(this).val();
                        if (ptp == '0') {
                            $(this).nextAll("div").hide();
                        } else if (ptp == '1' || ptp == '3') {
                            var aa = $(this).parents(".selptype").nextAll(".selpara").children(".cf");
                            $(this).parents(".selptype").nextAll(".selpara").children().hide();
                            aa.find(".pbase").val($.fn.problemlist.cfdefaults.base);
                            aa.find(".minp").val($.fn.problemlist.cfdefaults.minp);
                            aa.find(".para_a").val($.fn.problemlist.cfdefaults.para_a);
                            aa.find(".para_b").val($.fn.problemlist.cfdefaults.para_b);
                            aa.show();
                            $(this).parent().nextAll(".selpara").show();
                        } else if (ptp == '2') {
                            var aa = $(this).parents(".selptype").nextAll(".selpara").children(".tc");
                            $(this).parents(".selptype").nextAll(".selpara").children().hide();
                            aa.find(".pbase").val($.fn.problemlist.tcdefaults.base);
                            aa.find(".minp").val($.fn.problemlist.tcdefaults.minp);
                            aa.find(".para_a").val($.fn.problemlist.tcdefaults.para_a);
                            aa.find(".para_b").val($.fn.problemlist.tcdefaults.para_b);
                            aa.find(".para_c").val($.fn.problemlist.tcdefaults.para_c);
                            aa.find(".para_d").val($.fn.problemlist.tcdefaults.para_d);
                            aa.find(".para_e").val($.fn.problemlist.tcdefaults.para_e);
                            aa.show();
                            $(this).parent().nextAll(".selpara").show();
                        }
                    });
                    $(this).delegate(".vpid", "change", function () {
                        if ($(this).parent().is(":last-child")) $(root).problemlist('spawn');
                    });
                    $(this).problemlist('spawn');
                });
            }
        };

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            return methods.init.apply(this, arguments);
        }
    }
    $.fn.problemlist.cfdefaults = {
        base: 500,
        minp: 150,
        para_a: 2,
        para_b: 50
    };
    $.fn.problemlist.tcdefaults = {
        base: 500,
        minp: 150,
        para_a: 0.3,
        para_b: 0.7,
        para_c: 4500,
        para_d: 10,
        para_e: 10
    };
    $.fn.problemlist.ojoptions = {};
})(jQuery);

(function ($) {
    $(document).ready(function () {
        $(".contest-time-pick").each(function () {
            var $start_picker = $(this).find("input[name=start_time]");
            var $end_picker = $(this).find("input[name=end_time]");
            var $lock_picker = $(this).find("input[name=lock_board_time]");
            var $duration_picker = $(this).find("input[name=duration]");
            var lockbefore = moment.duration(moment($end_picker.val()).unix() - moment($lock_picker.val()).unix(), 's');

            function setDuration(duration) {
                var hh = Math.floor(duration.asHours());
                var mm = duration.minutes();
                var ss = duration.seconds();
                $duration_picker.val(hh + ":" + (mm < 10 ? "0" : "") + mm + ":" + (ss < 10 ? "0" : "") + ss);
            }

            function parseDuration() {
                var duration = moment.duration($duration_picker.val());
                setDuration(duration);
                return duration;
            }

            function updateTimeSelect() {
                var duration = parseDuration()

                var end_time = moment($start_picker.val()).add(duration);
                if (end_time.isValid()) $end_picker.val(end_time.format("YYYY-MM-DD HH:mm:ss"));

                var lock_time = end_time.subtract(lockbefore);
                if (lock_time.isValid()) $lock_picker.val(lock_time.format("YYYY-MM-DD HH:mm:ss"));
            }

            $(this).children(".date").datetimepicker({
                format: 'yyyy-mm-dd hh:ii:ss'
            });

            $duration_picker.change(function () {
                var $text = $(this).parent().next();
                var duration = parseDuration();
                duration.asMinutes() < 30 || duration.asDays() > 15 ? $text.addClass("text-warning") : $text.removeClass("text-warning");
                updateTimeSelect();
            });

            $start_picker.change(function () {
                var $text = $(this).parent().next();
                moment($(this).val()).unix() - moment().unix() < 10 * 60 ? $text.addClass("text-warning") : $text.removeClass("text-warning");
                updateTimeSelect();
            });

            $end_picker.change(function () {
                var $text = $(this).parent().next();
                var duration = moment.duration(moment($(this).val()).unix() - moment($start_picker.val()).unix(), 's');
                if (duration.asSeconds() > 0) {
                    $text.removeClass("text-warning");
                    setDuration(duration);
                } else {
                    $text.addClass("text-warning");
                }
                updateTimeSelect();
            });

            $lock_picker.change(function () {
                lockbefore = moment.duration(moment($end_picker.val()).unix() - moment($lock_picker.val()).unix(), 's');
            });
        });
    });
})(jQuery);
