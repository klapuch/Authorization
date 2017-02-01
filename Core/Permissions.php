<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

interface Permissions {
	/**
	 * All the resources
	 * @return array
	 */
	public function resources(): array;
}