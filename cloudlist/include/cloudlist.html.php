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
    <title>CloudList Classified Ad System</title>
    <link rel="stylesheet" type="text/css" media="all" href="css/styles.css" />
  </head>
  <body>
    <h1>CloudList Classified Ad System</h1>
    <div id="menu">
      <ul>
        <?php foreach ($cities as $key => $attrs): 
          $menuCity  = $attrs['City'];
          $menuState = $attrs['State'];
          $link = "?city="  . urlencode($menuCity) . "&state=" . urlencode($menuState);
          $menuClass = (($currentCity == $menuCity) && ($currentState == $menuState)) ? "activemenu" : "menu";
        ?>
          <li class="<?php echo $menuClass; ?>">
            <a href="<?php echo $link; ?>"><?php echo "${menuCity}, ${menuState}"; ?></a>
          </li>
        <?php endforeach ?>  
      </ul>
      <p id="newitemlink"><a href="add_form.php">Add new item ...</a></p>
    </div>
    <div id="items">
    <?php foreach ($itemCat as $category => $items): ?>
      <div class="category">
        <h2><?php echo $category; ?></h2>
        <?php foreach ($items as $key => $attrs): ?>
        <div class="item">
          <h3><?php echo $attrs['Title']; ?></h3>
          <?php if (isset($attrs['Thumb'])): ?>
          <a href="<?php echo $attrs['Image']; ?>">
            <img src="<?php echo $attrs['Thumb']; ?>"/>
          </a>
          <?php endif ?>
          <p class="date">Listed <?php echo $attrs['Date']; ?></p>
          <p class="price">Priced at $<?php echo number_format($attrs['Price']); ?></p>
          <p class="desc"><?php echo file_get_contents($attrs['Description']); ?></p>
        </div>
        <?php endforeach ?>
        <div class="clear"></div>
      </div>
    <?php endforeach ?>
    </div>
  </body>
</html>