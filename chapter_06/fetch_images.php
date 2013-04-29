#!/usr/bin/php
<?php
/*
 * fetch_images.php
 *
 * Repeatedly pull an array of image URLs from the image 
 * queue. Fetch each image, create a thumbnail from it,
 * and store it in S3. Post a message containing the
 * S3 keys to the render queue.
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

// Create the SQS and S3 access objects
$sqs = new AmazonSQS();
$s3  = new AmazonS3();

// Get the Queue URLs
$queueURL_Image  = $sqs->create_queue(IMAGE_QUEUE)->body->CreateQueueResult->QueueUrl;
$queueURL_Render = $sqs->create_queue(RENDER_QUEUE)->body->CreateQueueResult->QueueUrl;

// Pull, process, post
while (true)
{
  // Pull the message from the queue
  $message = pullMessage($sqs, $queueURL_Image);

  if ($message != null)
  {
    // Extract message detail
    $messageDetail = $message['MessageDetail'];
    $receiptHandle = (string)$message['ReceiptHandle'];
    $imageURLs     = $messageDetail['Data'];

    print("Processing message with " .
    count($imageURLs)          .
    " images:\n");

    // Do the work
    $s3ImageKeys = array();
    foreach ($imageURLs as $imageURL)
    {
      // Fetch the image
      print("  Fetch image '${imageURL}'\n");
      $image = file_get_contents($imageURL);
      print("  Retrieved " . strlen($image) . " byte image\n");

      // Create a thumbnail
      $imageThumb = thumbnailImage($image, 'image/png');

      // Store the image in S3
      $key  = 'image_' . md5($imageURL) . '.png';

      if (uploadObject($s3, BOOK_BUCKET, $key, $imageThumb))
      {
        print("  Stored image in S3 using key '${key}'\n");
        $s3ImageKeys[] = $key;
      }
    }

    // If the images were processed, pass them to the image renderer
    if (count($imageURLs) == count($s3ImageKeys))
    {
      // Form message to pass page along to image renderer
      $origin    = $messageDetail['Origin'];
      $history   = $messageDetail['History'];
      $pageTitle = $messageDetail['PageTitle'];

      $history[] = 'Processed by ' . $argv[0] . ' at ' . date('c');

      $message = json_encode(array('Action'    => 'RenderImages',
           'Origin'    => $origin,
           'Data'      => $s3ImageKeys,
           'History'   => $history,
           'PageTitle' => $pageTitle));

      // Pass the page along to the image renderer
      $res = $sqs->send_message($queueURL_Render, $message);
      print("  Sent page to image renderer\n");

      if ($res->isOK())
      {
        // Delete the message
        $sqs->delete_message($queueURL_Image, $receiptHandle);
        print("  Deleted message from fetch queue\n");
      }

      print("\n");
    }
  }
}
?>
