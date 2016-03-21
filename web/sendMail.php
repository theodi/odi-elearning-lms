<?php
require_once('mandrill/Mandrill.php');

include_once 'config.inc.php';

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

	$query = array('email_sent' => "false");

	$cursor = $col->find($query);
//	$cursor->fields(array("_id"=>true,"email"=>true));

	$m->close();

	foreach ($cursor as $doc) {
	   $doc = json_encode($doc);
	   processEmail($doc);
//	   processEmail(str_replace("\uff0e",".",$doc));
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
	$data = json_decode($data,true);
	$id = $data["_id"];
	$email = $data["email"];
	$sent = $data["email_sent"];
	if ($email && $sent == "false") {
		$email = str_replace("．",".",$email);
		sendEmail($id,$email);
		markDone($id);
	}
}

function sendEmail($id,$email) {
	global $mandrill_key,$eLearning_prefix;
	try {
		$mandrill = new Mandrill($mandrill_key);
    $template_name = 'ODI - eLearning resume email';
    $template_content = array();

		$message = array(
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
      'merge_vars' => array(
        array(
          'rcpt' => $email,
          'vars' => array(
            array(
              'name' => 'ELEARNING_PREFIX',
              'content' => $eLearning_prefix
            ),
            array(
              'name' => 'ELEARNING_RESUME_ID',
              'content' => $id
            )
          )
        )
      )
		);
		$async = false;
    $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async);
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
