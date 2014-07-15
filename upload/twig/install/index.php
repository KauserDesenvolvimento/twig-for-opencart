<?php

/**
 *
 * @package Twig installation script
 * @author Lucas <lucas@kauser.com.br>
 * @author Victor <victor@logixdigital.com.br>
 * @copyright 2014
 * @version 0.1
 *
 * @information
 * This file will perform all necessary file alterations for the
 * OpenCart index.php files both in the root directory and in the
 * Administration folder. Please note that if you have changed your
 * default folder name from admin to something else, you will need
 * to edit the admin/index.php in this file to install successfully
 *
 * @warning
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESSED OR IMPLIED.
 *
 */

// CHANGE THIS IF YOU EDIT YOUR ADMIN FOLDER NAME
$admin = 'admin';

// Counters
$changes = 0;
$writes = 0;

// Load class required for installation
require('ugrsr.class.php');

// Get directory two above installation directory
$opencart_path = realpath(dirname(__FILE__) . '/../../') . '/';

// Verify path is correct
if(!$opencart_path) die('COULD NOT DETERMINE CORRECT FILE PATH');

$write_errors = array();
if(!is_writeable($opencart_path . 'index.php')) {
	$write_errors[] = 'index.php not writeable';
}
if(!is_writeable($opencart_path . $admin . '/index.php')) {
	$write_errors[] = 'Administrator index.php not writeable';
}

if(!empty($write_errors)) {
	die(implode('<br />', $write_errors));
}

// Create new UGRSR class
$u = new UGRSR($opencart_path);

// remove the # before this to enable debugging info
#$u->debug = true;

// Set file searching to off
$u->file_search = false;

// Set the index files
$u->addFile('index.php');
$u->addFile($admin . '/index.php');

// Check if VQMOD is installed
$u->addPattern('~(require_once\(\'\.\.?\/vqmod\/vqmod\.php\'\);)~','$1');
$result = $u->run();
if ($result['changes'] > 0) {
	$vqmod = true;
} else {
	$vqmod = false;
}

$u->clearPatterns();

// Check if TWIG is installed
$u->addPattern('~(\/\/ Twig for Opencart)~','$1');
$result = $u->run();
if ($result['changes'] > 0) {
	die('TWIG ALREADY INSTALLED!');
} else {
	$twig = false;
}

$u->clearPatterns();


// Include our autoloader and register
if ($vqmod) {
	$u->addPattern('~(require_once\((.*)[\'"]startup\.php[\'"]\)(.*);)~', '$1
require_once(VQMod::modCheck(DIR_SYSTEM . \'library/vendor/autoload.php\'));
Twig_Autoloader::register();
	');
} else {
	$u->addPattern('~(require_once\((.*)[\'"]startup\.php[\'"]\)(.*);)~', '$1
require_once(DIR_SYSTEM . \'library/vendor/autoload.php\');
Twig_Autoloader::register();
	');
}

$result = $u->run();
$writes += $result['writes'];
$changes += $result['changes'];

$u->clearPatterns();

// Load twig and create a new enviroment
$u->addPattern('~(\$registry \= new Registry\(\);)~', '$1
// Twig for Opencart
$loader = new Twig_Loader_Filesystem(DIR_TEMPLATE);
$twig = new Twig_Environment($loader);
');

$result = $u->run();
$writes += $result['writes'];
$changes += $result['changes'];


$u->resetFileList();

if(!$changes) die('TWIG ALREADY INSTALLED!');
if($writes != 4) die('ONE OR MORE FILES COULD NOT BE WRITTEN');
die('TWIG HAS BEEN INSTALLED ON YOUR SYSTEM!');
