#!/usr/bin/php
<?php
/*
 * ch7_batch_insert_items.php
 *
 *      Insert a batch of items into a SimpleDB domain.
 *
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

// Create the SimpleDB access object
$sdb = new AmazonSDB();

// Create array of items for batch insertion
$items = array();

// Insert an entry for every PHP file in the current directory
$dir = opendir(".");
while (($file = readdir($dir)) !== false)
{
  if (preg_match("/^[a-zA-Z0-9_-]*\.php$/", $file))
  {
    $data = file_get_contents($file);
    $hash = md5($data);
    $size = filesize($file);

    $items[$file] = array('Name' => $file,
                          'Hash' => $hash,
                          'Size' => sprintf("%08s", $size));
  }

  // Insert the batch when it has 25 items
  if (count($items) == 25)
  {
    WriteBatch($sdb, $items);
    $items = array();
  }
}
closedir($dir);

// Insert final batch
if (count($items) > 0)
{
  WriteBatch($sdb, $items);
}

function WriteBatch($sdb, &$items)
{
  $res = $sdb->batch_put_attributes(BOOK_FILE_DOMAIN, $items, true);

  if ($res->isOK())
  {
    print("Inserted " . count($items) . " items\n");
    return true;
  }
  else
  {
    $error = $res->body->Errors->Error->Message;
    print("Could not insert items: ${error}\n");
    return false;
  }
}
?>
