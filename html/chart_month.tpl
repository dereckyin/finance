<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>本月開支圖</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/mobile.css?v=12" rel="stylesheet">
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
  <p class="month-pie-note">以選擇的月份為中心，前後各三個月。圓餅面積依該月總支出，顏色代表同一個分類。</p>
  <form class="form-inline" method="get" action="chart_month.php" style="margin-bottom:10px;">
    <div class="form-group">
      <label>月份</label>
      <input type="month" class="form-control" name="ym" value="{$ym}">
    </div>
    <button type="submit" class="btn btn-default">查詢</button>
  </form>
  <div id="month_pies" class="month-pies"></div>
  <div id="month_legend" class="month-legend"></div>
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
  });

  names.forEach(function (name) {
    var item = document.createElement('span');
    var swatch = document.createElement('i');
    swatch.style.background = colors[name];
    item.appendChild(swatch);
    item.appendChild(document.createTextNode(name));
    legend.appendChild(item);
  });
}
</script>
{/literal}
</body>
</html>
