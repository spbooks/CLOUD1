#!/usr/bin/php
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

$userData = file_get_contents('http://169.254.169.254/latest/user-data');
$options  = array();

foreach (explode(",", $userData) as $userDataItem)
{
  if (preg_match("!^([a-zA-Z]{1,})=([a-zA-Z0-9]{1,})$!",
                 $userDataItem, $parts))
  {
    $name  = $parts[1];
    $value = $parts[2];

    $options[$name] = $value;
  }
}

print_r($options);
?>