<?php
/*
Plugin Name: Installation Tracker
Plugin URI: http://www.kyght.com/?page_id=147
Description: Tracks the distribution of your installable products. The plugin provides an ajax API to accept installation registrations & usage data.
Also provides a mechanism for pushing product upgrades to clients. Custom build tags can also be used to track clients with customized products
and push custom upgrades to these clients.

.Net client library is available at www.kyght.com . Other client libraries will be available later.
Author: Tim O'Brien
Version: 1.7
Author URI: www.kyght.com
*/

global $wpdb;
global $jal_db_version;
$jal_db_version = '1.7';

//*************** PLUGIN DEFINES ***********************

//** Setup DB Table Names **
//Registration Table for clients
define('COMPANY_TABLE', $wpdb->prefix . "kyght_companytry");

//List of products distributed and their usage
define('PRODUCT_TABLE', $wpdb->prefix . "kyght_producttry");

//User controlled table where versions can be published
//   Custom fields can be used for clients that have a custom version
define('UPGRADE_TABLE', $wpdb->prefix . "kyght_upgrade");


//***** Plugin Files *******
include( plugin_dir_path( __FILE__ ) . 'options.php');
include( plugin_dir_path( __FILE__ ) . 'admin.php');


//************** CLASSES *********************

/*All objects returned from the API should be based on the ResultObject.
It provides every reply with a valid flag and message.
Descendant classes can override getArray method to add additional properties
to encoded data. Currently supports json encoding.
*/
class ResultObject {
	public $message = "";
	public $valid = "FALSE";

	protected function getArray() {
	  return null;
	}
	private function resultArray() {
	 	return array(
		 							'msg' => $this->message,
			            'valid' => $this->valid,
			        );
	}

	public function to_json() {
	 	$objarr = $this->getArray();
	 	$kyarr = $this->resultArray();
	 	
	 	if ($objarr == NULL) {
	 		return json_encode($kyarr);
	 	} else {
			$merarr = array_merge($objarr, $kyarr);
			return json_encode($merarr);
		}
	}
}

//Registration class which is returned when client registers.
//We only need to provide a property to set Regid and encode in getArray
//our base class ResultObject handles the rest.
class Registration extends ResultObject {
	public $regid = "";
	protected function getArray() {
	  return array('regID' => $this->regid );
	}
}

//Upgrade class returned when an upgrade is available
class Upgrade extends ResultObject {
	public $upid = 0;
	public $product = "";
	public $version = "";
	public $vernum = 0;
	public $custom = "";
	public $url = "";
	public $notesurl = "";
	protected function getArray() {
	  return array(
				'upid' => $this->upid,
				'product' => $this->product,
				'version' => $this->version,
				'vernum' => $this->vernum,
				'custom' => $this->custom,
				'url' => $this->url,
				'notesurl' => $this->notesurl
				);
	}
}
//************** END CLASSES *********************


//************** DATABASE and PLUGIN Setup *********************
function createtables() {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();

	//CREATE COMPANY TABLE
	$sql = "CREATE TABLE " . COMPANY_TABLE . " (
		id int(10) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		keyguid varchar(100) NOT NULL,
		name varchar(60) NOT NULL,
		email varchar(120) NULL,
		contact varchar(60) NULL,
		phone varchar(30) NULL,
		address varchar(120) NULL,
		city varchar(60) NULL,
		state varchar(50) NULL,
		zipcode varchar(15) NULL,
		product varchar(50) NULL,
		version varchar(15) NULL,
		usecount int(10) NULL,
		lastused datetime DEFAULT '0000-00-00 00:00:00' NULL,
		custom varchar(60) NULL,
		UNIQUE KEY id (id),
		INDEX (keyguid)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	//CREATE PRODUCT TABLE
	$sql = "CREATE TABLE " . PRODUCT_TABLE . " (
		id int(10) NOT NULL AUTO_INCREMENT,
		product varchar(50) NOT NULL,
		version varchar(15) NOT NULL,
		usecount int(10) NULL,
		lastupdate datetime DEFAULT '0000-00-00 00:00:00' NULL,
		UNIQUE KEY id (id),
		INDEX (product)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	//CREATE UPGRADE TABLE
	$sql = "CREATE TABLE " . UPGRADE_TABLE . " (
		id int(10) NOT NULL AUTO_INCREMENT,
		product varchar(50) NOT NULL,
		version varchar(15) NOT NULL,
		vernum int(10) NOT NULL,
		custom varchar(60) NULL,
		url varchar(255) NOT NULL,
		notesurl varchar(255) NULL,
		UNIQUE KEY id (id),
		INDEX upidx (product, custom)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

}

//Call only on Activate of Plugin
function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$installed_ver = get_option( "jal_db_version" );

	if ( $installed_ver != $jal_db_version ) {
		createtables();
		add_option( 'jal_db_version', $jal_db_version );
	}
	
}
register_activation_hook( __FILE__, 'jal_install' );


function jal_upgrade() {
	global $wpdb;
	global $jal_db_version;

	$charset_collate = $wpdb->get_charset_collate();
	
	$installed_ver = get_option( "jal_db_version" );

	if ( $installed_ver != $jal_db_version ) {
		createtables();
		update_option( 'jal_db_version', $jal_db_version );
	}
}

function jal_update_db_check() {
    global $jal_db_version;
    if ( get_site_option( 'jal_update_db_check' ) != $jal_db_version ) {
        jal_upgrade();
    }
}
add_action( 'plugins_loaded', 'jal_update_db_check' );

//************** END - DATABASE and PLUGIN Setup *********************


//************** DATABASE FUNCTIONS *********************
function jal_register( $keyguid, $company, $email, $contact, $phone, $address, $city, $state, $product, $version, $custom, $zipcode ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'kyght_companytry';

	$wpdb->insert(
		COMPANY_TABLE,
		array(
			'keyguid' => $keyguid,
			'name' => $company,
			'address' => $address,
			'city' => $city,
			'state' => $state,
			'zipcode' => $zipcode,
			'email' => $email,
			'contact' => $contact,
			'phone' => $phone,
			'product' => $product,
			'version' => $version,
			'custom' => $custom,
			'time' => current_time('mysql', 1),
		)
	);
	
	$lastid = $wpdb->insert_id;
	return $lastid;
}

function jal_register_update( $trackid, $keyguid, $company, $email, $contact, $phone, $address, $city, $state, $product, $version, $custom, $zipcode ) {
	global $wpdb;

	$uprows = $wpdb->update(
		COMPANY_TABLE,
		array(
			'name' => $company,
			'address' => $address,
			'city' => $city,
			'state' => $state,
			'zipcode' => $zipcode,
			'email' => $email,
			'contact' => $contact,
			'phone' => $phone,
			'product' => $product,
			'version' => $version,
			'custom' => $custom,
			'time' => current_time('mysql', 1),
		),
		array(
					'id' => $trackid,
					'keyguid' => $keyguid,
					)
	);
	
	return $uprows;
}


function jal_isregistered( $keyguid ) {
	global $wpdb;

	$user_count = $wpdb->get_var(  $wpdb->prepare( "SELECT COUNT(*) FROM " . COMPANY_TABLE . " WHERE keyguid = %s", $keyguid) );
	if ($user_count > 0) return true;
	return false;

}

function jal_productupdate( $product, $ver, $custom ) {
	global $wpdb;

  //strip per
	$vernum = str_replace('.', '', $ver);
	$vernum = str_replace(' ', '', $vernum);

	//Order by ID and limit 1 to get the lastest entry
	if ($custom == NULL) {
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . UPGRADE_TABLE . " WHERE product = %s and (custom is null or custom = '') and vernum > %d order by id desc limit 1", $product, $vernum) );
	} else {
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . UPGRADE_TABLE . " WHERE product = %s and custom = %s and vernum > %d order by id limit 1", $product, $custom, $vernum) );
	}
	
}

function jal_usage( $keyguid, $product, $version, $custom ) {
	global $wpdb;

	$procnt = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . PRODUCT_TABLE . " WHERE product = %s and version = %s", $product, $version) );
	if ($procnt <= 0) {
	  //INSERT Product record
		$wpdb->insert(
			PRODUCT_TABLE,
			array(
				'usecount' => 1,	// Integer
				'product' => $product,	
				'version' => $version,
				'lastupdate' => current_time('mysql', 1),
			)
		);
	} else {
	  //UPDATE Product count
		$usage = $wpdb->get_var( $wpdb->prepare( "SELECT usecount FROM " . PRODUCT_TABLE . " WHERE product = %s and version = %s", $product, $version) );
		if ($usage == NULL) $usage = 0;
		$usage = $usage + 1;

		$wpdb->update(
			PRODUCT_TABLE,
			array(
				'usecount' => $usage,	// Integer
				'lastupdate' => current_time('mysql', 1),
			),
			array(
					  'product' => $product,
						'version' => $version,
						)
		);
	}

	//POSSIBLE Company usage update
	//If we were give the system id of the installation, then also update usage for the company
	if ($keyguid != null) {
		$usage = $wpdb->get_var( $wpdb->prepare( "SELECT usecount FROM " . COMPANY_TABLE . " WHERE keyguid = %s and product = %s", $keyguid, $product) );
		if ($usage == NULL) $usage = 0;
		//Increae Usage by 1
		$usage = $usage + 1;

		//If this company (if keyguid is not registered then the update will just not find anything\
		$wpdb->update(
			COMPANY_TABLE,
			array(
				'custom' => $custom,	// Update custom so we know if the client was update with a custom build
				'version' => $version,	// Update version so we know what version the client is on
				'usecount' => $usage,	// Integer
				'lastused' => current_time('mysql', 1),
			),
			array(
						'keyguid' => $keyguid,
					  'product' => $product,
						)
		);
	}
}

function jal_addUpgrade( $product, $version, $vernum, $custom, $url, $notesurl ) {
	global $wpdb;

	$procnt = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . UPGRADE_TABLE . " WHERE product = %s and version = %s", $product, $version) );
	if ($procnt <= 0) {

		$addrows = $wpdb->insert(
			UPGRADE_TABLE,
			array(
				'product' => $product,
				'version' => $version,
				'vernum' => $vernum,
				'custom' => $custom,
				'url' => $url,
				'notesurl' => $notesurl,
			)
		);

		//$lastid = $wpdb->insert_id;
		return $addrows;
	}
	return 0;
}

function jal_editUpgrade($id, $product, $version, $vernum, $custom, $url, $notesurl ) {
	global $wpdb;

	$procnt = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . UPGRADE_TABLE . " WHERE id = %d", $id) );
	if ($procnt >= 1) {

		$wpdb->update(
			UPGRADE_TABLE,
			array(
				'product' => $product,
				'version' => $version,
				'vernum' => $vernum,
				'custom' => $custom,
				'url' => $url,
				'notesurl' => $notesurl,
			),
			array(
			  'id' => $id,
			)
		);

		//$lastid = $wpdb->insert_id;
		return true;
	}
	return false;
}

function jal_editRegistration($id, $product, $version, $name, $address, $state, $city, $phone, $email, $contact, $custom, $zipcode) {
	global $wpdb;

	$procnt = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . COMPANY_TABLE . " WHERE id = %d", $id) );
	if ($procnt >= 1) {

		$wpdb->update(
			COMPANY_TABLE,
			array(
				'product' => $product,
				'version' => $version,
				'name' => $name,
				'address' => $address,
				'state' => $state,
				'zipcode' => $zipcode,
				'city' => $city,
				'contact' => $contact,
				'phone' => $phone,
				'email' => $email,
				'custom' => $custom,
			),
			array(
			  'id' => $id,
			)
		);

		//$lastid = $wpdb->insert_id;
		return true;
	}
	return false;
}

//*************** END DATABASE FUNCTIONS ***********************


//*************** AJAX API Calls ***********************
function kyjax_update_registration() {

		$kyoptions = get_option( 'kytracker_option_name' );
		$secret_key = 0;
		if ($kyoptions != NULL) {
			$secret_key = $kyoptions['secret_key'];
		}

		$sKey = $_POST['sky'];
		//We need to guard all API calls with a key. We will read our key from Admin config later
		if ($sKey != $secret_key) {
		 		echo 0; //WP missing AJax Action Found
		 		exit;
		 }


		//We will process this request
		header( "Content-Type: application/json" );

		//Read all Post variables
    $product = $_POST['product'];
		$ver = $_POST['ver'];
		$custom = $_POST['custom'];

		$key = $_POST['key'];
    $name = $_POST['name'];
    $addr = $_POST['addr'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zipcode = $_POST['zipcode'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $phone = $_POST['phone'];

		$kyreg = new Registration();
		
		//Validate Supplied Data
		$valid = "";
		if ($key == null) $valid = "Key not supplied";
		if (strlen($key) < 32) $valid = "Key too short";
		if (strlen($key) > 38) $valid = "Key too long";
		if ($key == "00000000-0000-0000-0000-000000000000") $valid = "Key not initialized";

		//Return valadation failure reason
		if ($valid != "") {
			$kyreg->message = $valid;
		  echo $kyreply->to_json();
		  exit;
		}

		//Process Registration Records
		if (jal_isregistered($key)) {
		  //Mmmm, the key being updated may be an update or a hack on records
		  //We will have to apply some logic to the update or have an archive table
		  //Ok, if the user wants to update the registration records, they must supply
		  //the key and our database id that we retured previously
    	$sysid = $_POST['trackid'];

    	if ($sysid == null) {
				$kyreg->message = 'Registration Track ID must be supplied';
		  	echo $kyreply->to_json();
		  	exit;
    	}
		  //updateRegister
		  $uprows = jal_register_update($sysid, $key, $name, $email, $contact, $phone, $addr, $city, $state, $product, $ver, $custom, $zipcode);
		  if ($uprows > 0) {
				$kyreg->message = 'Registration Update';
				$kyreg->valid = 'TRUE';
				$kyreg->regID = $sysid;
		  	echo $kyreply->to_json();
			} else {
				$kyreg->message = 'Unable to find Registration';
				$kyreg->valid = 'FALSE';
				$kyreg->regID = $sysid;
		  	echo $kyreply->to_json();
			}
		  exit;
		  
		} else {
    	$lastid = jal_register($key, $name, $email, $contact, $phone, $addr, $city, $state, $product, $ver, $custom, $zipcode);

			$kyreg->message = 'Registration Added';
			$kyreg->valid = 'TRUE';
			$kyreg->regID = $lastid;
	  	echo $kyreply->to_json();
    }

    // IMPORTANT: don't forget to "exit"
    exit;
}

//AJAX API Calls Registration ***********************
add_action( 'wp_ajax_nopriv_regupdate', 'kyjax_update_registration' );
add_action( 'wp_ajax_regupdate', 'kyjax_update_registration' );

function kyjax_usage() {
		$kyoptions = get_option( 'kytracker_option_name' );
		$secret_key = 0;
		if ($kyoptions != NULL) {
			$secret_key = $kyoptions['secret_key'];
		}

		$sKey = $_POST['sky'];
		//We need to guard all API calls with a key. We will read our key from Admin config later
		if ($sKey != $secret_key) {
		 		echo 0; //WP missing AJax Action Found
		 		exit;
		 }

		//We will process this request
		header( "Content-Type: application/json" );

		//Read all Post variables
    $product = $_POST['product'];
		$ver = $_POST['ver'];
		$key = $_POST['key'];
		$custom = $_POST['custom'];

		$kyreply = new ResultObject();

		//Validate Supplied Data
		$valid = "";
		if ($key == null) $valid = "Key not supplied";
		if (strlen($key) < 32) $valid = "Key too short";
		if (strlen($key) > 38) $valid = "Key too long";
		if ($key == "00000000-0000-0000-0000-000000000000") $valid = "Key not initialized";
		if ($valid != "") {
			$kyreply->message = valid;
		  echo $kyreply.to_json();
		  exit;
		}
		
	  jal_usage( $key, $product, $ver, $custom );
	  
		$kyreply->message = "Usage Updated";
		$kyreply->valid = "TRUE";
		
		echo $kyreply->to_json();
		exit;

}

add_action( 'wp_ajax_nopriv_useapp', 'kyjax_usage' );
add_action( 'wp_ajax_useapp', 'kyjax_usage' );


function kyjax_upgrade() {
		$kyoptions = get_option( 'kytracker_option_name' );
		$secret_key = 0;
		if ($kyoptions != NULL) {
			$secret_key = $kyoptions['secret_key'];
		}

		$sKey = $_POST['sky'];
		//We need to guard all API calls with a key. We will read our key from Admin config later
		if ($sKey != $secret_key) {
		 		echo 0; //WP missing AJax Action Found
		 		exit;
		 }


		//We will process this request
		header( "Content-Type: application/json" );

		//Read all Post variables
    $product = $_POST['product'];
		$custom = $_POST['custom'];
		$ver = $_POST['ver'];

		$kyreply = new Upgrade();

		//Validate Supplied Data
	  $uprow = jal_productupdate( $product, $ver, $custom );
	  
	  if ($uprow != null) {
			$kyreply->upid = $uprow->id;
			$kyreply->product = $uprow->product;
			$kyreply->version = $uprow->version;
			$kyreply->vernum = $uprow->vernum;
			$kyreply->custom = $uprow->custom;
			$kyreply->url = $uprow->url;
			$kyreply->notesurl = $uprow->notesurl;

			$kyreply->message = "Upgrade Available";
			$kyreply->valid = "TRUE";
    } else {
			$kyreply->message = "No Upgrade";
			$kyreply->valid = "FALSE";
    }

		echo $kyreply->to_json();
		exit;

}
add_action( 'wp_ajax_nopriv_upgrade', 'kyjax_upgrade' );
add_action( 'wp_ajax_upgrade', 'kyjax_upgrade' );

function kyjax_upgrade_add() {

	$kyreply = new ResultObject();
	$product = $_POST['product'];
	$version = $_POST['version'];
	$vernum = $_POST['vernum'];
	$custom = $_POST['custom'];
	$url = $_POST['url'];
	$notesurl = $_POST['notesurl'];


	$rowaff = jal_addUpgrade( $product, $version, $vernum, $custom, $url, $notesurl );
	if ($rowaff > 0) {
		$kyreply->message = "Upgrade Added Sucessfully";
		$kyreply->valid = "TRUE";
	} else {
		$kyreply->message = "Add Upgrade Failed";
		$kyreply->valid = "FALSE";
	}

	header( "Content-Type: application/json" );
	echo $kyreply->to_json();
	exit;
}
add_action( 'wp_ajax_upgrade_add', 'kyjax_upgrade_add' );

function kyjax_upgrade_edit() {

	$kyreply = new ResultObject();
	$id = $_POST['id'];
	$product = $_POST['product'];
	$version = $_POST['version'];
	$vernum = $_POST['vernum'];
	$custom = $_POST['custom'];
	$url = $_POST['url'];
	$notesurl = $_POST['notesurl'];


	$updated = jal_editUpgrade( $id, $product, $version, $vernum, $custom, $url, $notesurl );
	if ($updated) {
		$kyreply->message = "Upgrade Edited Sucessfully";
		$kyreply->valid = "TRUE";
	} else {
		$kyreply->message = "Edit Upgrade Failed";
		$kyreply->valid = "FALSE";
	}

	header( "Content-Type: application/json" );
	echo $kyreply->to_json();
	exit;
}
add_action( 'wp_ajax_upgrade_edit', 'kyjax_upgrade_edit' );


function kyjax_reg_edit() {

	$kyreply = new ResultObject();
	$id = $_POST['id'];
	$name = $_POST['name'];
	$address = $_POST['address'];
	$state = $_POST['state'];
	$city = $_POST['city'];
	$phone = $_POST['phone'];
	$email = $_POST['email'];
	$contact = $_POST['contact'];
	$product = $_POST['product'];
	$version = $_POST['version'];
	$custom = $_POST['custom'];
	$zipcode = $_POST['zipcode'];


	$updated = jal_editRegistration( $id, $product, $version, $name, $address, $state, $city, $phone, $email, $contact, $custom, $zipcode );
	if ($updated) {
		$kyreply->message = "Registration Edited Sucessfully";
		$kyreply->valid = "TRUE";
	} else {
		$kyreply->message = "Edit Registration Failed";
		$kyreply->valid = "FALSE";
	}

	header( "Content-Type: application/json" );
	echo $kyreply->to_json();
	exit;
}
add_action( 'wp_ajax_reg_edit', 'kyjax_reg_edit' );

//*************** END AJAX API Calls ***********************

