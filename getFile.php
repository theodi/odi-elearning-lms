<?php
error_reporting(E_ALL & ~E_NOTICE);
//session_start();
//if (!$_SESSION["isAdmin"]){
//    header('Location: /401.php');
//    exit();
//}
require 'vendor/autoload.php';
//$fileId = $_GET["fileId"];

define('APPLICATION_NAME', 'Drive API PHP Quickstart');
//define('CREDENTIALS_PATH', '~/.credentials/drive-php-quickstart.json');
define('CLIENT_SECRET_PATH', '../../client_secret.json');
define('SCOPES', implode(' ', array(
  Google_Service_Drive::DRIVE_READONLY)
));

$fileId = $argv[1];
echo getcwd();
echo $fileId;
$debug = false;
echo getFile($fileId);

//if (php_sapi_name() != 'cli') {
//  throw new Exception('This application must be run on the command line.');
//}

function getFile($fileId) {
  if ($debug) { echo "Got to stage 1\n";}

  $client = getClient();
  $service = new Google_Service_Drive($client);

  if ($debug) { echo "Got to stage 2\n";}

  $file = $service->files->get($fileId);
  $url = $file->getExportLinks()["text/csv"];
  if ($debug) { echo "Got a download url of " . $url . "<br/>\n";}
  
  if ($url) {
    return downloadFile($client,$url);
    //header('Content-Type: text/csv');
    //header('Content-disposition: filename="data.csv"');
    //echo downloadFile($client,$url);
  }
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  if ($debug) { echo "get client <br/>\n";}
  $client = new Google_Client();
  if ($debug) { echo "set a client <br/>\n"; }
  $client->setApplicationName(APPLICATION_NAME);
  if ($debug) { echo "set application name <br/>\n";}
  $client->setScopes(SCOPES);
  if ($debug) { echo "set scopes <br/>\n";}
  $client->setAuthConfigFile(CLIENT_SECRET_PATH);
  if ($debug) { echo "set client secret <br/>\n";}
  $client->setAccessType('offline');
  if ($debug) { echo "set access type <br/>\n";}

  // Load previously authorized credentials from a file.
  $accessToken = getenv("ODI_DRIVE_TOKEN");
  if ($debug) { echo "got access token : " . $accessToken . "<br/>\n";}
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
  if ($debug) { echo "set access token <br/>\n";}

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    if ($debug) { echo "need refresh token <br/>\n";}
    $client->refreshToken($client->getRefreshToken());
    if ($debug) { echo "got refrest token <br/>\n";}
    putenv("ODI_DRIVE_TOKEN=".$client->getAccessToken());
    if ($debug) { echo "set access token<br/>\n";}
  }
  return $client;
}

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
