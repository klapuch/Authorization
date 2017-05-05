<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Authorization\Unit;

use Klapuch\Authorization;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class RolePermissions extends Tester\TestCase {
	public function testPermissionsByRole() {
		$permissions = new Authorization\RolePermissions(
			'guest',
			new Authorization\FakePermissions([
				new Authorization\FakePermission('first', 'guest'),
				new Authorization\FakePermission('second', 'admin'),
				new Authorization\FakePermission('third', 'guest'),
			])
		);
		Assert::equal(
			[
				0 => new Authorization\FakePermission('first', 'guest'),
				2 => new Authorization\FakePermission('third', 'guest'),
			],
			iterator_to_array($permissions)
		);
	}

	public function testPassingWithNoGivenPermissions() {
		$permissions = new Authorization\RolePermissions(
			'guest',
			new Authorization\FakePermissions([])
		);
		Assert::equal([], iterator_to_array($permissions));
	}

	public function testCaseSensitiveMatching() {
		$permissions = new Authorization\RolePermissions(
			'guest',
			new Authorization\FakePermissions([
				new Authorization\FakePermission('first', 'guest'),
				new Authorization\FakePermission('third', 'Guest'),
			])
		);
		Assert::equal(
			[new Authorization\FakePermission('first', 'guest')],
			iterator_to_array($permissions)
		);
	}
}


(new RolePermissions())->run();