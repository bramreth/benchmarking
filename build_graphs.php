<?php

function standard_deviation($sample){
  if(is_array($sample)){
    $mean = array_sum($sample) / count($sample);
    foreach($sample as $key => $num) $devs[$key] = pow($num - $mean, 2);
    return sqrt(array_sum($devs) / (count($devs) - 1));
  }
}
$space = "";
$count = 0;
$options = getopt("f:d::t::h::p::");
if ($options["f"] == ""){
  echo "fail", "\n";
  exit();
}else{
  echo "target csv files: ", $options["f"], "\n";
  var_dump(explode(",", $options["f"]));
  foreach (explode(",", $options["f"]) as $file) {
    $frametime = array();
    $freq = 0;
    $total_time = array();
    $percentile = array();
    $unique_val = array();
      echo "current file: $file\n";
      $myfile = fopen($file, "r") or die("Unable to open file!");
    //$myfile = fopen($options["f"], "r") or die("Unable to open file!");
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
    $csv_col .= "data.addColumn('number', '$file frametime (ms)');";
    $csv_dat .= "data.addRows([";
    $csv_dat .= "[$total_time[1]";
    for ($i = 0; $i < count(explode(",", $options["f"])); $i++) {
      if(explode(",", $options["f"])[$i] == $file){
        $csv_dat .= ",$frametime[$i]";
      }else{
        $csv_dat .= ",null";
      }
    }
    $csv_dat .= "]";
    for ($i = 2; $i <= count($frametime)-2; $i++) {
      $csv_dat .= ",[$total_time[$i]";
      for ($j = 0; $j < count(explode(",", $options["f"])); $j++) {
        if(explode(",", $options["f"])[$j] == $file){
          $csv_dat .= ",$frametime[$i]";
        }else{
          $csv_dat .= ",null";
        }
      }
      $csv_dat .= "]";
    }
    $csv_dat .=  "]);";
    //build percentile data
    sort($frametime);
    $csv_dat_percentile .= "data.addRows([";
    for ($i = 0.9; $i < 0.9999; $i+= 0.0001) {
      $csv_dat_percentile .= "[$i";
      for ($j = 0; $j < count(explode(",", $options["f"])); $j++) {
        if(explode(",", $options["f"])[$j] == $file){
          $var = floatval($frametime[round(($i) * count($frametime))]);
          $csv_dat_percentile .= ",$var";
        }else{
          $csv_dat_percentile .= ",null";
        }
      }
      $csv_dat_percentile .= "],";
    }
    $csv_dat_percentile = substr(trim( $csv_dat_percentile), 0, -1);
    $csv_dat_percentile .= "]);";
    //build histogram data
    $csv_dat_histogram .= "data.addRows([";
    for ($i = 3; $i <= count($frametime)-2; $i++) {
      $tmp = round($frametime[$i]);
      $csv_dat_histogram .= "[";
      for ($j = 0; $j < count(explode(",", $options["f"])); $j++) {
        if(explode(",", $options["f"])[$j] == $file){
          $var = floatval($frametime[round(($i) * count($frametime))]);
          $csv_dat_histogram .= "$tmp";
        }else{
          $csv_dat_histogram .= "null";
        }
        $csv_dat_histogram .= ",";
      }
      $csv_dat_histogram = substr(trim( $csv_dat_histogram), 0, -1);
      $csv_dat_histogram .= "],";
    }
    $csv_dat_histogram .= "  ]);";
    //create info table
      $avg = array_sum($frametime) / count($frametime);
      $min = min($frametime);
      $max = max($frametime);
      $std_dev = standard_deviation($frametime);
      $text_data .= <<<EOT
      </div>
      <div class = container>
      <h1>$file</h1>
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
  $csv_col
  $csv_dat
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
  $csv_col
  $csv_dat_percentile
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
  $csv_col
  $csv_dat_histogram
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