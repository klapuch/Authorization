<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Role intended to inspect HTTP based permissions
 */
final class HttpRole implements Role {
	private const UNRESERVED_CHARACTERS = [
		'a-z', 'A-Z', '0-9', '_', '\.', '\~', '-',
	];
	private $permissions;

	public function __construct(Permissions $permissions) {
		$this->permissions = $permissions;
	}

	public function allowed(string $resource): bool {
		return (bool)array_filter(
			array_pad($this->resources($this->permissions), 1, $resource),
			function(string $pattern) use($resource): bool {
				return (bool)preg_match(sprintf('~%s$~i', $pattern), $resource);
			}
		);
	}

	private function resources(Permissions $permissions): array {
		return array_map(
			function(Permission $permission): string {
				return str_ireplace(
					'<var>',
					sprintf('[%s]+', implode(self::UNRESERVED_CHARACTERS)),
					$permission->resource()
				);
			},
			iterator_to_array($this->permissions)
		);
	}
}