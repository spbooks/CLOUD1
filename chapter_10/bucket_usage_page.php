<?php
/*
 * bucket_usage_page.php 
 *
 *  Retrieve bucket usage statistics from SimpleDB and show them in a 
 *  simple table.
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

//error_reporting(E_ALL);

require_once('sdk.class.php');
require_once('include/book.inc.php');

// Set parameter to display
$usage = "DataTransfer-Out-Bytes";

// Create the S3 and SimpleDB access objects
$s3  = new AmazonS3();
$sdb = new AmazonSDB();

// Get list of S3 buckets
$buckets = $s3->get_bucket_list();

// Get today's date, which is also last day for query
$today   = date_create("now");
$lastDay = $today->format("Y-m-d");

// Form list of last 7 days in form YYYY-MM-DD
$days = array();
for ($i = 0; $i < 7; $i++)
{
  date_modify($today, "-1 day");
  $days[] = $today->format("Y-m-d");
}

// Get first day for query
$firstDay = $days[6];

$rows = array();
// Generate data for each table row
foreach ($buckets as $bucket)
{
  // Get usage for entire date range for this bucket
  $dailyUsage = GetUsage($sdb, $usage, $bucket, $firstDay, $lastDay);

  if (count($dailyUsage) > 0)
  {
    $rows[$bucket] = array();

    foreach ($days as $day)
    {
      if (isset($dailyUsage[$day]))
      {
        $rows[$bucket][] = $dailyUsage[$day];
      }
      else
      {
        $rows[$bucket][] = '';
      }
    }
    $rows[$bucket][] = array_sum($dailyUsage);
  }
}

// create a page header and an explanatory message
$output_title = 'Chapter 9 Sample - S3 Per-Bucket, Per-Day Outbound Data Transfer';
$output_message = "Table of S3 usage Statistics";

// Output the HTML
include 'include/statistics.html.php';

exit(0);

/*
 * GetUsage -
 * 
 *  Get the given type of S3 usage for the given bucket
 *  over the given day range and return it.
 */

function GetUsage($sdb, $usage, $bucket, $firstDay, $lastDay)
{
  // Create the query
  $query =
    "select StartTime, UsageValue "    .
    " from " . BOOK_AWS_USAGE_DOMAIN   .
    " where"                           .
    " Service='AmazonS3' and "         .
    " StartTime >= '${firstDay}' and " .
    " StartTime <= '${lastDay}' and "  .
    " Resource='${bucket}' and "       .
    " UsageType='${usage}'";

  // Query for the data
  $res = $sdb->select($query);

  // Check the result
  if (!$res->isOK())
  {
    return null;
  }

  // Build array of results
  $dailyUsage = array();
  foreach ($res->body->SelectResult->Item as $item)
  {
    $attrs = getItemAttributes($item);
    $startTime = substr($attrs['StartTime'], 0, 10);
    $usage     = $attrs['UsageValue'];

    $dailyUsage[$startTime] = $usage;
  }

  return $dailyUsage;
}
?>
