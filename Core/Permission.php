<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

interface Permission {
	/**
	 * Is the given resource allowed by the permission
	 * @param string $resource
	 * @return bool
	 */
	public function allowed(string $resource): bool;
}