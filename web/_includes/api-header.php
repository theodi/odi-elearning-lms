<?php
session_start();
//Google API PHP Library includes
require_once 'src/Google/autoload.php';
require_once 'src/Google/Client.php';
require_once 'src/Google/Service/Oauth2.php';
require_once 'config.inc.php';
 
//Create Client Request to access Google API
$client = new Google_Client();
$client->setApplicationName("PHP Google OAuth Login Example");
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/userinfo.email");

//Send Client Request
$objOAuthService = new Google_Service_Oauth2($client);

//Logout
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
  unset($_SESSION['userData']);
  unset($userData);
  $client->revokeToken();
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL)); //redirect user back to page
}

//Authenticate code from Google OAuth Flow
//Add Access Token to Session
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

//Set Access Token to make Request
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
}

//Get User Data from Google Plus
//If New, Insert to Database
if ($client->getAccessToken()) {
  $userData = $objOAuthService->userinfo->get();
  $email = $userData["email"];
  $suffix = substr($email,strrpos($email,"@")+1,strlen($email));
  if ($suffix == "theodi.org") {
	$userData["isAdmin"] = true;
	$_SESSION["isAdmin"] = true;
  } else {
	$userData["isAdmin"] = false;
	$_SESSION["isAdmin"] = false;
  }
  $_SESSION['userData'] = $userData;
  $_SESSION['access_token'] = $client->getAccessToken();
} else {
  $authUrl = $client->createAuthUrl();
}

	$pages = [];
	$page['url'] = '/api/archive.php';
	$page['title'] = 'Archive';
	$page['admin'] = true;
	$pages[] = $page;
	$page['url'] = '/api/update_courses.php';
	$page['title'] = 'Update Courses';
	$page['admin'] = true;
	$pages[] = $page;
	for($i=0;$i<count($pages);$i++) {
		if ($pages[$i]['url'] == $location) {
			$current = $pages[$i];
		}
	}
	if (!$current) {
		header('Location: /401.php');
		exit();
	}
	if ($current['admin'] && !$userData["isAdmin"]) {
		header('Location: /401.php');
		exit();
	}
	if (!$userData && $current['loggedIn']) {
		header('Location: /401.php');
		exit();
	}
?>
