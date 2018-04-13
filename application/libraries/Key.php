<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Keys Library
 *
 * This is a basic Key Management REST controller to make and delete keys.
 *
 * @package		CodeIgniter
 * @subpackage          Key
 * @category            Library
 * @author		trivialworks
 * @link		https://github.com/rahul-trivialworks/codeIgniter-rest-api.git
*/

// This can be removed if you use __autoload() in config.php

class Key
{
        /**
        * To contain the codeigniter instance
        * Must be overridden it in a controller so that it is set
        *
        * @var variable
        */
        protected $CI;
        /**
        * This defines construtor
        * Must be overridden it in a controller so that it is set
        *
        * @def function
        */
        public function __construct() { 
            
            //creatint ci instance to access it's all properties
            $this->CI =& get_instance();
            //loading database into this library to access db driver
            $this->CI->load->database();
            //$this->CI->load->helper('form');
            
        }
        /**
	 * Key Create
	 *
	 * Insert a key into the database.
	 *
	 * @access	public
	 * @return	void
	 */
	public function generate($level = 1, $ignore_limits = 0, $user_id)
        {
            if (empty(self::_key_details($user_id, 1)))
            {
		// Build a new key
		$key = self::_generate_key($user_id);                
		// Insert the new key                                
		if (self::_insert_key($key, array('level' => $level, 'ignore_limits' => $ignore_limits, 'user_id' => $user_id)))
		{
			return $key;
		}
		else
		{
			return false;
		}
            }
            else{
                
                return self::_key_details($user_id, 1)->key;
            }
        }

	// --------------------------------------------------------------------

	/**
	 * Key Delete
	 *
	 * Remove a key from the database to stop it working.
	 *
	 * @access	public
	 * @return	void
	 */
	public function delete($key)
        {
            // Does this key even exist?
            if ( ! self::_key_exists($key))
            {
                    // NOOOOOOOOO!
                    return false;
            }

            // Kill it
            self::_delete_key($key);

            // Tell em we killed it
            return true;
    }

	// --------------------------------------------------------------------

	/**
	 * Update Key
	 *
	 * Change the level
	 *
	 * @access	public
	 * @return	void
	 */
	public function update_level($key,$new_level = 1)
        {		
            // Does this key even exist?
            if ( ! self::_key_exists($key))
            {
                    // NOOOOOOOOO!
                    return false;
            }

            // Update the key level
            if (self::_update_key($key, array('level' => $new_level)))
            {
                    return true;
            }

            else
            {
                    return false;
            }
    }

	// --------------------------------------------------------------------

	/**
	 * Update Key
	 *
	 * Change the level
	 *
	 * @access	public
	 * @return	void
	 */
	public function suspend($key, $user_id, $level)
        {		

		// Does this key even exist?
		if ( ! self::_key_exists($key, $user_id, $level))
		{
			// NOOOOOOOOO!
			return false;
		}

		// Update the key level
		if (self::_update_key($key, array('level' => 0)))
		{
			return true;
		}

		else
		{
			return false;
		}
    }

	// --------------------------------------------------------------------

	/**
	 * Regenerate Key
	 *
	 * Remove a key from the database to stop it working.
	 *
	 * @access	public
	 * @return	void
	 */
	public function regenerate($old_key, $user_id)
        {
		
		$key_details = self::_get_key($old_key);

		// The key wasnt found
		if ( ! $key_details)
		{
			// NOOOOOOOOO!
			return false;
		}

		// Build a new key
		$new_key = self::_generate_key();

		// Insert the new key
		if (self::_insert_key($new_key, array('level' => $key_details->level, 'ignore_limits' => $key_details->ignore_limits, 'user_id' => $user_id)))
		{
			// Suspend old key
			self::_update_key($old_key, array('level' => 0));

			return $new_key;
		}

		else
		{
			return false;
		}
    }

	// --------------------------------------------------------------------

	/* Helper Methods */
	
	private function _generate_key($user_id)
	{
		$this->CI->load->helper('security');				 
                do
                {
                    // Generate a random salt
                    $salt = base_convert(bin2hex($this->CI->security->get_random_bytes(64)), 16, 36);

                    // If an error occurred, then fall back to the previous method
                    if ($salt === FALSE)
                    {
                        $salt = hash('sha256', time() . mt_rand());
                    }

                    $new_key = substr($salt, 0, config_item('rest_key_length'));
                }
                while ($this->_key_exists($new_key, $user_id));

                return $new_key;
	}

	// --------------------------------------------------------------------

	/* Private Data Methods */

	private function _get_key($key)
	{
            return $this->CI->db
                ->where(config_item('rest_key_column'), $key)
                ->get(config_item('rest_keys_table'))
                ->row();
	}

	// --------------------------------------------------------------------

	private function _key_exists($key, $user_id, $level = 1)
	{
            return $this->CI->db
                ->where(array(config_item('rest_key_column') => $key, 'user_id' => $user_id/*, 'level' => $level*/))                
                ->count_all_results(config_item('rest_keys_table')) > 0;
	}
        
        // --------------------------------------------------------------------

	private function _key_details($user_id)
	{
            return $this->CI->db
                ->where(array('user_id' => $user_id, 'level' => 1))                
                ->get(config_item('rest_keys_table'))
                ->row();
	}
	// --------------------------------------------------------------------

	private function _insert_key($key, $data)
	{		 		
            $data[config_item('rest_key_column')] = $key;
            $data['date_created'] = function_exists('now') ? now() : time();
            $data['ip_addresses'] = $this->CI->input->ip_address();
            return $this->CI->db
                ->set($data)
                ->insert(config_item('rest_keys_table'));
	}

	// --------------------------------------------------------------------

	private function _update_key($key, $data)
	{
            return $this->CI->db
                ->where(config_item('rest_key_column'), $key)
                ->update(config_item('rest_keys_table'), $data);
	}

	// --------------------------------------------------------------------

	private function _delete_key($key)
	{
            return $this->CI->db
                ->where(config_item('rest_key_column'), $key)
                ->delete(config_item('rest_keys_table'));
	}                
}
