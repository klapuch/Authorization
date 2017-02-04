<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Role intended to inspect HTTP based permissions
 */
final class HttpRole implements Role {
	private $role;
	private $permissions;

	public function __construct(string $role, Permissions $permissions) {
		$this->role = $role;
		$this->permissions = $permissions;
	}

	public function allowed(string $resource): bool {
		return (bool)array_uintersect(
			array_pad($this->resources($this->permissions, $this->role), 1, $resource),
			[$resource],
			'strcasecmp'
		);
	}

	private function resources(Permissions $permissions, string $role): array {
		return array_map(
			function(Permission $permission): string {
				return $permission->resource();
			},
			array_filter(
				iterator_to_array($this->permissions),
				function(Permission $permission) use($role): bool {
					return $permission->role() === $role;
				}
			)
		);
	}
}