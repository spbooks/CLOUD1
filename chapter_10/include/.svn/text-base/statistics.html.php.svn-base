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
    <title><?php echo $output_title ?></title>
  </head>
  <body>
    <h1><?php echo $output_title ?></h1>
    <p><?php echo $output_message ?></p>
    <table>
      <thead>
        <tr>
          <th>Bucket</th>
          <?php foreach($days as $day): ?>
          <th><?php echo $day ?></th>
          <?php endforeach ?>
          <th>Total For Bucket</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $bucket => $cells): ?>
        <tr>
          <td><?php echo $bucket ?></td>
          <?php foreach($cells as $cell): ?>
          <td>
          <?php echo ($cell == '') ? '&nbsp;' : number_format($cell); ?>
          </td>
          <?php endforeach ?>
        </tr>
        <?php endforeach ?>     
      </tbody>
    </table>
  </body>
</html>