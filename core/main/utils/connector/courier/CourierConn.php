<?php
interface CourierConn
{
	/**
	 * Creating a manifest for the delivery
	 *
	 * @param string $userId The id of the user(from the courier) is making the manifest
	 *
	 * @throws Exception
	 * @return mixed
	 */
	public function createManifest($userId = '');
	/**
	 * Creating a consignment note for the delivery
	 *
	 * @param Shippment $shippment  The Shippment
	 * @param string    $manifestId The manifest id from the courier
	 *
	 * @throws Exception
	 * @return mixed
	 */
	public function createConsignment(Shippment &$shippment, $manifestId = '');
	/**
	 * Closing a manifest
	 *
	 * @param string $manifestId The ID of a manifest
	 *
	 * @throws Exception
	 * @return mixed
	 */
	public function closeManifest($manifestId);
	/**
	 * Getting the tracking url for a label
	 *
	 * @param string $label The lable that we are trying to track
	 *
	 * @return string
	 */
	public function getTrackingURL($label);
	/**
	 * removing a manifest and all its consignments in it
	 * 
	 * @param string $manifestId
	 * 
	 * @return CourierConn
	 */
	public function removeManifest($manifestId);
}