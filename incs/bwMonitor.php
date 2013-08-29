<?php
/*
 Hosting Panel 0.2
 by Author: Ian Anderson Gray
 NOTE: Put this in a directory called "incs" in your user folder.
       For example /home/user/incs/bwMonitor.php
       Create a directory in incs called "cache" and give it write permissions.
 Thanks to Paul Or, for much of the code:
 http://www.paulor.net/tag/cpanel-2/
*/
$current_dir = dirname(__FILE__);

// Set the cached file for our get lists request
$cached_output = "$current_dir/cache/cache.html";

// Let's check the cached list file to see if it is less thean 5 minutes old.
if (file_exists($cached_output) && (filemtime($cached_output) > (time() - $cached_time )))
    { // Since the cached file is less than 5 mins old, let's use it!
        $string = file_get_contents($cached_output) OR exit("Sorry, there was an error");
        echo $string;
        echo "<p><span style=\"font-size:0.8em; color:#eee; padding:2px; background-color:#999; border-radius:5px; \">(cached)</span></p>";
        return;
    } 



## WHM ROOT USER & HASH
    $whmusername = "<-- ENTER WHM Username here -->";
    $whmhash =     "<-- ENTER WHM Hash here -->";
 
## THE QUERY TO THE API
$query = "$server_name:2087/json-api/showbw?search=$user&searchtype=user";
 
## CRAZY CURL STUFF TO AUTH AND RETURN DATA
$curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
$header[0] = "Authorization: WHM $whmusername:" . preg_replace("'(\r|\n)'","",$whmhash);
curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_URL, $query);
$result = curl_exec($curl);
if ($result == false)
   {
      error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
   }
curl_close($curl);
 
## COVERT BYTES TO MEGABYTES
function MBFormat($bytes,$decimals=1)
   {
      return round($bytes/(1024*1024));
   }
 
## DECODE JSON
$obj = json_decode($result);
 
## ROUND THE DIGITS
$bw_limit = round(MBFormat($obj->bandwidth[0]->acct[0]->limit)); // LIMIT
$bw_used = round(MBFormat($obj->bandwidth[0]->totalused)); // USED
  
## THE QUERY TO THE API
$query = "$server_name:2087/json-api/accountsummary?user=$user";
 
## CRAZY CURL STUFF TO AUTH AND RETURN DATA
$curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
$header[0] = "Authorization: WHM $whmusername:" . preg_replace("'(\r|\n)'","",$whmhash);
curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
curl_setopt($curl, CURLOPT_URL, $query);
$result = curl_exec($curl);
if ($result == false)
   {
      error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
   }
curl_close($curl);
 
## DECODE JSON
$obj = json_decode($result);
 
## ROUND THE DIGITS
$diskused =  substr($obj->acct[0]->diskused,0,-1);
$disklimit = substr($obj->acct[0]->disklimit,0,-1);
	
$bwusagePercentage = ($bw_used/$bw_limit)*100;
$diskusagePercentage = ($diskused/$disklimit)*100;

## NOW JAVASCRIPT FROM GOOGLE CHARTS :]
$string = "
      <style>#pb_backupbuddy, #rg_forms_dashboard {display:none;}</style>
      <script type='text/javascript' src='https://www.google.com/jsapi'></script>
      <script type='text/javascript'>
      google.load('visualization', '1', {packages:['gauge']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Label');
        data.addColumn('number', 'Value');
        data.addRows(2);
        data.setValue(0, 0, 'Disk');
        data.setValue(0, 1, $diskusagePercentage);
        data.setValue(1, 0, 'Bandwidth');
        data.setValue(1, 1, $bwusagePercentage);
        var chart = new google.visualization.Gauge(document.getElementById('chart_div'));
        var options = {width: 400, height: 200, redFrom: 85, redTo: 100,
            yellowFrom:70, yellowTo: 85, greenFrom:55, greenTo:70, minorTicks: 5};
        chart.draw(data, options);
         }
      </script>
	   <h4>Current Plan: <strong style=\"color:#0074a2;\">$current_plan</strong></h4>
	   <div id='chart_div'></div>
	   <table width=\"400\">
	      <tr><td style=\"width:200px; text-align:center;\">Used: <strong>".number_format($diskused,0,".",",")."Mb</strong> <em>(of ".number_format($disklimit,0,".",",")."Mb)</em></td><td style=\"width:200px; text-align:center;\">Used: <strong>".number_format($bw_used,0,".",",")."Mb</strong> <em>(out of ".number_format($bw_limit,0,".",",")."Mb)</em></td></tr>
	  </table>
     <p style=\"font-size:0.8em; color:#454545;\">Updated every ".number_format(($cached_time/60),0)."hrs. <em>(last updated: ".date("H:i")." Server Time)</em></p>
";
echo $string;
file_put_contents($cached_output, $string, LOCK_EX) OR exit("Sorry, there was a problem. We couldn't save the cached file.");


?>