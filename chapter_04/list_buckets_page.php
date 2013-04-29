<?php
/*
 * list_buckets_page.php
 *
 *	Generate a web page with a list of S3 buckets.
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

// Create the S3 access object
$s3 = new AmazonS3();

// Retrieve list of S3 buckets
$buckets = $s3->get_bucket_list();

// create a page header and an explanatory message
$output_title = 'Chapter 4 Sample - List of S3 Buckets';
$output_message = 'A simple HTML list of your S3 Buckets';

// Output the HTML
include 'include/list_buckets.html.php';

exit(0);
?>
