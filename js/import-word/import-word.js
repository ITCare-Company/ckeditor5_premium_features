! function(t) {
  const e = t.en = t.en || {};
  e.dictionary = Object.assign(e.dictionary || {}, {
    "Import from Word": "Import from Word"
  })
}(window.CKEDITOR_TRANSLATIONS || (window.CKEDITOR_TRANSLATIONS = {})),
  /*!
   * @license Copyright (c) 2003-2022, CKSource Holding sp. z o.o. All rights reserved.
   * For licensing, see LICENSE.md.
   */
  (() => {
    var t = {
        847: (t, e, r) => {
          "use strict";
          r.d(e, {
            Z: () => i
          });
          var o = r(758),
            n = r.n(o)()((function(t) {
              return t[1]
            }));
          n.push([t.id, ":root{--ck-toolbar-spinner-size:18px}.ck-importword_status-pending{position:relative}.ck.ck-importword__spinner-container{display:none}.ck.ck-importword__spinner{border:2px solid var(--ck-color-text);border-radius:50%;border-top:2px solid transparent;height:var(--ck-toolbar-spinner-size);left:0;margin:0 auto;position:absolute;right:0;top:50%;transform:translateY(-50%);width:var(--ck-toolbar-spinner-size);z-index:1}.ck-importword_status-pending .ck.ck-importword__spinner-container{animation:rotate 1.5s linear infinite;display:block;height:var(--ck-toolbar-spinner-size);position:absolute;width:var(--ck-toolbar-spinner-size)}.ck-importword_status-pending .ck.ck-button__icon{color:transparent}@keyframes rotate{to{transform:rotate(1turn)}}", ""]);
          const i = n
        },
        758: t => {
          "use strict";
          t.exports = function(t) {
            var e = [];
            return e.toString = function() {
              return this.map((function(e) {
                var r = t(e);
                return e[2] ? "@media ".concat(e[2], " {").concat(r, "}") : r
              })).join("")
            }, e.i = function(t, r, o) {
              "string" == typeof t && (t = [
                [null, t, ""]
              ]);
              var n = {};
              if (o)
                for (var i = 0; i < this.length; i++) {
                  var s = this[i][0];
                  null != s && (n[s] = !0)
                }
              for (var a = 0; a < t.length; a++) {
                var c = [].concat(t[a]);
                o && n[c[0]] || (r && (c[2] ? c[2] = "".concat(r, " and ").concat(c[2]) : c[2] = r), e.push(c))
              }
            }, e
          }
        },
        278: (t, e, r) => {
          "use strict";
          var o, n = function() {
              return void 0 === o && (o = Boolean(window && document && document.all && !window.atob)), o
            },
            i = function() {
              var t = {};
              return function(e) {
                if (void 0 === t[e]) {
                  var r = document.querySelector(e);
                  if (window.HTMLIFrameElement && r instanceof window.HTMLIFrameElement) try {
                    r = r.contentDocument.head
                  } catch (t) {
                    r = null
                  }
                  t[e] = r
                }
                return t[e]
              }
            }(),
            s = [];

          function a(t) {
            for (var e = -1, r = 0; r < s.length; r++)
              if (s[r].identifier === t) {
                e = r;
                break
              } return e
          }

          function c(t, e) {
            for (var r = {}, o = [], n = 0; n < t.length; n++) {
              var i = t[n],
                c = e.base ? i[0] + e.base : i[0],
                l = r[c] || 0,
                d = "".concat(c, " ").concat(l);
              r[c] = l + 1;
              var u = a(d),
                p = {
                  css: i[1],
                  media: i[2],
                  sourceMap: i[3]
                }; - 1 !== u ? (s[u].references++, s[u].updater(p)) : s.push({
                identifier: d,
                updater: g(p, e),
                references: 1
              }), o.push(d)
            }
            return o
          }

          function l(t) {
            var e = document.createElement("style"),
              o = t.attributes || {};
            if (void 0 === o.nonce) {
              var n = r.nc;
              n && (o.nonce = n)
            }
            if (Object.keys(o).forEach((function(t) {
              e.setAttribute(t, o[t])
            })), "function" == typeof t.insert) t.insert(e);
            else {
              var s = i(t.insert || "head");
              if (!s) throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");
              s.appendChild(e)
            }
            return e
          }
          var d, u = (d = [], function(t, e) {
            return d[t] = e, d.filter(Boolean).join("\n")
          });

          function p(t, e, r, o) {
            var n = r ? "" : o.media ? "@media ".concat(o.media, " {").concat(o.css, "}") : o.css;
            if (t.styleSheet) t.styleSheet.cssText = u(e, n);
            else {
              var i = document.createTextNode(n),
                s = t.childNodes;
              s[e] && t.removeChild(s[e]), s.length ? t.insertBefore(i, s[e]) : t.appendChild(i)
            }
          }

          function h(t, e, r) {
            var o = r.css,
              n = r.media,
              i = r.sourceMap;
            if (n ? t.setAttribute("media", n) : t.removeAttribute("media"), i && "undefined" != typeof btoa && (o += "\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(i)))), " */")), t.styleSheet) t.styleSheet.cssText = o;
            else {
              for (; t.firstChild;) t.removeChild(t.firstChild);
              t.appendChild(document.createTextNode(o))
            }
          }
          var f = null,
            m = 0;

          function g(t, e) {
            var r, o, n;
            if (e.singleton) {
              var i = m++;
              r = f || (f = l(e)), o = p.bind(null, r, i, !1), n = p.bind(null, r, i, !0)
            } else r = l(e), o = h.bind(null, r, e), n = function() {
              ! function(t) {
                if (null === t.parentNode) return !1;
                t.parentNode.removeChild(t)
              }(r)
            };
            return o(t),
              function(e) {
                if (e) {
                  if (e.css === t.css && e.media === t.media && e.sourceMap === t.sourceMap) return;
                  o(t = e)
                } else n()
              }
          }
          t.exports = function(t, e) {
            (e = e || {}).singleton || "boolean" == typeof e.singleton || (e.singleton = n());
            var r = c(t = t || [], e);
            return function(t) {
              if (t = t || [], "[object Array]" === Object.prototype.toString.call(t)) {
                for (var o = 0; o < r.length; o++) {
                  var n = a(r[o]);
                  s[n].references--
                }
                for (var i = c(t, e), l = 0; l < r.length; l++) {
                  var d = a(r[l]);
                  0 === s[d].references && (s[d].updater(), s.splice(d, 1))
                }
                r = i
              }
            }
          }
        },
        945: (t, e, r) => {
          t.exports = r(79)("./src/clipboard.js")
        },
        704: (t, e, r) => {
          t.exports = r(79)("./src/core.js")
        },
        492: (t, e, r) => {
          t.exports = r(79)("./src/engine.js")
        },
        273: (t, e, r) => {
          t.exports = r(79)("./src/ui.js")
        },
        448: (t, e, r) => {
          t.exports = r(79)("./src/upload.js")
        },
        209: (t, e, r) => {
          t.exports = r(79)("./src/utils.js")
        },
        79: t => {
          "use strict";
          t.exports = CKEditor5.dll
        }
      },
      e = {};

    function r(o) {
      var n = e[o];
      if (void 0 !== n) return n.exports;
      var i = e[o] = {
        id: o,
        exports: {}
      };
      return t[o](i, i.exports, r), i.exports
    }
    r.n = t => {
      var e = t && t.__esModule ? () => t.default : () => t;
      return r.d(e, {
        a: e
      }), e
    }, r.d = (t, e) => {
      for (var o in e) r.o(e, o) && !r.o(t, o) && Object.defineProperty(t, o, {
        enumerable: !0,
        get: e[o]
      })
    }, r.o = (t, e) => Object.prototype.hasOwnProperty.call(t, e), r.r = t => {
      "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, {
        value: "Module"
      }), Object.defineProperty(t, "__esModule", {
        value: !0
      })
    }, r.nc = void 0;
    var o = {};
    (() => {
      "use strict";
      r.r(o), r.d(o, {
        ImportWord: () => _,
        ImportWordEditing: () => b,
        ImportWordUI: () => l
      });
      var t = r(704),
        e = r(273),
        n = r(448);
      var i = r(278),
        s = r.n(i),
        a = r(847),
        c = {
          injectType: "singletonStyleTag",
          attributes: {
            "data-cke": !0
          },
          insert: "head",
          singleton: !0
        };
      s()(a.Z, c);
      a.Z.locals;
      class l extends t.Plugin {
        static get pluginName() {
          return "ImportWordUI"
        }
        init() {
          const t = this.editor,
            r = t.t;
          t.ui.componentFactory.add("importWord", (o => {
            const i = t.commands.get("importWord"),
              s = new n.FileDialogButtonView(o);
            s.set({
              acceptedType: "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            }), s.buttonView.set({
              label: r("Import from Word"),
              icon: '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#a)"><path clip-rule="evenodd" d="m14 0 5 4.5v8.892l-1.5-1.8V6.5h-5v-5h-8v3H3V0h11Zm0 2v3h3.3L14 2Z"/><path d="M10.74 18a2.76 2.76 0 0 1-.469-.5H3V19h9.25v-.374A2.737 2.737 0 0 1 10.74 18Z"/><path clip-rule="evenodd" d="M8.5 6a1.5 1.5 0 0 1 1.493 1.356L10 7.5v7a1.5 1.5 0 0 1-1.356 1.493L8.5 16h-7a1.5 1.5 0 0 1-1.493-1.356L0 14.5v-7a1.5 1.5 0 0 1 1.356-1.493L1.5 6h7ZM7.483 8.795l-.042.088-.986 2.534-.947-2.528-.043-.092a.601.601 0 0 0-1.042.008l-.042.093-.883 2.465-.937-2.475-.042-.089a.6.6 0 0 0-1.107.42l.027.093 1.514 4 .044.092a.6.6 0 0 0 1.041-.01l.041-.092.88-2.458.925 2.467.046.096a.602.602 0 0 0 1.032 0l.043-.09 1.554-4 .028-.093a.6.6 0 0 0-1.104-.43v.001ZM11.674 15.407a.75.75 0 1 0 1.152.96L14 14.96V20h1.5v-5.04l1.174 1.408a.75.75 0 1 0 1.152-.96l-2.346-2.816a.95.95 0 0 0-1.46 0l-2.346 2.815Z"/></g><defs><clipPath id="a"><path d="M0 0h20v20H0z"/></clipPath></defs></svg>',
              tooltip: !0
            }), s.buttonView.bind("isOn", "isEnabled").to(i, "isBusy", "isEnabled"), s.buttonView.extendTemplate({
              attributes: {
                class: [s.buttonView.bindTemplate.if("isOn", "ck-importword_status-pending")]
              }
            });
            const a = new e.View;
            return a.setTemplate({
              tag: "span",
              attributes: {
                class: ["ck", "ck-importword__spinner-container"]
              },
              children: [{
                tag: "span",
                attributes: {
                  class: ["ck", "ck-importword__spinner"]
                }
              }]
            }), s.buttonView.children.add(a), s.on("done", ((e, r) => {
              t.execute("importWord", r[0], {}), t.editing.view.focus()
            })), s
          }))
        }
      }
      var d = r(945),
        u = r(209),
        p = r(492);
      class h extends t.Command {
        constructor(t) {
          super(t), this.set("isBusy", !1), this._importMarker = null, this._undoStepBatch = null, this._abortController = null, this.on("dataInsert", ((e, r) => {
            const o = new DataTransfer;
            o.setData("text/html", r.html);
            const n = this._importMarker.getRange();
            this.editor.model.enqueueChange(this._undoStepBatch, (e => {
              e.setSelection(n), t.editing.view.document.fire("clipboardInput", {
                dataTransfer: o
              })
            }))
          }), {
            priority: "low"
          })
        }
        refresh() {
          this.isEnabled = this.i()
        }
        execute(t, e = {}) {
          if (this.isBusy) return;
          const r = this.editor.config.get("importWord"),
            o = {
              url: r.converterUrl,
              file: t,
              serviceConfig: {
                ...e,
                default_styles: r.defaultStyles
              }
            };
          return this.s(), this.o(o).then(this.h.bind(this)).catch(this.l.bind(this)).finally(this.p.bind(this))
        }
        i() {
          if (this.isBusy) return !1;
          const t = this.editor.model,
            e = t.schema,
            r = t.document.selection,
            o = (0, u.first)(r.getSelectedBlocks());
          return !!o && e.checkChild(o.parent, "paragraph")
        }
        s() {
          const t = this.editor,
            e = t.model,
            r = e.document.selection;
          this._abortController = new AbortController, this._undoStepBatch = t.model.createBatch(), e.enqueueChange(this._undoStepBatch, (t => {
            r.isCollapsed || e.deleteContent(r, {
              leaveUnmerged: !0
            });
            const o = r.getFirstPosition(),
              n = (0, u.first)(r.getSelectedBlocks());
            let i;
            i = n.is("element", "paragraph") && n.isEmpty ? o : o.isAtStart ? e.createPositionBefore(n) : o.isAtEnd ? e.createPositionAfter(n) : t.split(o).position, this._importMarker = t.addMarker("importWord:" + (0, u.uid)(), {
              usingOperation: !1,
              affectsData: !1,
              range: new p.Range(i)
            }), this._importMarker.on("change:range", (() => {
              "$graveyard" === this._importMarker.getRange().root.rootName && this._abortController.abort()
            }))
          })), this.isBusy = !0, this.refresh()
        }
        p() {
          const t = this.editor.model;
          t.markers.has(this._importMarker) && t.enqueueChange(this._undoStepBatch, (t => {
            t.removeMarker(this._importMarker)
          })), this._importMarker = null, this._undoStepBatch = null, this._abortController = null, this.isBusy = !1, this.refresh()
        }
        o({
            url: t,
            file: e,
            serviceConfig: r
          }) {
          const o = this.editor.plugins.get("ImportWordEditing").getToken(),
            n = new FormData;
          n.set("config", JSON.stringify(r)), n.set("file", e);
          const i = {
            method: "POST",
            headers: {
              Authorization: o.value
            },
            body: n,
            signal: this._abortController.signal
          };
          return fetch(t, i)
        }
        h(t) {
          return t.ok ? t.json().then((t => {
            this.editor.model.markers.has(this._importMarker) && "$graveyard" !== this._importMarker.getRange().root.rootName && this.fire("dataInsert", t)
          })) : Promise.reject()
        }
        l() {
          if (this._abortController.signal.aborted) return void(0, u.logWarning)("import-word-plugin-import-cancelled");
          const t = this.editor,
            e = (0, t.t)("An error occurred while importing the Word file.");
          t.plugins.get("Notification").showWarning(e), (0, u.logError)("import-word-plugin-conversion-failed")
        }
      }
      const f = ["config", "requires", "cloudServices.tokenUrl", "ImportWordEditing", "plugins", "init", "add", "token", "get", "CloudServices", "importWord", "editor", "createToken", "getToken", "pluginName", "define", "importWord.tokenUrl"];
      var m, g;
      m = f, g = 387,
        function(t) {
          for (; --t;) m.push(m.shift())
        }(++g);
      const v = function(t, e) {
        return f[t -= 0]
      };
      class b extends t.Plugin {
        static get[v("0x1")]() {
          return v("0x7")
        }
        static get[v("0x5")]() {
          return [e.Notification, d.ClipboardPipeline, v("0xd")]
        }
        constructor(t) {
          super(t), this.m = null
        }
        async [v("0x9")]() {
          const t = this[v("0xf")];
          if (t[v("0x4")][v("0x2")](v("0xe"), {
            converterUrl: "https://docx-converter.cke-cs.com/v2/convert/docx-html",
            tokenUrl: t[v("0x4")][v("0xc")]("cloudServices.tokenUrl"),
            defaultStyles: !1
          }), !t[v("0x4")][v("0xc")](v("0x3"))) throw new u.CKEditorError("import-word-plugin-missing-token-url", null);
          const e = t[v("0x4")].get(v("0x3")),
            r = t.config.get(v("0x6"));
          this.m = e === r ? t[v("0x8")][v("0xc")](v("0xd"))[v("0xb")] : await t[v("0x8")].get("CloudServicesCore")[v("0x10")](e)[v("0x9")](), t.commands[v("0xa")](v("0xe"), new h(t))
        } [v("0x0")]() {
          return this.m
        }
      }
      var x, w, k = ["requires", "pluginName", "ImportWord"];
      x = k, w = 207,
        function(t) {
          for (; --t;) x.push(x.shift())
        }(++w);
      var y = function(t, e) {
        return k[t -= 0]
      };
      class _ extends t.Plugin {
        static get[y("0x1")]() {
          return y("0x2")
        }
        static get[y("0x0")]() {
          return [b, l]
        }
      }
    })(), (window.CKEditor5 = window.CKEditor5 || {}).importWord = o
  })();
