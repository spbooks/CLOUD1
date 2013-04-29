<?php
/*
 * cloudlist.php
 *
 *	Very simple classified ad system. This file is
 *	the home page, a list of cities.
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

// Get city and state from request
if (isset($_GET['city'])  &&
    isSet($_GET['state']) &&
    preg_match("/^[A-Za-z\+ ]{1,}$/", $_GET['city']) &&
    preg_Match("/^[A-Z]{2}$/",        $_GET['state']))
{
  $currentCity  = urldecode($_GET['city']);
  $currentState = urldecode($_GET['state']);
}
else
{
  $currentCity  = null;
  $currentState = null;
}

// Create access object
$sdb = new AmazonSDB();

// Fetch city list
$cities = getCities($sdb);

// If City and State supplied, generate list of items
$itemCat = array();
if ($currentCity != '' && $currentState != '')
{
  // Fetch list of items
  $items = getItems($sdb, $currentCity, $currentState);

  // Reorganize by category
  foreach ($items as $key => $attrs)
  {
    $category = $attrs['Category'];
    if (!isset($itemCat[$category]))
    {
      $itemCat[$category] = array();
    }
    $itemCat[$category][$key] = $attrs;
  }
}

include 'include/cloudlist.html.php'
?>
