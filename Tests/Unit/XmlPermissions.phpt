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

final class XmlPermissions extends Tester\TestCase {
	/**
	 * @throws \RuntimeException XML can not be loaded
	 */
	public function testThrowingOnNoPermissions() {
		$xml = Tester\FileMock::create('', 'xml');
		(new Authorization\XmlPermissions($xml))->getIterator();
	}

	/**
	 * @throws \RuntimeException XML can not be loaded
	 */
	public function testThrowingOnUnknownFile() {
		(new Authorization\XmlPermissions(__FILE__))->getIterator();
	}

	/**
	 * @throws \InvalidArgumentException No available permissions
	 */
	public function testThrowingOnMissingRootElement() {
		$xml = Tester\FileMock::create(
			'<foo><permission/></foo>',
			'xml'
		);
		(new Authorization\XmlPermissions($xml))->getIterator();
	}

	/**
	 * @throws \InvalidArgumentException No available permissions
	 */
	public function testThrowingOnMissingChildrenElements() {
		$xml = Tester\FileMock::create(
			'<permissions><foo/></permissions>',
			'xml'
		);
		(new Authorization\XmlPermissions($xml))->getIterator();
	}

	public function testThrowingOnMissingAttributes() {
		$exposedFault = Tester\FileMock::create(
			'<permissions><permission/></permissions>',
			'xml'
		);
		$hiddenFault = Tester\FileMock::create(
			'<permissions>
				<permission/>
				<permission foo="bar"/>
			</permissions>',
			'xml'
		);
		Assert::exception(function() use ($exposedFault) {
			(new Authorization\XmlPermissions($exposedFault))->getIterator();
		}, \InvalidArgumentException::class, 'No available permissions');
		Assert::exception(function() use ($hiddenFault) {
			(new Authorization\XmlPermissions($hiddenFault))->getIterator();
		}, \InvalidArgumentException::class, 'No available permissions');
	}

	public function testExtractedPermissions() {
		$xml = Tester\FileMock::create(
			'<permissions>
				<permission resource="first" role="member"/>
				<permission resource="second" role="admin"/>
				<permission resource="third" role="guest"/>
				<permission resource="0" role="guest"/>
			</permissions>',
			'xml'
		);
		Assert::equal(
			[
				new Authorization\ResurrectedPermission([
					'resource' => 'first',
					'role' => 'member',
				]),
				new Authorization\ResurrectedPermission([
					'resource' => 'second',
					'role' => 'admin',
				]),
				new Authorization\ResurrectedPermission([
					'resource' => 'third',
					'role' => 'guest',
				]),
				new Authorization\ResurrectedPermission([
					'resource' => '0',
					'role' => 'guest',
				]),
			],
			iterator_to_array(new Authorization\XmlPermissions($xml))
		);
	}

	public function testExtractingAllPermissionAttributes() {
		$xml = Tester\FileMock::create(
			'<permissions>
				<permission resource="first" role="guest"/>
				<permission resource="second" role="guest" bar="foo"/>
				<permission resource="third" role="guest" foo="bar"/>
			</permissions>',
			'xml'
		);
		Assert::equal(
			[
				new Authorization\ResurrectedPermission([
					'resource' => 'first',
					'role' => 'guest',
				]),
				new Authorization\ResurrectedPermission([
					'resource' => 'second',
					'role' => 'guest',
					'bar' => 'foo',
				]),
				new Authorization\ResurrectedPermission([
					'resource' => 'third',
					'role' => 'guest',
					'foo' => 'bar',
				]),
			],
			iterator_to_array(new Authorization\XmlPermissions($xml))
		);
	}

	public function testReEnabledErrorsInAllCases() {
		$validXml = Tester\FileMock::create(
			'<permissions>
				<permission resource="first" role="guest"/>
				<permission resource="second" role="guest"/>
				<permission resource="third" role="guest"/>
			</permissions>',
			'xml'
		);
		$invalidXml = __FILE__;
		Assert::noError(function() use ($validXml) {
			(new Authorization\XmlPermissions($validXml))->getIterator();
		});
		Assert::false(libxml_use_internal_errors(false));
		Assert::exception(function() use ($invalidXml) {
			(new Authorization\XmlPermissions($invalidXml))->getIterator();
		}, \Throwable::class);
		Assert::false(libxml_use_internal_errors(false));
	}
}


(new XmlPermissions())->run();