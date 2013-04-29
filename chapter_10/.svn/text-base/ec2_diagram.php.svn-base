#!/usr/bin/php
<?php
/*
 * ec2_diagram.php
 *
 * Fetch information about all of the caller's EC2 instances, 
 * EBS volumes, and EBS volume snapshots and use it to draw
 * a system diagram. Store the diagram in S3 and display
 * its URL.
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

// Define shape geometry
define('LEFT_MARGIN',    16);
define('RIGHT_MARGIN',    16);
define('TOP_MARGIN',    16);
define('BOTTOM_MARGIN',    16);
define('TEXT_MARGIN',    4);
define('TEXT_LINE_HEIGHT',  14);
define('INSTANCE_WIDTH',  128);
define('INSTANCE_HEIGHT',  64);
define('VOLUME_WIDTH',    96);
define('VOLUME_HEIGHT',    64);
define('SNAP_WIDTH',    96);
define('SNAP_HEIGHT',    64);
define('VOLUME_GAP',    16);
define('SNAP_GAP',    16);

// Create the access objects
$ec2 = new AmazonEC2();
$s3  = new AmazonS3();

// Get the EC2 instances, EBS volumes, and snapshots
$resInstances = $ec2->describe_instances();
$resVolumes   = $ec2->describe_volumes();
$resSnapshots = $ec2->describe_snapshots();

// Check for errors
if (!$resInstances->isOK() ||
    !$resVolumes->isOK()   ||
    !$resSnapshots->isOK())
{
  exit("Error retrieving system information.");
}

// Create an object to represent the EC2 region
$region = new Region('us-east-1');

// Add each EC2 instance to the region
foreach ($resInstances->body->reservationSet->item as $itemSet)
{
  foreach ($itemSet->instancesSet->item as $item)
  {
    $instanceId       = (string) $item->instanceId;
    $state            = (string) $item->instanceState->name;
    $instanceType     = (string) $item->instanceType;
    $availabilityZone = (string) $item->placement->availabilityZone;

    if ($state != 'terminated')
    {
      $region->AddInstance(new Instance($availabilityZone,
          $instanceId,
          $state,
          $instanceType));
    }
  }
}

// Add each attached EBS volume to the region
foreach ($resVolumes->body->volumeSet->item as $item)
{
  $volumeId         = (string) $item->volumeId;
  $size             = (string) $item->size;
  $availabilityZone = (string) $item->availabilityZone;
  
  if ($item->attachmentSet->item)
  {
    $instanceId = (string) $item->attachmentSet->item->instanceId;
    $device     = (string) $item->attachmentSet->item->device;

    $region->AddVolume(new Volume($availabilityZone,
          $volumeId,
          $instanceId,
          $size,
          $device));
  }
}

// Add each snapshot to the region
foreach ($resSnapshots->body->snapshotSet->item as $item)
{
  $snapshotId = (string) $item->snapshotId;
  $volumeId   = (string) $item->volumeId;
  $startTime  = (string) $item->startTime;

  $region->AddSnapshot(new Snapshot($snapshotId,
            $volumeId,
            $startTime));
}

// Dump data structure for debugging
//print_r($region);

// Render the region into an image
$image = $region->Draw();

// Store the image in a local file
$imageOut = tempnam("/tmp", "aws") . ".gif";
ImageGIF($image, $imageOut);

// Retrieve the image's bits
$imageOutBits = file_get_contents($imageOut);
$imageKey     = 'ec2_diagram_' . date('Y_m_d_H_i_s') . '.gif';
if (uploadObject($s3, BOOK_BUCKET, $imageKey, $imageOutBits,
		 AmazonS3::ACL_PUBLIC, "image/gif"))
{
  $imageURL = $s3->get_object_url(BOOK_BUCKET, $imageKey);

  print("EC2 diagram is at ${imageURL}\n");
}

// Region - Representation of an AWS region
class Region
{
  var $name;
  var $instances;

  public function __construct($name)
  {
    $this->Name      = $name;
    $this->Instances = array();
  }

  public function AddInstance($instance)
  {
    $this->Instances[$instance->InstanceId()] = $instance;
  }

  public function AddVolume($volume)
  {
    $this->Instances[$volume->InstanceId()]->AddVolume($volume);
  }

  public function AddSnapshot($snapshot)
  {
    // Find the instance containing the snapshot's volume
    foreach ($this->Instances as $instance)
    {
      if ($instance->HasVolume($snapshot->VolumeId))
      {
        $instance->AddSnapshot($snapshot);
      }
    }
  }

  public function Draw()
  {
    // Figure out how large of an image is needed
    $totalW = 0;
    $totalH = 0;

    foreach ($this->Instances as $instance)
    {
      $thisW = $instance->GetDrawWidth();
      $thisH = $instance->GetDrawHeight();

      $totalW = max($totalW, $thisW);
      $totalH += $thisH;
    }
print_r($totalW);
print_r($totalH);
    // Create white image with black border
    $image = ImageCreate($totalW, $totalH);
    ImageFilledRectangle($image, 0, 0,
       $totalW - 1, $totalH - 1,
       ImageColorAllocate($image, 255, 255, 255));
    ImageRectangle($image, 0, 0,
       $totalW - 1, $totalH - 1,
       ImageColorAllocate($image, 0, 0, 0));

    // Draw each instance
    $startY = 0;
    foreach ($this->Instances as $instance)
    {
      $instance->Draw($image, 0, $startY);
      $startY += $instance->GetDrawHeight();
    }

    return $image;
  }
}

// Instance - Representation of an EC2 instance
class Instance
{
  var $availabilityZone;
  var $instanceId;
  var $state;
  var $instanceType;
  var $volumes;

  public function __construct($availabilityZone, $instanceId, $state, $instanceType)
  {
    $this->AvailabilityZone = $availabilityZone;
    $this->InstanceId       = $instanceId;
    $this->State            = $state;
    $this->InstanceType     = $instanceType;
    $this->Volumes          = array();
  }

  public function InstanceId()
  {
    return $this->InstanceId;
  }

  public function VolumeCount()
  {
    return count($this->Volumes);
  }
  
  public function AddVolume($volume)
  {
    $this->Volumes[$volume->VolumeId()] = $volume;
  }

  public function HasVolume($volumeId)
  {
    return isset($this->Volumes[$volumeId]);
  }

  public function AddSnapshot($snapshot)
  {
    $this->Volumes[$snapshot->VolumeId]->AddSnapshot($snapshot);
  }

  public function Draw($image, $startX, $startY)
  {
    // Outline rectangle for instance
    ImageRectangle($image,
       $startX + LEFT_MARGIN,
       $startY + TOP_MARGIN,
       $startX + LEFT_MARGIN + INSTANCE_WIDTH,
       $startY + TOP_MARGIN + INSTANCE_HEIGHT,
       ImageColorAllocate($image, 0, 0, 0));

    // Fill it in
    ImageFill($image,
        $startX + LEFT_MARGIN + 1,
        $startY + TOP_MARGIN + 1,
        ImageColorAllocate($image, 0x66, 0xff, 0xcc));

    // Draw instance label
    ImageString($image,
    2,
    $startX + LEFT_MARGIN + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN,
    "EC2 Instance",
    ImageColorAllocate($image, 0, 0, 0));

    // Draw instance id
    ImageString($image,
    2,
    LEFT_MARGIN + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN + TEXT_LINE_HEIGHT,
    $this->InstanceId,
    ImageColorAllocate($image, 0, 0, 0));

    // Draw instance type
    ImageString($image,
    2,
    $startX + LEFT_MARGIN + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN + (2 * TEXT_LINE_HEIGHT),
    $this->InstanceType,
    ImageColorAllocate($image, 0, 0, 0));

    // Draw each volume
    $startX += LEFT_MARGIN + INSTANCE_WIDTH;
    foreach ($this->Volumes as $volume)
    {
      $volume->Draw($image, $startX, $startY);
      $startX += VOLUME_GAP + VOLUME_WIDTH;
    }
  }

  // Return width of space needed to draw the instance
  public function GetDrawWidth()
  {
    $volumeCount  = $this->VolumeCount();

    return
      LEFT_MARGIN    +
      INSTANCE_WIDTH +
      ($volumeCount * (VOLUME_GAP + VOLUME_WIDTH)) +
      RIGHT_MARGIN;
  }

  // Return height of space needed to draw the instance
  public function GetDrawHeight()
  {
    $maxSnapCount = $this->MaxSnapCount();
    
    return
      TOP_MARGIN      +
      INSTANCE_HEIGHT +
      ($maxSnapCount * (SNAP_GAP + SNAP_HEIGHT)) +
      BOTTOM_MARGIN;
  }



  // Return maximum number of snapshots for any of instance's volumes
  public function MaxSnapCount()
  {
    $maxSnapCount = 0;
    foreach ($this->Volumes as $volume)
    {
      $snapCount    = $volume->SnapCount();
      $maxSnapCount = max($maxSnapCount, $snapCount);
    }

    return $maxSnapCount;
  }
}

// Volume - Representation of an EBS volume
class Volume
{
  var $availabilityZone;
  var $volumeId;
  var $instanceId;
  var $size;
  var $device;
  var $snapshots;

  public function __construct($availabilityZone, $volumeId, $instanceId, $size, $device)
  {
    $this->AvailabilityZone = $availabilityZone;
    $this->VolumeId         = $volumeId;
    $this->InstanceId       = $instanceId;
    $this->Size             = $size;
    $this->Device           = $device;
    $this->Snapshots        = array();
  }

  public function AddSnapshot($snapshot)
  {
    $this->Snapshots[] = $snapshot;
  }

  public function InstanceId()
  {
    return $this->InstanceId;
  }

  public function VolumeId()
  {
    return $this->VolumeId;
  }

  public function SnapCount()
  {
    return count($this->Snapshots);
  }

  public function Draw($image, $startX, $startY)
  {
    // Outline rectangle for volume
    ImageRectangle($image,
       $startX + VOLUME_GAP,
       $startY + TOP_MARGIN,
       $startX + VOLUME_GAP + VOLUME_WIDTH,
       $startY + VOLUME_HEIGHT + TOP_MARGIN,
       ImageColorAllocate($image, 0, 0, 0));

    // Fill it in
    ImageFill($image,
        $startX + VOLUME_GAP + 1,
        $startY + TOP_MARGIN + 1,
        ImageColorAllocate($image, 0xff, 0x66, 0xff));

    // Connect to previous volume or instance
    ImageLine($image,
        $startX,
        $startY + TOP_MARGIN + (VOLUME_HEIGHT / 2),
        $startX + VOLUME_GAP,
        $startY + TOP_MARGIN + (VOLUME_HEIGHT / 2),
        ImageColorAllocate($image, 128, 128, 128));

    // Draw volume label
    ImageString($image,
    2,
    $startX + VOLUME_GAP + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN,
    "EBS Volume",
    ImageColorAllocate($image, 0, 0, 0));

    // Draw volume id
    ImageString($image,
    2,
    $startX + VOLUME_GAP + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN + TEXT_LINE_HEIGHT,
    $this->VolumeId,
    ImageColorAllocate($image, 0, 0, 0));

    // Draw volume size
    ImageString($image,
    2,
    $startX + VOLUME_GAP + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN + (2 * TEXT_LINE_HEIGHT),
    (string) $this->Size . ' GB',
    ImageColorAllocate($image, 0, 0, 0));

    // Draw device
    ImageString($image,
    2,
    $startX + VOLUME_GAP + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN + (3 * TEXT_LINE_HEIGHT),
    $this->Device,
    ImageColorAllocate($image, 0, 0, 0));

    // Draw each snapshot
    $startY += TOP_MARGIN + VOLUME_HEIGHT;
    foreach ($this->Snapshots as $snapshot)
    {
      $snapshot->Draw($image, $startX, $startY);
      $startY += SNAP_HEIGHT + SNAP_GAP;
    }
  }
}

// Snapshot - Representation of a volume snapshot
class Snapshot
{
  var $snapshotId;
  var $volumeId;
  var $startTime;

  public function __construct($snapshotId, $volumeId, $startTime)
  {
    $this->SnapshotId = $snapshotId;
    $this->VolumeId   = $volumeId;
    $this->StartTime  = $startTime;
  }

  public function Draw($image, $startX, $startY)
  {
    // Outline rectangle for snap
    ImageRectangle($image,
       $startX + VOLUME_GAP,
       $startY + SNAP_GAP,
       $startX + VOLUME_GAP + SNAP_WIDTH,
       $startY + SNAP_HEIGHT + VOLUME_GAP,
       ImageColorAllocate($image, 0, 0, 0));

    // Fill it in
    ImageFill($image,
        $startX + VOLUME_GAP + 1,
        $startY + SNAP_GAP + 1,
        ImageColorAllocate($image, 0xff, 0xff, 0x99));

    // Connect to previous snapshot or volume
    ImageLine($image,
        $startX + SNAP_GAP + (SNAP_WIDTH / 2),
        $startY + 1,
        $startX + SNAP_GAP + (SNAP_WIDTH / 2),
        $startY + SNAP_GAP - 1,
        ImageColorAllocate($image, 0xff, 0, 0));

    // Draw snapshot label
    ImageString($image,
    2,
    $startX + SNAP_GAP + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN,
    "EBS Snapshot",
    ImageColorAllocate($image, 0, 0, 0));

    // Draw snapshot id
    ImageString($image,
    2,
    $startX + SNAP_GAP + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN + TEXT_LINE_HEIGHT,
    $this->SnapshotId,
    ImageColorAllocate($image, 0, 0, 0));

    // Draw snapshot date
    ImageString($image,
    2,
    $startX + SNAP_GAP + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN + (2 * TEXT_LINE_HEIGHT),
    substr($this->StartTime, 0, 10),
    ImageColorAllocate($image, 0, 0, 0));

    // Draw snapshot time
    ImageString($image,
    2,
    $startX + SNAP_GAP + TEXT_MARGIN,
    $startY + TOP_MARGIN + TEXT_MARGIN + (3 * TEXT_LINE_HEIGHT),
    substr($this->StartTime, 11, 8),
    ImageColorAllocate($image, 0, 0, 0));
  }
}
?>
