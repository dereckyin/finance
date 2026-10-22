var app = new Vue({
  el: "#app",
  data: {
    receive_records: [],
    receive_records_length: 0,

    class: "",

    uri: "",
    clasify: "",
    item: "",
    usedate: "",
    amount: "",
    desc: "",
    receipt_lines: "",
    receipt_hint: "",
    receipt_reading: false,
    receipt_row_sid: null,
    receipt_paste_sid: null,
    receipt_paste_item: null,
    classes: [],
    items: [],

    month_spend: 0,

    UseDate: "",
    Class: "",
    Item: "",
    Amount: 0,
    Descri: "",
    is_modifying: false,
    keyword: "",

    submit: false,
    // paging
    page: 1,
    //perPage: 10,
    pages: [],

    inventory: [
      { name: "20", id: 20 },
      { name: "50", id: 50 },
      { name: "100", id: 100 },
      { name: "All", id: 10000 },
    ],
    perPage: 20,
  },

  created() {
    this.getClasify();
    this.getTotalRecords();
    this.getReceiveRecords();
    this.getMonthSpend();
  },

  computed: {
    displayedPosts() {
      return this.receive_records;
    },
    noteLength() {
      return [...String(this.desc || "")].length;
    },
  },

  mounted() {
    this._onWindowPaste = this.onWindowPaste.bind(this);
    this._onWindowKeydown = this.onWindowKeydown.bind(this);
    window.addEventListener("paste", this._onWindowPaste);
    window.addEventListener("keydown", this._onWindowKeydown);
  },

  beforeDestroy() {
    window.removeEventListener("paste", this._onWindowPaste);
    window.removeEventListener("keydown", this._onWindowKeydown);
  },

  watch: {
    clasify(value) {
      this.getItems(value);
    },

    page: function (value) {
      this.getReceiveRecords();
    },

    perPage: function (value) {
      this.getReceiveRecords();
      this.setPages();
    },

    keyword() {
      this.searchLedger();
    },
  },

  methods: {
    searchLedger: function () {
      this.getTotalRecords();
      if (this.page !== 1) {
        this.page = 1;
        return;
      }
      this.getReceiveRecords();
    },

    formatMoney: function (value) {
      var number = Number(value) || 0;
      return number.toLocaleString("zh-TW", { maximumFractionDigits: 0 });
    },

    setPages() {
      var total = Number(this.receive_records_length) || 0;
      var perPage = Number(this.perPage) || 20;
      var numberOfPages = Math.max(1, Math.ceil(total / perPage));
      var pages = [];
      for (var index = 1; index <= numberOfPages; index++) {
        pages.push(index);
      }
      this.pages = pages;
      if (this.page > numberOfPages) {
        this.page = numberOfPages;
      }
    },

    paginate: function (posts) {
      console.log("paginate");
      if (this.page < 1) this.page = 1;
      if (this.page > this.pages.length) this.page = this.pages.length;

      let page = this.page;
      let perPage = this.perPage;
      let from = page * perPage - perPage;
      let to = page * perPage;
      return this.receive_records.slice(from, to);
    },

    add() {
      var form_Data = new FormData();

      this.usedate = document.querySelector("input[id=txtUseDate]").value;
      if (String(this.receipt_lines || "").trim() !== "") {
        this.applyReceiptLines();
      }
      var entry = this.validateEntry({
        clasify: this.clasify,
        item: this.item,
        date: this.usedate,
        amount: this.amount,
        note: this.desc,
      });
      if (!entry) {
        return;
      }
      this.amount = entry.amount;
      this.desc = entry.note;

      form_Data.append("op", "adddata");
      form_Data.append("strClass", entry.item);
      form_Data.append("strItem", entry.clasify);
      form_Data.append("txtUseDate", entry.date);
      form_Data.append("nAmount", entry.amount);
      form_Data.append("strDesc", entry.note);

      let token = localStorage.getItem("accessToken");

      axios({
        method: "post",
        headers: {
          "Content-Type": "multipart/form-data",
          Authorization: `Bearer ${token}`,
        },
        url: "save_data.php",
        data: form_Data,
      })
        .then(
          (res) => {
            this.resetForm();
            this.getReceiveRecords();
            this.getTotalRecords();
            this.getMonthSpend();
          },
          (err) => {
            var msg = err.response && err.response.data && err.response.data.msg;
            alert(msg || "儲存失敗");
          }
        )
        .finally(() => {});
    },

    editRow: function (item) {
      if (this.is_modifying) return;
      else this.is_modifying = true;

      item["is_edited"] = 0;

      this.UseDate = item["UseDate"];
      this.Item = item["Item"];
      this.Class = item["Class"];
      this.Amount = item["Amount"];
      this.Descri = item["Descri"];
    },

    getMonthSpend: function () {
      var d = new Date();
      var y = d.getFullYear();
      var m = d.getMonth() + 1;
      const params = {
        year: y,
        month: m,
      };

      let token = localStorage.getItem("accessToken");

      let _this = this;

      axios
        .get("getmonthspend.php", {
          params,
          headers: { Authorization: `Bearer ${token}` },
        })
        .then(function (response) {
          var data = response.data;
          _this.month_spend = data && typeof data === "object" ? Number(data.spend) || 0 : Number(data) || 0;
        })
        .catch(function (error) {
          console.log(error);
        });
    },

    getTotalRecords: function (keyword) {
      let token = localStorage.getItem("accessToken");

      let _this = this;

      axios
        .get("gettotal.php", {
          params: { keyword: this.keyword },
          headers: { Authorization: `Bearer ${token}` },
        })
        .then(function (response) {
          _this.receive_records_length = parseInt(response.data[0]);

          _this.setPages();
        })
        .catch(function (error) {
          console.log(error);
        });
    },

    getReceiveRecords: function (keyword) {
      const params = {
        page: this.page,
        pagesize: this.perPage,
        keyword: this.keyword,
      };

      let token = localStorage.getItem("accessToken");

      let _this = this;

      axios
        .get("getresult.php", {
          params,
          headers: { Authorization: `Bearer ${token}` },
        })
        .then(function (response) {
          _this.receive_records = response.data;
        })
        .catch(function (error) {
          console.log(error);
        });
    },

    getClasify() {
      let _this = this;

      let token = localStorage.getItem("accessToken");

      axios
        .get("getclasses.php", {
          headers: { Authorization: `Bearer ${token}` },
        })
        .then(
          (res) => {
            _this.classes = res.data;
          },
          (err) => {
            alert(err.response.data.msg);
          }
        )
        .finally(() => {});
    },

    getItems: function (keyword) {
      let _this = this;

      const params = {
        item: keyword,
      };

      let token = localStorage.getItem("accessToken");

      axios
        .get("getitems.php", {
          params,
          headers: { Authorization: `Bearer ${token}` },
        })
        .then(
          (res) => {
            _this.items = res.data;
          },
          (err) => {
            alert(err.response);
          }
        )
        .finally(() => {});
    },

    resetForm() {
      this.clasify = "";
      this.items = "";
      this.usedate = "";
      this.desc = "";
      this.amount = "";
      this.receipt_lines = "";
      this.receipt_hint = "";
    },

    clipboardImageFromEvent: function (event) {
      var clipboard = event && event.clipboardData;
      if (!clipboard || !clipboard.items) {
        return null;
      }
      for (var i = 0; i < clipboard.items.length; i++) {
        var entry = clipboard.items[i];
        if (entry && entry.kind === "file" && entry.type && entry.type.indexOf("image/") === 0) {
          var file = entry.getAsFile();
          if (file) {
            return file;
          }
        }
      }
      return null;
    },

    onWindowKeydown: function (event) {
      if (event.key === "Escape" && this.receipt_paste_item) {
        this.clearReceiptPasteArm();
      }
    },

    clearReceiptPasteArm: function () {
      this.receipt_paste_sid = null;
      this.receipt_paste_item = null;
    },

    armReceiptPaste: function (item) {
      if (!item) {
        return;
      }
      if (this.is_modifying) {
        alert("請先完成或取消正在修改的資料");
        return;
      }
      if (this.receipt_row_sid !== null || this.receipt_reading) {
        return;
      }
      if (this.receipt_paste_sid === item.SId) {
        this.clearReceiptPasteArm();
        return;
      }
      this.receipt_paste_sid = item.SId;
      this.receipt_paste_item = item;
    },

    onWindowPaste: function (event) {
      if (!this.receipt_paste_item) {
        return;
      }
      var file = this.clipboardImageFromEvent(event);
      if (!file) {
        return;
      }
      event.preventDefault();
      var item = this.receipt_paste_item;
      this.clearReceiptPasteArm();
      this.processRowReceipt(file, item);
    },

    onReceiptPaste: function (event) {
      var file = this.clipboardImageFromEvent(event);
      if (file) {
        event.preventDefault();
        if (this.receipt_paste_item) {
          var item = this.receipt_paste_item;
          this.clearReceiptPasteArm();
          this.processRowReceipt(file, item);
          return;
        }
        this.processFormReceipt(file);
        return;
      }
      var _this = this;
      setTimeout(function () {
        _this.applyReceiptLines();
      }, 0);
    },

    normalizeReceiptText: function (text) {
      return String(text || "")
        .replace(/\r\n/g, "\n")
        .replace(/\r/g, "\n")
        .replace(/\u00a0/g, " ")
        .replace(/　/g, " ")
        .replace(/[０-９]/g, function (ch) {
          return String.fromCharCode(ch.charCodeAt(0) - 0xfee0);
        })
        .replace(/[＋]/g, "+")
        .replace(/[－]/g, "-");
    },

    looksLikeReceiptText: function (text) {
      var source = String(text || "");
      if (source.trim() === "") {
        return false;
      }
      if (/品名|數量|金額|消費明細|消費日期|發票金額|全聯福利/.test(source)) {
        return true;
      }
      if (/\*[0-9]+/.test(source)) {
        return true;
      }
      if ((source.match(/\t/g) || []).length >= 2) {
        return true;
      }
      if ((source.match(/\n/g) || []).length >= 1 && /[0-9]/.test(source)) {
        return true;
      }
      return false;
    },

    padDateParts: function (year, month, day) {
      var y = String(year);
      var m = String(month);
      var d = String(day);
      if (m.length < 2) {
        m = "0" + m;
      }
      if (d.length < 2) {
        d = "0" + d;
      }
      return y + "/" + m + "/" + d;
    },

    parseInvoiceMeta: function (text) {
      var source = this.normalizeReceiptText(text);
      var meta = {
        date: "",
        invoiceAmount: null,
        detail: "",
        category: "",
      };
      var dateMatch = source.match(/消費日期[：:]\s*(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/);
      if (dateMatch) {
        meta.date = this.padDateParts(dateMatch[1], dateMatch[2], dateMatch[3]);
      }
      var amountMatch = source.match(/發票金額[：:]\s*(-?[0-9]+(?:\.[0-9]+)?)/);
      if (amountMatch) {
        meta.invoiceAmount = Number(amountMatch[1]);
      }
      if (/全聯/.test(source)) {
        meta.detail = "全聯";
        meta.category = "shopping";
      } else if (/家樂福/.test(source)) {
        meta.detail = "家樂福";
        meta.category = "shopping";
      } else if (/costco|好市多/i.test(source)) {
        meta.detail = "costco";
        meta.category = "shopping";
      }
      return meta;
    },

    setFormDate: function (dateText) {
      if (!dateText || !this.isDate(dateText)) {
        return;
      }
      this.usedate = dateText;
      var field = document.getElementById("txtUseDate");
      if (field) {
        field.value = dateText;
      }
    },

    parseReceiptLines: function (text) {
      var lines = this.normalizeReceiptText(text).split("\n");
      var items = [];
      var skipped = [];
      var headerRe = /^(名稱|品名|數量|金額|消費明細|明細)(\s|$|\t)/;
      var metaRe = /^(交易|機台|序號|條碼|日期|總計|合計|小計|消費全額|店名|會員|營業所|客服|月份|是否捐贈)/;
      var junkRe = /機台[：:]|全支付|發票金額|卡餘額|餘點|會員卡號|營業所|客服電話|客服信箱|統編|是否捐贈|福利卡會員|電子發票|零元發票|中獎請持/;
      var amountTokenRe = /^-?[0-9]+(?:\.[0-9]+)?$/;
      var qtyTokenRe = /^\*?[0-9]+(?:\.[0-9]+)?$/;
      var stuckAmountRe = /^(.*?)(-?[0-9]+(?:\.[0-9]+)?)$/;
      for (var i = 0; i < lines.length; i++) {
        var raw = lines[i].trim();
        if (raw === "") {
          continue;
        }
        if (headerRe.test(raw) || metaRe.test(raw) || junkRe.test(raw)) {
          continue;
        }
        if (/^[^\d]*[：:]/.test(raw) && !/^\S+\s+\*?[0-9]/.test(raw)) {
          continue;
        }
        var tokens = raw.split(/[\t ]+/).filter(Boolean);
        var name = "";
        var amountText = "";
        if (tokens.length >= 2 && amountTokenRe.test(tokens[tokens.length - 1])) {
          amountText = tokens[tokens.length - 1];
          var nameTokens = tokens.slice(0, -1);
          if (nameTokens.length >= 2 && qtyTokenRe.test(nameTokens[nameTokens.length - 1])) {
            nameTokens = nameTokens.slice(0, -1);
          }
          name = nameTokens.join(" ");
        } else if (tokens.length === 1) {
          var stuck = raw.match(stuckAmountRe);
          if (stuck && stuck[1].trim() !== "" && /[^\d.\-]/.test(stuck[1])) {
            name = stuck[1].trim();
            amountText = stuck[2];
          }
        }
        name = name.replace(/[\t ]+/g, " ").trim();
        if (name === "" || amountText === "") {
          skipped.push(raw);
          continue;
        }
        var amount = this.evaluateAmount(amountText);
        if (amount === "" || !this.isNumeric(amount)) {
          skipped.push(raw);
          continue;
        }
        var number = Number(amount);
        if (number === 0) {
          continue;
        }
        items.push({ name: name, amount: number });
      }
      return { items: items, skipped: skipped };
    },

    noteFromItems: function (items) {
      var noteParts = [];
      var total = 0;
      for (var i = 0; i < items.length; i++) {
        var item = items[i];
        var amountText = String(Math.round(item.amount * 100) / 100);
        if (amountText.indexOf(".") >= 0) {
          amountText = amountText.replace(/(\.\d*?)0+$/, "$1").replace(/\.$/, "");
        }
        noteParts.push(item.name + amountText);
        total += item.amount;
      }
      return {
        note: noteParts.join(" "),
        total: Math.round(total * 100) / 100,
      };
    },

    organizeNote: function (text) {
      var source = String(text == null ? "" : text);
      if (!this.looksLikeReceiptText(source)) {
        return source.trim();
      }
      var parsed = this.parseReceiptLines(source);
      if (!parsed.items.length) {
        return source.trim();
      }
      return this.noteFromItems(parsed.items).note;
    },

    onDescriPaste: function (event) {
      var clipboard = event && event.clipboardData;
      var text = clipboard ? clipboard.getData("text") : "";
      if (!text || !this.looksLikeReceiptText(text)) {
        return;
      }
      event.preventDefault();
      this.Descri = this.organizeNote(text);
    },

    onDescriBlur: function () {
      this.Descri = this.organizeNote(this.Descri);
    },

    onDescPaste: function (event) {
      var clipboard = event && event.clipboardData;
      var text = clipboard ? clipboard.getData("text") : "";
      if (!text || !this.looksLikeReceiptText(text)) {
        return;
      }
      event.preventDefault();
      this.desc = this.organizeNote(text);
    },

    onDescBlur: function () {
      this.desc = this.organizeNote(this.desc);
    },

    applyReceiptLines: function () {
      var text = String(this.receipt_lines || "").trim();
      if (text === "") {
        this.receipt_hint = "";
        return;
      }
      var meta = this.parseInvoiceMeta(text);
      var parsed = this.parseReceiptLines(text);
      var built = parsed.items.length ? this.noteFromItems(parsed.items) : { note: "", total: 0 };
      var total = built.total;
      if ((!parsed.items.length || total === 0) && meta.invoiceAmount != null && !isNaN(meta.invoiceAmount)) {
        total = meta.invoiceAmount;
      }
      if (!parsed.items.length && (meta.invoiceAmount == null || isNaN(meta.invoiceAmount))) {
        this.receipt_hint = "找不到金額，請確認每一行最後是數字";
        return;
      }
      if (meta.category) {
        this.clasify = meta.category;
      }
      if (meta.detail) {
        this.item = meta.detail;
      }
      if (meta.date) {
        this.setFormDate(meta.date);
      }
      if (parsed.items.length) {
        this.receipt_lines = parsed.items
          .map(function (row) {
            var amountText = String(Math.round(row.amount * 100) / 100);
            if (amountText.indexOf(".") >= 0) {
              amountText = amountText.replace(/(\.\d*?)0+$/, "$1").replace(/\.$/, "");
            }
            return row.name + "  " + amountText;
          })
          .join("\n");
        this.desc = built.note;
      }
      this.amount = String(total);
      var filled = [];
      if (meta.category) {
        filled.push("分類");
      }
      if (meta.detail) {
        filled.push("明細");
      }
      if (meta.date) {
        filled.push("日期");
      }
      filled.push("金額");
      var hint = "已帶入 " + (parsed.items.length || 1) + " 項，合計 " + this.formatMoney(total);
      if (filled.length) {
        hint += "；已填 " + filled.join("、");
      }
      if (parsed.skipped.length) {
        hint += "；略過 " + parsed.skipped.length + " 行";
      }
      if (built.note && [...built.note].length > 200) {
        hint += "；備註超過 200 字，請刪減後再新增";
      }
      this.receipt_hint = hint;
    },

    uploadReceiptFile: function (file) {
      var formData = new FormData();
      formData.append("image", file);
      var token = localStorage.getItem("accessToken");
      return axios({
        method: "post",
        url: "read_receipt.php",
        data: formData,
        headers: {
          Authorization: "Bearer " + token,
        },
      }).then(function (response) {
        var data = response.data || {};
        var lines = data.lines || [];
        if (!lines.length) {
          var err = new Error(data.msg || "沒有辨識到細項");
          err.response = { data: { msg: data.msg || "沒有辨識到細項" } };
          throw err;
        }
        return lines.map(function (row) {
          return {
            name: String(row.name || "").trim(),
            amount: Number(row.amount) || 0,
          };
        }).filter(function (row) {
          return row.name !== "" && row.amount > 0;
        });
      });
    },

    validateReceiptImage: function (file) {
      if (!file) {
        return "請貼上截圖";
      }
      if (!/^image\//.test(file.type || "")) {
        return "請貼上圖片";
      }
      if (file.size > 8 * 1024 * 1024) {
        return "圖片請小於 8MB";
      }
      return "";
    },

    processFormReceipt: function (file) {
      var invalid = this.validateReceiptImage(file);
      if (invalid) {
        alert(invalid);
        return;
      }
      var _this = this;
      this.receipt_reading = true;
      this.receipt_hint = "辨識中…";
      this.uploadReceiptFile(file)
        .then(function (items) {
          var text = items
            .map(function (row) {
              return row.name + "  " + row.amount;
            })
            .join("\n");
          _this.receipt_lines = text;
          _this.applyReceiptLines();
        })
        .catch(function (err) {
          var msg = err.response && err.response.data && err.response.data.msg;
          _this.receipt_hint = msg || err.message || "辨識失敗";
          alert(msg || err.message || "辨識失敗");
        })
        .finally(function () {
          _this.receipt_reading = false;
        });
    },

    processRowReceipt: function (file, item) {
      if (!item) {
        return;
      }
      var invalid = this.validateReceiptImage(file);
      if (invalid) {
        alert(invalid);
        return;
      }
      if (this.is_modifying) {
        alert("請先完成或取消正在修改的資料");
        return;
      }
      var _this = this;
      this.receipt_row_sid = item.SId;
      this.uploadReceiptFile(file)
        .then(function (items) {
          if (!items.length) {
            alert("沒有辨識到細項");
            return;
          }
          var built = _this.noteFromItems(items);
          if ([...built.note].length > 200) {
            alert("辨識後的備註超過 200 字，請改用上方細項區刪減後再存");
            _this.receipt_lines = items
              .map(function (row) {
                return row.name + "  " + row.amount;
              })
              .join("\n");
            _this.applyReceiptLines();
            return;
          }
          var formData = new FormData();
          formData.append("op", "mdf");
          formData.append("txtUseDate", item.UseDate);
          formData.append("strItem", item.Item);
          formData.append("strClass", item.Class);
          formData.append("nAmount", item.Amount);
          formData.append("strDesc", built.note);
          formData.append("SId", item.SId);
          var token = localStorage.getItem("accessToken");
          return axios({
            method: "post",
            headers: {
              "Content-Type": "multipart/form-data",
              Authorization: "Bearer " + token,
            },
            url: "save_data.php",
            data: formData,
          }).then(function () {
            item.Descri = built.note;
          });
        })
        .catch(function (err) {
          var msg = err.response && err.response.data && err.response.data.msg;
          alert(msg || err.message || "更新備註失敗");
        })
        .finally(function () {
          _this.receipt_row_sid = null;
        });
    },

    shallowCopy(obj) {
      console.log("shallowCopy");
      var result = {};
      for (var i in obj) {
        result[i] = obj[i];
      }
      return result;
    },

    editRow: function (item) {
      if (this.is_modifying) 
        return;
      else 
        this.is_modifying = true;

      item["is_edited"] = 0;

      this.UseDate = item["UseDate"];
      this.Item = item["Item"];
      this.Class = item["Class"];
      this.Amount = item["Amount"];
      this.Descri = item["Descri"];
    },

    cancelRow: function (item) {
      this.UseDate = "";
      this.Item = "";
      this.Class = "";
      this.Amount = 0;

      item["is_edited"] = 1;
      this.is_modifying = false;
    },

    confirmRow: function (item) {
      this.Descri = this.organizeNote(this.Descri);
      var entry = this.validateEntry({
        clasify: this.Item,
        item: this.Class,
        date: this.UseDate,
        amount: this.Amount,
        note: this.Descri,
      });
      if (!entry) {
        return false;
      }
      this.Item = entry.clasify;
      this.Class = entry.item;
      this.Amount = entry.amount;
      this.Descri = entry.note;

      let formData = new FormData();

      formData.append("op", "mdf");
      formData.append("txtUseDate", entry.date);
      formData.append("strItem", entry.clasify);
      formData.append("strClass", entry.item);
      formData.append("nAmount", entry.amount);
      formData.append("strDesc", entry.note);

      formData.append("SId", item["SId"]);

      let token = localStorage.getItem("accessToken");

      const _this = this;

      axios({
        method: "post",
        headers: {
          "Content-Type": "multipart/form-data",
          Authorization: `Bearer ${token}`,
        },
        url: "save_data.php",
        data: formData,
      })
        .then(function (response) {
          _this.getMonthSpend();
          item["UseDate"] = entry.date;
          item["Item"] = entry.clasify;
          item["Class"] = entry.item;
          item["Amount"] = entry.amount;
          item["Descri"] = entry.note;
          item["is_edited"] = 1;
          _this.is_modifying = false;
        })
        .catch(function (err) {
          var msg = err.response && err.response.data && err.response.data.msg;
          alert(msg || "儲存失敗");
        });
    },

    validateEntry: function (fields) {
      var clasify = String(fields.clasify == null ? "" : fields.clasify).trim();
      var item = String(fields.item == null ? "" : fields.item).trim();
      var date = String(fields.date == null ? "" : fields.date).trim().replace(/-/g, "/");
      var amount = this.evaluateAmount(fields.amount);
      var note = String(fields.note == null ? "" : fields.note).trim();
      if (clasify === "" || item === "") {
        alert("分類和明細都要填");
        return null;
      }
      if ([...clasify].length > 200 || [...item].length > 200) {
        alert("分類或明細超過 200 字");
        return null;
      }
      if ([...note].length > 200) {
        alert("備註超過 200 字");
        return null;
      }
      if (!this.isDate(date)) {
        alert("請輸入正確日期，例如 2026/10/22");
        return null;
      }
      if (amount === "" || !this.isNumeric(amount) || Math.abs(Number(amount)) > 99999999.99) {
        alert(amount === "" || !this.isNumeric(amount) ? "金額請輸入數字或算式" : "金額超出可記錄的範圍");
        return null;
      }
      return {
        clasify: clasify,
        item: item,
        date: date,
        amount: amount,
        note: note,
      };
    },

    isDate: function (txtDate, separator) {
      var aoDate, // needed for creating array and object
        ms, // date in milliseconds
        month,
        day,
        year; // (integer) month, day and year
      // if separator is not defined then set '/'
      if (separator === undefined) {
        separator = "/";
      }
      // split input date to month, day and year
      aoDate = txtDate.split(separator);
      // array length should be exactly 3 (no more no less)
      if (aoDate.length !== 3) {
        return false;
      }
      // define month, day and year from array (expected format is m/d/yyyy)
      // subtraction will cast variables to integer implicitly
      month = aoDate[1] - 1; // because months in JS start from 0
      day = aoDate[2] - 0;
      year = aoDate[0] - 0;
      // test year range
      if (year < 1000 || year > 3000) {
        return false;
      }
      // convert input date to milliseconds
      ms = new Date(year, month, day).getTime();
      // initialize Date() object from milliseconds (reuse aoDate variable)
      aoDate = new Date();
      aoDate.setTime(ms);
      // compare input date and parts from Date() object
      // if difference exists then input date is not valid
      if (
        aoDate.getFullYear() !== year ||
        aoDate.getMonth() !== month ||
        aoDate.getDate() !== day
      ) {
        return false;
      }
      // date is OK, return true
      return true;
    },

    isNumeric: function (n) {
      return !isNaN(parseFloat(n)) && isFinite(n);
    },

    evaluateAmount: function (raw) {
      var source = String(raw == null ? "" : raw);
      var s = source
        .replace(/，/g, "")
        .replace(/,/g, "")
        .replace(/＋/g, "+")
        .replace(/－/g, "-")
        .replace(/×/g, "*")
        .replace(/÷/g, "/")
        .replace(/\s+/g, "");
      if (s === "" || !/[+\-*/]/.test(s) || !/^[\d+\-*/().]+$/.test(s)) {
        return source.trim();
      }
      var i = 0;
      function peek() {
        return s.charAt(i);
      }
      function parseExpr() {
        var v = parseTerm();
        while (peek() === "+" || peek() === "-") {
          var op = peek();
          i++;
          var r = parseTerm();
          v = op === "+" ? v + r : v - r;
        }
        return v;
      }
      function parseTerm() {
        var v = parseFactor();
        while (peek() === "*" || peek() === "/") {
          var op = peek();
          i++;
          var r = parseFactor();
          if (op === "/" && r === 0) {
            throw new Error("div");
          }
          v = op === "*" ? v * r : v / r;
        }
        return v;
      }
      function parseFactor() {
        if (peek() === "+") {
          i++;
          return parseFactor();
        }
        if (peek() === "-") {
          i++;
          return -parseFactor();
        }
        if (peek() === "(") {
          i++;
          var v = parseExpr();
          if (peek() !== ")") {
            throw new Error("paren");
          }
          i++;
          return v;
        }
        var start = i;
        while (i < s.length && /[\d.]/.test(s.charAt(i))) {
          i++;
        }
        if (start === i) {
          throw new Error("num");
        }
        var n = Number(s.slice(start, i));
        if (!isFinite(n)) {
          throw new Error("num");
        }
        return n;
      }
      try {
        var value = parseExpr();
        if (i !== s.length || !isFinite(value)) {
          return source.trim();
        }
        var text = (Math.round(value * 100) / 100).toFixed(2);
        return text.replace(/\.00$/, "").replace(/(\.\d)0$/, "$1");
      } catch (e) {
        return source.trim();
      }
    },

    formatDate: function (date) {
      if (date !== "") {
        var d = new Date(date),
          month = "" + (d.getMonth() + 1),
          day = "" + d.getDate(),
          year = d.getFullYear();

        if (month.length < 2) month = "0" + month;
        if (day.length < 2) day = "0" + day;

        return [year, month, day].join("/");
      } else {
        return "";
      }
    },

    deleteRow: function(item){
      // Ajax request to write changes back to db
      if(!confirm("是否真的要刪除這筆資料？"))
          return;

      
      let formData = new FormData();
      
      formData.append('op', 'del');
      formData.append('SId', item['SId']);

      let token = localStorage.getItem('accessToken');

      let _this = this;

      axios({
              method: 'post',
              headers: {
                  'Content-Type': 'multipart/form-data',
                  Authorization: `Bearer ${token}`
              },
              url: 'save_data.php',
              data: formData
          })
          .then(function(response) {
              //handle success
              _this.getReceiveRecords();
              _this.getTotalRecords();
              _this.getMonthSpend();
        
          })
          .catch(function(err) {
              //handle error
              console.log(err)
          });

      this.is_modifying = false;
    },
  },
});
