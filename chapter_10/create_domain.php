#!/usr/bin/php
<?php
/*
 * create_domain.php
 *
 *	Create needed BOOK_AWS_USAGE_DOMAIN SimpleDB domain.
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
require_once('include/book.inc.php');

$domain = BOOK_SNAP_LOG_DOMAIN;

// Create the SimpleDB access object
$sdb = new AmazonSDB();

// Create the SimpleDB domain
$res = $sdb->create_domain($domain);

// Check result
if (!$res->isOK())
{
   exit("Create domain operation failed for domain ${domain}\n");
}

print("Domain ${domain} created.\n");

exit(0);
?>
