<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Permission resurrected from the array
 */
final class ResurrectedPermission implements Permission {
	private const RESOURCE = 'resource',
		ROLE = 'role';
	private $permissions;

	public function __construct(array $permissions) {
		$this->permissions = $permissions;
	}

	public function resource(): string {
		$permissions = $this->normalize($this->permissions);
		if(!$this->present(self::RESOURCE, $permissions))
			throw new \InvalidArgumentException('No resource available');
		return $permissions[self::RESOURCE];
	}

	public function role(): string {
		$permissions = $this->normalize($this->permissions);
		if(!$this->present(self::ROLE, $permissions))
			throw new \InvalidArgumentException('No role available');
		return $permissions[self::ROLE];
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