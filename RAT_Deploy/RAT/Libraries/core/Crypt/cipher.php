<?php
/**
 * @package     T.Platform
 * @subpackage  Crypt
 */

defined('T_PLATFORM') or die;

/**
 * TCrypt cipher interface.
 *
 * @package     T.Platform
 * @subpackage  Crypt
 * @since       5/2014
 */
interface TCryptCipher
{
	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string     $data  The encrypted string to decrypt.
	 * @param   TCryptKey  $key   The key[/pair] object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   5/2014
	 */
	public function decrypt($data, TCryptKey $key);

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string     $data  The data string to encrypt.
	 * @param   TCryptKey  $key   The key[/pair] object to use for encryption.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   5/2014
	 */
	public function encrypt($data, TCryptKey $key);

	/**
	 * Method to generate a new encryption key[/pair] object.
	 * 
	 * @param   array  $options  Key generation options.
	 * 
	 * @return  TCryptKey
	 * 
	 * @since   5/2014
	 */
	public function generateKey(array $options = array());
}
