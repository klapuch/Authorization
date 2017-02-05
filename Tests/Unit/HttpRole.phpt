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
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed(''));
		Assert::true($role->allowed('foo'));
	}

	public function testEmptyResourceWithoutMatchingPermission() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts')
		]);
		Assert::false((new Authorization\HttpRole($permissions))->allowed(''));
	}

	public function testMatchingSingleAllowedPermission() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts')
		]);
		Assert::true((new Authorization\HttpRole($permissions))->allowed('parts'));
	}

	public function testCaseInsensitiveMatching() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('pArTs')
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('parts'));
		Assert::true($role->allowed('PARTS'));
	}

	public function testTrailingSlashWithDifferentMeaning() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('/parts'),
			new Authorization\FakePermission('parts/')
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::false($role->allowed('parts'));
	}

	public function testMatchingInMultipleResources() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts'),
			new Authorization\FakePermission('parts/'),
			new Authorization\FakePermission('pages/all'),
			new Authorization\FakePermission('pages'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('pages/all'));
	}

	public function testMatchingForAnyRole() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts', 'guest'),
			new Authorization\FakePermission('pages', 'admin'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('pages'));
		Assert::true($role->allowed('parts'));
	}
}


(new HttpRole())->run();