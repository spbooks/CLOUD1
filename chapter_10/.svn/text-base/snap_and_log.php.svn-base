#!/usr/bin/php
<?php
/*
 * snap_and_log.php -
 *
 * 	Generate a snapshot of each EBS volume mentioned on the
 *	command line, with logging of the snapshot id and a
 *	message to SimpleDB.
 *
 * Usage: ch8_snap_and_log.php "message" VOLUMEID...
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

// Check arguments
if ($argc < 3)
{
  exit("Usage: " . $argv[0] . " \"message\" VOLUMEID...\n");
}

// Get message
$message = $argv[1];

// Create access objects
$sdb = new AmazonSDB();
$ec2 = new AmazonEC2();

// Process each volume
for ($i = 2; $i < $argc; $i++)
{
  $volId = $argv[$i];

  // Create snapshot
  $res1 = $ec2->create_snapshot($volId, $message);
  
  if ($res1->isOK())
  {
    $snapId    = $res1->body->snapshotId;
    $startTime = $res1->body->startTime;

    $key = $volId . '_' . $startTime;

    $attrs = array('VolId'     => $volId,
		   'Message'   => $message,
		   'StartTime' => $startTime);

    $res2 = $sdb->put_attributes(BOOK_SNAP_LOG_DOMAIN, $key, $attrs, true);
  }
}
exit(0);
?>
