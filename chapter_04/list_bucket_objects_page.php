<?php
/*
 * list_bucket_objects_page.php
 *
 *	Generate a web page with a list of the S3 objects in a bucket,
 *	with links to each item.
 *
 *	If the Bucket parameter is given, use that bucket name,
 *	otherwise use the default bucket.
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

// Set up page title


// Create the S3 access object
$s3 = new AmazonS3();

// Get list of all objects in bucket
$objects = getBucketObjects($s3, $bucket);

$fileList = array();
foreach ($objects as $object)
{
  $key = $object->Key;
  $url = $s3->get_object_url($bucket, $key);
  $fileList[] = array('url' => $url, 'name' => $key, 'size' => number_format((int)$object->Size));
}

// create a page header and an explanatory message
$output_title = "Chapter 4 Sample - List of S3 Objects in Bucket '${bucket}'";
$output_message = "A simple HTML table displaying of all the objects in the '${bucket}' bucket.";

// Output the HTML
include 'include/list_bucket_objects.html.php';

exit(0);
?>