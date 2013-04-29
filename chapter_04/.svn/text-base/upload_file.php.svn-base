#!/usr/bin/php
<?php
/*
 * upload_file.php
 *
 *  Upload each file on the command line to the bucket in the
 *   first argument.
 *
 *   If bucket is "-" then the default bucket is used.
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

// Make sure that some arguments were supplied
if ($argc < 3)
{
  exit("Usage: " . $argv[0] . " bucket files...\n");
}

// Get Bucket argument
$bucket = ($argv[1] == '-') ? BOOK_BUCKET : $argv[1];

// Create the S3 access object
$s3  = new AmazonS3();

// Upload each file
for ($i = 2; $i < $argc; $i++)
{
  $file        = $argv[$i];
  $data        = file_get_contents($file);
  $contentType = guessType($file);

  if (uploadObject($s3, $bucket, $file, $data, AmazonS3::ACL_PUBLIC,
       $contentType))
  {
    print("Uploaded file '${file}' " .
    "to bucket '{$bucket}'\n");
  }
  else
  {
    exit("Could not "             .
   "upload file '${file}' " .
   "to bucket '{$bucket}'\n");
  }
}

exit(0);
?>