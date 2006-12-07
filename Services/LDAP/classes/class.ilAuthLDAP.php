<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* Auth LDAP overwrites PEAR Auth to perform LDAP authentication with specific ILIAS options
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesLDAP 
*/

include_once('Auth.php');

class ilAuthLDAP extends Auth
{
	private $ldap_server = null;
	private $ldap_attr_to_user = null;
	private $log = null;
	private $logCache = '';
	
	public function __construct()
	{
		global $ilLog;
		
		$this->log = $ilLog;
		
		// Read setting of LDAP server
		$this->initServer();
		parent::Auth('LDAP',$this->ldap_server->toPearAuthArray(),'',false);
		
		// Set callbacks
		$this->setCallbacks();
	}
	
	/** 
	 * Called from base class after successful login
	 *
	 * @param string username
	 */
	protected function loginObserver($a_username)
	{
		$user_data = array_change_key_case($this->getAuthData(),CASE_LOWER);
		
		$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("ldap",$a_username);
		$users[$a_username] = $user_data;
		
		if($this->ldap_server->enabledSyncOnLogin())
		{
			// Refresh user data
			$this->initLDAPAttributeToUser();
			$this->ldap_attr_to_user->setUserData($users);
			$this->ldap_attr_to_user->refresh();
			
			// set auth 
			$this->setAuth($user_data['ilInternalAccount'] ? $user_data['ilInternalAccount'] : $a_username);
			return;
		}
		
		// No sync
		if(!$user_data['ilInternalAccount'])
		{
			// No syncronisation allowed => create Error
			$this->status = AUTH_LDAP_NO_ILIAS_USER;
			$this->logout();
			return;
		}
		// Finally setAuth
	}
	
	/** 
	 * Called from base class after failed login
	 *
	 * @param string username
	 */
	protected function failedLoginObserver()
	{
		#$this->log->write($this->logCache);
		$this->logCache = '';
	}
	
	
	/** 
	 * update user
	 *
	 * @param string username
	 * @return bool success status
	 */
	private function updateUser()
	{
		
	}
	
	/**
	 * Init LDAP attribute mapping
	 *
	 * @access private
	 * 
	 */
	private function initLDAPAttributeToUser()
	{
		include_once('Services/LDAP/classes/class.ilLDAPAttributeToUser.php');
		$this->ldap_attr_to_user = new ilLDAPAttributeToUser($this->ldap_server);
	}

	private function initServer()
	{
		include_once 'Services/LDAP/classes/class.ilLDAPServer.php';
		$this->ldap_server = new ilLDAPServer(ilLDAPServer::_getFirstActiveServer());
	}
	
	/** 
	 * Set callback function for PEAR Auth 
	 *
	 */
	private function setCallbacks() 
	{
		parent::setLoginCallback(array($this,'loginObserver'));
		parent::setFailedLoginCallback(array($this,'failedLoginObserver'));
	}
	
}
?>