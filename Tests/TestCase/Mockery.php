<?php
declare(strict_types = 1);
namespace Klapuch\Authorization\TestCase;

use Mockery\MockInterface;
use Tester;

abstract class Mockery extends Tester\TestCase {
	final protected function mock($class): MockInterface {
		return \Mockery::mock($class);
	}

	protected function tearDown(): void {
		parent::tearDown();
		\Mockery::close();
	}
}