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
  },

  mounted() {},

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
      this.getReceiveRecords(this.keyword);
    },
  },

  methods: {
    setPages() {
      console.log("setPages");
      this.pages = [];
      let numberOfPages = Math.ceil(this.receive_records.length / this.perPage);

      if (numberOfPages == 1) this.page = 1;
      for (let index = 1; index <= numberOfPages; index++) {
        this.pages.push(index);
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

      form_Data.append("op", "adddata");
      form_Data.append("strClass", this.item);
      form_Data.append("strItem", this.clasify);
      form_Data.append("txtUseDate", this.usedate);
      form_Data.append("nAmount", this.amount);
      form_Data.append("strDesc", this.desc);

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
            this.getMonthSpend();
          },
          (err) => {
            console.log(err);
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
          _this.month_spend = response.data || 0;
        })
        .catch(function (error) {
          console.log(error);
        });
    },

    getTotalRecords: function (keyword) {
      let token = localStorage.getItem("accessToken");

      let _this = this;

      axios
        .get("gettotal.php", { headers: { Authorization: `Bearer ${token}` } })
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
      // Ajax request to write changes back to db
      if (!this.isDate(this.UseDate) && !this.UseDate == "") {
        alert("必須是日期 (date required)");
        return false;
      }

      if (!this.isNumeric(this.Amount) && !this.Amount == "") {
        alert("必須是數字 (numeric required)");
        return false;
      }

      if (this.Item == "" || this.Class == "") {
        alert("必須輸入");
        return false;
      }

      let formData = new FormData();

      const UseDate = this.formatDate(this.UseDate);
      const Item = this.Item.trim();
      const Class = this.Class.trim();
      const Amount = this.Amount;
      const Descri = this.Descri.trim();

      formData.append("op", "mdf");
      formData.append("txtUseDate", this.formatDate(this.UseDate));
      formData.append("strItem", Item);
      formData.append("strClass", Class);
      formData.append("nAmount", Amount);
      formData.append("strDesc", Descri);

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
          //handle success

          _this.getMonthSpend();

          item["UseDate"] = UseDate;
          item["Item"] = Item;
          item["Class"] = Class;
          item["Amount"] = Amount;
          item["Descri"] = Descri;
        })
        .catch(function (err) {
          //handle error
          console.log(err);
        });

      item["is_edited"] = 1;
      this.is_modifying = false;
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
