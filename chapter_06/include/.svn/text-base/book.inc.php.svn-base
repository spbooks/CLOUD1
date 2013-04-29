<?php
/*
 * book.inc.php - Common definitions and functions for the book.
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

// Buckets
define('BOOK_BUCKET', 'sitepoint-aws-cloud-book');
define('THUMB_BUCKET_SUFFIX', '-thumbs');

// Sizes and limits
define('THUMB_SIZE', 200);

// Queues
define('URL_QUEUE',  'c_url');
define('PARSE_QUEUE',  'c_parse');
define('IMAGE_QUEUE',  'c_image');
define('RENDER_QUEUE',  'c_render');



// Utility Functions

/*
 * getBucketObjects - Return list of all objects in the given
 *          bucket on success, optionally limited
 *          to those with the given prefix. Return
 *          null on failure.
 */

function getBucketObjects($s3, $bucket, $prefix = '')
{
  // Start with empty result array
  $objects = array();

  // Start at beginning of bucket
  $next = '';

  // Retrieve 1000 objects at a time until there are no more
  do
  {
    // Get next 1000 objects
    $res = $s3->list_objects($bucket,
           array('marker' => urlencode($next),
           'prefix' => $prefix));

    // Make sure the call succeeded
    if (!$res->isOK())
    {
      return null;
    }

    // Get the list of objects
    $contents = $res->body->Contents;

    // Append each object to the result array
    foreach ($contents as $object)
    {
      $objects[] = $object;
    }

    // See if there's more, get next key if so
    $isTruncated = $res->body->IsTruncated == 'true';

    if ($isTruncated)
    {
      $next = $objects[count($objects) - 1]->Key;
    }
  }
  while ($isTruncated);

  return $objects;
}

/*
 * uploadObject -
 *
 *  Upload the given data to the indicated bucket name and key.
 *  Return true on success, false on error.
 */
function uploadObject($s3, $bucket, $key, $data,
		      $acl = AmazonS3::ACL_PRIVATE, $contentType = "text/plain")
{
  $try = 1;
  $sleep = 1;
  do
  {
  // Do the upload
    $res = $s3->create_object($bucket,
			      $key,
        array(
          'body'        => $data,
          'acl'         => $acl,
          'contentType' => $contentType
        ));

  //check upload status
    if ($res->isOK()) {
      return true;
    }
    sleep($sleep);
    $sleep *= 2;
  }
  while(++$try < 6);
  return false;
}

/*
 * guessType - 
 *
 *  Make a simple guess as to the file's content type,
 *  and return a MIME type.
 */

function guessType($file)
{
  $info = pathinfo($file, PATHINFO_EXTENSION);

  switch (strtolower($info))
  {
    case "jpg":
    case "jpeg":
      return "image/jpg";

    case "png":
      return "image/png";

    case "gif":
      return "image/gif";

    case "htm":
    case "html":
      return "text/html";

    case "txt":
      return "text/plain";

    default:
      return "text/plain";
  }
}

/*
 * thumbnailImage - 
 *
 *  Generate and return a thumbnail of the given image bits and
 *  with the given content type. The thumbnail will be at most
 *  THUMB_SIZE pixels tall or wide. The GD extention to PHP
 *  must be installed in order for this function to work.
 *  Return null on failure.
 */

function thumbnailImage($imageBitsIn, $contentType)
{
  // Create a GD image
  $imageIn = ImageCreateFromString($imageBitsIn);

  // Measure the image
  $inX = ImageSx($imageIn);
  $inY = ImageSy($imageIn);

  // Decide how to scale it
  if ($inX > $inY)
  {
    $outX = THUMB_SIZE;
    $outY = (int) (THUMB_SIZE * ((float) $inY / $inX));
  }
  else
  {
    $outX = (int) (THUMB_SIZE * ((float) $inX / $inY));
    $outY = THUMB_SIZE;
  }

  // Create thumbnail image and fill it with white
  $imageOut = ImageCreateTrueColor($outX, $outY);
  ImageFill($imageOut, 0, 0,
      ImageColorAllocate($imageOut, 255, 255, 255));

  // Copy / resize the original image into the thumbnail image
  ImageCopyResized($imageOut, $imageIn,
       0, 0, 0, 0,
       $outX, $outY, $inX, $inY);

  // Write the image to a temporary file in the requested format
  $fileOut = tempnam("/tmp", "aws") . ".aws";

  switch ($contentType)
  {
    case "image/jpg":
      $ret = ImageJPEG($imageOut, $fileOut, 100);
      break;

    case "image/png":
      $ret = ImagePNG($imageOut, $fileOut, 0);
      break;
      
    case "image/gif":
      $ret = ImageGIF($imageOut, $fileOut);
      break;

    default:
      unlink($fileOut);
      return false;
  }

  // Verify success
  if (!$ret)
  {
    unlink($fileOut);
    return false;
  }

  // Read the image back in
  $imageBitsOut = file_get_contents($fileOut);

  // Clean up
  unlink($fileOut);

  return $imageBitsOut;
}

/*
 * findDistributionForBucket -
 *
 *  Return the CloudFront Distribution (if any) for the
 *  given bucket. Return null if no distribution could be
 *  found.
 */

function findDistributionForBucket($cf, $bucket)
{
  // Retrieve list of CloudFront distributions
  $res = $cf->list_distributions();

  if (!$res->isOK())
  {
    return null;
  }

  // Form string to search for
  $needle = $bucket . ".";

  // Get the list of distributions
  $distributions = $res->body->DistributionSummary;

  foreach ($distributions as $distribution)
  {
    if (substr($distribution->Origin, 0, strlen($needle)) ==
  $needle)
    {
      return $distribution;
    }
  }

  return null;
}

/*
 * pullMessage -
 *
 *  Poll the given queue every second until a message is
 *  available.  Return the queue, the raw message,
 *  the message body, the JSON-decoded message body, the
 *  receipt handle, and a timestamp in an associative
 *  array. Return null on error. Sleep one second between
 *  polls.
 */

function pullMessage($sqs, $queue)
{
  while (true)
  {
    $res = $sqs->receive_message($queue);

    if ($res->isOk())
    {
      if (isset($res->body->ReceiveMessageResult->Message))
      {
        $message = $res->body->ReceiveMessageResult->Message;
        $messageBody = $message->Body;
        $messageDetail = json_decode($messageBody, true);
        $receiptHandle = $message->ReceiptHandle;

        return array(
         'Queue'      => $queue,
         'Timestamp'     => date('c'),
         'Message'       => $message,
         'MessageBody'   => $messageBody,
         'MessageDetail' => $messageDetail,
         'ReceiptHandle' => $receiptHandle
        );
      }
      else
      {
        sleep(1);
      }
    }
    else
    {
      print("Could not pull message from queue '${queue}': " .
      $res->body->Error->Message . "\n");
      return null;
    }
  }
}


?>