<?php
/*
 * list_bucket_objects_page_thumbs.php
 *
 *	Generate a web page with a list of the S3 objects in
 *	a bucket, with links to each item and embedded
 *	thumbnails, if they exist in a bucket name ending
 *	with the THUMB_BUCKET_SUFFIX.
 *
 *	If the Bucket parameter is given, use that bucket name,
 *	otherwise use the default bucket.
 *
 *	If CloudFront distributions exist for the thumbnails
 *	or the images, reference those instead of the S3 buckets.
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

// Get parameters
$bucket = isset($_GET['bucket']) ? $_GET['bucket'] : BOOK_BUCKET;

// Set name of bucket for thumbnails
$bucketThumbs = $bucket . THUMB_BUCKET_SUFFIX;

// Create the S3 and CloudFront access objects
$s3 = new AmazonS3();
$cf = new AmazonCloudFront();

// Find distributions for the two buckets
$dist       = findDistributionForBucket($cf, $bucket);
$thumbsDist = findDistributionForBucket($cf, $bucketThumbs);

// Get list of all objects in main bucket
$objects = getBucketObjects($s3, $bucket);

// Get list of all objects in thumbnail bucket
$objectThumbs = getBucketObjects($s3, $bucketThumbs);

/*
 * Create associative array of available thumbnails,
 * mapping object key to thumbnail URL (either S3
 * or CloudFront).
 */

$thumbs = array();
foreach ($objectThumbs as $objectThumb)
{
  $key = (string) $objectThumb->Key;

  if ($thumbsDist != null)
  {
    $thumbs[$key] = 'http://' . $thumbsDist->DomainName . "/" . $key;
  }
  else
  {
    $thumbs[$key] = $s3->get_object_url($bucketThumbs, $key);
  }
}

$fileList = array();
foreach ($objects as $object)
{
  $key = (string) $object->Key;

  if ($dist != null)
  {
    $url = 'http://' . $dist->DomainName . "/" . $key;
  }
  else
  {
    $url = $s3->get_object_url($bucket, $key);
  }

  $thumbURL = isset($thumbs[$key]) ? $thumbs[$key] : '';
  $fileList[] = array('thumb' => $thumbURL, 'url' => $url, 'name' => $key, 'size' => number_format((int)$object->Size));
}

// create a page header and an explanatory message
$output_title = "Chapter 4 Sample - List of S3 Objects in Bucket '${bucket}'";
$output_message = "A simple HTML table displaying of all the objects in the '${bucket}' bucket with thumbnails.";

// Output the HTML
include 'include/list_bucket_objects_thumbs.html.php';

exit(0);
?>