<?php
/*
 * statistics_chart_page.php
 *
 *  Retrieve CloudFront statistics and use them to
 *  generate a series of Google charts.
 *
 *  Accepts the following parameters:
 *
 *      Period  Metrics period in minutes (15).
 *      Start   Starting date in form YYYY-MM-DD (24 hours ago)
 *      End     Starting date in form YYYY-MM-DD (now).
 *
 * Copyright 2009-2010 Amazon.com, Inc. or its affiliates. All Rights
 * Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"). You
 * may not use this file except in compliance with the License. A copy
 * of the License is located at
 *
 *       http://aws.amazon.com/apache2.0/
 *
 * or in the "license.txt" file accompanying this file. This file is
 * distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations under the
 * License.
 */

error_reporting(E_ALL);

require_once('sdk.class.php');

// Set default date values
$startDate_DT = new DateTime('now');
$endDate_DT   = new DateTime('now');
$startDate_DT->modify('-1 day');

$startDate = $startDate_DT->format('Y-m-d');
$endDate   = $endDate_DT->format('Y-m-d');

// Get parameters
$period = isset($_GET['period']) ? $_GET['period'] : 15;
$start  = isset($_GET['start'])  ? $_GET['start']  : $startDate;
$end    = isset($_GET['end'])    ? $_GET['end']    : $endDate;

// Adjust parameters as needed
$period *= 60;

// Create array of chart parameters, one interior array per chart
$charts = array(
    array('M' => 'NetworkIn',
          'U' => 'Bytes',
          'L' => 'Network In (Bytes)'),
    
    array('M' => 'NetworkOut',
          'U' => 'Bytes',
          'L' => 'Network Out (Bytes)'),
          
    array('M' => 'CPUUtilization',
          'U' => 'Percent',
          'L' => 'CPU Utilization (Percent)'),

    array('M' => 'DiskReadBytes',
          'U' => 'Bytes',
          'L' => 'Disk Read Bytes'),
    
    array('M' => 'DiskReadOps',
          'U' => 'Count',
          'L' => 'Disk Read Operations/Second'),
    
    array('M' => 'DiskWriteBytes',
          'U' => 'Bytes',
          'L' => 'Disk Write Bytes'),
    
    array('M' => 'DiskWriteOps',
          'U' => 'Count',
          'L' => 'Disk Write Operations/Second'),    
    );

// Create the CloudWatch access object
$cw = new AmazonCloudWatch(null, null, 'ap-southeast-1.monitoring.amazonaws.com');

// Prepare to get metrics
$opt = array('Namespace' => 'AWS/EC2', 'Period' => $period);
$statistics = array('Average','Minimum','Maximum','Sum');
$chartImages = array();
// Generate one chart for each member of $charts
foreach ($charts as &$chart)
{
  $measure = $chart['M'];
  $unit    = $chart['U'];
  $label   = $chart['L'];

  // Get the metrics
  $res = $cw->get_metric_statistics($measure,
            $statistics,
            $unit,
            $start,
            $end,
            $opt);

  if ($res->isOK())
  {
    $datapoints = $res->body->GetMetricStatisticsResult->Datapoints->member;

    // Populate an array with the metrics for sorting
    $dataRows = array();
    foreach ($datapoints as $datapoint)
    {
      $timestamp = (string) $datapoint->Timestamp;
      
      $dataRows[$timestamp] =
        array('Timestamp' => (string) $datapoint->Timestamp,
          'Units'     => (string) $datapoint->Unit,
          'Samples'   => (string) $datapoint->Samples,
          'Average'   => (float)  $datapoint->Average,
          'Minimum'   => (float)  $datapoint->Minimum,
          'Maximum'   => (float)  $datapoint->Maximum,
          'Sum'       => (float)  $datapoint->Sum);
    }

    // Sort the metrics
    ksort ($dataRows);

    // Form arrays for each of the statistics
    $averages = array();
    $minimums = array();
    $maximums = array();
    $sums     = array();
  
    foreach ($dataRows as $dataRow)
    {
      $averages[] = $dataRow['Average'];
      $minimums[] = $dataRow['Minimum'];
      $maximums[] = $dataRow['Maximum'];
      $sums[]     = $dataRow['Sum'];
    }
  
    // Compute scale so that largest value is 100 or less
    $chartMax = max(max($averages), max($minimums),
        max($maximums), max($sums));
    $scale    = 100.0 / $chartMax;

    // Scale the arrays
    for ($i = 0; $i < count($averages); $i++)
    {
      $averages[$i] = (int) ($averages[$i] * $scale);
      $minimums[$i] = (int) ($minimums[$i] * $scale);
      $maximums[$i] = (int) ($maximums[$i] * $scale);
      $sums[$i]     = (int) ($sums[$i]     * $scale);
    }

    // Create comma-delimited string from each array
    $average = implode(',', $averages);
    $minimum = implode(',', $minimums);
    $maximum = implode(',', $maximums);
    $sum     = implode(',', $sums);

    // Combine arrays for use in chart
    $series  = $average . '|' .
               $minimum . '|' .
               $maximum . '|' .
               $sum;

    // Prepare title
    $label = str_replace(' ', '+', $label);

    // Prepare colors
    $colors = 'ff0000,00ff00,0000ff,800080';

    // Form the URL for the Google chart
    $chartURL = "http://chart.apis.google.com/chart";
    $chartURL .= '?chs=300x180';    // Chart size
    $chartURL .= '&cht=lc';      // Line chart
    $chartURL .= '&chtt=' . $label;    // Label
    $chartURL .= '&chdlp=b';      // Legend at bottom
    $chartURL .= '&chdl=Avg|Min|Max|Sum';  // Legend
    $chartURL .= '&chco=' . $colors;      // Colors
    $chartURL .= '&chd=t:' . $series;    // Data series

    // Generate the chart
    $chartImages[] = $chartURL;
  }
}

// create a page header and an explanatory message
$output_title = 'Chapter 7 Sample - Charts of CloudWatch Statistics';
$output_message = "Charts of CloudWatch Statistics from ${start} to ${end}";

// Output the HTML
include 'include/statistics.html.php';

exit(0);
?>
