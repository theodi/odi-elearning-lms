<?php
require_once('mandrill/Mandrill.php');

include_once 'config.inc.php';

findEmails();

function getMailLock() {
   global $connection_url, $db_name, $collection;
	$m = new MongoClient($connection_url);
	
	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$query = array('_id' => "email_process");
	$count = $col->count($query);
	if ($count > 0) {
		return true;
	} else {
		return false;
	}
}

function setMailLock($state) {
   global $connection_url, $db_name, $collection;
	$m = new MongoClient($connection_url);
	
	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$query = array('_id' => "email_process");
	if($state) {
		$col->save($query);
	} else {
		$col->remove($query);
	}
}

function findEmails() {
   global $connection_url, $db_name, $collection;
   setMailLock(true);
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);
	
	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$query = array('email_sent' => null);

	$cursor = $col->find($query);	
//	$cursor->fields(array("_id"=>true,"email"=>true));
	
	$m->close();
	
	foreach ($cursor as $doc) {
	   $doc = json_encode($doc);
	   processEmail(str_replace("\uff0e",".",$doc));
	}
   } catch ( MongoConnectionException $e ) {
//	return false;
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
//	return false;
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
   setMailLock(false);
}

function markDone($id) {
   global $connection_url, $db_name, $collection;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);
	
	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);

	$newdata = array('$set' => array("email_sent" => "true"));	
	$col->update(array("_id"=>$id),$newdata);

	$m->close();
	
   } catch ( MongoConnectionException $e ) {
//	return false;
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
//	return false;
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}

function processEmail($data) {
	echo "In here";
	print_r($data);
	$data = json_decode($data,true);
	$id = $data["_id"];
	$email = $data["email"];
	if ($email) {
		echo "Need to send email for $email ($id)";
	}
//	if (sendEmail($id,$email)) {
		markDone($id);
//	}
}

function sendEmail($id,$email) {
	global $mandrill_key,$eLearning_prefix;
	try {
		$mandrill = new Mandrill($mandrill_key);
		$message = array(
				'html' => '<h1>Welcome</h1><p>The link below allows you to resume your learning from any device:</p>
				<p><a href="' . $eLearning_prefix .'?id=' . $id . '" target="_blank">' . $eLearning_prefix .'?id=' . $id . '</a></p>
				<p>It is advised that you don\'t open this link on more than one device at a time or it will get all confused.</p>
				<p><b>Thanks!</b></p><p>The ODI Training Team</p>',
				'subject' => 'Welcome to ODI eLearning',
				'from_email' => 'training@theodi.org',
				'from_name' => 'ODI eLearning',
				'to' => array(
					array(
						'email' => $email,
						'type' => 'to'
					     )
					),
				'headers' => array('Reply-To' => 'training@theodi.org'),
				'important' => false,
				);
		$async = false;
		$result = $mandrill->messages->send($message, $async);
		if($result[0]["status"] == "sent") {
			return true;
		} else {
			return false;
		}
	} catch(Mandrill_Error $e) {
		// Mandrill errors are thrown as exceptions
		return false;
		// A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
	}
}

?>
