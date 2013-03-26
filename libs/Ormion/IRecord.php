<?php

namespace Ormion;

/**
 * Interface Record
 *
 * @author Jan Marek, Michal Koutny
 * @license MIT
 */
interface IRecord
{
	const STATE_NEW = 1;
	const STATE_EXISTING = 2;
	const STATE_DELETED = 3;

	/**
	 * Gets hash of the record for comparison due to OCC
	 * @return string
	 */
	public function getHash();
}