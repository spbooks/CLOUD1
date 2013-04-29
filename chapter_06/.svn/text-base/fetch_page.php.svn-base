#!/usr/bin/php
<?php
/*
 * fetch_page.php
 *
 * Repeatedly pull a URL from the URL queue, fetch the page,
 * store the page in S3, and post a message to the Parse queue.
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
$queueURL_URL   = $sqs->create_queue(URL_QUEUE)->body->CreateQueueResult->QueueUrl;
$queueURL_Parse = $sqs->create_queue(PARSE_QUEUE)->body->CreateQueueResult->QueueUrl;

// Pull, process, post
while (true)
{
  // Pull the message from the queue
  $message = pullMessage($sqs, $queueURL_URL);
  
  if ($message != null)
  {
    // Extract message detail
    $messageDetail = $message['MessageDetail'];
    $receiptHandle = (string)$message['ReceiptHandle'];
    $pageURL       = $messageDetail['Data'];

    // Fetch the page
    print("Processing URL '${pageURL}':\n");
    $html = file_get_contents($pageURL);
    print("  Retrieved " . strlen($html) . " bytes of HTML\n");

    // Store the page in S3
    $key = 'page_' . md5($pageURL) . '.html';
    if (uploadObject($s3, BOOK_BUCKET, $key, $html, AmazonS3::ACL_PUBLIC))
    {
      // Get URL in S3
      $s3URL = $s3->get_object_url(BOOK_BUCKET, $key);
      print("  Uploaded page to S3 as '${key}'\n");

      // Form message to pass page along to parser
      $origin    = $messageDetail['Origin'];
      $history   = $messageDetail['History'];
      $history[] = 'Fetched by ' . $argv[0] . ' at ' . date('c');

      $message = json_encode(array('Action'  => 'ParsePage',
           'Origin'  => $origin,
           'Data'    => $s3URL,
           'PageURL' => $pageURL,
           'History' => $history));

      // Pass the page along to the parser
      $res = $sqs->send_message($queueURL_Parse, $message);
      print("  Sent page to parser\n");

      if ($res->isOK())
      {
        // Delete the message
        $sqs->delete_message($queueURL_URL, $receiptHandle);
        print("  Deleted message from URL queue\n");
      }
      print("\n");
    }
    else
    {
      print("Error uploading HTML to S3\n");
    }
  }
}

?>
