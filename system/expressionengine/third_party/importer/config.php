<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Importer - Config
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.6
 * @filesource	importer/config.php
 */

require_once 'constants.importer.php';

$config['name']									= 'Importer';
$config['version']								= IMPORTER_VERSION;
$config['nsm_addon_updater']['versions_xml'] 	= 'http://solspace.com/software/nsm_addon_updater/importer';
