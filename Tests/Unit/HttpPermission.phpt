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

final class HttpPermission extends Tester\TestCase {
	public function testAllowingEverythingWithoutSpecifiedPermissions() {
		Assert::true((new Authorization\HttpPermission([]))->allowed(''));
		Assert::true((new Authorization\HttpPermission([]))->allowed('foo'));
	}

	public function testEmptyResourceWithoutMatchingPermission() {
		Assert::false((new Authorization\HttpPermission(['parts']))->allowed(''));
	}

	public function testMatchingAllowedPermission() {
		$permission = new Authorization\HttpPermission(['parts']);
		Assert::true($permission->allowed('parts'));
	}

	public function testTypeStrictMatching() {
		$permission = new Authorization\HttpPermission([true]);
		Assert::false($permission->allowed('parts'));
	}

	public function testCaseInsensitiveMatching() {
		$permission = new Authorization\HttpPermission(['pArTs']);
		Assert::true($permission->allowed('parts'));
		Assert::true($permission->allowed('PARTS'));
	}

	public function testTrailingSlashWithDifferentMeaning() {
		$permission = new Authorization\HttpPermission(['parts/', '/parts']);
		Assert::false($permission->allowed('parts'));
	}

	public function testMatchingInMultipleResources() {
		$resources = ['parts', 'parts/', 'pages/all', 'pages'];
		$permission = new Authorization\HttpPermission($resources);
		Assert::true($permission->allowed('pages/all'));
	}
}


(new HttpPermission())->run();