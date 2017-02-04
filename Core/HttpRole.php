<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Role intended to inspect HTTP based permissions
 */
final class HttpRole implements Role {
	private const RESOURCE = 'resource';
	private const ROLE = 'role';
	private $role;
	private $permissions;

	public function __construct(string $role, Permissions $permissions) {
		$this->role = $role;
		$this->permissions = $permissions;
	}

	public function allowed(string $resource): bool {
		return (bool)array_uintersect(
			array_pad($this->resources($this->permissions, $this->role), 1, $resource),
			[self::ROLE => $this->role, self::RESOURCE => $resource],
			'strcasecmp'
		);
	}

	private function resources(Permissions $permissions, string $role): array {
		return array_column(
			array_filter(
				iterator_to_array($this->permissions),
				function(array $permission) use($role): bool {
					return $permission[self::ROLE] === $role;
				}
			),
			self::RESOURCE
		);
	}
}