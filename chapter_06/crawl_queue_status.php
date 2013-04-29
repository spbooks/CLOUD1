#!/usr/bin/php
<?php
/*
 * crawl_queue_status.php
 *
 * Display the number of items in each of the crawl queues.
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

// Create the SQS access object
$sqs = new AmazonSQS();

// Form list of queues
$queues = array(URL_QUEUE, PARSE_QUEUE, IMAGE_QUEUE, RENDER_QUEUE);

// Titles
$underlines = '';
foreach ($queues as $queueName)
{
  printf("%-12s  ", $queueName);
  $underlines .= str_repeat('-', strlen($queueName)) .
                 str_repeat(' ', 12 - strlen($queueName)) . "  ";
}
print("\n");
print($underlines . "\n");

// Report on each queue
foreach ($queues as $queueName)
{
  $res = $sqs->create_queue($queueName);
  if ($res->isOK())
  {
    $size     = $sqs->get_queue_size($res->body->CreateQueueResult->QueueUrl);
    printf("%-12s  ", number_format($size));
  }
}
print("\n");

exit(0);
?>
