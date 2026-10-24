<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>本月開支圖</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/mobile.css?v=20" rel="stylesheet">
</head>
<body class="chart-page">
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="add_data.php">家庭速記簿</a>
    </div>
    <ul class="nav navbar-nav">
      <li><a href="chart_bar.php">長條圖</a></li>
      <li><a href="chart_history.php">歷史圖</a></li>
      <li class="active"><a href="chart_month.php">本月開支圖</a></li>
    </ul>
  </div>
</nav>

<div class="container">
  <h3>本月開支圖</h3>
  <p class="month-pie-note">以選擇的月份為中心，前後各三個月。圓餅面積依該月總支出，顏色代表同一個分類。點選某一塊可查看當月該分類明細。</p>
  <form class="form-inline" method="get" action="chart_month.php" style="margin-bottom:10px;">
    <div class="form-group">
      <label>月份</label>
      <input type="month" class="form-control" name="ym" value="{$ym}">
    </div>
    <button type="submit" class="btn btn-default">查詢</button>
  </form>
  <div id="month_pies" class="month-pies"></div>
  <div id="month_legend" class="month-legend"></div>

  <div id="month_detail" class="month-detail" hidden>
    <div class="month-detail-head">
      <h4 id="month_detail_title"></h4>
      <button type="button" class="btn btn-default btn-sm" id="month_detail_close">關閉</button>
    </div>
    <div id="month_detail_status" class="month-detail-status"></div>
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
        <tbody id="month_detail_body"></tbody>
      </table>
    </div>
  </div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
var monthCharts = {$monthChartsJson};
</script>
{literal}
<script>
google.charts.load('current', {packages: ['corechart']});
google.charts.setOnLoadCallback(drawCharts);

var palette = ['#3366cc','#dc3912','#ff9900','#109618','#990099','#0099c6','#dd4477','#66aa00','#b82e2e','#316395','#994499','#22aa99','#aaaa11','#6633cc','#e67300','#8b0707','#651067','#329262','#5574a6','#3b3eac'];
var money = function (value) {
  return Math.round(Number(value) || 0).toLocaleString('zh-TW');
};

function showMonthDetail(ym, label, itemName) {
  var panel = document.getElementById('month_detail');
  var title = document.getElementById('month_detail_title');
  var status = document.getElementById('month_detail_status');
  var body = document.getElementById('month_detail_body');
  panel.hidden = false;
  title.textContent = label + '｜' + itemName;
  status.textContent = '載入中…';
  body.innerHTML = '';

  var url = 'getmonthdetail.php?ym=' + encodeURIComponent(ym) + '&item=' + encodeURIComponent(itemName);
  fetch(url, {
    credentials: 'same-origin'
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error('載入失敗');
      }
      return response.json();
    })
    .then(function (data) {
      var rows = (data && data.rows) || [];
      if (!rows.length) {
        status.textContent = '這個分類在該月沒有明細';
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

function drawCharts() {
  var root = document.getElementById('month_pies');
  var legend = document.getElementById('month_legend');
  var months = monthCharts || [];
  var max = 0;
  var names = [];
  months.forEach(function (month) {
    if (month.total > max) max = month.total;
    (month.slices || []).forEach(function (slice) {
      if (names.indexOf(slice.name) === -1) names.push(slice.name);
    });
  });
  if (max <= 0) max = 1;
  names.sort();
  var colors = {};
  names.forEach(function (name, index) {
    colors[name] = palette[index % palette.length];
  });

  months.forEach(function (month) {
    var card = document.createElement('div');
    card.className = 'month-pie' + (month.current ? ' is-current' : '');
    var size = month.total > 0 ? Math.max(88, Math.round(Math.sqrt(month.total / max) * 300)) : 88;

    var label = document.createElement('div');
    label.className = 'month-pie-label';
    label.textContent = month.label + (month.current ? '（所選）' : '') + (month.partial ? ' 截至今天' : '');
    card.appendChild(label);

    var total = document.createElement('div');
    total.className = 'month-pie-total';
    total.textContent = month.total > 0 ? Math.round(month.total).toLocaleString('zh-TW') : '尚無紀錄';
    card.appendChild(total);

    var box = document.createElement('div');
    box.className = 'month-pie-chart';
    box.style.width = size + 'px';
    box.style.height = size + 'px';
    card.appendChild(box);
    root.appendChild(card);

    if (!month.slices || !month.slices.length) {
      box.classList.add('month-pie-empty');
      return;
    }

    var data = new google.visualization.DataTable();
    data.addColumn('string', '分類');
    data.addColumn('number', '金額');
    var sliceColors = {};
    month.slices.forEach(function (slice, index) {
      data.addRow([slice.name, slice.amount]);
      sliceColors[index] = { color: colors[slice.name] };
    });
    var chart = new google.visualization.PieChart(box);
    chart.draw(data, {
      legend: 'none',
      pieSliceText: 'none',
      backgroundColor: 'transparent',
      chartArea: { left: 6, top: 6, width: '88%', height: '88%' },
      slices: sliceColors,
      tooltip: { text: 'both' }
    });
    google.visualization.events.addListener(chart, 'select', function () {
      var selection = chart.getSelection();
      if (!selection.length || selection[0].row == null) {
        return;
      }
      var itemName = data.getValue(selection[0].row, 0);
      showMonthDetail(month.ym, month.label, itemName);
    });
  });

  names.forEach(function (name) {
    var item = document.createElement('span');
    var swatch = document.createElement('i');
    swatch.style.background = colors[name];
    item.appendChild(swatch);
    item.appendChild(document.createTextNode(name));
    legend.appendChild(item);
  });

  document.getElementById('month_detail_close').addEventListener('click', function () {
    document.getElementById('month_detail').hidden = true;
  });
}
</script>
{/literal}
</body>
</html>
