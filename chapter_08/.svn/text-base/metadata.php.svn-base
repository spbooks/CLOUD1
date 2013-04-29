#!/usr/bin/php
<?php
/*
 * metadata.php
 *
 *  Display the metadata for each of the SimpleDB domains.
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

foreach (array(BOOK_FILE_DOMAIN,
         BOOK_FEED_DOMAIN,
         BOOK_FEED_ITEM_DOMAIN) as $domain)
{
  $res = $sdb->domain_metadata($domain);

  // Check result
  if ($res->isOK())
  {
    $metadata = $res->body->DomainMetadataResult;

    $itemCount           = (int) $metadata->ItemCount;
    $attributeNameCount  = (int) $metadata->AttributeNameCount;
    $attributeValueCount = (int) $metadata->AttributeValueCount;
    $itemNamesSize       = (int) $metadata->ItemNamesSizeBytes;
    $attributeNamesSize  = (int) $metadata->AttributeNamesSizeBytes;
    $attributeValuesSize = (int) $metadata->AttributeValuesSizeBytes;

    printf($domain . ":\n" . 
     "\tItem Count:      " .
     number_format($itemCount)           . "\n" . 
     "\tAttrs:           " .
     number_format($attributeNameCount)  . "\n" .
     "\tValues:          " .
     number_format($attributeValueCount) . "\n" . 
     "\tName Size:       " .
     number_format($itemNamesSize)       . "\n" . 
     "\tAttr Name Size:  " .
     number_format($attributeNamesSize)  . "\n" . 
     "\tAttr Value Size: " .
     number_format($attributeValuesSize) . "\n" .
     "\n");
  }
}
exit(0);
?>
