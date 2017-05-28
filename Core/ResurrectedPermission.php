<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Permission resurrected from the array
 */
final class ResurrectedPermission implements Permission {
	private const RESOURCE = 'resource',
		ROLE = 'role';
	private $permission;

	public function __construct(array $permission) {
		$this->permission = $permission;
	}

	public function resource(): string {
		$permission = $this->normalize($this->permission);
		if (!$this->present(self::RESOURCE, $permission))
			throw new \InvalidArgumentException('No resource available');
		return $permission[self::RESOURCE];
	}

	public function role(): string {
		$permission = $this->normalize($this->permission);
		if (!$this->present(self::ROLE, $permission))
			throw new \InvalidArgumentException('No role available');
		return $permission[self::ROLE];
	}

	/**
	 * Normalize keys to single form
	 * @param array $permissions
	 * @return array
	 */
	private function normalize(array $permissions): array {
		return array_change_key_case($permissions, CASE_LOWER);
	}

	/**
	 * Is the field present in the permissions
	 * @param string $field
	 * @param array $permissions
	 * @return bool
	 */
	private function present(string $field, array $permissions): bool {
		return isset($permissions[$field]);
	}
}