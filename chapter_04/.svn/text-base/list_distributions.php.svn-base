#!/usr/bin/php
<?php
/*
 * ch3_list_distributions.php
 *
 *	List CloudFront distributions.
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

// Create the CloudFront access object
$cf = new AmazonCloudFront();

// Retrieve list of CloudFront distributions
$res = $cf->list_distributions();

if (!$res->isOK())
{
  exit("Could not retrieve list of CloudFront distributions\n");
}

$distributions = $res->body->DistributionSummary;

printf("%-16s %-32s %-40s\n", "ID", "Domain Name", "Origin");
printf("%'=-16s %'=-32s %'=40s\n", "", "", "");

// Display list of distributions
foreach ($distributions as $distribution)
{
  $id         = $distribution->Id;
  $domainName = $distribution->DomainName;
  $origin     = $distribution->S3Origin->DNSName;

  printf("%-16s %-32s %-40s\n", $id, $domainName, $origin);
}

?>