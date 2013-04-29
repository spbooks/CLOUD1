#!/usr/bin/php
<?
/*
 * ch5_list_queues.php
 *
 * List first 1000 SQS queues.
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

require_once('tarzan.class.php');
require_once('book.php');

// Create the SQS access object
$sqs = new AmazonSQS();

// Retrieve list of SQS queues
$res = $sqs->list_queues();

if ($res->isOK())
{
  $queues = $res->body->ListQueuesResult->QueueUrl;
  for ($i = 0; $i < count($queues); $i++)
  {
    print($queues[$i] . "\n");
  }
}
else
{
  print("Could not retrieve list of SQS queues\n");
}

exit(0);
?>
