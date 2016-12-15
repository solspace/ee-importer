<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Extension
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.6
 * @filesource	importer/ext.importer.php
 */

require_once 'addon_builder/extension_builder.php';

class Importer_ext extends Extension_builder_importer
{
	public $name			= "Importer";
	public $version			= "";
	public $description		= "";
	public $settings_exist	= "n";
	public $docs_url		= "http://solspace.com/docs/";

	private $importer_object	= FALSE;

	public $required_by 	= array('module');

	// --------------------------------------------------------------------

	/**
	 *	Constructor
	 *
	 *	@access		public
	 *	@param		array
	 *	@return		null
	 */

	public function __construct( $settings = '' )
	{
		// --------------------------------------------
		//  Load Parent Constructor
		// --------------------------------------------

		parent::__construct();

		// --------------------------------------------
		//  Settings!
		// --------------------------------------------

		$this->settings = $settings;
	}
	// END constructor

	// --------------------------------------------------------------------

	/**
	 * AJAX Processing via cp_js_end hook
	 *
	 * @access	public
	 * @return	null
	 */

	public function cp_js_end()
	{
		$allowed = array('importer_batch_import', 'importer_import_statistics');

		// Not the correct kind of request.
		if ( ! in_array(ee()->input->get('call'), $allowed))
		{
			return ee()->extensions->last_call;
		}

		switch(ee()->input->get('call'))
		{
			case 'importer_batch_import' :
				$this->iob()->batch_import();
			break;

			case 'importer_import_statistics' :
				$this->iob()->import_statistics();
			break;
		}
	}
	// END cp_js_end()



	// --------------------------------------------------------------------

	/**
	 * required, but unused functions for EE
	 */

	public function activate_extension(){}
	public function disable_extension(){}
	public function update_extension(){}

	// --------------------------------------------------------------------

	/**
	 *	Importer Object Setter
	 *
	 *	Create an object from the mod.importer.php file, which gives us all of the necessary
	 *	class variables and methods we need from AOB and whatnot.
	 *
	 *	@access		private
	 *	@return		string
	 */

	private function iob()
	{
		if ( ! is_object($this->importer_object))
		{
			require_once PATH_THIRD . 'importer/mod.importer.php';

			$this->importer_object = new Importer();
		}

		return $this->importer_object;
	}
	// END iob()
}

/**	END class */