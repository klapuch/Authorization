<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Permissions for particular role
 */
final class RolePermissions implements Permissions {
	private $role;
	private $origin;

	public function __construct(string $role, Permissions $origin) {
		$this->role = $role;
		$this->origin = $origin;
	}

	public function getIterator(): \Traversable {
		return new \CallbackFilterIterator(
			$this->origin->getIterator(),
			function(Permission $permission): bool {
				return $permission->role() === $this->role;
			}
		);
	}
}