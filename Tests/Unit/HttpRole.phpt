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
			new Authorization\FakePermission('parts', 'guest')
		]);
		Assert::false(
			(new Authorization\HttpRole(
				'guest', $permissions
			))->allowed('')
		);
	}

	public function testMatchingAllowedPermission() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts', 'guest')
		]);
		Assert::true(
			(new Authorization\HttpRole(
				'guest', $permissions
			))->allowed('parts')
		);
	}

	public function testCaseInsensitiveMatching() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('pArTs', 'guest')
		]);
		$role = new Authorization\HttpRole('guest', $permissions);
		Assert::true($role->allowed('parts'));
		Assert::true($role->allowed('PARTS'));
	}

	public function testTrailingSlashWithDifferentMeaning() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('/parts', 'guest'),
			new Authorization\FakePermission('parts/', 'guest')
		]);
		$role = new Authorization\HttpRole('guest', $permissions);
		Assert::false($role->allowed('parts'));
	}

	public function testMatchingInMultipleResources() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts', 'guest'),
			new Authorization\FakePermission('parts/', 'guest'),
			new Authorization\FakePermission('pages/all', 'guest'),
			new Authorization\FakePermission('pages', 'guest'),
		]);
		$role = new Authorization\HttpRole('guest', $permissions);
		Assert::true($role->allowed('pages/all'));
	}

	public function testMatchingForParticularRole() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts', 'guest'),
			new Authorization\FakePermission('pages', 'admin'),
		]);
		$role = new Authorization\HttpRole('admin', $permissions);
		Assert::true($role->allowed('pages'));
		Assert::false($role->allowed('parts'));
	}
}


(new HttpRole())->run();