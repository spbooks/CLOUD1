#!/usr/bin/php
<?php
/*
 * thumbnail_bucket.php
 *
 *  Generate a thumbnail (small) version of every image found in
 *  bucket given as the first argument, and store it in the 
 *  bucket given as the second argument. Measure and display the 
 *  time taken to generate each thumbnail.
 *
 *  If the first argument is "-" the default bucket is used.
 *
 *  If the second argument is "-" the input bucket name with
 *  THUMB_BUCKET_SUFFIX appended is used.
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

// Make sure that the right number of arguments were supplied
if ($argc != 3)
{
  exit("Usage: " . $argv[0] . "in-bucket out-bucket\n");
}

// Get arguments
$bucketIn  = ($argv[1] == '-')
             ? BOOK_BUCKET
             : $argv[1];

$bucketOut = ($argv[2] == '-')
         ? $bucketIn . THUMB_BUCKET_SUFFIX
             : $argv[2];

// Confirm intent
print("Thumbnailing '${bucketIn}' to '${bucketOut}'\n");

// Create the S3 access object
$s3  = new AmazonS3();

// Get object list from input bucket
$objectsIn = getBucketObjects($s3, $bucketIn);

// Process each object. Generate thumbnails only for images
foreach ($objectsIn as $objectIn)
{
  $key = $objectIn->Key;
  print("Processing item '${key}':\n");

  if (substr(guessType($key), 0, 6) == "image/")
  {
    $startTime   = microtime(true);
    $dataIn      = $s3->get_object($bucketIn, $key);
    $endTime     = microtime(true);
    $contentType = guessType($key);

    printf("\tDownloaded from S3 in %.2f seconds.\n",
     ($endTime - $startTime));

    $startTime = microtime(true);
    $dataOut   = thumbnailImage($dataIn->body, $contentType);
    $endTime   = microtime(true);

    printf("\tGenerated thumbnail in %.2f seconds.\n",
     ($endTime - $startTime));

    $startTime = microtime(true);
    if (uploadObject($s3, $bucketOut, $key, $dataOut,
		     AmazonS3::ACL_PUBLIC, $contentType))
    {
      $endTime = microtime(true);
      
      printf("\tUploaded thumbnail to S3 in %.2f seconds.\n",
       ($endTime - $startTime));
    }
    else
    {
      print("\tCould not upload thumbnail.\n");
    }
  }
  else
  {
    print("\tSkipping - not an image\n");
  }
  print("\n");
}

?>