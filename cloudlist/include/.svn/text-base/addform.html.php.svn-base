<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
 Copyright 2009-2010 Amazon.com, Inc. or its affiliates. All Rights
 Reserved.
 
 Licensed under the Apache License, Version 2.0 (the "License"). You
 may not use this file except in compliance with the License. A copy
 of the License is located at
 
       http://aws.amazon.com/apache2.0/
 
 or in the "license.txt" file accompanying this file. This file is
 distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 OF ANY KIND, either express or implied. See the License for the
 specific language governing permissions and limitations under the
 License.
-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>CloudList Classified Ad System -- Add Item</title>
    <link rel="stylesheet" type="text/css" media="all" href="css/styles.css" />
  </head>
  <body>
    <h1>CloudList Classified Ad System -- Add Item</h1>
    <p>Please enter the new item information.</p>
    <form method="post" enctype="multipart/form-data" action="?">
      <input type="hidden" name="formsubmit" value="1"/>
      <input type="hidden" name="MAX_FILE_SIZE" value="2048000"/>
      <div>
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" />
      </div>
      <div>
        <label for="price">Price:</label>
        <input type="text" name="price" id="price" />
      </div>
      <div>
        <label for="desc">Description:</label>
        <textarea rows="8" cols="40" name="description" name="desc"></textarea>
      </div>
      <div>
        <label for="category">Category:</label>
        <select name="category" id="category">
        <?php foreach ($categories as $key => $attrs): ?>
          <option value="<?php echo $key; ?>"><?php echo $key; ?></option>
        <?php endforeach ?>
        </select>
      </div>
      <div>
        <label for="statecity">Location:</label>
        <select name="statecity">
        <?php foreach ($cities as $key => $attrs):
          $city  = $attrs['City'];
          $state = $attrs['State'];
        ?>
          <option value="<?php echo $key; ?>"><?php echo "${city}, ${state}"; ?></option>
        <?php endforeach ?>
        </select>
      </div>
      <div>
        <label for="statecity">Photo (optional):</label>
        <input type="file" name="image"/>
      </div>
      <div>
        <input type="submit" value="Add"/> <a href="cloudlist.php">Cancel &amp; return ...</a>
      </div>
    </form>
  </body>
</html>