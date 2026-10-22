<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>歷史圖</title>
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
      <li class="active"><a href="chart_history.php">歷史圖</a></li>
      <li><a href="chart_month.php">本月開支圖</a></li>
    </ul>
  </div>
</nav>

<div class="container">
  <h3>歷史圖</h3>
  <form class="form-inline" method="get" action="chart_history.php" style="margin-bottom:10px;">
    <div class="form-group">
      <label>起始月份</label>
      <input type="month" class="form-control" name="start" value="{$start}">
    </div>
    <div class="form-group" style="margin-left:10px;">
      <label>結束月份</label>
      <input type="month" class="form-control" name="end" value="{$end}">
    </div>
    <button type="submit" class="btn btn-default" style="margin-left:10px;">查詢</button>
  </form>
  <div style="margin:8px 0;">
    <strong>總計金額：</strong> {$totalAmount|number_format:0}
  </div>
  <div id="chart1_div" style="max-width:900px;height:420px"></div>
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
{literal}
<script>
google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(drawHistory);

function drawHistory() {
  var data = new google.visualization.DataTable();
  data.addColumn('string', 'date');
  data.addColumn('number', 'subtotal');
  data.addRows([
{/literal}
  {if !empty($spend)}
    {section name=res loop=$spend step=-1}
      ['{$spend[res]['UseDate']}', {$spend[res]['subtotal']}],
    {/section}
  {/if}
{literal}
  ]);

  var options = { hAxis: { title: 'Time' }, vAxis: { title: 'Amount' }, backgroundColor: '#f1f8e9' };
  var chart = new google.visualization.LineChart(document.getElementById('chart1_div'));
  chart.draw(data, options);
}
</script>
{/literal}
</body>
</html>

