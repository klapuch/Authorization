<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

interface Permission {
	/**
	 * Resource of the permission
	 * @return string
	 */
	public function resource(): string;

	/**
	 * Role of the permission
	 * @return string
	 */
	public function role(): string;
}