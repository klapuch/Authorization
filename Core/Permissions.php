<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

interface Permissions {
	/**
	 * Go through all the permissions
	 * @return \Traversable
	 */
	public function all(): \Traversable;
}