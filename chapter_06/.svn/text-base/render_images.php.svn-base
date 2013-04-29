#!/usr/bin/php
<?php
/*
 * render_images.php
 *
 * Repeatedly pull an array of S3 image URLs from the
 * render queue. Fetch the images, thumbnail them, and
 * create a new image containing all of the thumbnails
 * in a grid. Upload the final image to S3 and display
 * the URL.
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

// Define image layout constants
define('BORDER_LEFT', 12);
define('BORDER_RIGHT', 12);
define('BORDER_TOP', 12);
define('BORDER_BOTTOM', 12);
define('IMAGES_ACROSS', 4);
define('IMAGES_DOWN', 4);
define('GAP_SIZE', 6);

// Create the SQS and S3 access objects
$sqs = new AmazonSQS();
$s3  = new AmazonS3();

// Get the queue URL
$queueURL_Render = $sqs->create_queue(RENDER_QUEUE)->body->CreateQueueResult->QueueUrl;

// Pull, process
while (true)
{
  // Pull the message from the queue
  $message = pullMessage($sqs, $queueURL_Render);

  if ($message != null)
  {
    // Extract message detail
    $messageDetail = $message['MessageDetail'];
    $receiptHandle = (string)$message['ReceiptHandle'];
    $imageKeys     = $messageDetail['Data'];
    $pageTitle     = $messageDetail['PageTitle'];

    print("Processing message with " .
    count($imageKeys)          .
    " images:\n");

    // Create destination image
    $outX = BORDER_LEFT + BORDER_RIGHT   +
            (IMAGES_ACROSS * THUMB_SIZE) +
            ((IMAGES_ACROSS - 1) * GAP_SIZE);

    $outY = BORDER_TOP + BORDER_BOTTOM +
            (IMAGES_DOWN * THUMB_SIZE) +
            ((IMAGES_DOWN - 1) * GAP_SIZE);

    $imageOut = ImageCreateTrueColor($outX, $outY);

    // Paint the image white
    ImageFill($imageOut, 0, 0,
        ImageColorAllocate($imageOut, 255, 255, 255));

    // Draw a border in destination image
    ImageRectangle($imageOut, 0, 0,
       $outX - 1, $outY - 1,
       ImageColorAllocate($imageOut, 0, 0, 0));

    // Do the work
    $nextX = BORDER_LEFT;
    $nextY = BORDER_TOP;

    foreach ($imageKeys as $imageKey)
    {
      // Fetch the image
      print("  Fetch image '${imageKey}'\n");
      $image = $s3->get_object(BOOK_BUCKET, $imageKey);

      // Convert it to GD format
      $imageBits = ImageCreateFromString($image->body);

      // Copy it to proper spot in the destination
      print("  Render image at ${nextX}, ${nextY}\n");
      ImageCopy($imageOut, $imageBits, $nextX, $nextY,
          0, 0, ImageSx($imageBits), ImageSy($imageBits));

      // Draw a border around it
      ImageRectangle($imageOut, $nextX, $nextY,
         $nextX + ImageSx($imageBits),
         $nextY + ImageSy($imageBits),
         ImageColorAllocate($imageOut, 0, 0, 0));

      // Update position for next image
      $nextX += THUMB_SIZE + GAP_SIZE;
      if (($nextX + THUMB_SIZE) > $outX)
      {
        $nextX = BORDER_LEFT;
        $nextY += THUMB_SIZE + GAP_SIZE;
      }
    }

    // Get the bits of the destination image
    $imageFileOut = tempnam('/tmp', 'aws') . '.png';
    ImagePNG($imageOut, $imageFileOut, 0);
    $imageBitsOut = file_get_contents($imageFileOut);
    unlink($imageFileOut);

    // Store the final image in S3
    $key = 'page_image_' . md5($pageTitle) . '.png';

    if (uploadObject($s3, BOOK_BUCKET, $key, $imageBitsOut,
		     AmazonS3::ACL_PUBLIC))
    {
      print("  Stored final image in S3 using key '${key}'\n");

      print_r($messageDetail['History']);

      // Delete the message
      $sqs->delete_message($queueURL_Render, $receiptHandle);
      print("  Deleted message from render queue\n");
    }

    print("\n");
  }
}
?>
