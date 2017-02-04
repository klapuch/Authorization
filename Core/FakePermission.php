<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

final class FakePermission implements Permission {
	private $resource;
	private $role;

	public function __construct(string $resource = null, string $role = null) {
		$this->resource = $resource;
		$this->role = $role;
	}

	public function resource(): string {
		return $this->resource;
	}

	public function role(): string {
		return $this->role;
	}
}