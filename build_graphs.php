<?php
$frametime = array();
$freq = 0;
$total_time = array();
$percentile = array();
$unique_val = array();
$options = getopt("f:d::t::h::p::");
if ($options["f"] == ""){
  echo "fail", "\n";
  exit();
}else{
  echo "target csv file: ", $options["f"], "\n";
  $myfile = fopen($options["f"], "r") or die("Unable to open file!");
  while(!feof($myfile)) {
    $line = fgets($myfile);
    preg_match('/(\d+.\d+),(\d+)/', $line, $matches, PREG_OFFSET_CAPTURE);
    if($matches != NULL){
      $frametime[] = $matches[1][0];
      $freq += $matches[1][0]*0.001;
      $total_time[] = $freq;
      $unique_val[round(floatval($matches[1][0]))]++;
    }
  }
  fclose($myfile);
  //build time graph data
  $csv_dat = "[$total_time[1],$frametime[1]]";
  for ($i = 2; $i <= count($frametime)-2; $i++) {
    $csv_dat .= ",[$total_time[$i],$frametime[$i]]";
  }
  //build percentile data
  sort($frametime);
  $percentile[0] = floatval($frametime[round((90/100) * count($frametime))]);
  $percentile[1] = floatval($frametime[round((91/100) * count($frametime))]);
  $percentile[2] = floatval($frametime[round((92/100) * count($frametime))]);
  $percentile[3] = floatval($frametime[round((93/100) * count($frametime))]);
  $percentile[4] = floatval($frametime[round((94/100) * count($frametime))]);
  $percentile[5] = floatval($frametime[round((95/100) * count($frametime))]);
  $percentile[6] = floatval($frametime[round((96/100) * count($frametime))]);
  $percentile[7] = floatval($frametime[round((97/100) * count($frametime))]);
  $percentile[8] = floatval($frametime[round((98/100) * count($frametime))]);
  $percentile[9] = floatval($frametime[round((99/100) * count($frametime))]);
  $percentile[10] = floatval($frametime[round((99.5/100) * count($frametime))]);
  $percentile[11] = floatval($frametime[round((99.75/100) * count($frametime))]);
  $percentile[12] = floatval($frametime[round((99.875/100) * count($frametime))]);
  $percentile[13] = floatval($frametime[round((99.9375/100) * count($frametime))]);
  $percentile[14] = floatval($frametime[round((99.96875/100) * count($frametime))]);
  $percentile[15] = floatval($frametime[count($frametime)-1]);
  $csv_dat_percentile = <<<EOT
  [0.9, $percentile[0]],
  [0.91, $percentile[1]],
  [0.92, $percentile[2]],
  [0.93, $percentile[3]],
  [0.94, $percentile[4]],
  [0.95, $percentile[5]],
  [0.96, $percentile[6]],
  [0.97, $percentile[7]],
  [0.98, $percentile[8]],
  [0.99, $percentile[9]],
  [0.995, $percentile[10]],
  [0.9975, $percentile[11]],
  [0.99875, $percentile[12]],
  [0.999375, $percentile[13]],
  [0.9996875, $percentile[14]],
  [1,$percentile[15]]
EOT;
  //build histogram data
  $tmp = round($frametime[$i]);
  $csv_dat_histogram = "[$tmp]";
  for ($i = 3; $i <= count($frametime)-2; $i++) {
    $tmp = round($frametime[$i]);
    $csv_dat_histogram .= ",[$tmp]";
  }
}
$myfile = fopen("csv_graphs.html", "w"); 
$text_pre = <<< EOT
<!DOCTYPE html>

<html lang="en-US">
<style>
table, th, td {
    border: 1px solid ;
    border-collapse: collapse;
}
#title{
  font-weight: bold;
}
.container{
  background-color: white;
  border: 2px solid;
  border-radius: 5px;
}
*{
  font-family: sans-serif;
}
</style>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<body>
<div class = "container">
EOT;
fwrite($myfile, $text_pre);

$text_time = <<<EOT
<div id="graph"></div>
<script type="text/javascript">
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);
function drawChart() {
  var data = new google.visualization.DataTable();
  data.addColumn('number', 'time (s)');
  data.addColumn('number', 'frametime (ms)');
  data.addRows([
    $csv_dat
  ]);
  var options = {'title':'frametime/time:'};
  var chart = new google.visualization.LineChart(document.getElementById('graph'));
  chart.draw(data, options);
}
</script>
EOT;

if (!array_key_exists("t", $options)){
  echo "has frametime/ time", "\n";
  fwrite($myfile, $text_time);
}

$text_percentile = <<<EOT
<div id="graph_percentile"></div>
<script type="text/javascript">
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);
function drawChart() {
  var data = new google.visualization.DataTable();
  data.addColumn('number', 'percentile (%)');
  data.addColumn('number', 'frametime (ms)');
  data.addRows([
    $csv_dat_percentile
  ]);
  var options = {'title':'frametime percentile:', hAxis: {format: 'percent'}};
  var chart = new google.visualization.LineChart(document.getElementById('graph_percentile'));
  chart.draw(data, options);
}
</script>
EOT;

if (!array_key_exists("p", $options)){
  echo "has percentile", "\n";
  fwrite($myfile, $text_percentile);
}

$text_histogram = <<<EOT
<div id="graph_hist"></div>
<script type="text/javascript">
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(drawChart);
function drawChart() {
  var data = new google.visualization.DataTable();
  data.addColumn('number', 'frametime');
  data.addRows([
    $csv_dat_histogram
  ]);
  var options = {'title':'frametime histogram:'};
  var chart = new google.visualization.Histogram(document.getElementById('graph_hist'));
  chart.draw(data, options);
}
</script>
EOT;

if (!array_key_exists("h", $options)){
  echo "has histogram", "\n";
  fwrite($myfile, $text_histogram);
}

$avg = array_sum($frametime) / count($frametime);
$min = min($frametime);
$max = max($frametime);
function standard_deviation($sample){
  if(is_array($sample)){
    $mean = array_sum($sample) / count($sample);
    foreach($sample as $key => $num) $devs[$key] = pow($num - $mean, 2);
    return sqrt(array_sum($devs) / (count($devs) - 1));
  }
}
$std_dev = standard_deviation($frametime);
$text_data = <<<EOT
</div>
<div class = container>
<table style="width:100%">
  <tr>
    <td id = "title">frametime quality</td>
    <td id = "title">value</td>
  </tr>
  <tr>
    <td>average</td>
    <td>$avg (ms)</td>
  </tr>
  <tr>
    <td>minimum</td>
    <td>$min (ms)</td>
  </tr>
  <tr>
    <td>maximum</td>
    <td>$max (ms)</td>
  </tr>
  <tr>
    <td>standard deviation</td>
    <td>$std_dev (ms)</td>
  </tr>
</table>  
</div>
EOT;

if (!array_key_exists("d", $options)){
  echo "has data", "\n";
  fwrite($myfile, $text_data);
}

$text_post = <<<EOT
</body>
</html>
EOT;
fwrite($myfile, $text_post);
fclose($myfile);
?>