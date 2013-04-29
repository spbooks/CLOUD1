#!/usr/bin/php
<?php
/*
 * add_item.php
 *
 *  Add an item to CloudList.
 *
 *  Usage: add_item.php CITY STATE DATE PRICE CATEGORY "TITLE" "DESCRIPTION" [IMAGE_URL]
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

require_once('cloudfusion.class.php');
require_once('include/cloudfunctions.inc.php');

// Check arguments
if (($argc < 8) || ($argc > 9))
{
  exit("Usage: " . $argv[0] .
       " CITY STATE DATE PRICE CATEGORY \"TITLE\" \"DESCRIPTION\" [IMAGEURL]\n");
}

// Get item info
$city        = $argv[1];
$state       = $argv[2];
$date        = $argv[3];
$price       = $argv[4];
$category    = $argv[5];
$title       = $argv[6];
$description = $argv[7];
$imageURL    = null;

if ($argc > 8)
{
  $imageURL = $argv[8];
}

// Create access objects
$s3  = new AmazonS3();
$sdb = new AmazonSDB();

if (addCloudListItem($sdb, $s3,
         $city, $state, $date, $price,
         $category, $title, $description,
         $imageURL))
{
  print("Added item ${title} in ${city}, ${state}\n");
}
else
{
  print("Could not add item!\n");
}
?>
