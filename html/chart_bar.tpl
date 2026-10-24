<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>長條圖</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/mobile.css?v=21" rel="stylesheet">
</head>
<body class="chart-page">
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="add_data.php">家庭速記簿</a>
    </div>
    <ul class="nav navbar-nav">
      <li class="active"><a href="chart_bar.php">長條圖</a></li>
      <li><a href="chart_history.php">歷史圖</a></li>
      <li><a href="chart_month.php">本月開支圖</a></li>
    </ul>
  </div>
</nav>

<div class="container">
  <h3>長條圖</h3>
  <p class="month-pie-note">可切換依分類或明細加總。點選長條可查看當月該項目明細。</p>
  <form class="form-inline" method="get" action="chart_bar.php" style="margin-bottom:10px;">
    <div class="form-group">
      <label>月份</label>
      <input type="month" class="form-control" name="ym" value="{$ym}">
    </div>
    <div class="form-group chart-group-toggle">
      <label class="radio-inline">
        <input type="radio" name="group" value="item"{if $group ne 'class'} checked{/if}> 分類
      </label>
      <label class="radio-inline">
        <input type="radio" name="group" value="class"{if $group eq 'class'} checked{/if}> 明細
      </label>
    </div>
    <button type="submit" class="btn btn-default">查詢</button>
  </form>
  <div class="month-detail-status">{$groupLabel}加總，合計 {$barTotalLabel}</div>
  <div id="bar_chart" style="max-width:900px;height:420px"></div>

  <div id="bar_detail" class="month-detail" hidden>
    <div class="month-detail-head">
      <h4 id="bar_detail_title"></h4>
      <button type="button" class="btn btn-default btn-sm" id="bar_detail_close">關閉</button>
    </div>
    <div id="bar_detail_status" class="month-detail-status"></div>
    <div class="table-responsive">
      <table class="table table-condensed table-striped table-bordered month-detail-table">
        <thead>
          <tr>
            <th>日期</th>
            <th>明細</th>
            <th>分類</th>
            <th>金額</th>
            <th>備註</th>
          </tr>
        </thead>
        <tbody id="bar_detail_body"></tbody>
      </table>
    </div>
  </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
var barCharts = {$barChartsJson};
var barYm = '{$ym|escape:"javascript"}';
var barGroup = '{$group|escape:"javascript"}';
var barGroupLabel = '{$groupLabel|escape:"javascript"}';
</script>
{literal}
<script>
google.charts.load('current', {packages: ['corechart']});
google.charts.setOnLoadCallback(drawChart);

var money = function (value) {
  return Math.round(Number(value) || 0).toLocaleString('zh-TW');
};

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function escapeAttr(value) {
  return escapeHtml(value).replace(/'/g, '&#39;');
}

function showBarDetail(name) {
  var panel = document.getElementById('bar_detail');
  var title = document.getElementById('bar_detail_title');
  var status = document.getElementById('bar_detail_status');
  var body = document.getElementById('bar_detail_body');
  panel.hidden = false;
  title.textContent = barYm.replace('-', '/') + '｜' + barGroupLabel + '｜' + name;
  status.textContent = '載入中…';
  body.innerHTML = '';

  var url = 'getmonthdetail.php?ym=' + encodeURIComponent(barYm)
    + '&by=' + encodeURIComponent(barGroup)
    + '&name=' + encodeURIComponent(name);

  fetch(url, { credentials: 'same-origin' })
    .then(function (response) {
      if (!response.ok) {
        throw new Error('載入失敗');
      }
      return response.json();
    })
    .then(function (data) {
      var rows = (data && data.rows) || [];
      if (!rows.length) {
        status.textContent = '這個項目在該月沒有明細';
        return;
      }
      var total = 0;
      rows.forEach(function (row) {
        total += Number(row.Amount) || 0;
        var tr = document.createElement('tr');
        tr.innerHTML =
          '<td>' + escapeHtml(row.UseDate || '') + '</td>' +
          '<td>' + escapeHtml(row.Class || '') + '</td>' +
          '<td>' + escapeHtml(row.Item || '') + '</td>' +
          '<td class="text-right">' + money(row.Amount) + '</td>' +
          '<td class="note-cell"><span class="note-text" title="' + escapeAttr(row.Descri || '') + '">' + escapeHtml(row.Descri || '') + '</span></td>';
        body.appendChild(tr);
      });
      status.textContent = '共 ' + rows.length + ' 筆，合計 ' + money(total);
      panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    })
    .catch(function () {
      status.textContent = '載入失敗，請再試一次';
    });
}

function drawChart() {
  var bars = barCharts || [];
  var data = new google.visualization.DataTable();
  data.addColumn('string', barGroupLabel);
  data.addColumn('number', '金額');
  bars.forEach(function (bar) {
    data.addRow([bar.name, bar.amount]);
  });

  var view = new google.visualization.DataView(data);
  view.setColumns([0, 1, {
    calc: function (dt, row) { return dt.getValue(row, 1).toLocaleString('zh-TW'); },
    type: 'string',
    role: 'annotation'
  }]);

  var options = {
    legend: { position: 'none' },
    chartArea: { left: 60, top: 20, width: '80%', height: '70%' },
    hAxis: { slantedText: bars.length > 8, slantedTextAngle: 45 }
  };
  var chart = new google.visualization.ColumnChart(document.getElementById('bar_chart'));
  chart.draw(view, options);
  google.visualization.events.addListener(chart, 'select', function () {
    var selection = chart.getSelection();
    if (!selection.length || selection[0].row == null) {
      return;
    }
    showBarDetail(data.getValue(selection[0].row, 0));
  });

  document.getElementById('bar_detail_close').addEventListener('click', function () {
    document.getElementById('bar_detail').hidden = true;
  });
}
</script>
{/literal}
</body>
</html>
