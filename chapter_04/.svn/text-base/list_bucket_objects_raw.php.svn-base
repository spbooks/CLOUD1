#!/usr/bin/php
<?php
/*
 * list_bucket_objects_raw.php
 *
 * Display the raw bucket data returned by list_objects
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

// Create the S3 access object
$s3 = new AmazonS3();

// List the bucket
$res = $s3->list_objects(BOOK_BUCKET);

// Display the resulting object tree
print_r($res);
?>