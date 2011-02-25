<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";

/**
* Class ilObjWorkspaceFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjWorkspaceFolderGUI: ilInfoScreenGUI, ilPermissionGUI, 
*
* @extends ilObject2GUI
*/
class ilObjWorkspaceFolderGUI extends ilObject2GUI
{
	var $folder_tree;		// folder tree

	function getType()
	{
		return "wsfold";
	}

	function setTabs()
	{
		global $lng;

		$this->ctrl->setParameter($this,"wsp_id",$this->node_id);

		if ($this->getAccessHandler()->checkAccess('read', '', $this->node_id))
		{
			$this->tabs_gui->addTab('view_content', $lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}

		if ($this->getAccessHandler()->checkAccess('write', '', $this->node_id))
		{
			$force_active = ($_GET["cmd"] == "edit")
				? true
				: false;
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this)
				, "", $force_active);
		}
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$this->prepareOutput();
				if(!$cmd)
				{
					$cmd = "render";
				}
				$this->$cmd();
				break;
		}

		return true;
	}

	/**
	* Render root folder
	*/
	function render()
	{
		global $tpl;
		
		include_once "Modules/WorkspaceFolder/classes/class.ilObjWorkspaceFolderTableGUI.php";
		$table = new ilObjWorkspaceFolderTableGUI($this, "render", $this->node_id);
		$tpl->setContent($table->getHTML());
	}
}

?>