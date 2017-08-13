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

final class HttpRole extends Tester\TestCase {
	public function testAllowingEverythingWithoutSpecifiedPermissions() {
		$permissions = new Authorization\FakePermissions([]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed(''));
		Assert::true($role->allowed('foo'));
	}

	public function testEmptyResourceWithoutMatchingPermission() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts'),
		]);
		Assert::false((new Authorization\HttpRole($permissions))->allowed(''));
	}

	public function testMatchingSingleAllowedPermission() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts'),
		]);
		Assert::true((new Authorization\HttpRole($permissions))->allowed('parts'));
	}

	public function testCaseInsensitiveMatching() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('pArTs'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('parts'));
		Assert::true($role->allowed('PARTS'));
	}

	public function testTrailingSlashWithDifferentMeaning() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/'),
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
			new Authorization\FakePermission('parts'),
			new Authorization\FakePermission('pages'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('pages'));
		Assert::true($role->allowed('parts'));
	}

	public function testMatchingWithAnyParameter() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{any}'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::false($role->allowed('parts'));
		Assert::false($role->allowed('parts/'));
		Assert::true($role->allowed('parts/123'));
		Assert::true($role->allowed('parts/foo'));
		Assert::true($role->allowed('parts/12foo34'));
		Assert::true($role->allowed('parts/foo.bar'));
		Assert::true($role->allowed('parts/foo~bar'));
		Assert::true($role->allowed('parts/foo-bar'));
		Assert::true($role->allowed('parts/foo_bar'));
		Assert::true($role->allowed('parts/foo:bar'));
	}

	public function testMatchingCaseInsensitiveAnyParameter() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{ANY}'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('parts/123'));
		Assert::true($role->allowed('parts/foo'));
	}

	public function testMatchingAnyParameterAsSingleValue() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{any}'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::false($role->allowed('parts/foo/'));
		Assert::false($role->allowed('parts/foo/bar'));
	}

	public function testAnyParameterOutOfUnreservedCharacters() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{any}'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::false($role->allowed('parts//'));
		Assert::false($role->allowed('parts//foo'));
	}

	public function testAnyParameterInBetween() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{any}/view'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('parts/foo/view'));
		Assert::true($role->allowed('parts/123/view'));
	}

	public function testMatchingWithMultipleAnyParameters() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{any}/view/{any}'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('parts/foo/view/123'));
		Assert::true($role->allowed('parts/123/view/foo'));
	}

	public function testNotMatchingPlaceholder() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{any}'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::false($role->allowed('parts/{any}'));
	}

	public function testMatchingWithNumericParameter() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{num}'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::false($role->allowed('parts'));
		Assert::false($role->allowed('parts/'));
		Assert::false($role->allowed('parts/foo'));
		Assert::false($role->allowed('parts/12foo34'));
		Assert::false($role->allowed('parts/foo.bar'));
		Assert::true($role->allowed('parts/123'));
		Assert::true($role->allowed('parts/0'));
		Assert::true($role->allowed('parts/66666666666666666666'));
	}

	public function testCombibingAnyAndNumericParameter() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{num}/foo/{any}'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('parts/123/foo/bar'));
		Assert::true($role->allowed('parts/0/foo/666'));
		Assert::false($role->allowed('parts/bar/foo/666'));
	}

	public function testConflictingParametersStrongerWinner() {
		$firstStronger = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{any}'),
			new Authorization\FakePermission('parts/{num}'),
		]);
		$firstWeaker = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts/{num}'),
			new Authorization\FakePermission('parts/{any}'),
		]);
		Assert::true((new Authorization\HttpRole($firstStronger))->allowed('parts/foo'));
		Assert::true((new Authorization\HttpRole($firstStronger))->allowed('parts/123'));
		Assert::true((new Authorization\HttpRole($firstWeaker))->allowed('parts/foo'));
		Assert::true((new Authorization\HttpRole($firstWeaker))->allowed('parts/123'));
	}

	public function testIgnoringContainingPart() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::false($role->allowed('v1/parts'));
	}

	public function testSameMeaningWithBeginningSlashInResource() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('parts'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('/parts'));
	}

	public function testSameMeaningWithBeginningSlashInPermissions() {
		$permissions = new Authorization\FakePermissions([
			new Authorization\FakePermission('/parts'),
		]);
		$role = new Authorization\HttpRole($permissions);
		Assert::true($role->allowed('parts'));
	}
}


(new HttpRole())->run();