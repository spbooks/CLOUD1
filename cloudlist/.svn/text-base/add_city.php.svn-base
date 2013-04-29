#!/usr/bin/php
<?php
/*
 * add_city.php
 *
 *	Add a city to CloudList.
 *
 *	Usage: add_city.php CITY STATE
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
if ($argc < 3)
{
  exit("Usage: " . $argv[0] . " CITY STATE\n");
}

// Get city and state
$city  = $argv[1];
$state = $argv[2];

// Form key
$key = $state . '_' . $city;

// Form attributes
$attrs = array('City'	=> $city,
	       'State'  => $state);

// Create access object
$sdb = new AmazonSDB();
       
// Insert item
$res = $sdb->put_attributes(CL_CITY_DOMAIN, $key, $attrs, true);

if ($res->isOK())
{
  print("Added city ${city} in ${state}\n");
}
else
{
  $error = $res->body->Errors->Error->Message;
  print("Could not add city: ${error}\n");
}
?>
