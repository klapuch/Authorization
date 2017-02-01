<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Permission suitable for HTTP resources
 */
final class HttpPermission implements Permission {
	private $resources;

	public function __construct(array $resources) {
		$this->resources = $resources;
	}

	public function allowed(string $resource): bool {
		return (bool)array_uintersect(
			array_pad($this->resources, 1, $resource),
			[$resource],
			'strcasecmp'
		);
	}
}