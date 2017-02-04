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

	/**
	 * @throws \InvalidArgumentException No available permissions
	 */
	public function testThrowingOnMissingAttributes() {
		$xml = Tester\FileMock::create(
			'<permissions><permission/></permissions>',
			'xml'
		);
		(new Authorization\XmlPermissions($xml))->getIterator();
	}

	public function testThrowingOnMissingResourceAttribute() {
		$singleFault = Tester\FileMock::create(
			'<permissions>
				<permission role="guest"/>
			</permissions>',
			'xml'
		);
		$hiddenSingleFault = Tester\FileMock::create(
			'<permissions>
				<permission role="guest"/>
				<permission resource="first" role="guest"/>
			</permissions>',
			'xml'
		);
		Assert::exception(function() use($singleFault) {
			(new Authorization\XmlPermissions(
				$singleFault
			))->getIterator();
		}, \InvalidArgumentException::class, 'No available permissions');
		Assert::exception(function() use($hiddenSingleFault) {
			(new Authorization\XmlPermissions(
				$hiddenSingleFault
			))->getIterator();
		}, \InvalidArgumentException::class, 'No available permissions');
	}

	public function testThrowingOnMissingRoleAttribute() {
		$singleFault = Tester\FileMock::create(
			'<permissions>
				<permission resource="first"/>
			</permissions>',
			'xml'
		);
		$hiddenSingleFault = Tester\FileMock::create(
			'<permissions>
				<permission resource="first"/>
				<permission resource="first" role="guest"/>
			</permissions>',
			'xml'
		);
		Assert::exception(function() use($singleFault) {
			(new Authorization\XmlPermissions(
				$singleFault
			))->getIterator();
		}, \InvalidArgumentException::class, 'No available permissions');
		Assert::exception(function() use($hiddenSingleFault) {
			(new Authorization\XmlPermissions(
				$hiddenSingleFault
			))->getIterator();
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
			new \ArrayIterator([
				['resource' => 'first', 'role' => 'member'],
				['resource' => 'second', 'role' => 'admin'],
				['resource' => 'third', 'role' => 'guest'],
				['resource' => '0', 'role' => 'guest'],
			]),
			(new Authorization\XmlPermissions($xml))->getIterator()
		);
	}

	public function testIgnoringExtraAttributes() {
		$xml = Tester\FileMock::create(
			'<permissions>
				<permission resource="first" role="guest"/>
				<permission resource="second" role="guest" bar="foo"/>
				<permission resource="third" role="guest" foo="bar"/>
			</permissions>',
			'xml'
		);
		Assert::equal(
			new \ArrayIterator([
				['resource' => 'first', 'role' => 'guest'],
				['resource' => 'second', 'role' => 'guest'],
				['resource' => 'third', 'role' => 'guest'],
			]),
			(new Authorization\XmlPermissions($xml))->getIterator()
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
		Assert::noError(function() use($validXml) {
			(new Authorization\XmlPermissions($validXml))->getIterator();
		});
		Assert::false(libxml_use_internal_errors(false));
		Assert::exception(function() use($invalidXml) {
			(new Authorization\XmlPermissions($invalidXml))->getIterator();
		}, \Throwable::class);
		Assert::false(libxml_use_internal_errors(false));
	}
}


(new XmlPermissions())->run();