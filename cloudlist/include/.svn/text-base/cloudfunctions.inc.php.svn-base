<?php
/*
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

// Domains
define('CL_CITY_DOMAIN', 'cl_cities');
define('CL_CAT_DOMAIN', 'cl_categories');
define('CL_ITEM_DOMAIN', 'cl_items');

// S3 Bucket
define('CL_BUCKET', 'atetlaw-sitepoint-book');

// Sizes and limits
define('THUMB_SIZE', 200);
/*
 * runQuery - 
 *
 *  Run the given SDB query and return the results as an associative
 *  array. Return null on error.
 */

function runQuery($sdb, $query)
{
  $next    = '';
  $results = array();

  do
  {
    // Query the SimpleDB domain
    $res = $sdb->select($query, array('NextToken' => $next));

    // Check result
    if (!$res->isOK())
    {
      return null;
    }
  
    // See if there's more data
    $next = isset($res->body->SelectResult->NextToken) ?
      (string) $res->body->SelectResult->NextToken
      : '';

    // Process the data
    foreach ($res->body->SelectResult->Item as $item)
    {
      // Put the attributes into a more convenient form
      $attributes = array();
      foreach ($item->Attribute as $attribute)
      {
        $attributes[(string) $attribute->Name] = (string) $attribute->Value;
      }

      $key = (string) $item->Name;

      $results[$key] = $attributes;
    }
  }
  while ($next != '');

  return $results;
}

/*
 * getCities -
 *
 *  Get the list of CloudList cities and return them
 *  as an array.
 */

function getCities($sdb)
{
  $query = "select * from " . CL_CITY_DOMAIN;
  return runQuery($sdb, $query);
}

/*
 * getCategories -
 *
 *  Get the list of CloudList categories and return them
 *  as an array.
 */

function getCategories($sdb)
{
  $query = "select * from " . CL_CAT_DOMAIN;
  return runQuery($sdb, $query);
}

/*
 * getItems -
 *
 *	Get the list of CloudList items in the given city and state
 *	and return them as an array.
 */

function getItems($sdb, $city, $state)
{
  $query =
    "select * from " . CL_ITEM_DOMAIN .
    " where City=\"${city}\" and State=\"${state}\"";

  return runQuery($sdb, $query);
}

/*
 * addCloudListItem - 
 *
 *  Add a new CloudList item to S3 (image, thumbnailed image, and
 *  description) and SimpleDB (listing fields). Return true
 *  on success, false on error.
 */

function addCloudListItem($sdb, $s3, $city, $state, $date,
        $price, $category, $title, $description,
        $imagePath)
{
  // Form key
  $key = md5($city . $state . $date . $price . $category . $title);

  // Fetch image, store original and thumbnail in S3
  if ($imagePath !== null)
  {
    // Get original
    $imageIn  = file_get_contents($imagePath);
    $imageMem = ImageCreateFromString($imageIn);

    // Render as-is to JPEG and read in
    $fileOut  = tempnam("/tmp", "aws") . ".aws";
    $ret      = ImageJPEG($imageMem, $fileOut, 100);
    $imageOut = file_get_contents($fileOut);

    // Create thumbnail
    $thumbOut = thumbnailImage($imageOut, "image/jpg");
  
    // Store original and thumbnail in S3
    $imageKey = $key . '.jpg';
    $thumbKey = $key . '_thumb.jpg';

    if (!uploadObject($s3, CL_BUCKET,
          $imageKey, $imageOut,
          S3_ACL_PUBLIC, "image/jpeg") ||
  !uploadObject($s3, CL_BUCKET,
          $thumbKey, $thumbOut,
          S3_ACL_PUBLIC, "image/jpeg"))
    {
      return false;
    }

    $imageURL = $s3->get_object_url(CL_BUCKET, $imageKey);
    $thumbURL = $s3->get_object_url(CL_BUCKET, $thumbKey);
  }
  else
  {
    $imageURL = null;
    $thumbURL = null;
  }

  // Store the description in S3
  if (uploadObject($s3,
       CL_BUCKET,
       $key,
       $description,
       S3_ACL_PUBLIC))
  {
    $descriptionURL =
      $s3->get_object_url(CL_BUCKET, $key);
  }
  else
  {
    return false;
  }

  // Form attributes
  $attrs = array('City'        => $city,
     'State'       => $state,
     'Date'        => $date,
     'Price'       => $price,
     'Category'    => $category,
     'Title'       => $title,
     'Description' => $descriptionURL);

  if ($imageURL !== null)
  {
    $attrs['Image'] = $imageURL;
    $attrs['Thumb'] = $thumbURL;
  }

  // Insert item
  $res = $sdb->put_attributes(CL_ITEM_DOMAIN, $key, $attrs, true);
  
  return $res->isOK();
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
 * uploadObject -
 *
 *  Upload the given data to the indicated bucket name and key.
 *  Return true on success, false on error.
 */
function uploadObject($s3, $bucket, $key, $data,
          $acl = S3_ACL_PRIVATE, $contentType = "text/plain")
{
  $try = 1;
  $sleep = 1;
  do
  {
  // Do the upload
    $res = $s3->create_object($bucket,
        array(
          'filename'    => $key,
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
?>
