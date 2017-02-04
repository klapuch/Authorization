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

final class ResurrectedPermission extends Tester\TestCase {
	public function testThrowingOnUnknownMandatoryFields() {
		Assert::exception(function() {
			(new Authorization\ResurrectedPermission([]))->role();
		}, \InvalidArgumentException::class, 'No role available');
		Assert::exception(function() {
			(new Authorization\ResurrectedPermission([]))->resource();
		}, \InvalidArgumentException::class, 'No resource available');
	}

	public function testFindingExactField() {
		$permission = new Authorization\ResurrectedPermission([
			'role' => 'some role',
			'resource' => 'some resource',
		]);
		Assert::same('some role', $permission->role());
		Assert::same('some resource', $permission->resource());
	}

	public function testFoundCaseInsensitiveFields() {
		$permission = new Authorization\ResurrectedPermission([
			'RoLE' => 'some role',
			'ResOurcE' => 'some resource',
		]);
		Assert::same('some role', $permission->role());
		Assert::same('some resource', $permission->resource());
	}

	public function testMatchingLastDuplicatedField() {
		$permission = new Authorization\ResurrectedPermission([
			'RoLE' => 'some role',
			'ResOurcE' => 'some resource',
			'ROLE' => 'some ROLE',
		]);
		Assert::same('some ROLE', $permission->role());
		Assert::same('some resource', $permission->resource());
	}
}


(new ResurrectedPermission())->run();