#!/usr/bin/php
<?php
/*
 * query_usage_cmd.php
 *
 *	Query the AWS usage SimpleDB domain using a where
 *	clause specified on the command line.
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
require_once('include/book.inc.php');

// Set the query
$query = "select * from " . BOOK_AWS_USAGE_DOMAIN;

if ($argc > 1)
{
  $query .= " where ";

  for ($i = 1; $i < $argc; $i++)
  {
    $query .= ' ' . $argv[$i] . ' ';
  }
}

print("Final query: ${query}\n");

// Create the SimpleDB access object
$sdb = new AmazonSDB();

$recordCount = 0;
$totalUsage  = 0;

// Query the SimpleDB domain
$next = null;
do
{
  $attrs = ($next == null) ? null : array('NextToken' => $next);
  $res   = $sdb->select($query, $attrs);
  $next  = (string) $res->body->SelectResult->NextToken;

  // Check result
  if (!$res->isOK())
  {
    exit("Select operation failed\n");
  }

  // Display results
  foreach ($res->body->SelectResult->Item as $item)
  {
    $recordCount++;
    foreach ($item->Attribute as $attribute)
    {
      print($attribute->Name . ": " . $attribute->Value . ", ");

      if ($attribute->Name == 'UsageValue')
      {
        $totalUsage += (int) $attribute->Value;
      }
    }
    print("\n");
  }
}
while ($next != null);

print("Total records: " . number_format($recordCount) . "\n");
print("Total usage:   " . number_format($totalUsage)  . "\n");

exit(0);
?>
