#!/usr/bin/php
<?php
/*
 * create_queues.php
 *
 * Create queues as specified in the command line
 * arguments.
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

// Make sure that at least one argument was supplied
if (count($argv) < 2)
{
  exit("Usage: " . $argv[0] . " QUEUE...\n");
}

// Create the SQS access object
$sqs = new AmazonSQS();

// Process each argument
for ($i = 1; $i < count($argv); $i++)
{
  $queue = $argv[$i];

  $res = $sqs->create_queue($queue);

  if ($res->isOK())
  {
    print("Created queue '${queue}'\n");
  }
  else
  {
    $error = (string) $res->body->Error->Message;
    print("Could not create queue '${queue}': ${error}.\n");
  }
}

exit(0);
?>
