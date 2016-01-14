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
  } else {
	$userData["isAdmin"] = false;
  }
  $_SESSION['userData'] = $userData;
  $_SESSION['access_token'] = $client->getAccessToken();
} else {
  $authUrl = $client->createAuthUrl();
}

function getUserStatusLink($pages) {
	global $authUrl;
	if ($authUrl) {
		$page['url'] = $authUrl;
		$page['title'] = 'Login';
		$pages[] = $page;
	} else {
		$page['url'] = '?logout';
		$page['title'] = 'Logout';
		$pages[] = $page;
	}
	return $pages;
}

	$site_title = "ODI LMS";
	$pages = [];
	$page['url'] = '/';
	$page['title'] = 'Home';
	$page['long_title'] = "Learning Management System";
	$pages[] = $page;
	$page['url'] = '/dashboard/index.php?module=1';
	$page['title'] = 'Dashboards';
	$page['long_title'] = "Dashboards";
	$pages[] = $page;
	$page['url'] = '/profile.php';
	$page['title'] = 'Profile';
	$page["long_title"] = "Your profile";
	//$page['admin'] = true;
	$page['loggedIn'] = true;
	$pages[] = $page;
	$page['url'] = '/admin.php';
	$page['title'] = 'Admin';
	$page["long_title"] = "LMS Administration";
	$page['admin'] = true;
	$pages[] = $page;
	$pages = getUserStatusLink($pages);
	for($i=0;$i<count($pages);$i++) {
		if ($pages[$i]['url'] == $location) {
			$current = $pages[$i];
		}
	}
	if ($current['admin'] && !$userData["isAdmin"]) {
		header('Location: 401.php');
		exit();
	}
	if (!$userData && $current['loggedIn']) {
		header('Location: 401.php');
		exit();
	}
?>
<!DOCTYPE html>
<html prefix="dct: http://purl.org/dc/terms/
              rdf: http://www.w3.org/1999/02/22-rdf-syntax-ns#
              dcat: http://www.w3.org/ns/dcat#
              odrs: http://schema.theodi.org/odrs#">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $site_title ?></title>
<!--<link href="http://assets.theodi.org/css/odi-bootstrap-crimson.css" rel="stylesheet">
<link href="http://assets.theodi.org/css/odi-bootstrap-green.css" rel="stylesheet">
<link href="http://assets.theodi.org/css/odi-bootstrap-orange.css" rel="stylesheet">
<link href="http://assets.theodi.org/css/odi-bootstrap-pomegranate.css" rel="stylesheet">
<link href="http://assets.theodi.org/css/odi-bootstrap-red.css" rel="stylesheet">-->
<link href="http://assets.theodi.org/css/odi-bootstrap.css" rel="stylesheet">
<link rel="shortcut icon" href="/images/odifavicon32.ico">
</head>
<body>
<nav>
	<div class='navbar navbar-inverse navbar-static-top' id='topbar'>
		<div class='container'>
			<div class='navbar-inner'>
				<h1>ODI LMS</h1>
				<a class='brand' href='/admin'>
					<img alt="Logo" src="/images/logo.png" />
				</a>
			</div>
		</div>
	</div>
	<div class='navbar navbar-static-top' id='mainnav'>
		<div class='container'>
			<div class='navbar-inner'>
				<ul class='nav pull-right'>
					<?php 
					for ($i=0;$i<count($pages);$i++) {
						$page = $pages[$i];
						if (!$page["admin"] && !$page["loggedIn"]) {
							echo '<li><a href="'.$page["url"].'">'.$page["title"].'</a>';
							if ($current == $page["url"]) {
								echo '<div class="arrow-down"></div>';
							}
							echo '</li>';
						} elseif ($page["admin"] && $userData["isAdmin"]) {
							echo '<li><a href="'.$page["url"].'">'.$page["title"].'</a>';
							if ($current == $page["url"]) {
								echo '<div class="arrow-down"></div>';
							}
							echo '</li>';
						} elseif ($page["loggedIn"] && $userData) {
							echo '<li><a href="'.$page["url"].'">'.$page["title"].'</a>';
							if ($current == $page["url"]) {
								echo '<div class="arrow-down"></div>';
							}
							echo '</li>';
						}
					}
					?>
				</ul>
			</div>
		</div>
	</div>
</nav>

<div class='whiteout'>
	<header>
		<div class='container'>
			<h1><?php echo $current['long_title']; ?></h1>
		</div>
	</header>

<div class='container main-default' id='main'>
