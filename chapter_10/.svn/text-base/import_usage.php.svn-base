#!/usr/bin/php
<?php
/*
 * import_usage.php
 *
 *  Parse and storage usage AWS statistics provided in
 *  CSV files, as downloaded from the AWS portal.
 *
 *   Usage: ch8_import_usage.php CSV_FILE ...
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

// Check for arguments
if ($argc == 1)
{
  exit("Usage: " . $argv[0] . " CSV_FILE ...\n");
}

// Create the SimpleDB access object
$sdb = new AmazonSDB();

// Process each argument
for ($i = 1; $i < $argc; $i++)
{
  $file = $argv[$i];

  if (($ret = ImportCSV($sdb, $file)) !== false)
  {
    print("Imported ${file}: ${ret} records\n");
  }
  else
  {
    print("Did not import ${file}\n");
  }
}

/*
 * ImportCSV -
 *
 *  Import a CVS file containing some AWS
 *  usage statistics and store each row in
 *  SimpleDB. Return record count on
 *  success, false on error.
 */

function ImportCSV($sdb, $file)
{
  // Open input file
  $fp = fopen($file, 'r');
  if ($fp === false)
  {
    return false;
  }

  // Get first line with field names
  $fields = fgetcsv($fp);

  // Count records
  $recordCount = 0;

  // Process remaining lines, storing each one using field names
  while (($data = fgetcsv($fp)) !== false)
  {
    $recordCount++;

    $key     = '';
    $keyData = '';

    // Create attribute array and item key
    $attrs = array();
    for ($i = 0; $i < count($fields); $i++)
    {
      // Alter date fields
      if (($fields[$i] == 'StartTime') ||
          ($fields[$i] == 'EndTime'))
      {
        $data[$i] = date_create($data[$i])->format('c');
      }

      $attrs[$fields[$i]] = $data[$i];

      if ($fields[$i] == 'Service')
      {
        $key = $data[$i];
      }

      if ($fields[$i] != 'UsageValue')
      {
        $keyData .= $data[$i];
      }
    }

    // Form final key
    $key = $key . '_' . md5($keyData);

    // Insert item
    $res = $sdb->put_attributes(BOOK_AWS_USAGE_DOMAIN,
        $key, $attrs, true);
    if (!$res->isOK())
    {
      $error = $res->body->Errors->Error->Message;
      print("Could not insert ${key}: ${error}\n");
    }
  }

  // All done
  fclose($fp);

  // Succeed
  return $recordCount;
}
?>
