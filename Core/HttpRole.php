<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Role intended to inspect HTTP based permissions
 */
final class HttpRole implements Role {
	private $permissions;

	public function __construct(Permissions $permissions) {
		$this->permissions = $permissions;
	}

	public function allowed(string $resource): bool {
		return (bool)array_uintersect(
			array_pad($this->resources($this->permissions), 1, $resource),
			[$resource],
			'strcasecmp'
		);
	}

	private function resources(Permissions $permissions): array {
		return array_map(
			function(Permission $permission): string {
				return $permission->resource();
			},
			iterator_to_array($this->permissions)
		);
	}
}