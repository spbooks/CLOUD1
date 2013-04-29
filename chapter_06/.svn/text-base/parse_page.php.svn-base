#!/usr/bin/php
<?php
/*
 * parse_page.php
 *
 * Repeatedly pull an S3 URL from the parse queue, fetch
 * the HTML from S3, parse the HTML, and capture the 
 * first 16 images with absolute URLs. Post the array
 * of URLs to the image queue.
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
require_once('include/simple_html_dom.php');
require_once('include/book.inc.php');

// Create the SQS and S3 access objects
$sqs = new AmazonSQS();
$s3  = new AmazonS3();

// Get the Queue URLs
$queueURL_Parse = $sqs->create_queue(PARSE_QUEUE)->body->CreateQueueResult->QueueUrl;
$queueURL_Image = $sqs->create_queue(IMAGE_QUEUE)->body->CreateQueueResult->QueueUrl;

// Pull, process, post
while (true)
{
  // Pull the message from the queue
  $message = pullMessage($sqs, $queueURL_Parse);

  if ($message != null)
  {
    // Extract message detail
    $messageDetail = $message['MessageDetail'];
    $receiptHandle = (string)$message['ReceiptHandle'];
    $pageURL       = $messageDetail['Data'];

    // Fetch and parse the page
    print("Processing URL '${pageURL}':\n");
    $dom = new simple_html_dom();
    $dom->load_file($pageURL);

    // Get the page title
    $pageTitle = $dom->find('title', 0)->innertext();
    print("  Retrieved page '${pageTitle}'\n");

    // Capture up to 16 image URLs 
    $imageURLs = array();
    foreach ($dom->find('img') as $image)
    {
      $imageURL = $image->src;
      if (preg_match('!^http://!', $imageURL))
      {
        print("  Found absolute URL '${imageURL}'\n");
        $imageURLs[] = $imageURL;
        if (count($imageURLs) == 16)
        {
          break;
        }
      }
    }

    // If at least one URL was found, pass along the collection
    if (count($imageURLs) > 0)
    {
      // Form message to pass page along to image fetcher
      $origin    = $messageDetail['Origin'];
      $history   = $messageDetail['History'];
      $history[] = 'Processed by ' . $argv[0] . ' at ' . date('c');

      $message = json_encode(array('Action'    => 'FetchImages',
           'Origin'    => $origin,
           'Data'      => $imageURLs,
           'History'   => $history,
           'PageTitle' => $pageTitle));

      // Pass the page along to the image fetcher
      $res = $sqs->send_message($queueURL_Image, $message);
      print("  Sent page to image fetcher\n");

      if ($res->isOK())
      {
        // Delete the message
        $sqs->delete_message($queueURL_Parse, $receiptHandle);
        print("  Deleted message from parse queue\n");
      }

      print("\n");
    }
  }
}
?>
