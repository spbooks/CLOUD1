<?php
/*
 * add_form.php
 *
 *  Form to add a new item.
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

// Create access objects
$s3  = new AmazonS3();
$sdb = new AmazonSDB();

// Handle form submission
if (isset($_POST['formsubmit']))
{
  // Get submitted values
  $stateCity   = $_POST['statecity'];
  $price       = $_POST['price'];
  $category    = $_POST['category'];
  $title       = $_POST['title'];
  $description = $_POST['description'];

  // Use today's date
  $date = date('Y-m-d');

  // Split StateCity
  $state = substr($stateCity, 0, 2);
  $city  = substr($stateCity, 3);

  // Process image if supplied
  if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name']))
  {
    $imagePath = $_FILES['image']['tmp_name'];
  }
  else
  {
    $imagePath = null;
  }

  // Insert item
  $success = addCloudListItem($sdb, $s3,
           $city, $state, $date, $price,
           $category, $title, $description,
           $imagePath);
  
  // Output thank you message
  include 'include/addthanks.html.php';
  exit(0);
}
else
{
  // Get cities and categories
  $cities     = getCities($sdb);
  $categories = getCategories($sdb);
  
  // Output form
  include 'include/addform.html.php';
  exit(0);
}

?>