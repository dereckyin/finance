<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>本月開支圖</title>
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/mobile.css" rel="stylesheet">
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
  <form class="form-inline" method="get" action="chart_month.php" style="margin-bottom:10px;">
    <div class="form-group">
      <label>月份</label>
      <input type="month" class="form-control" name="ym" value="{$ym|replace:'-':'-'}">
    </div>
    <button type="submit" class="btn btn-default">查詢</button>
  </form>
  <div id="month_pie" style="max-width:900px;height:420px"></div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
{literal}
<script>
google.charts.load('current', {packages: ['corechart']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
  var data = new google.visualization.DataTable();
  data.addColumn('string', '項目');
  data.addColumn('number', '金額');
  data.addRows([
{/literal}
  {if !empty($items)}
    {foreach from=$items key=label item=classes}
      ['{$label|escape:"javascript"}', {if !empty($monthTotals[$label])}{$monthTotals[$label]}{else}0{/if}],
    {/foreach}
  {/if}
{literal}
  ]);

  var options = { pieHole: 0.45, chartArea: {left:20, top:20, width:'90%', height:'80%'} };
  // Add slice labels with values
  var view = new google.visualization.DataView(data);
  view.setColumns([0, 1, {
    calc: function (dt, row) { return dt.getValue(row, 0) + ' ' + dt.getValue(row, 1).toLocaleString(); },
    type: 'string', role: 'tooltip'
  }]);
  var chart = new google.visualization.PieChart(document.getElementById('month_pie'));
  chart.draw(view, options);
}
</script>
{/literal}
</body>
</html>

