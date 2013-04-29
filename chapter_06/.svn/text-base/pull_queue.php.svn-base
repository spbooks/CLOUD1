#!/usr/bin/php
<?php
/*
 * pull_queue.php
 *
 *	Repeatedly pull items from the queue URL given as
 *	the argument.
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

// Make sure that an argument was supplied
if ($argc != 2)
{
  exit("Usage: " . $argv[0] . " QUEUE_NAME\n");
}

// Create the SQS access object
$sqs = new AmazonSQS();
$queueName = $argv[1];

// Get URL for queue
$queueURL = $sqs->create_queue($queueName)->body->CreateQueueResult->QueueUrl;

// Poll for new items
while (true)
{
  $res = $sqs->receive_message($queueURL);

  if ($res->isOK())
  {
    if (isset($res->body->ReceiveMessageResult->Message))
    {
      $message       = $res->body->ReceiveMessageResult->Message;
      $messageBody   = $message->Body;
      $receiptHandle = (string)$message->ReceiptHandle;

      print("Message: '${messageBody}'\n");

      $sqs->delete_message($queueURL, $receiptHandle);
    }
    else
    {
      sleep(1);
    }
  }
  else
  {
    $error = $res->body->Error->Message;
    print("Could not pull message from queue: ${error}\n");
  }
}

exit(0);
?>
