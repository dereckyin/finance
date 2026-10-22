<?php
require_once __DIR__ . '/security.php';
require_jwt_user();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>家庭速記簿</title>
  <!-- Bootstrap -->
  <link href="css/bootstrap.css" rel="stylesheet">

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->

  <style>
    .select-editable {
      position: relative;
      background-color: white;
      border: solid grey 1px;
      width: 120px;
      height: 18px;
    }

    .select-editable select {
      position: absolute;
      top: 0px;
      left: 0px;
      font-size: 14px;
      border: none;
      width: 120px;
      margin: 0;
    }

    .select-editable input {
      position: absolute;
      top: 0px;
      left: 0px;
      width: 100px;
      padding: 1px;
      font-size: 12px;
      border: none;
    }

    .select-editable select:focus,
    .select-editable input:focus {
      outline: none;
    }
  </style>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="class/jQuery-Validation-Engine/js/languages/jquery.validationEngine-zh_TW.js" type="text/javascript" charset="utf-8"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.20.0-0/axios.min.js" type="text/javascript" charset="utf-8"></script>
  <script src="class/jQuery-Validation-Engine/js/jquery.validationEngine.js" type="text/javascript" charset="utf-8">
  </script>
  <link rel="stylesheet" href="class/jQuery-Validation-Engine/css/validationEngine.jquery.css" type="text/css" />

  <script type="text/javascript">
    function validate() {

    }

    function formSuccess() {
      alert('Success!');
    }

    function formFailure() {
      alert('Failure!');
    }
    jQuery(document).ready(function() {
      // binds form submission and fields to the validation engine
      jQuery("#Main").validationEngine({
        onFormSuccess: formSuccess,
        onFormFailure: formFailure
      });
    });

    function onSelect(val, idx) {

      var sel = document.getElementById('txtClass');

      var obj2 = document.getElementById("strItem");


      obj2.value = sel.options[idx].parentNode.label;

    }

    function onClassSelect(val) {

      $("#txtClass > optgroup").each(function() {
        if ($(this)[0].label == val) {
          $(this).show();
        } else {
          $(this).hide();
        }

      });

    }

    function onDelete(id) {
      if (confirm("是否真的要刪除這筆資料？")) {
        document.Main.SId.value = id;
        document.Main.op.value = "del";
        document.Main.action = "add_data.php";
        document.Main.submit();
      }
    }

    function onEdit(id) {
      alert('Success!');
      document.Main.SId.value = id;
      document.Main.op.value = "onedit";
      document.Main.action = "add_data.php";
      document.Main.submit();

    }

    function onUpdate(id) {
      alert('Success!');
      document.Main.SId.value = id;
      document.Main.op.value = "onupdate";
      document.Main.action = "add_data.php";


      jQuery('#Main').submit();

    }

    function today() {
      var today = new Date();
      var dd = today.getDate();
      var mm = today.getMonth() + 1; //January is 0!
      var yyyy = today.getFullYear();

      if (dd < 10) {
        dd = '0' + dd
      }

      if (mm < 10) {
        mm = '0' + mm
      }

      today = yyyy + '/' + mm + '/' + dd;
      document.getElementById("txtUseDate").value = today;
      //document.Main.action = "adddata.asp";
      //document.Main.submit();
    }

    function dday() {
      var mydate = new Date();
      mydate.setDate(mydate.getDate() - 1);
      var dd = mydate.getDate();
      var mm = mydate.getMonth() + 1; //January is 0!
      var yyyy = mydate.getFullYear();

      if (dd < 10) {
        dd = '0' + dd
      }

      if (mm < 10) {
        mm = '0' + mm
      }

      yesterday = yyyy + '/' + mm + '/' + dd;
      document.getElementById("txtUseDate").value = yesterday;
      //document.Main.action = "adddata.asp";
      //document.Main.submit();
    }
  </script>

  <script type="text/javascript" src="js/moment.js"></script>
  <script type="text/javascript" src="js/bootstrap-datetimepicker.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="js/bootstrap.js"></script>

  <script defer src="js/adddata.js?v=6"></script>
  <script type="text/javascript">
    $(function() {
      $('#datetimepicker1').datetimepicker({
        format: "YYYY/MM/DD"
      });
    });
  </script>
  <link href="css/mobile.css?v=15" rel="stylesheet">
</head>

<body>

  <div id="app" class="container-fluid">
    <nav class="navbar navbar-default">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="add_data.php">家庭速記簿</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav navbar-nav">
            <li class="active"><a href="add_data.php">我的存摺 <span class="sr-only">(current)</span></a></li>
            <li><a href="detail.php">花費統計</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">圖表 <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="chart_bar.php">長條圖</a></li>
                <li><a href="chart_history.php">歷史圖</a></li>
                <li><a href="chart_month.php">本月開支圖</a></li>
              </ul>
            </li>
          </ul>
          <form class="navbar-form navbar-left" @submit.prevent="searchLedger">
            <div class="form-group">
              <input type="search" v-model="keyword" class="form-control" placeholder="搜尋分類、明細或備註">
            </div>
            <button type="submit" class="btn btn-default">搜尋</button>
          </form>

        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>

    <div id=Main name=Main>
      <div class="container contentwrap entry-form">
        <div class="month-spend">
          <div class="spend-total">本月已消費 {{ formatMoney(month_spend) }}</div>
        </div>

        <div>
          <div class="input-group col-md-8"> <span class="input-group-addon">分類</span>

            <div class="select-editable">
              <select v-model="clasify">
                <option v-for="(item, key) in classes" :value="item">
                  {{ item }}
                </option>
              </select>

            </div>
          </div>
        </div>

        <div class="input-group col-md-8"> <span class="input-group-addon">明細</span>
          <div class="select-editable">
            <select v-model="item">
              <option v-for="(item, key) in items" :value="item">
                {{ item }}
              </option>
            </select>

          </div>
        </div>

        <div class="input-group date-field"><span class="input-group-addon">日期</span>
          <div class='input-group date' id='datetimepicker1'>
            <input type='text' class="form-control" name="txtUseDate" id="txtUseDate" maxlength="10" />
            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span> </span><span id="yesterday" class="input-group-addon" style="cursor:hand; cursor:pointer;" onClick="javascript:dday();">昨天</span>
            <span id="today" class="input-group-addon" style="cursor:hand; cursor:pointer;" onClick="javascript:today();">今天</span>
          </div>

        </div>

        <div class="input-group col-md-8"><span class="input-group-addon">金額</span>
          <input type="text" v-model="amount" class="form-control" placeholder="請輸入金額，可計算" name="nAmount" id="nAmount" @blur="amount = evaluateAmount(amount)">
        </div>
        <div class="input-group col-md-8"><span class="input-group-addon">描述</span>
          <input type="text" v-model="desc" class="form-control" name="strDesc" id="strDesc" maxlength="200" placeholder="備註，可不填" value="">
        </div>

        <div class="row">
          <div class="col-md-12">
            <input type="hidden" name="op" value="adddata">
            <input id="SId" name="SId" type=hidden value="">
            <button type="button" class="btn btn-primary" @click="add()">新增一筆</button>
          </div>
        </div>
    </div>

    <hr />

    <div class="table-responsive">

      <div class="listheader">
        <div class="pageblock" style="float:right;"> Page Size:
          <select v-model="perPage">
            <option v-for="item in inventory" :value="item.id" :key="item.id">
              {{ item.name }}
            </option>
          </select> Page:
          <div class="page-nav">
            <a class="first micons" @click="page=1">首頁</a>
            <a class="prev micons" :disabled="page == 1" @click="page < 1 ? page = 1 : page--">上一頁</a>
            <select v-model="page">
              <option v-for="pg in pages" :value="pg" :key="pg">
                {{ pg }}
              </option>
            </select>

            <a class="next micons" :disabled="page == pages.length" @click="page++">下一頁</a>
            <a class="last micons" @click="page=pages.length">末頁</a>
          </div>
        </div>
      </div>

      <table class="table table-condensed table-striped table-bordered table-hover no-margin ledger-table">
        <thead class="tablebox s02">
          <!-- <table class="table table-hover table-striped table-sm table-bordered" id="showUser1" ref="showUser1"> -->
          <tr class="header">
            <th>日期</th>
            <th>明細</th>
            <th>分類</th>
            <th>金額</th>
            <th>備註</th>
            <th>功能</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for='(receive_record, index) in displayedPosts' :key="index">
            <td data-label="日期">
              <div v-show="receive_record.is_edited == 1">
                <label> {{receive_record.UseDate}}</label>
              </div>
              <input name="UseDate" v-show="receive_record.is_edited == 0" v-model="UseDate" maxlength="10">
            </td>
            <td data-label="明細">
              <div v-show="receive_record.is_edited == 1">
                <label> {{receive_record.Class}}</label>
              </div>
              <input name="Class" v-show="receive_record.is_edited == 0" v-model="Class" maxlength="200">
            </td>
            <td data-label="分類">
              <div v-show="receive_record.is_edited == 1">
                <label> {{receive_record.Item}}</label>
              </div>
              <input name="Item" v-show="receive_record.is_edited == 0" v-model="Item" maxlength="200">
            </td>
            <td data-label="金額">
              <div v-show="receive_record.is_edited == 1">
                <label> {{Number(receive_record.Amount).toLocaleString()}}</label>
              </div>
              <input name="Amount" v-show="receive_record.is_edited == 0" v-model="Amount" maxlength="40" @blur="Amount = evaluateAmount(Amount)">
            </td>
            <td class="note-cell" data-label="備註">
              <div v-show="receive_record.is_edited == 1">
                <label class="note-text" :title="receive_record.Descri">{{receive_record.Descri}}</label>
              </div>
              <input name="Descri" v-show="receive_record.is_edited == 0" v-model="Descri" maxlength="200">
            </td>
            <td class="row-actions" data-label="功能"><button v-show="receive_record.is_edited == 1" @click="editRow(receive_record)">修改</button>
              <button v-show="receive_record.is_edited == 1" @click="deleteRow(receive_record)">刪除</button>
              <button v-show="receive_record.is_edited == 0" @click="confirmRow(receive_record)">確認</button>
              <button v-show="receive_record.is_edited == 0" @click="cancelRow(receive_record)">取消</button>
            </td>
          </tr>

        </tbody>

      </table>
    </div>
  </div>

  </div>



</body>


</html>
