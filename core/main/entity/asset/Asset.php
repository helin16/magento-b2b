<?php
/**
 * Entity for tracking location of Asset assets in shared storage
 *
 * @package    Core
 * @subpackage Entity
 * @author     lhe<helin16@gmail.com>
 */
class Asset extends BaseEntityAbstract
{
	/**
	 * @var string
	 */
	private $assetId;
	/**
	 * @var string
	 */
	private $filename;
	/**
	 * @var string
	 */
	private $mimeType;
	/**
	 * The path
	 * 
	 * @var string
	 */
	private $path;
	/**
	 * getter assetId
	 *
	 * @return string
	 */
	public function getAssetId()
	{
		return $this->assetId;
	}
	/**
	 * setter assetId
	 * 
	 * @param string $assetId The asset Id
	 * 
	 * @return Asset
	 */
	public function setAssetId($assetId)
	{
		$this->assetId = $assetId;
		return $this;
	}
	/**
	 * getter filename
	 *
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}
	/**
	 * setter filename
	 * 
	 * @param string $filename The filename of the asset
	 * 
	 * @return Asset
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
		return $this;
	}
	/**
	 * getter mimeType
	 *
	 * @return string
	 */
	public function getMimeType()
	{
		return $this->mimeType;
	}
	/**
	 * setter mimeType
	 * 
	 * @param string $mimeType The mimeType
	 * 
	 * @return Asset
	 */
	public function setMimeType($mimeType)
	{
		$this->mimeType = $mimeType;
		return $this;
	}
	/**
	 * Getter for the path
	 * 
	 * @return string
	 */
	public function getPath()
	{
	    return $this->path;
	}
	/**
	 * Setter for the path
	 * 
	 * @param string $path The path
	 * 
	 * @return Asset
	 */
	public function setPath($path)
	{
	    $this->path = $path;
	    return $this;
	}
	/**
	 * (non-PHPdoc)
	 * @see BaseEntityAbstract::__toString()
	 */
	public function __toString()
	{
	    return '/assets/get/?id=' . $assetId;
	}
	/**
	 * (non-PHPdoc)
	 * @see HydraEntity::__loadDaoMap()
	 */
	public function __loadDaoMap()
	{
		DaoMap::begin($this, 'con');
		
		DaoMap::setStringType('assetId', 'varchar', 32);
		DaoMap::setStringType('filename', 'varchar', 100);
		DaoMap::setStringType('mimeType', 'varchar', 50);
		DaoMap::setStringType('path', 'varchar', 200);
		parent::__loadDaoMap();
		
		DaoMap::createUniqueIndex('assetId');
		DaoMap::commit();
	}
}

?>