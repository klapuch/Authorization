<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

interface Role {
	/**
	 * Is the given resource allowed by the role?
	 * @param string $resource
	 * @return bool
	 */
	public function allowed(string $resource): bool;
}