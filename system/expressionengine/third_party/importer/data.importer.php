<?php if ( ! defined('BASEPATH') ) exit('No direct script access allowed');

/**
 * Importer - Data Models
 *
 * @package		Solspace:Importer
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2016, Solspace, Inc.
 * @link		https://solspace.com/expressionengine/importer
 * @license		https://solspace.com/software/license-agreement
 * @version		2.2.6
 * @filesource	importer/data.importer.php
 */

require_once 'addon_builder/data.addon_builder.php';

class Importer_data extends Addon_builder_data_importer
{

	// --------------------------------------------------------------------

	/**
	 * Importer Profile
	 *
	 * @access	public
	 * @return	array - associative array of name => filepath
	 */

	function importer_profiles($params = array())
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Perform the Actual Work
		//  --------------------------------------------

		$data_sources = array();

		$query = ee()->db->query("SELECT value, profile_id FROM exp_importer_profile_settings
								  WHERE setting = 'data_source'");

		foreach($query->result_array() as $row)
		{
			$data_sources[$row['profile_id']] = $row['value'];
		}

		$query = ee()->db->query('SELECT * FROM exp_importer_profiles');

		if ($query->num_rows() == 0)
		{
			return array();
		}

		foreach($query->result_array() as $row)
		{
			$row['data_source'] = (isset($data_sources[$row['profile_id']])) ? $data_sources[$row['profile_id']] : '';

			$this->cached[$cache_name][$cache_hash][$row['profile_id']] = $row;
		}

		//  --------------------------------------------
		//   Return Data
		//  --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}
	// END importer_profiles

	// --------------------------------------------------------------------

	/**
	 * Get List of Channels for Each Site
	 *
	 * @access	public
	 * @return	array - associate multi-dimensional array site_id => channel_id => channel_title
	 */

	function get_channels_per_site($params = array())
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Perform the Actual Work
		//  --------------------------------------------

		$query = ee()->db->query("SELECT {$this->sc->db->channel_id} AS channel_id,
										 {$this->sc->db->channel_title} AS channel_title,
										 site_id
								FROM {$this->sc->db->channels}
								ORDER BY site_id, {$this->sc->db->channel_name}");

		foreach($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][$row['site_id']][$row['channel_id']] = $row['channel_title'];
		}

		//  --------------------------------------------
		//   Return Data
		//  --------------------------------------------

		return $this->cached[$cache_name][$cache_hash];
	}
	// END get_channels_per_site

	// --------------------------------------------------------------------

	/**
	 *	Get Statuses for Channel ID
	 *
	 *	@access	public
	 *
	 *	@return	object
	 */
	function get_statuses_for_channel_id($channel_id, $group_id = 1)
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Fetch Disallowed Statuses
		//  --------------------------------------------

		$no_status_access = array();

		if ($group_id != 1)
		{
			ee()->db->select('status_id');
			ee()->db->from('exp_status_no_access');
			ee()->db->where('member_group', $group_id);

			$result = ee()->db->get();

			if ($result->num_rows() > 0)
			{
				foreach ($result->result_array() as $row)
				{
					$no_status_access[] = $row['status_id'];
				}
			}
		}

		//  --------------------------------------------
		//   Fetch Statuses
		//  --------------------------------------------

		ee()->lang->load('content'); // Includes language for open/closed statuses

		ee()->db->from('exp_statuses AS s');
		ee()->db->join('exp_channels AS c', 's.group_id = c.status_group', 'left');
		ee()->db->where('c.channel_id', $channel_id);

		if ( ! empty($no_status_access))
		{
			ee()->db->where_not_in('s.status_id', $no_status_access);
		}

		ee()->db->order_by('status_order');
		$query = ee()->db->get();

		foreach($query->result_array() as $row)
		{
			$status_name = (in_array($row['status'], array('open','closed')) ? lang($row['status']) : $row['status']);
			$this->cached[$cache_name][$cache_hash][$row['status']] = $status_name;
		}

		if ( count($this->cached[$cache_name][$cache_hash]) == 0)
		{
			$this->cached[$cache_name][$cache_hash]['closed'] = lang('closed');
		}

		return $this->cached[$cache_name][$cache_hash];
	}
	// END get_statuses_for_channel_id()

	// --------------------------------------------------------------------

	/**
	 * Get Channel Authors
	 *
	 * Returns a list of available authors for channels
	 *
	 * @access	public
	 * @param	integer
	 * @return	mixed
	 */
	function get_channel_authors($limit = FALSE, $offset = FALSE)
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Perform the Actual Work
		//  --------------------------------------------

		ee()->db->select('m.member_id, m.group_id, m.username, m.screen_name, m.in_authorlist');
		ee()->db->from('members AS m');
		ee()->db->join("exp_member_groups AS mg", "m.group_id = mg.group_id", 'left');

		ee()->db->where('(m.in_authorlist = "y" OR mg.include_in_authorlist = "y")');
		ee()->db->where('mg.site_id', ee()->config->item('site_id'));

		ee()->db->order_by('m.screen_name', 'ASC');
		ee()->db->order_by('m.username', 'ASC');

		if ($limit)
		{
			ee()->db->limit($limit, $offset);
		}

		$query = ee()->db->get();

		foreach($query->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][$row['member_id']] = $row;
		}

		return $this->cached[$cache_name][$cache_hash];
	}
	// END get_channel_authors()


	// --------------------------------------------------------------------

	/**
	 *	Get Field Groups for Channel ID
	 *
	 *	@access	public
	 *
	 *	@return	object
	 */
	function get_custom_fields_for_channel_id($channel_id)
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Perform the Actual Work
		//  --------------------------------------------

		ee()->db->select('cf.field_label, cf.field_id, cf.field_name, cf.field_type, cf.field_required, cf.field_fmt, cf.field_list_items');
		ee()->db->from('exp_channel_fields AS cf');
		ee()->db->join('exp_channels AS c', 'cf.group_id = c.field_group', 'left');
		ee()->db->where('c.channel_id', $channel_id);
		ee()->db->order_by('cf.field_order');

		$query = ee()->db->get();

		foreach($query->result_array() as $row)
		{
			if ( ! empty($row['field_list_items']))
			{
				$options = preg_split('/[\r\n]+/', $row['field_list_items'], -1, PREG_SPLIT_NO_EMPTY);
				array_walk($options, create_function('&$val', '$val = trim($val);'));
				$row['field_list_items'] = $options;
			}

			$this->cached[$cache_name][$cache_hash][$row['field_id']] = $row;
		}

		return $this->cached[$cache_name][$cache_hash];
	}
	// END get_custom_fields_for_channel_id()


	// --------------------------------------------------------------------

	/**
	 *	Get Field IDs for Blog and Text fields
	 *
	 *	@access	public
	 *
	 *	@return	object
	 */
	function get_custom_field_ids()
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Perform the Actual Work
		//  --------------------------------------------

		$all_fields = ee()->db->field_data('channel_data');

		foreach ($all_fields as $field)
		{
			if (strncmp($field->name, 'field_id_', 9) == 0)
			{
				$this->cached[$cache_name][$cache_hash][] = $field->name;
			}
		}

		return $this->cached[$cache_name][$cache_hash];
	}
	// END get_text_custom_fields()

	// --------------------------------------------------------------------

	/**
	 *	Get Categories for Channel ID
	 *
	 *	@access	public
	 *	@param  integer
	 *	@return	object
	 */
	function get_categories_for_channel_id($channel_id)
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Perform the Actual Work
		//  --------------------------------------------

		$sql = "SELECT  c.cat_id, c.cat_name, c.parent_id, c.group_id
				FROM	exp_categories AS c, exp_channels
				WHERE   FIND_IN_SET(c.group_id, REPLACE(exp_channels.cat_group, '|', ','))
				AND     exp_channels.channel_id = '".ee()->db->escape_str($channel_id)."' #Categories for Channel ID";

		$query = ee()->db->query($sql);

		return $this->cached[$cache_name][$cache_hash] = $query->result_array();
	}

	// --------------------------------------------------------------------

	/**
	 *	Get Categories for Category Group ID
	 *
	 *	@access	public
	 *	@param  integer
	 *	@return	object
	 */
	function get_categories_for_group_id($group_id)
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Perform the Actual Work
		//  --------------------------------------------

		$sql = "SELECT  c.cat_id, c.cat_name, c.parent_id, c.group_id
				FROM    exp_categories AS c
				WHERE   c.group_id = '".ee()->db->escape_str($group_id)."' #Categories for Group ID";

		$query = ee()->db->query($sql);

		return $this->cached[$cache_name][$cache_hash] = $query->result_array();
	}

	// --------------------------------------------------------------------

	/**
	 *	Get Assigned Channels for Group ID
	 *
	 *	@access	public
	 *	@param	integer  $group_id
	 *	@return	array
	 */
	function get_assigned_channels($group_id)
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Perform the Actual Work
		//  --------------------------------------------

		if ($group_id == 1)
		{
			$result = ee()->db->query("SELECT channel_id FROM exp_channels #Assigned channels");
		}
		else
		{
			$result = ee()->db->query("SELECT channel_id
										FROM exp_channel_member_groups
										WHERE group_id = '".ee()->db->escape_str($group_id)."' #Assigned channels");
		}

		foreach ($result->result_array() as $row)
		{
			$this->cached[$cache_name][$cache_hash][] = $row['channel_id'];
		}

		return $this->cached[$cache_name][$cache_hash];
	}
	// END get_assigned_channels()


	// --------------------------------------------------------------------

	/**
	 *	Get Basic Profile Info
	 *
	 *	@access	public
	 *	@param	integer  $profile_id
	 *	@return	array
	 */
	function get_profile_data($profile_id)
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Perform the Actual Work
		//  --------------------------------------------

		$query = ee()->db->where('profile_id', $profile_id)->get('exp_importer_profiles');

		foreach ($query->result_array() as $row)
		{
			foreach($row as $key => $val)
			{
				$this->cached[$cache_name][$cache_hash][$key] = $val;
			}
		}

		return $this->cached[$cache_name][$cache_hash];
	}
	// END get_profile_data()


	// --------------------------------------------------------------------

	/**
	 *	Get Solspace Tags
	 *
	 *	@access	public
	 *	@return	array
	 */
	function get_solspace_tags($binary = '')
	{
		//  --------------------------------------------
		//   Prep Cache, Return if Set
		//  --------------------------------------------

		$cache_name = __FUNCTION__;
		$cache_hash = $this->_imploder(func_get_args());

		if (isset($this->cached[$cache_name][$cache_hash]))
		{
			return $this->cached[$cache_name][$cache_hash];
		}

		$this->cached[$cache_name][$cache_hash] = array();

		//  --------------------------------------------
		//   Perform the Actual Work
		//  --------------------------------------------

		$sql = "SELECT t.tag_id, t.tag_name FROM exp_tag_tags AS t";

		$query = ee()->db->query($sql);

		foreach ($query->result_array() as $row)
		{
			if ($binary != 'n')
			{
				$row['tag_name'] = strtolower($row['tag_name']);
			}

			$this->cached[$cache_name][$cache_hash][] = $row;
		}

		return $this->cached[$cache_name][$cache_hash];
	}
	// END get_solspace_tags()

}
// END CLASS Importer_data