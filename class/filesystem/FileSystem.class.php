<?php

namespace core\filesystem;

uses ("core.exceptions.IOException","core.exceptions.IllegalArgumentException","core.base.Quobject");


/**
 * Class FileSystem
 *
 * @deprecated The Quantum Framework
 * @author Tristan Cebulla <t.cebulla@lichtspiele.org>
 * @copyright 2006 - 2009
 * @package core.filesystem
 *
 * @var object $_type
 * @var String $_handle
 * @var String $_pointer
 */
class FileSystem extends \core\base\Quobject {
	private $_type		= NULL;	## valid types are File, Directory or Device

	private $_handle	= NULL;
	private $_pointer	= NULL;

	/**
	 * class constructor
	 */
	function __construct($handle = null, $type = "File") {
		parent::__construct();
        $this->setType($type);
		if (!is_null($handle)) $this->setHandle($handle);
	}

	/**
	 * class destructor
	 */
	function __destruct() {
		parent::__destruct();
	}

	/**
	 * reset handle and its pointer
	 */
	public function reset() {
		debug("resetting Filesystem class attributes",$this);
		if (!is_null($this->_pointer)) {
			$this->closePointer();
		}
		$this->setType();
		$this->_handle = NULL;
	}

	/**
	 * set the handle type
	 * this is "File" be default, but can be anything we can find in a filesystem ("File","Directory")
	 *
	 * @var String $type
	 */
	public function setType($type="File") {
	#$this->_type = $type;
		// suppress empty handle logs
		if (!is_null($this->_handle)) {
			$this->_type = $type;
			debug("Handle type set to '$type' for handle ".$this->_handle,$this);
		}
	}

	/**
	 * return the handle type
	 *
	 * @returns String handle-type
	 */
	public function getType() {
		return $this->_type;
	}

	/**
	 * sets the handle, may be a file name or directory locator
	 *
	 * @var String $handle - handle name (e.g. a file or directory)
	 */
	public function setHandle($handle) {
		$this->_handle = $handle;
		debug("setting FS handle to $handle",$this);
	
        $this->setType("File");
	
		if (is_link($handle)) $this->setType("Link");
		else if (is_file($handle)) $this->setType("File");
		else if (is_dir($handle)) $this->setType("Directory");
	}

	/**
	 * return the value of the current handle
	 * @returns String handle name
	 */
	public function getHandle() {
		return $this->_handle;
	}

	/**
	 * check if a handle exists
	 * This uses the file_exists(); method, which tests files and directories.
	 *
	 * @var String $handle
	 * @returns bool
	 */
	public function exists($handle = null) {
        if (is_null($handle)) $handle = $this->getHandle();

		if (substr($handle,0,1) == "/") $basepath = "";
		else $basepath = APP_ROOT."/";

		return file_exists($basepath."/".$handle);
	}

	/**
	 * open a pointer
	 * this opens a pointer handle for a file or directory
	 *
	 * @var String mode (any of the fopen modes - default is "r")
	 * @returns bool (true if pointer has been set, false if not)
	 * @throws IllegalArgumentException
	 */
	private function openPointer($mode="r") {
		switch ($this->_type) {
			case "File":
				$this->_pointer = @fopen($this->_handle,$mode);
				break;
			case "Directory":
				$this->_pointer = @opendir($this->_handle);
				break;
			default:
				throw new \core\exceptions\IllegalArgumentException("Pointer type '".$this->_type."' is not yet implemented");
		}

		if (!is_resource($this->_pointer)) {
			throw new \core\exceptions\IOException("Failed creating pointer type '".$this->_type."' for handle '".$this->_handle."'");
		}
		debug("created FS pointer (".$this->_pointer.")",$this);
		return true;
	}

	/**
	 * close a pointer
	 *
	 * @returns bool
	 * @access private
	 */
	private function closePointer() {
		if (!is_null($this->_pointer)) {
			switch ($this->_type) {
				case "File":
					fclose($this->_pointer);
					break;
				case "Directory":
					closedir($this->_pointer);
					break;
				default:
					debug("Could not apply any close method to pointer (".$this->_pointer.").",$this);
					break;
			}
			debug("closing FS pointer (".$this->_pointer.")",$this);
			unset($this->_pointer);
			$this->_pointer = NULL;
		}
		return true;
	}

	/**
	 * delete a (non-empty) directory recursively
	 *
	 * @returns bool true
	 */
	private function rmdir_recurse($path) {
		$path = rtrim($path, '/').'/';
		$handle = opendir($path);
		while(false !== ($file = readdir($handle))) {
			if($file != '.' and $file != '..' ) {
				$fullpath = $path.$file;
				if(is_dir($fullpath))	$this->rmdir_recurse($fullpath);
				else			unlink($fullpath);
			}
		}
		closedir($handle);
		rmdir($path);
	}

	public function delete($recursive=false) {
		switch ($this->_type) {
			case 'File':
				unlink($this->_handle);
				debug("deleted file ".$this->_handle,$this);
				break;
			case 'Directory':
				if (!$recursive) {
					rmdir($this->_handle); // directory must be empty!
					debug("removed directory ".$this->_handle,$this);
				} else {
					$this->rmdir_recurse($this->_handle);
					debug("removed directory ".$this->_handle." recursively",$this);
				}
				break;
			case 'Link':
				unlink($this->_handle);
				debug("deleted link ".$this->_handle,$this);
				break;
			default:
				throw new \core\exceptions\IllegalArgumentException("Only files and directories can be deleted.");
		}
		return true;
	}

	public function create($mode="") {
		switch ($this->_type) {
			case 'File':
				if (!touch($this->_handle)) {
					throw new \core\exceptions\IOException("Could not create file ".$this->_handle);
				}
				if (!empty($mode)) {
					$this->chmod($mode);
				}
				break;
			case 'Directory':
				if (empty($mode)) $mode = "755";
				if (!mkdir($this->_handle,$mode)) {
					throw new \core\exceptions\IOException("Could not create directory ".$this->_handle);
				}
				break;
			default:
				throw new \core\exceptions\IllegalArgumentException("File type '".$this->_type."' not yet implemented");
		}
		return true;
	}

	public function cd() {
		switch ($this->_type) {
			case Directory:
				if ($this->exists($this->_handle)) {
					chdir($this->_handle);
					debug("changed directory to ".$this->_handle,$this);
				} else {
					throw new \core\exceptions\IOException("Directory ".$this->_handle." does not exist.");
				}
				break;
			default:
				throw new \core\exceptions\IllegalArgumentException("Can only change to directories.");
		}
		return true;
	}

	public function rename($destination) {
		if ($this->exists($destination)) {
			debug("destination ".$destination." already exists and will be overwritten or something like this while renaming ".$this->_handle.".",$this);
		}
		if (!rename($this->_handle,$destination)) {
			throw new \core\exceptions\IOException("Could not rename ".$this->_handle." to ".$destination.".");
		}
		debug("renamed ".$this->_handle." to $destination",$this);
		return true;
	}

	public function copy($destination) {
		switch ($this->_type) {
			case 'File':
				if (exists($destination)) {
					debug("copy: $destination already exists and will be overwritten!",$this);
				}

				if (!copy($this->_handle,$destination)) {
					throw new \core\exceptions\IOException("Could not copy ".$this->_handle." to ".$destination.".");
				}
				debug("copied ".$this->_handle." to $destination",$this);
				break;
			case 'Directory':
				throw new \core\exceptions\IllegalArgumentException("Not yet implemented");
				break;
			default:
				throw new \core\exceptions\IllegalArgumentException("Only File and Directory handles can be copied.");
		}
		return true;
	}

	/**
	 * move
	 * this is just an alias to rename();
	 */
	public function move($destination) {
		return $this->rename($destination);
	}

	/**
	 * please make sure, that the links' name has *NO* / at the end! 
	 */
	public function link($link,$hard=false) {
		if (!$this->exists($link)) {
			if ($hard) {
				link($this->_handle,$link);
				debug("created hard link from ".$this->_handle." to $link",$this);
			} else {
				symlink($this->_handle,$link);
				debug("created soft link from $link to ".$this->_handle,$this);
			}
		} else {
			throw New \core\exceptions\IOException($link." already exists");
		}
		return true;
	}

	public function is_link($handle="") {
		if (empty($handle)) $handle = $this->_handle;
		return is_link($handle);
	}

	public function read($handle="") {
		if (!empty($handle)) $this->setHandle($handle);
		if (is_null($this->_pointer)) $this->openPointer();

		debug("performing read operation on handle ".$this->_handle." (type ".$this->_type.")",$this);

#        echo "type for ".$this->_handle." = ".$this->_type."\n";

		switch ($this->_type) {
			case "File":
				$buffer = NULL;
				while (!feof($this->_pointer)) {
					$buffer .= fgets($this->_pointer, 4096);
				}
				break;
			case "Directory":
#                echo "bin da\n";
				$buffer = array();
				while (false !== ($file = readdir($this->_pointer))) {
					if ($file != "." && $file != "..") {
						array_push($buffer,$file);
					}
				}
				break;
			default:
				throw new \core\exceptions\IllegalArgumentException("Only File and Directory handles can be read.");
		}

		$this->closePointer();

		return $buffer;
	}

	public function write($content,$mode="a") {
		switch ($this->_type) {
			case "File": 
				if (is_null($this->_pointer)) $this->openPointer($mode);
		
#				if (!fwrite($this->_pointer,$content,strlen($content))) {
				if (!fwrite($this->_pointer,$content)) {
					throw new \core\exceptions\IOException("Could not write to file ".$this->_handle);
				}
#				debug("Writing contents to file",$this);
			break;
			default:
				throw new \core\exceptions\IllegalArgumentException("Not yet implemented for type '".$this->_type."'");
		}
		return true;
	}

	public function close() {
		return $this->closePointer();
	}

	public function chown($user) {
		if (is_link($this->_handle)) {
			lchown($this->_handle,$user);
		} else {
			chown($this->_handle,$user);
		}
		debug("changed user ownership to $user for handle ".$this->_handle,$this);
		return true;
	}

	public function chgrp($group) {
		if (is_link($this->_handle)) {
			lchgrp($this->_handle,$group);
		} else {
			chgrp($this->_handle,$group);
		}
		debug("changed group ownership to $group for handle ".$this->_handle,$this);
		return true;
	}

	public function chmod($mode) {
		if ( (!is_string($mode)) || (strlen($mode) != 4) ) throw new \core\exceptions\IllegalArgumentException("chmod mode must be a numeric string, no integer variable and 4-digits long (e.g. 0644)");

		switch ($this->_type) {
			case 'File':
			case 'Directory':
				chmod($this->_handle,octdec($mode));
				break;
			default:
				throw new \core\exceptions\IllegalArgumentException("Not yet implemented");
		}
		debug("changed mode to ".$mode." (decimal ".octdec($mode).") for handle ".$this->_handle,$this);
		return true;
	}

	/**
	 * self destruction
	 */
	public function destroy() {
		$this->reset();
		$this->__destruct();
	}

}

?>
