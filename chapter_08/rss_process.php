#!/usr/bin/php
<?php
/*
 * rss_process.php -
 *
 *  Process (fetch, parse, and store) a list of RSS feeds.
 *  The list can come from a file or from an SQS queue.
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

//error_reporting(E_ALL);

require_once('sdk.class.php');
require_once('include/book.inc.php');
require_once ('include/magpierss/rss_fetch.inc');

// Tell the RSS parser to disable caching of feed data
define('MAGPIE_CACHE_ON', 0);


$doFile  = false;
$doQueue = false;

// Check arguments and determine mode
if (($argc != 2) ||
    (($argv[1] != '-f') && ($argv[1] != '-q')))
{
  exit("Usage:\n".
       $argv[0] . " -f\n" .
       $argv[0] . " -q\n");
}

switch ($argv[1])
{
  case '-f':
    $doFile = true;
    break;

  case '-q':
    $doQueue = true;
    break;
}

// Create the SimpleDB and access objects
$sdb = new AmazonSDB();
$sqs = new AmazonSQS();

// Create array of interesting fields at feed level
$feedFields = array('link',
        'title',
        'pubdate',
        'tagline',
        'language',
        'generator',
        'description');

// Create array of interesting fields at item level
$itemFields = array('guid',
        'link',
        'title',
        'description');

// Create array of possible fields for item key, in descending order of preference
$itemKeyFields = array('guid',
           'link',
           'title');

// Process file argument
if ($doFile)
{
  // Get list of feeds to process
  $urls = file(FEEDS);
  print("Begin processing " . count($urls) . " feeds\n");

  foreach ($urls as $url)
  {
    $url = trim($url);

    if (updateFeed($sdb, $url))
    {
      print($url . " - updated.\n");
    }
    else
    {
      print($url . " - not updated.\n");
    }
  }
}

// Process queue argument
if ($doQueue)
{
  $queueURL = $sqs->create_queue(FEED_QUEUE)->body->CreateQueueResult->QueueUrl;

  while (true)
  {
    $message = pullMessage($sqs, $queueURL);

    if ($message != null)
    {
      $messageDetail = $message['MessageDetail'];
      $receiptHandle = (string)$message['ReceiptHandle'];

      $url = $messageDetail['FeedURL'];

      if (updateFeed($sdb, $url))
      {
        print($url . " - updated.\n");
      }
      else
      {
        print($url . " - not updated.\n");
      }

      // Delete the message
      $sqs->delete_message($queueURL, $receiptHandle);
    }
  }
}

/*
 * updateFeed -
 *
 *  Fetch the latest version of the RSS from the given URL.
 *  Return true on success, false on error.
 *
 *  If the feed cannot be fetched, update the BOOK_FEED_DOMAIN
 *  with the current date and time and a status of FEED_NO_FETCH.
 *
 *  If the feed can be fetched, update the BOOK_FEED_DOMAIN
 *  with the current date and time, a status of FEED_YES_FETCH,
 *  and values for any fields set in FeedFields. Also update
 *  the BOOK_FIELD_ITEM_DOMAIN, with one item for each item
 *  in the feed, using any values in the fields named in
 *  ItemFields.
 */

function updateFeed($sdb, $url)
{
  global $stats;
  global $feedFields;
  global $itemFields;
  global $itemKeyFields;

  // Fetch the RSS
  $rss = fetch_rss($url);

  // Handle success or failure
  if ($rss !== false)
  {

    // Prepare to update BOOK_FEED_DOMAIN
    $key   = $url;
    $attrs = array('feed_url'   => $url,
       'fetch_date' => date('c'),
       'status'     => FEED_YES_FETCH);

    foreach ($feedFields as $field)
    {
      if (isset($rss->channel[$field]) && ($rss->channel[$field] != ''))
      {
        $attrs[$field] = $rss->channel[$field];
      }
    }

    // Update BOOK_FEED_DOMAIN
    $res = $sdb->put_attributes(BOOK_FEED_DOMAIN, $key, $attrs, true);

    // Check status
    if (!$res->isOK())
    {
      return false;
    }

    // Update BOOK_FEED_ITEM_DOMAIN for each item in the feed
    foreach ($rss->items as $item)
    {
      $attrs = array();
      foreach ($itemFields as $field)
      {
        if (isset($item[$field]) && ($item[$field] != ''))
        {
          $attrs[$field] = $item[$field];
        }
      }

      // Find a field to use as a key
      $itemKey = null;
      foreach ($itemKeyFields as $field)
      {
        if (isset($item[$field]) && ($item[$field] != ''))
        {
          $itemKey = $item[$field];
          break;
        }
      }
      
      // Use the md5 hash of all fields as a last resort
      if ($itemKey == null)
      {
        $all = '';
        foreach ($attrs as $key => $value)
        {
          $all .= $key . '_' . $value . '__';
        }
        $key = md5($all);
      }

      // Update BOOK_FEED_ITEM_DOMAIN
      $res = $sdb->put_attributes(BOOK_FEED_ITEM_DOMAIN, $itemKey, $attrs, true);

      // Check status
      if (!$res->isOK())
      {
        return false;
      }
    }
    
    return true;
  }
  else
  {

    // Prepare to update BOOK_FEED_DOMAIN
    $key   = $url;
    $attrs = array('feed_url'   => $url,
       'fetch_date' => date('c'),
       'status'     => FEED_NO_FETCH);

    // Update BOOK_FEED_DOMAIN
    $res = $sdb->put_attributes(BOOK_FEED_DOMAIN, $key, $attrs, true);

    // Check status
    if (!$res->isOK())
    {
      return false;      // We failed at failing
    }

    // Signify fetch failure
    return false;
  }
}

?>
