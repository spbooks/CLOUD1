#!/usr/bin/php
<?php
/*
 * ec2_setup.php
 *
 * Launch an EC2 instance, allocate and assign it a 
 * public IP address, and then create and attach a
 * pair of EBS volumes.
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

// Create the EC2 access object
$ec2 = new AmazonEC2();

// Run an instance
$options = array('KeyName' => "Jeff's Keys",
		 'InstanceType' => "m1.small");

$res = $ec2->run_instances("ami-48aa4921", 1, 1, $options);

if (!$res->isOK())
{
  exit("Could not launch instance: "      .
       $res->body->Errors->Error->Message . "\n");
}

// Get the Id and Availability Zone of the instance
$instances        = $res->body->instancesSet;
$instanceId       = (string)$instances->item->instanceId;
$availabilityZone = (string)$instances->item->placement->availabilityZone;

print("Launched instance ${instanceId} " .
      "in availability zone ${availabilityZone}.\n");

// Wait for the instance's state to change to running
// before attaching volumes
do
{
  $options    = array('InstanceId.1' => $instanceId);
  $res       = $ec2->describe_instances($options);
  $instances = $res->body->reservationSet->item->instancesSet;
  $state     = $instances->item->instanceState->name;
  $running   = ($state == 'running');

  if (!$running)
  {
    print("Instance is currently in " .
	  "state ${state}, waiting 10 seconds\n");
    sleep(10);
  }
}
while (!$running);

// Allocate an Elastic IP address
$res = $ec2->allocate_address();
if (!$res->isOK())
{
  exit("Could not allocate public IP address.\n");
}

// Get the allocated Elastic IP address
$publicIP = (string)$res->body->publicIp;
print("Assigned IP address ${publicIP}.\n");

// Associate the Elastic IP address with the instance
$res = $ec2->associate_address($instanceId, $publicIP);
if (!$res->IsOK())
{
  exit("Could not associate IP address ${publicIP} " .
       "with instance ${instanceId}.\n");
}

print("Associated IP address ${publicIP} " .
      "with instance ${instanceId}.\n");

// Create two EBS volumes in the instance's availability zone
$res1 = $ec2->create_volume($availabilityZone, array('Size' => 1));
$res2 = $ec2->create_volume($availabilityZone, array('Size' => 1));

if (!$res1->isOK() || !$res2->isOK())
{
  exit("Could not create EBS volumes.\n");
}

// Get the volume Ids
$volumeId1 = (string)$res1->body->volumeId;
$volumeId2 = (string)$res2->body->volumeId;

print("Created EBS volumes ${volumeId1} and ${volumeId2}.\n");

// Attach the volumes to the instance as /dev/sdf and /dev/sdg
$res1 = $ec2->attach_volume($volumeId1, $instanceId, '/dev/sdf');
$res2 = $ec2->attach_volume($volumeId2, $instanceId, '/dev/sdg');

if (!$res1->isOK() || !$res2->isOK())
{
  exit("Could not attach EBS volumes "  .
       "${volumeId1} and ${volumeId2} " .
       "to instance ${instanceId}.\n");
}

print("Attached EBS volumes ${volumeId1} and ${volumeId2} " .
      "to instance ${instanceId}.\n");

?>
