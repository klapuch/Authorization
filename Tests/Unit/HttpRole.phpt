<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Authorization\Unit;

use Tester;
use Tester\Assert;
use Klapuch\Authorization;

require __DIR__ . '/../bootstrap.php';

final class HttpRole extends Tester\TestCase {
	public function testAllowingEverythingWithoutSpecifiedPermissions() {
		$permissions = new Authorization\FakePermissions([]);
		$role = new Authorization\HttpRole('guest', $permissions);
		Assert::true($role->allowed(''));
		Assert::true($role->allowed('foo'));
	}

	public function testEmptyResourceWithoutMatchingPermission() {
		$permissions = new Authorization\FakePermissions([
			['role' => 'guest', 'resource' => 'parts'],
		]);
		Assert::false(
			(new Authorization\HttpRole(
				'guest', $permissions
			))->allowed('')
		);
	}

	public function testMatchingAllowedPermission() {
		$permissions = new Authorization\FakePermissions([
			['role' => 'guest', 'resource' => 'parts'],
		]);
		Assert::true(
			(new Authorization\HttpRole(
				'guest', $permissions
			))->allowed('parts')
		);
	}

	public function testTypeStrictMatching() {
		$permissions = new Authorization\FakePermissions([
			['role' => 'guest', 'resource' => true],
		]);
		Assert::false(
			(new Authorization\HttpRole(
				'guest', $permissions
			))->allowed('parts')
		);
	}

	public function testCaseInsensitiveMatching() {
		$permissions = new Authorization\FakePermissions([
			['role' => 'guest', 'resource' => 'pArTs'],
		]);
		$role = new Authorization\HttpRole('guest', $permissions);
		Assert::true($role->allowed('parts'));
		Assert::true($role->allowed('PARTS'));
	}

	public function testTrailingSlashWithDifferentMeaning() {
		$permissions = new Authorization\FakePermissions([
			['role' => 'guest', 'resource' => 'parts/'],
			['role' => 'guest', 'resource' => '/parts'],
		]);
		$role = new Authorization\HttpRole('guest', $permissions);
		Assert::false($role->allowed('parts'));
	}

	public function testMatchingInMultipleResources() {
		$permissions = new Authorization\FakePermissions([
			['role' => 'guest', 'resource' => 'parts'],
			['role' => 'guest', 'resource' => 'parts/'],
			['role' => 'guest', 'resource' => 'pages/all'],
			['role' => 'guest', 'resource' => 'pages'],
		]);
		$role = new Authorization\HttpRole('guest', $permissions);
		Assert::true($role->allowed('pages/all'));
	}

	public function testMatchingForParticularRole() {
		$permissions = new Authorization\FakePermissions([
			['role' => 'guest', 'resource' => 'parts'],
			['role' => 'admin', 'resource' => 'pages'],
		]);
		$role = new Authorization\HttpRole('admin', $permissions);
		Assert::true($role->allowed('pages'));
		Assert::false($role->allowed('parts'));
	}
}


(new HttpRole())->run();