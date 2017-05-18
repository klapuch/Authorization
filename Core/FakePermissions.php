<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

final class FakePermissions implements Permissions {
	private $permissions;

	public function __construct(array $permissions = null) {
		$this->permissions = $permissions;
	}

	public function all(): \Traversable {
		return new \ArrayIterator($this->permissions);
	}
}