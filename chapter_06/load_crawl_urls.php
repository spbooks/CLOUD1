#!/usr/bin/php
<?php
/*
 * load_crawl_urls.php
 *
 * Load the given URLs into the the URL_QUEUE.
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

// Make sure that at least one argument was given
if ($argc < 2)
{
  exit('Usage: ' . $argv[0] . " URL...\n");
}

// Create the SQS access object
$sqs = new AmazonSQS();

// Get the Queue URL
$queueURL = $sqs->create_queue(URL_QUEUE)->body->CreateQueueResult->QueueUrl;

// Load each URL
for ($i = 1; $i < $argc; $i++)
{
  // Create message
  $histItem = array('Posted by ' . $argv[0] . ' at ' . date('c'));

  $message  = json_encode(array('Action' => 'FetchPage',
        'Origin' => $argv[0],
        'Data'   => $argv[$i],
        'History' => $histItem));
  // Post message
  $res = $sqs->send_message($queueURL, $message);

  if ($res->isOK())
  {
    print("Posted '${message}' to queue " . URL_QUEUE . "\n");
  }
  else
  {
    $error = $res->body->Error->Message;
    print("Could not post message to queue: ${error}\n");
  }
}
?>
