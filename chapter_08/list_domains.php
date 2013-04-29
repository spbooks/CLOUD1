#!/usr/bin/php
<?php
/*
 * list_domains.php
 *
 *	List SimpleDB domains.
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

// Create the SimpleDB access object
$sdb = new AmazonSDB();

// List the SimpleDB domains
$res = $sdb->list_domains();

// Check result
if (!$res->isOK())
{
  exit("List domain operation failed\n");
}

foreach ($res->body->ListDomainsResult->DomainName as $domainName)
{
  print($domainName . "\n");
}
exit(0);
?>
