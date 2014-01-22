<?php
/*
 
  Simwood Call Map 

  This is a simple demonstration of the Simwood realtime calls
  in progress API.  It is not intended for production use.

  The inline PHP in the HTML document is intended to more clearly
  show how the chart data in the JavaScript output is generated,
  in production it would be better to seperate the data and 
  presentation logic.

  This could easily be implemented entirely in HTML/JS however 
  please be aware that your API keys offer full access to your 
  Simwood account it would therefore *not* be appropriate to store
  them in JavaScript code available to a client side browser in
  most cases.

  THIS CODE IS PROVIDED ON AN "AS IS" BASIS, WITHOUT WARRANTY 
  OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, WITHOUT 
  LIMITATION, WARRANTIES THAT THE CODE IS FREE OF DEFECTS, 
  MERCHANTABLE OR FIT FOR A PARTICULAR PURPOSE. 

*/

/* 

  Your Simwood API Details 

  These are in your welcome eMail, if you've not used our API 
  before or don't know your API details please raise a support
  ticket (https://support.simwood.com/) and we'll be happy to
  provide them.

  Note: your api details are NOT the same as your portal login.

 */

$api_user = 'YOUR API USER HERE';
$api_pass =  'YOUR API KEY HERE';
$accountcode = 'XXXXXX';


/* 

  Make a simple "GET" call to the Simwood API, the calls in progress 
  information is returned as a JSON object which PHP then parses into
  an array.

  For full documentation on the Simwood API please see 
  https://mirror.simwood.com/pdfs/APIv3.pdf

 */
$json = file_get_contents("https://$api_user:$api_pass@api.simwood.com/v3/voice/$accountcode/inprogress/current");
$arrApiResponse = json_decode($json,true);

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Simwood Fraud Monitoring Demo</title>
<script type='text/javascript' src='http://www.google.com/jsapi'></script>
<script type='text/javascript'>
    // Use Google GeoChart Visualation 
    google.load('visualization', '1', {packages:['geochart']});
    google.setOnLoadCallback(drawChart);

    function drawChart() {
        var data=new google.visualization.DataTable();
        var options={};
        data.addColumn('string', 'Country', 'ISO');
        data.addColumn('number', 'Cost', 'a');
        data.addColumn('number', 'Count', 'b');

<?php

        // Server side, loop through the data returned from the API call
        $arrCountries = $arrApiResponse['countries'];
        foreach($arrCountries as $isoCountryCode => $data) {
            /*
             * We create a row in the Google Chart for each country returned
             * from the API call - for more information on the Google Chart
             * library see https://developers.google.com/chart/interactive/docs/reference
             *
             * Each entry contains [ Country Code, Total Value of Calls, Total Number of Calls ]
             *
             * NB: There are some inconsitencies between the ISO 3166 country
             *     codes used by Simwood and Google's Charts. Notably some 
             *     'reserved' codes (like AC) are not recognised by Google.
             *
             *     Additionally we use the code 'XX' for destinations such as
             *     satellite phones and International Freephone which cannot 
             *     be accurately represented on the map
             *
             */
            $arrMapRowData[] = "['$isoCountryCode',{$data['total']},{$data['callcount']}]";
        }
        // Generate the JavaScript that will add this data to the chart 
        echo "        data.addRows([".implode(",\n", $arrMapRowData)."]);\n";

?>
        // Draw the chart
        var chart=new google.visualization.GeoChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }

    // Generate a Google table showing breakouts
    google.load('visualization', '1', {packages:['table']});
    google.setOnLoadCallback(drawTable);

    function drawTable() {
        var data = new google.visualization.DataTable();
        var options = { 'sortColumn':2, 'sortAscending':false};
        data.addColumn('number', 'ID', 'id');
        data.addColumn('string', 'Breakout', 'breakout');
        data.addColumn('number', 'Cost', 'cost');
        data.addColumn('number', 'Count', 'count');
<?php
    
        // As above, we loop through the data returned from the API call (by breakout)
        $arrBreakout = $arrApiResponse['calls'];
        foreach($arrBreakout as $id => $data) {
            /*
             * We create a row in the Google Table for each destination returned
             * from the API call similar to the above example by Country.
             *
             * Each entry contains [ ID, Location, Total Value of Calls, Total Number of Calls ]
             */
            $arrTableRowData[] = "[{$id},'{$data['location']}',".round($data['total'],3).",{$data['callcount']}]";
        }
        // Generate the JavaScript that will add this data to the table 
        echo "        data.addRows([".implode(",\n", $arrTableRowData)."]);\n";
?>
        var table = new google.visualization.Table(document.getElementById('table_div'));
        table.draw(data, options);
    }

    // Refresh this page periodically
    setInterval('location.reload();',"60000");
</script>
</head>
<body>
    <!-- This container will contain our map -->
    <div id="chart_div"></div>
    <!-- This container will contain our table -->
    <div id='table_div'></div>
</body>
</html>