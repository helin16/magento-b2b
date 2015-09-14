<?php

/**
 * TSecurityManager class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.pradosoft.com/
 * @copyright Copyright &copy; 2005-2014 PradoSoft
 * @license http://www.pradosoft.com/license/
 * @package System.Security
 */

/**
 * TSecurityManager class
 *
 * TSecurityManager provides private keys, hashing and encryption
 * functionalities that may be used by other PRADO components,
 * such as viewstate persister, cookies.
 *
 * TSecurityManager is mainly used to protect data from being tampered
 * and viewed. It can generate HMAC and encrypt the data.
 * The private key used to generate HMAC is set by {@link setValidationKey ValidationKey}.
 * The key used to encrypt data is specified by {@link setEncryptionKey EncryptionKey}.
 * If the above keys are not explicitly set, random keys will be generated
 * and used.
 *
 * To prefix data with an HMAC, call {@link hashData()}.
 * To validate if data is tampered, call {@link validateData()}, which will
 * return the real data if it is not tampered.
 * The algorithm used to generated HMAC is specified by {@link setValidation Validation}.
 *
 * To encrypt and decrypt data, call {@link encrypt()} and {@link decrypt()}
 * respectively. The encryption algorithm can be set by {@link setEncryption Encryption}.
 *
 * Note, to use encryption, the PHP Mcrypt extension must be loaded.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package System.Security
 * @since 3.0
 */
class TSecurityManager extends TModule
{
	const STATE_VALIDATION_KEY = 'prado:securitymanager:validationkey';
	const STATE_ENCRYPTION_KEY = 'prado:securitymanager:encryptionkey';

	private $_validationKey = null;
	private $_encryptionKey = null;
	private $_hashAlgorithm = 'sha1';
	private $_cryptAlgorithm = 'rijndael-256';
	private $_mbstring;

	/**
	 * Initializes the module.
	 * The security module is registered with the application.
	 * @param TXmlElement initial module configuration
	 */
	public function init($config)
	{
		$this->_mbstring=extension_loaded('mbstring');
		$this->getApplication()->setSecurityManager($this);
	}

	/**
	 * Generates a random key.
	 */
	protected function generateRandomKey()
	{
		return sprintf('%08x%08x%08x%08x',mt_rand(),mt_rand(),mt_rand(),mt_rand());
	}

	/**
	 * @return string the private key used to generate HMAC.
	 * If the key is not explicitly set, a random one is generated and returned.
	 */
	public function getValidationKey()
	{
		if(null === $this->_validationKey) {
			if(null === ($this->_validationKey = $this->getApplication()->getGlobalState(self::STATE_VALIDATION_KEY))) {
				$this->_validationKey = $this->generateRandomKey();
				$this->getApplication()->setGlobalState(self::STATE_VALIDATION_KEY, $this->_validationKey, null, true);
			}
		}
		return $this->_validationKey;
	}

	/**
	 * @param string the key used to generate HMAC
	 * @throws TInvalidDataValueException if the key is empty
	 */
	public function setValidationKey($value)
	{
		if('' === $value)
			throw new TInvalidDataValueException('securitymanager_validationkey_invalid');

		$this->_validationKey = $value;
	}

	/**
	 * @return string the private key used to encrypt/decrypt data.
	 * If the key is not explicitly set, a random one is generated and returned.
	 */
	public function getEncryptionKey()
	{
		if(null === $this->_encryptionKey) {
			if(null === ($this->_encryptionKey = $this->getApplication()->getGlobalState(self::STATE_ENCRYPTION_KEY))) {
				$this->_encryptionKey = $this->generateRandomKey();
				$this->getApplication()->setGlobalState(self::STATE_ENCRYPTION_KEY, $this->_encryptionKey, null, true);
			}
		}
		return $this->_encryptionKey;
	}

	/**
	 * @param string the key used to encrypt/decrypt data.
	 * @throws TInvalidDataValueException if the key is empty
	 */
	public function setEncryptionKey($value)
	{
		if('' === $value)
			throw new TInvalidDataValueException('securitymanager_encryptionkey_invalid');

		$this->_encryptionKey = $value;
	}

	/**
	 * This method has been deprecated since version 3.2.1.
	 * Please use {@link getHashAlgorithm()} instead.
	 * @return string hashing algorithm used to generate HMAC. Defaults to 'sha1'.
	 */
	public function getValidation()
	{
		return $this->_hashAlgorithm;
	}

	/**
	 * @return string hashing algorithm used to generate HMAC. Defaults to 'sha1'.
	 */
	public function getHashAlgorithm()
	{
		return $this->_hashAlgorithm;
	}

	/**
	 * This method has been deprecated since version 3.2.1.
	 * Please use {@link setHashAlgorithm()} instead.
	 * @param TSecurityManagerValidationMode hashing algorithm used to generate HMAC.
	 */
	public function setValidation($value)
	{
		$this->_hashAlgorithm = TPropertyValue::ensureEnum($value, 'TSecurityManagerValidationMode');
	}

	/**
	 * @param string hashing algorithm used to generate HMAC.
	 */
	public function setHashAlgorithm($value)
	{
		$this->_hashAlgorithm = TPropertyValue::ensureString($value);
	}

	/**
	 * This method has been deprecated since version 3.2.1.
	 * Please use {@link getCryptAlgorithm()} instead.
	 * @return string the algorithm used to encrypt/decrypt data.
	 */
	public function getEncryption()
	{
		if(is_string($this->_cryptAlgorithm))
			return $this->_cryptAlgorithm;
		// fake the pre-3.2.1 answer
		return "3DES";
	}

	/**
	 * This method has been deprecated since version 3.2.1.
	 * Please use {@link setCryptAlgorithm()} instead.
	 * @param string cipther name
	 */
	public function setEncryption($value)
	{
		$this->_cryptAlgorithm = $value;
	}

	/**
	 * @return mixed the algorithm used to encrypt/decrypt data. Defaults to the string 'rijndael-256'.
	 */
	public function getCryptAlgorithm()
	{
		return $this->_cryptAlgorithm;
	}

	/**
	 * Sets the crypt algorithm (also known as cipher or cypher) that will be used for {@link encrypt} and {@link decrypt}.
	 * @param mixed either a string containing the cipther name or an array containing the full parameters to call mcrypt_module_open().
	 */
	public function setCryptAlgorithm($value)
	{
		$this->_cryptAlgorithm = $value;
	}

	/**
	 * Encrypts data with {@link getEncryptionKey EncryptionKey}.
	 * @param string data to be encrypted.
	 * @return string the encrypted data
	 * @throws TNotSupportedException if PHP Mcrypt extension is not loaded
	 */
	public function encrypt($data)
	{
		$module=$this->openCryptModule();
		$key = $this->substr(md5($this->getEncryptionKey()), 0, mcrypt_enc_get_key_size($module));
		srand();
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);
		mcrypt_generic_init($module, $key, $iv);
		$encrypted = $iv.mcrypt_generic($module, $data);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return $encrypted;
	}

	/**
	 * Decrypts data with {@link getEncryptionKey EncryptionKey}.
	 * @param string data to be decrypted.
	 * @return string the decrypted data
	 * @throws TNotSupportedException if PHP Mcrypt extension is not loaded
	 */
	public function decrypt($data)
	{
		$module=$this->openCryptModule();
		$key = $this->substr(md5($this->getEncryptionKey()), 0, mcrypt_enc_get_key_size($module));
		$ivSize = mcrypt_enc_get_iv_size($module);
		$iv = $this->substr($data, 0, $ivSize);
		mcrypt_generic_init($module, $key, $iv);
		$decrypted = mdecrypt_generic($module, $this->substr($data, $ivSize, $this->strlen($data)));
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return $decrypted;
	}

	/**
	 * Opens the mcrypt module with the configuration specified in {@link cryptAlgorithm}.
	 * @return resource the mycrypt module handle.
	 * @since 3.2.1
	 */
	protected function openCryptModule()
	{
		if(extension_loaded('mcrypt'))
		{
			if(is_array($this->_cryptAlgorithm))
				$module=@call_user_func_array('mcrypt_module_open',$this->_cryptAlgorithm);
			else
				$module=@mcrypt_module_open($this->_cryptAlgorithm,'', MCRYPT_MODE_CBC,'');

			if($module===false)
				throw new TNotSupportedException('securitymanager_mcryptextension_initfailed');

			return $module;
		}
		else
			throw new TNotSupportedException('securitymanager_mcryptextension_required');
	}

	/**
	 * Prefixes data with an HMAC.
	 * @param string data to be hashed.
	 * @return string data prefixed with HMAC
	 */
	public function hashData($data)
	{
		$hmac = $this->computeHMAC($data);
		return $hmac.$data;
	}

	/**
	 * Validates if data is tampered.
	 * @param string data to be validated. The data must be previously
	 * generated using {@link hashData()}.
	 * @return string the real data with HMAC stripped off. False if the data
	 * is tampered.
	 */
	public function validateData($data)
	{
		$len=$this->strlen($this->computeHMAC('test'));

		if($this->strlen($data) < $len)
			return false;

		$hmac = $this->substr($data, 0, $len);
		$data2=$this->substr($data, $len, $this->strlen($data));
		return $hmac === $this->computeHMAC($data2) ? $data2 : false;
	}

	/**
	 * Computes the HMAC for the data with {@link getValidationKey ValidationKey}.
	 * @param string data to be generated HMAC
	 * @return string the HMAC for the data
	 */
	protected function computeHMAC($data)
	{
		$key = $this->getValidationKey();

		if(function_exists('hash_hmac'))
			return hash_hmac($this->_hashAlgorithm, $data, $key);

		if(!strcasecmp($this->_hashAlgorithm,'sha1'))
		{
			$pack = 'H40';
			$func = 'sha1';
		} else {
			$pack = 'H32';
			$func = 'md5';
		}

		$key = str_pad($func($key), 64, chr(0));
		return $func((str_repeat(chr(0x5C), 64) ^ substr($key, 0, 64)) . pack($pack, $func((str_repeat(chr(0x36), 64) ^ substr($key, 0, 64)) . $data)));
	}

	/**
	 * Returns the length of the given string.
	 * If available uses the multibyte string function mb_strlen.
	 * @param string $string the string being measured for length
	 * @return int the length of the string
	 */
	private function strlen($string)
	{
		return $this->_mbstring ? mb_strlen($string,'8bit') : strlen($string);
	}

	/**
	 * Returns the portion of string specified by the start and length parameters.
	 * If available uses the multibyte string function mb_substr
	 * @param string $string the input string. Must be one character or longer.
	 * @param int $start the starting position
	 * @param int $length the desired portion length
	 * @return string the extracted part of string, or FALSE on failure or an empty string.
	 */
	private function substr($string,$start,$length)
	{
		return $this->_mbstring ? mb_substr($string,$start,$length,'8bit') : substr($string,$start,$length);
	}
}

/**
 * TSecurityManagerValidationMode class.
 *
 * This class has been deprecated since version 3.2.1.
 *
 * TSecurityManagerValidationMode defines the enumerable type for the possible validation modes
 * that can be used by {@link TSecurityManager}.
 *
 * The following enumerable values are defined:
 * - MD5: an MD5 hash is generated from the data and used for validation.
 * - SHA1: an SHA1 hash is generated from the data and used for validation.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package System.Security
 * @since 3.0.4
 */
class TSecurityManagerValidationMode extends TEnumerable
{
	const MD5 = 'MD5';
	const SHA1 = 'SHA1';
}
