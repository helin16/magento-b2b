<?php
/**
 * BE AWARE!!!!! THIS COULD DROP ALL YOUR DATABASE'S DATA!!!!!!!
 * this is a tool to generate database schema automatically!
 * @package core
 * @subpackage database
 * @author Lin He<lhe@bytecraft.com.au>
 */
class SchemaGenerator
{
	private $isVersioned = array();
	const MAP_FILE="Map.txt";
	const EXCLUDING_FILES="BaseEntityAbstract.php;TreeEntityAbstract.php;InfoAbstract.php;InfoTypeAbstract.php;InfoEntityAbstract.php;.svn";
	
	const DB_DRIVER="mysql";
	const DB_PORT="3306";
	const DB_ENGINE="innodb";
	
	private $db_host;
	private $db_database;
	private $db_username;
	private $db_password;
	
	private $directory;
	private $exludes;
	private $id;
	
	private function _directoryToArray($directory, $recursive, array $exclude)
	{
		$exclude[] = ".";
		$exclude[] = "..";
		$array_items = array();
		if ($handle = opendir($directory)) 
		{
			while (false !== ($file = readdir($handle))) 
			{
				if(!in_array($file,$exclude)) 
				{
					if (is_dir($directory. "/" . $file) && $recursive) 
					{
						$array_items = array_merge($array_items, $this->_directoryToArray($directory. "/" . $file, $recursive,$exclude));
					}
					$file = $directory . '/' . $file;
					
					if (is_file($file))
						$array_items[] = $file;
				}
			}
			closedir($handle);
		}
		return $array_items;
	}

	public function __construct($id, $directory="", array $exclude=array())
	{
		$this->id = $id;
		if(!is_array($exclude) || count($exclude)==0)
			$this->exludes = explode(";",self::EXCLUDING_FILES);
			
		if(trim($directory)=="")
			$this->directory = dirname(__FILE__)."/../main/entity/";
	}
	
	private function _generateDropTable($class,$data)
	{
		if (isset($data['_']['engine']) && $this->db_host != 'localhost')
			return array();
			
		$output = array();
		$output[] = "DROP TABLE IF EXISTS `$class`;\n";
		foreach($data as $var => $mods)
		{
			if(isset($mods['rel']) && $mods['rel'] == DaoMap::MANY_TO_MANY && $mods['side'] == DaoMap::LEFT_SIDE)
			{
				$right = strtolower(substr($class,0,1)).substr($class,1);
				$left = strtolower(substr($mods['class'],0,1)).substr($mods['class'],1);
				$mm = strtolower($left)."_".strtolower($right);

				$output[] = "DROP TABLE IF EXISTS `$mm`;\n";
			}
		}

		return $output;
	}
	
	private function _generateDrop()
	{
		$output = array();
		$map = DaoMap::$map;
		foreach($map as $class => $value)
		{
			$output = array_merge($output,$this->_generateDropTable($class,$value));			
		}
		return $output;
	}
	
	private function _generateIndex($class, $fields, $data)
	{
		for ($i=0; $i<count($fields); $i++)
		{
			if (isset($data[$fields[$i]]['rel']))
			{
				$fields[$i] .= 'Id';
			}
		}
		
		$fields = '`' . implode('`,`', $fields) . '`';
		$output = "INDEX ($fields)\n";
		return $output;
	}
	
	private function _generateUniqueIndex($class, $fields, $data)
	{
		for ($i=0; $i<count($fields); $i++)
		{
			if (isset($data[$fields[$i]]['rel']))
			{
				$fields[$i] .= 'Id';
			}
		}
		
		$fields = '`' . implode('`,`', $fields) . '`';
		$output = "UNIQUE INDEX ($fields)\n";
		return $output;
	}
	
	private function _generateCreateTable($class, $data,$dbInserts=false)
	{
		$mms = array();
		$mm = null;
		
		$output = "CREATE TABLE `$class` (\n";
		$output .= "\t`id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n";
		
		foreach($data as $var => $mods)
		{
			if($var == '_')
				continue;
						
			if(isset($mods['rel']))
			{
				if($mods['rel'] == DaoMap::ONE_TO_ONE && $mods['owner'])
				{
					$output .= $this->_generateVariable($var.'Id', $mods);
				} else if($mods['rel'] == DaoMap::MANY_TO_ONE)
				{
					$output .= $this->_generateVariable($var.'Id', $mods);
				} else if($mods['rel'] == DaoMap::ONE_TO_MANY)
				{
					
				} else if($mods['rel'] == DaoMap::MANY_TO_MANY && $mods['side'] == DaoMap::LEFT_SIDE)
				{
					// CREATE MANY TO MANY
					$right = strtolower(substr($class,0,1)).substr($class,1);
					$left = strtolower(substr($mods['class'],0,1)).substr($mods['class'],1);
					$mm = "CREATE TABLE `".strtolower($left)."_".strtolower($right)."` (\n";
					$mm .= "\t`".$left."Id` int(10) unsigned NOT NULL,\n";
  					$mm .= "\t`".$right."Id` int(10) unsigned NOT NULL,\n";
  					$mm .= "\t`created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,\n";
					$mm .= "\t`createdById` int(10) unsigned NOT NULL,\n";
  					$mm .= "\tUNIQUE KEY `uniq_".$left."_".$right."` (`".$left."Id`,`".$right."Id`),\n";
  					$mm .= "\tKEY `idx_".$left."_".$right."_".$left."Id` (`".$left."Id`),\n";
  					$mm .= "\tKEY `idx_".$left."_".$right."_".$right."Id` (`".$right."Id`)\n";
					$mm .= ") ENGINE=".self::DB_ENGINE." DEFAULT CHARSET=utf8;\n";
					$mms[] = $mm;
				}
			} else 
				$output .= $this->_generateVariable($var, $mods);
		}
		
		// These are automatically added by the DaoMap via HydraEntity
//		$output .= "\t`active` int(1) NOT NULL default 1,\n";
//		$output .= "\t`created` datetime NOT NULL,\n";
//		$output .= "\t`createdBy` int(10) unsigned NOT NULL,\n";
//		$output .= "\t`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
//		$output .= "\t`updatedBy` int(10) unsigned NOT NULL,\n";
		
		$output .= "\tPRIMARY KEY (`id`)\n";
		
		if (isset($data['_']['index']))
		{
			foreach ($data['_']['index'] as $fields)
			{
				$output .= "\t," . $this->_generateIndex($class, $fields, $data);
			}
		}
		
		if (isset($data['_']['unique']))
		{
			foreach ($data['_']['unique'] as $fields)
			{
				$output .= "\t," . $this->_generateUniqueIndex($class, $fields, $data);
			}
		}
		
		$output .= ") ";
		
		if ((strtolower(self::DB_ENGINE) == 'ndbcluster' || strtolower(self::DB_ENGINE) == 'ndb') && isset($data['_']['tablespace']))
		{
			$output .= 'TABLESPACE ' . $data['_']['tablespace'] . ' STORAGE DISK ';
		}
		
		$output .= "ENGINE=" . self::DB_ENGINE . " DEFAULT CHARSET=utf8;\n";
		
		return array_merge(array($output),$mms);
	}

	private function _generateRawVariable($var,$mods)
	{
		return "`$var` ".$this->_generateDatatype($mods).$this->_generateSigned($mods).$this->_generateNull($mods).$this->_generateDefault($mods);
	}
	
	private function _generateVariable($var,$mods)
	{
		return "\t".$this->_generateRawVariable($var,$mods).",\n";
	}
	
	private function _generateDatatype($mods)
	{
		switch(strtolower($mods['type']))
		{
			case 'tinytext':
			case 'text':
			case 'mediumtext':
			case 'largetext':
			case 'tinyblob':
			case 'blob':
			case 'mediumblob':
			case 'largeblob':
			case 'longtext':
			case 'date':
			case 'datetime':
			case 'timestamp':
			case 'bool':
			case 'float':
				return $mods['type'].' ';
				break;
			default:
				return $mods['type'].'('.$mods['size'].') ';
				break;
		}
	}
	
	private function _generateSigned($mods)
	{
		return (isset($mods['unsigned']) && $mods['unsigned'] === true) ? 'unsigned ' : '';
	}
	
	private function _generateNull($mods)
	{
		return (isset($mods['nullable']) && $mods['nullable'] === true) ? 'NULL ' : 'NOT NULL ';
	}
	
	private function _generateDefault($mods)
	{
		$output = "";

		if (stripos($mods['type'], 'text') !== false || stripos($mods['type'], 'blob') !== false)
			return '';
			
		// Special case
		if ($mods['type'] == 'timestamp')
			return 'DEFAULT ' . $mods['default'];
		
		if(isset($mods['default']))
		{
			$value = $mods['default'];
			$output .= "DEFAULT ";
			if((isset($mods['rel']) && $mods['nullable']))
			{
				$output .= "NULL";
			} else if(is_string($value))
			{
				$output .= "'$value'";
			} else if(is_bool($value))
			{
				$output .= ($value === true) ? "true" : "false";
			} else if(is_integer($value))
			{
				$output .= $value;
			} else {
				// BAD
				$output .= $value;
			}
		}
		
		return $output;
	}
	
	private function _generateCreate($dbInserts=false)
	{
		//var_dump(DaoMap::$map);
		$output = array();
		$map = DaoMap::$map;
		foreach($map as $class => $value)
		{
			$output = array_merge($output,$this->_generateCreateTable($class, $value,$dbInserts));
		}
		return $output;		
	}
	
	public function updateClassCheck($class,$newValues,$oldValues,$doDrop=false)
	{
		$output = array();
		// Do drop alters
		foreach($oldValues as $field => $mods)
		{
			if($field == '_')
				continue;
				
			if(!isset($newValues[$field]))
			{			
				// DROP (ALTER NULLABLE)
				if(isset($mods['rel']))
				{
					if($mods['rel'] == DaoMap::ONE_TO_ONE && $mods['owner'])
					{
						if($doDrop)
							$output[] = "ALTER TABLE `$class` CHANGE `$field"."Id` NULL ;\n";
						else
							$output[] = "ALTER TABLE `$class` DROP `$field"."Id`;\n";
					} else if($mods['rel'] == DaoMap::MANY_TO_ONE)
					{
						if($doDrop)
							$output[] = "ALTER TABLE `$class` CHANGE `$field"."Id` NULL ;\n";
						else
							$output[] = "ALTER TABLE `$class` DROP `$field"."Id`;\n";  
					} else if($mods['rel'] == DaoMap::ONE_TO_MANY)
					{
						
					} else if($mods['rel'] == DaoMap::MANY_TO_MANY && $mods['side'] == DaoMap::LEFT_SIDE)
					{
						
					}
				} else 
					if($doDrop)
						$output[] = "ALTER TABLE `$class` CHANGE `$field` NULL ;\n";
					else
						$output[] = "ALTER TABLE `$class` DROP `$field`;\n";
				
			}
		}
		
		
		// Do Create Update Alters
		foreach($newValues as $field => $mods)
		{
			if($field == '_')
				continue;
							
			if(isset($oldValues[$field]))
			{
				// Update
				if($mods != $oldValues[$field])
				{
					if(isset($mods['rel']))
					{
						if($mods['rel'] == DaoMap::ONE_TO_ONE && $mods['owner'])
						{
							$output[] = "ALTER TABLE `$class` CHANGE `$field` ".$this->_generateRawVariable($field.'Id',$mods).";\n";
						} else if($mods['rel'] == DaoMap::MANY_TO_ONE)
						{
							$output[] = "ALTER TABLE `$class` CHANGE `$field` ".$this->_generateRawVariable($field.'Id',$mods).";\n";
						} else if($mods['rel'] == DaoMap::ONE_TO_MANY)
						{
							
						} else if($mods['rel'] == DaoMap::MANY_TO_MANY && $mods['side'] == DaoMap::LEFT_SIDE)
						{
	
						}
					} else 
						$output[] = "ALTER TABLE `$class` CHANGE `$field` ".$this->_generateRawVariable($field,$mods).";\n";						
				}
			} else {
				// Create
				if(isset($mods['rel']))
				{
					if($mods['rel'] == DaoMap::ONE_TO_ONE && $mods['owner'])
					{
						$output[] = "ALTER TABLE `$class` ADD ".$this->_generateRawVariable($field.'Id', $mods).";\n";
					} else if($mods['rel'] == DaoMap::MANY_TO_ONE)
					{
						$output[] = "ALTER TABLE `$class` ADD ".$this->_generateRawVariable($field.'Id', $mods).";\n";
					} else if($mods['rel'] == DaoMap::ONE_TO_MANY)
					{
						
					} else if($mods['rel'] == DaoMap::MANY_TO_MANY && $mods['side'] == DaoMap::LEFT_SIDE)
					{

					}
				} else 
					$output[] = "ALTER TABLE `$class` ADD ".$this->_generateRawVariable($field, $mods).";\n";	
			}
		}
		
		return $output;
	}
	
	private function _generateUpdate($previousMap,$doDrops=false,$dbInserts=false)
	{
		$output = array();
		$map = DaoMap::$map;
		
		
		if($doDrops)
		{
			// Do drops
			foreach($previousMap as $class => $value)
			{			
				if(!isset($map[$class]))
				{
					$output = array_merge($output,$this->_generateDropTable($class,$value));
				}
			}
		}	
		
		// Do Creates and Updates
		foreach($map as $class => $value)
		{
			// Check If class Exists in old schema
			if(isset($previousMap[$class]))
			{
				// Check sub elements
				$output = array_merge($output,$this->updateClassCheck($class,$value,$previousMap[$class]));
								
			} else {
				// Create Class
				$output = array_merge($output,$this->_generateCreateTable($class, $value,$dbInserts));
			}
		}
		
		return $output;
	}

	private function _saveMap($file)
	{
		$map = DaoMap::$map;
		$output = serialize($map);
		file_put_contents($file,$output);
	}
	
	public function _loadMap($file)
	{
		return unserialize(file_get_contents($file));
	}
	
	private function _setupDatabase($importFile=null,$echoDropCreate=false,$echoImport=false,$dbInserts=false)
	{
		$drop = $this->_generateDrop();
		$create = $this->_generateCreate();
		
		if(sizeof($drop) != sizeof($create))
		{
			throw new Exception("Size of Created Drop and Create Command lists isnt equal (".sizeof($drop).",".sizeof($create)."), Note from Peter Beardsley: This is a bad thing you should investigate.");
		}
		
		if($echoDropCreate)
			echo "-- Setting Up Database\n";
		Dao::connect();
		for($i=0;$i<sizeof($drop);$i++)
		{
			if($echoDropCreate)
				echo $drop[$i];
				
			if($dbInserts)
				Dao::execSql($drop[$i]);
				
			if($echoDropCreate)
				echo $create[$i];
				
			if($dbInserts)				
				Dao::execSql($create[$i]);
		}

		if($importFile != null)
		{

			if($echoImport)
				echo "\n\nImporting Data ($importFile)\n";

			try {
				$data = explode("\n",file_get_contents($importFile));
			} catch (Exception $e)
			{
				echo "File \"$importFile\" Failed to open.\n";
				return; 	
			}
			
			foreach($data as $row)
			{
				$sql = trim($row);
				if(strlen($sql) > 0)
				{
					$dont = false;
					try 
					{
						if($dbInserts)
							Dao::execSql($sql);
					} 
					catch (Exception $e)
					{
						echo $sql."\n";
						echo $e->getMessage()."\n";
						$dont = true;
					}
					
					if($echoImport && !$dont)
						echo $sql."\n";
		
				}
			}
		}
	}
	
	public function run($argv=array())
	{
		$this->db_host = isset($argv["server"]) ? trim($argv["server"]) : "";
		$this->db_database = isset($argv["database"]) ? trim($argv["database"]) : "";
		$this->db_username = isset($argv["username"]) ? trim($argv["username"]) : "";
		$this->db_password = isset($argv["password"]) ? trim($argv["password"]) : "";
		
		$importFile = isset($argv["sampleData"]) ? trim($argv["sampleData"]) : null;
		$echoCRUD = (isset($argv['echoCrud']) && strtoupper(trim($argv['echoCrud']))=="YES") ? true : false;
		$echoIMPORT = (isset($argv['echoImport']) && strtoupper(trim($argv['echoImport']))=="YES") ? true : false;
		$doUpdate = (isset($argv['doUpdate']) && strtoupper(trim($argv['doUpdate']))=="YES") ? true : false;
		$doDrop = (isset($argv['doDrop']) && strtoupper(trim($argv['doDrop']))=="YES") ? true : false;
		$saveMap = (isset($argv['saveMap']) && strtoupper(trim($argv['saveMap']))=="YES") ? true : false;
		$dbInserts = (isset($argv['dbInserts']) && strtoupper(trim($argv['dbInserts']))=="YES") ? true : false;
		
		$uploadedFile = (isset($argv['genType']) && trim($argv['genType'])=="showFileOnly") ? true : false;
		
		set_time_limit(1000);
		echo "<pre>";
		//generate Schema from a uploaded file
		if($uploadedFile)
		{
			$fileName = $_FILES['uploadedfile']['name'];
			if($fileName=="")
				die("No Entity Field Provided!");
			$className = str_replace(".php","",$fileName);
			$tmpFile =$_FILES['uploadedfile']['tmp_name'];
			$tmpClassName =basename($tmpFile);
			
			$fileContent = file_get_contents($tmpFile);
			echo "</pre><input type='button' value=\"show $fileName file\" onclick=\"var doc = document.getElementById('fileContent'); if(doc.style.display==''){doc.style.display='none';} else {doc.style.display='';} \"/><div id='fileContent' style='display:none; background:#cccccc; border: 1px #000000 solid;'><pre>";
				echo htmlentities($fileContent);
			echo "</pre></div><pre>";
			file_put_contents($tmpFile,str_replace("class $className","class $tmpClassName",$fileContent));
			
			require_once $tmpFile;
			$obj  = new $tmpClassName();
			$obj->__loadDaoMap();
			
			$output = array();
			$map = DaoMap::$map;
			foreach($map as $class => $value)
			{
				$output = array_merge($output,$this->_generateCreateTable($tmpClassName,DaoMap::$map[strtolower($tmpClassName)]));
			}
			echo str_replace(strtolower($tmpClassName),strtolower($className),str_replace("$tmpClassName",strtolower($className),implode("",$output)));
		}
		else
		{
			$entityDir = (isset($argv['entityDir']) && trim($argv['entityDir'])!="") ? trim($argv['entityDir']) : $this->directory;
			foreach($this->_directoryToArray($entityDir, true, $this->exludes) as $file)
			{
				$path = pathinfo($file);
				$class = new $path['filename']();
				try
				{
					$class->__loadDaoMap();
					$this->isVersioned[strtolower(get_class($class))] = ($class instanceof HydraVersionedEntity);
				} 
				catch (Exception $e)
				{}
			}
			
			if($doUpdate)
			{
				echo "-- Started Update Patch --\n\n";
				
				$a = $this->_generateUpdate($this->_loadMap(self::MAP_FILE),$doDrop,$dbInserts);
				foreach($a as $i)
					echo $i;
			
				echo "\n-- Completed Update Patch --.";
			} 
			else if($saveMap)
			{
				$this->_saveMap(self::MAP_FILE);
				echo "\n-- Saved Map.";
			} 
			else 
			{
				$this->_setupDatabase($importFile,$echoCRUD,$echoIMPORT,$dbInserts);
				echo "\n-- Completed CRUD Setup.";
			}
		}
		
		echo "</pre>";
	}
	
	public function form($defaultServer='localhost',$defaultDatabase='hydra',$defaultUsername='root',$defaultPassword='')
	{
		$html = "";
		$html .= "<h3>Schema Generator</h3>";
		$html .= "<style>";
			$html .= "form#shemaForm .title{width:80px; float:left;}";
			$html .= "form#shemaForm .trDiv{padding: 2px 0 2px 0;}";
			$html .= "form#shemaForm .inputText{width:450px;}";
		$html .= "</style>";
		$html .= "<p>Schema generator will generate the database schema automatically. Also, as a option, you can load some data in as well.</p>";
			$html .= "<form id='shemaForm' enctype='multipart/form-data'  style='width:600px; border:1px #cccccc solid;' action='".$_SERVER["SCRIPT_NAME"]."' method='post'>";
				
//				$html .="<div class='trDiv'>";
//					$html .= "<div class='title'>Host: </div>";
//					$html .= "<input id='server' name='server' class='inputText' value='$defaultServer'/>";
//				$html .="</div>";
//				
//				$html .="<div class='trDiv'>";
//					$html .= "<div class='title'>Database: </div>";
//					$html .= "<input id='database' name='database' class='inputText' value='$defaultDatabase' />";
//				$html .="</div>";
//				
//				$html .="<div class='trDiv'>";
//					$html .= "<div class='title'>Username: </div>";
//					$html .= "<input id='username' name='username' class='inputText' value='$defaultUsername' />";
//				$html .="</div>";
//				
//				$html .="<div class='trDiv'>";
//					$html .= "<div class='title'>Password: </div>";
//					$html .= "<input id='password' name='password' type='password' class='inputText' value='$defaultPassword' />";
//				$html .="</div>";
//				
//				$html .="<br /><br />";
				
				$html .="<div class='trDiv' style='display:none;'>";
					$html .= "<div class='title'>Echo Schema: </div>";
					$html .= "<select id='echoCrud' name='echoCrud' class='inputText'>";
							$html .= "<option value='yes' selected='true'>Yes</option>";
							$html .= "<option value='no'>No</option>";
					$html .= "</select>";
				$html .="</div>";
				
//				$html .="<div class='trDiv'>";
//					$html .= "<div class='title'>Echo Import: </div>";
//					$html .= "<select id='echoImport' name='echoImport' class='inputText'>";
//							$html .= "<option value='yes'>Yes</option>";
//							$html .= "<option value='no'>No</option>";
//					$html .= "</select>";
//				$html .="</div>";
				
//				$html .="<div class='trDiv'>";
//					$html .= "<div class='title'>Execute on DB: </div>";
//					$html .= "<select id='dbInserts' name='dbInserts' class='inputText'>";
//							$html .= "<option value='no'>No</option>";
//							$html .= "<option value='yes'>Yes</option>";
//					$html .= "</select>";
//				$html .="</div>";
				
//				$html .="<div class='trDiv' style='display:none;'>";
//					$html .= "<div class='title'>Load Sample: </div>";
//					$html .= "<select id='sampleData' name='sampleData' class='inputText'>";
//							$html .= "<option value=''>Do not load sample data.</option>";
//						$dir =dirname(__FILE__);
//						if ($handle = opendir($dir)) 
//						{
//							while (false !== ($file = readdir($handle))) 
//							{
//								if(strstr($file,".sql"))
//									$html .= "<option value='$dir/$file'>$file</option>";
//							}
//							closedir($handle);
//						}
//					$html .= "</select>";
//				$html .="</div>";
				
				$html .="<div class='trDiv'>";
					$html .= "<div class='title' >Gen From: </div>";
					$html .= "<div class='inputText'>";
							$html .= "<input type='radio' value='showAll' name='genType' checked='checked' onClick=\"document.getElementById('shemaForm_{$this->id}').style.display='none';\"/>From All Files On Test Server";
							$html .= "<input type='radio' value='showFileOnly'  name='genType' onClick=\"document.getElementById('shemaForm_{$this->id}').style.display='';\"/>From a uploaded file";
					$html .= "</div>";
				$html .="</div>";
				$html .="<div class='trDiv' id='shemaForm_{$this->id}' style='display:none;'>";
					$html .= "<div class='title' >&nbsp; </div>";
					$html .= "<div class='inputText'>";
						$html .= "<input name='uploadedfile' type='file'/>";
					$html .= "</div>";
				$html .="</div>";
				
				$html .="<div class='trDiv'>";
					$html .= "<input type='submit' name='submit' value='Generate Schema' style='width:95%;' />";
					$html .= "<input type='hidden' name='submitForm' value='{$this->id}'/>";
				$html .="</div>";
				
			$html .= "</form>";
		echo $html;
	}
	
	public function getId()
	{
		return $this->id;
	}
}
?>