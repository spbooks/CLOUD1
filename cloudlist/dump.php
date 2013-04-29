#!/usr/bin/php
<?php
/*
 * dump.php -
 *
 *	Dump out all cities and categories, and
 *	items from one city.
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

// Create access object
$sdb = new AmazonSDB();

// List cities
print("Cities\n");
print("======\n");
$cities = getCities($sdb);
print_r($cities);

// List categories
print("Categories\n");
print("==========\n");
$categories = getCategories($sdb);
print_r($categories);

// Dump items
print("Items\n");
print("=====\n");
$items = getItems($sdb, "Bethesda", "MD");
print_r($items);

?>
