#!/usr/bin/php
<?php
/*
 * post_queue.php
 *
 * The first argument is the queue name. The second and subsequent
 * items are messages, which are posted to the queue.
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

// Make sure that at least two arguments were supplied
if ($argc < 3)
{
  exit("Usage: " . $argv[0] . " QUEUE_NAME ITEM...\n");
}

// Create the SQS access object
$sqs = new AmazonSQS();
$queueName = $argv[1];

// Get URL for queue
$queueURL = $sqs->create_queue($queueName)->body->CreateQueueResult->QueueUrl;

// Put each of the items into the queue
for ($i = 2; $i < $argc; $i++)
{
  $message = $argv[$i];

  $res = $sqs->send_message($queueURL, $message);

  if ($res->isOK())
  {
    print("Posted '${message}' to queue '${queueName}'\n");
  }
  else
  {
    $error = $res->body->Error->Message;
    print("Could not post message to queue: ${error}\n");
  }
}

exit(0);
?>
