#!/usr/bin/php
<?php
/*
 * add_category.php
 *
 *	Add one or more categories to CloudList.
 *
 *	Usage: add_category.php CATEGORY ...
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
if ($argc < 2)
{
  exit("Usage: " . $argv[0] . " CATEGORY ...\n");
}

// Create access object
$sdb = new AmazonSDB();

// Process each category
for ($i = 1; $i < $argc; $i++)
{
  $category = $argv[$i];

  // Form key
  $key = $category;

  // Form attributes
  $attrs = array('Category' => $category);

  // Insert item
  $res = $sdb->put_attributes(CL_CAT_DOMAIN, $key, $attrs, true);

  if ($res->isOK())
  {
    print("Added category ${category}\n");
  }
  else
  {
    $error = $res->body->Errors->Error->Message;
    print("Could not add category: ${error}\n");
  }
}
?>