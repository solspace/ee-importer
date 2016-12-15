<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Importing DataType - Relationship Fields
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @filesource	importer/datatypes/importer.datatype.relationship_fields/datatype.relationship_fields.php
 */

require_once PATH_THIRD.'importer/datatype.importer.php';

class Importer_datatype_relationship_fields extends Importer_datatype
{
	public $version		    = '1.0.0';

	public $settings		= array();
	public $data_sources    = array('relationship_fields');

	public $batch_enabled   = TRUE;

	// --------------------------------------------------------------------

	/**
	 *	Constructor
	 *
	 *	@access		public
	 *	@return		string
	 */
	public function __construct()
	{
		parent::__construct();
	}
	// END constructor

	// --------------------------------------------------------------------

	/**
	 *	Returns Any Additional Data Source Fields
	 *
	 *	@access		public
	 *	@param		array	$settings - Current settings
	 *	@return		string
	 */

	public function data_source_fields_form(array $settings = array())
	{
	    $vars = array();

	    // --------------------------------------------
        //  Insure We Have Default Selections
        // --------------------------------------------

        $vars['selected_relationship_fields_channel'] = ( empty($settings['relationship_fields_channel'])) ? '' : $settings['relationship_fields_channel'];
	    $vars['selected_relationship_fields']  = ( empty($settings['relationship_fields'])) ? array() : explode('|', $settings['relationship_fields']);

        // --------------------------------------------
        //  Find Relationship Fields
        // --------------------------------------------

	    $vars['channels']	= $this->data->get_channels_per_site();
        $vars['sites']		= $this->data->get_sites();

		$query = ee()->db->query("SELECT field_id, field_label, field_settings, c.channel_id
                                  FROM exp_channel_fields AS cf, exp_channels AS c, exp_field_groups AS fg
                                  WHERE c.field_group = fg.group_id
                                  AND fg.group_id = cf.group_id
                                  AND cf.field_type = 'rel'
                                  ORDER BY c.channel_id, cf.field_order");

        foreach($query->result_array() AS $row)
        {
            $vars['relationship_fields'][$row['channel_id']]['field_id_'.$row['field_id']] = $row['field_label'];
        }

		return $this->view('profile_source_fields.html', $vars, TRUE, PATH_THIRD.'importer/datatypes/importer.datatype.relationship_fields/views/profile_data_fields.html');
	}

    // --------------------------------------------------------------------

	/**
	 *	Save Relationships Field Data ***Source***
	 *
	 *	@access		public
	 *	@param		object  The CP object
	 *	@return		array
	 */
	public function settings_relationship_fields($object)
	{
	    $vars = array(
	        'relationship_fields'           => array(),
	        'relationship_fields_channel'   => '');

	    foreach($vars as $var => $default)
	    {
	        if ( isset($_POST[$var]) && gettype($_POST[$var]) == gettype($default))
	        {
	            $vars[$var] = $_POST[$var];
	        }

	        if (is_array($vars[$var]))
	        {
	            $vars[$var] = implode('|', $vars[$var]);
	        }
	    }

	    if (empty($vars['relationship_fields']))
	    {
	        return $object->error_page('error_must_choose_valid_relationship_fields');
	    }

	    return $vars;
	}

	// --------------------------------------------------------------------

	/**
	 *	Batch Count
	 *
	 *  Returns a total count of entries that need to be processed.  Used by the Importer batch
	 *  processing routine to figure out how many batches it has to do.
	 *
	 *	@access		public
	 *	@param		array       $settings
	 *	@return		integer
	 */

    public function count_relationship_fields($settings)
    {
        if ( empty($settings['relationship_fields_channel']) OR empty($settings['relationship_fields'])) return FALSE;

	    $relations = preg_split("/[\r\n,\|]+/", $settings['relationship_fields'], -1, PREG_SPLIT_NO_EMPTY);

	    if ( empty($relations)) return FALSE;

	    // --------------------------------------------
        //  Insure there is at least one entry
        // --------------------------------------------

        $sql = "SELECT COUNT(t.entry_id) AS count
                FROM `exp_channel_titles` AS t, `exp_channel_data` AS d
                WHERE d.entry_id = t.entry_id
                AND d.channel_id = '".ee()->db->escape_str($settings['relationship_fields_channel'])."' ";

        $xsql = array();

        // Find entries where relationship fields are NOT empty
        foreach($relations as $field_id)
        {
            $xsql[] = "(`".ee()->db->escape_str($field_id)."` IS NOT NULL AND
                       `".ee()->db->escape_str($field_id)."` != '')";

        }

        if (count($xsql) == 0) return FALSE;

        $sql .= "AND (".implode(" OR ", $xsql).") ";

        return ee()->db->query($sql)->row('count');
    }

    // --------------------------------------------------------------------

	/**
	 *	Retrieve Entries for Importing ****Source****
	 *
	 *	@access		public
	 *	@param		object  The Actions object
	 *	@param		array   Settings for this Import
	 *  @param      integer Offset
	 *  @param      integer Limit
	 *	@return		array
	 */
	public function retrieve_relationship_fields($action, $settings, $offset = 0, $limit = 500)
	{
        // --------------------------------------------
        //  Retrieve the Data!
        // --------------------------------------------

        if ( empty($settings['relationship_fields']) OR empty($settings['relationship_fields_channel'])) return FALSE;

	    $relations = preg_split("/[\r\n,\|]+/", $settings['relationship_fields'], -1, PREG_SPLIT_NO_EMPTY);

	    if ( empty($relations)) return FALSE;

	    // --------------------------------------------
        //  Insure there is at least one entry
        // --------------------------------------------

        $sql = "SELECT t.entry_id, %the_fields%
                FROM `exp_channel_titles` AS t, `exp_channel_data` AS d
                WHERE d.entry_id = t.entry_id
                AND d.channel_id = '".ee()->db->escape_str($settings['relationship_fields_channel'])."'";

        $xsql = array();

        // Find entries where relationship fields are NOT empty
        foreach($relations as $field_id)
        {
            $fields[] = $field_id;

            $xsql[] = "(`".ee()->db->escape_str($field_id)."` IS NOT NULL AND
                      `".ee()->db->escape_str($field_id)."` != '')";

        }

        if (count($xsql) == 0) return FALSE;

        $sql .= "AND (".implode(" OR ", $xsql).") ";

        if ( is_numeric($offset) && is_numeric($limit))
		{
			$sql .= 'LIMIT '.ceil($offset).", ".ceil($limit);
		}

		$sql = str_replace('%the_fields%', implode(', ', $fields), $sql);

        return ee()->db->query($sql)->result_array();
	}

	// --------------------------------------------------------------------

	/**
	 *	Parse Data
	 *
	 *	@access		public
	 *	@param		string		$string - Data to be parsed, in this case simply an array to be serialized and returned
	 *	@return		array
	 */
	public function parse_data($data = '', $settings = array())
	{
	    if ( ! is_array($data)) return FALSE;

	    return $this->serialize_data_array($data);
	}
}
// END CLASS Importer_datatype_relationships