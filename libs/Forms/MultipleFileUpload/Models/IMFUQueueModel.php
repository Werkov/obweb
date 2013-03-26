<?php
use Nette\Http\FileUpload;

interface IMFUQueueModel {

	/**
	 * getts queues model
	 * @return IMFUQueuesModel
	 */
	function getQueuesModel();

	/**
	 *setts queues model
	 * @param IMFUQueuesModel $model
	 */
	function setQueuesModel(IMFUQueuesModel $model);

	/**
	 * Getts queue ID
	 * @return string
	 */
	function getQueueID();

	/**
	 * Setts queue ID
	 * @param string $queueID
	 */
	function setQueueID($queueID);

	/**
	 * When was queue last accessed?
	 * @return int timestamp
	 */
	function getLastAccess();

	/**
	 * Initializes driver
	 */
	function initialize();

	/**
	 * Adds file to queue
	 * @param FileUpload $file
	 */
	function addFile(FileUpload $file);

	/**
	 * Getts all files in queue
	 * @return array of HttpUploadedFile
	 */
	function getFiles();

	/**
	 * Deletes queue
	 */
	function delete();

}