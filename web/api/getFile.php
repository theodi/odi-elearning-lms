<?php
error_reporting(E_ALL & ~E_NOTICE);
require '../../vendor/autoload.php';

define('APPLICATION_NAME', 'Drive API PHP Quickstart');
//define('CREDENTIALS_PATH', '~/.credentials/drive-php-quickstart.json');
define('CLIENT_SECRET_PATH', '../../client_secret.json');
define('SCOPES', implode(' ', array(
  Google_Service_Drive::DRIVE_READONLY)
));

//if (php_sapi_name() != 'cli') {
//  throw new Exception('This application must be run on the command line.');
//}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  echo "get client <br/>\n";
  $client = new Google_Client();
  echo "set a client <br/>\n";
  $client->setApplicationName(APPLICATION_NAME);
  echo "set application name <br/>\n";
  $client->setScopes(SCOPES);
  echo "set scopes <br/>\n";
  $client->setAuthConfigFile(CLIENT_SECRET_PATH);
  echo "set client secret <br/>\n";
  $client->setAccessType('offline');
  echo "set access type <br/>\n";

  // Load previously authorized credentials from a file.
  $accessToken = getenv("ODI_DRIVE_TOKEN");
  echo "got access token : " . $accessToken . "<br/>\n";
  if ($accessToken != "") {
  } else {
    echo "No access token, please speak to a site admin!";
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->authenticate($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, $accessToken);
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);
  echo "set access token <br/>\n";

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    echo "need refresh token <br/>\n";
    $client->refreshToken($client->getRefreshToken());
    echo "got refrest token <br/>\n";
    putenv("ODI_DRIVE_TOKEN=".$client->getAccessToken());
    echo "set access token<br/>\n";
  }
  return $client;
}

echo "Got to stage 1\n";
// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);

echo "Got to stage 2\n";

//2015 people trained spreadsheet
//$fileId = '1aFdYjtPYKWjL8yYyVeByQlLbNEehS0N3VKADpZiZoug';
//LMS identifiers
$fileId = '17gtugoN05aYnWN07Exf6_RpdknlpCfi6a1WSTyJ_z7c';
$file = $service->files->get($fileId);
$url = $file->getExportLinks()["text/csv"];
echo "Got a download url of " . $url . "<br/>\n";
//$url = $file->getSelfLink() . "/export?format=csv&gid=0";
/*
echo $url;
echo "\n";
exit();
$url = "https://www.googleapis.com/drive/v3/files/".$fileId."/export?mimeType=text/csv";
*/
echo downloadFile($client,$url);

function downloadFile($service, $downloadUrl) {
  if ($downloadUrl) {
    $request = new Google_Http_Request($downloadUrl, 'GET', null, null);
    $httpRequest = $service->getAuth()->authenticatedRequest($request);
    //echo $httpRequest->getResponseHttpCode();
    if ($httpRequest->getResponseHttpCode() == 200) {
      return $httpRequest->getResponseBody();
    } else {
      // An error occurred.
      return null;
    }
  } else {
    // The file doesn't have any content stored on Drive.
    return null;
  }
}
