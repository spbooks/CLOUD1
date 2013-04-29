#!/usr/bin/php
<?
/*
 * ch8_delete_usage.php
 *
 *	Delete all of the AWS usatged data from the SimpleDB domain.
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
require_once('book.php');

// Create the SimpleDB access object
$sdb = new AmazonSDB();

// Set list of attributes to delete
$attrs = array('ModTime', 'Flavor');

// Query for each item
$next = null;
do
{
  $attrs = ($next == null) ? null : array('NextToken' => $next);
  $res1 = $sdb->select("select * from " . BOOK_AWS_USAGE_DOMAIN, $attrs);
  $next  = (string) $res1->body->SelectResult->NextToken;

  if ($res1->isOK())
  {
    foreach ($res1->body->SelectResult->Item as $item)
    {
      $itemName = $item->Name;

      // Get list of attributes
      $attrs = array_keys(getItemAttributes($item));

      // Delete the attributes
      $res2 = $sdb->delete_attributes(BOOK_AWS_USAGE_DOMAIN, $itemName, $attrs);

      if ($res2->isOK())
      {
	print("Deleted item $itemName\n");
      }
      else
      {
	$error = $res2->body->Errors->Error->Message;
	print("Could not delete item: ${error}\n");
      }
    }
  }
  else
  {
    $error = $res1->body->Errors->Error->Message;
    exit("Could not run query: ${error}\n");
  }
}
while ($next != null);

?>
