#!/usr/bin/php
<?php
/*
 * create_bucket.php
 *
 * Create an S3 bucket.
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

if ($argc != 2)
{
  exit("Usage: " . $argv[0] . " bucket name\n");
}

$bucket = ($argv[1] == '-') ? BOOK_BUCKET : $argv[1];

// Create the S3 access object
$s3 = new AmazonS3();

// Create an S3 bucket
$res = $s3->create_bucket($bucket, AmazonS3::REGION_US_E1);

// Report on status
if ($res->isOK())
{
  print("'${bucket}' bucket created\n");
}
else
{
  print("Error creating bucket '${bucket}'\n");
  print_r($res);
}
exit(0);
?>
