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
		(new Authorization\XmlPermissions($xml, 'guest'))->resources();
	}

	/**
	 * @throws \RuntimeException XML can not be loaded
	 */
	public function testThrowingOnUnknownFile() {
		(new Authorization\XmlPermissions(__FILE__, 'guest'))->resources();
	}

	/**
	 * @throws \InvalidArgumentException No available permissions
	 */
	public function testThrowingOnMissingRootElement() {
		$xml = Tester\FileMock::create(
			'<foo><permission/></foo>',
			'xml'
		);
		(new Authorization\XmlPermissions($xml, 'guest'))->resources();
	}

	/**
	 * @throws \InvalidArgumentException No available permissions
	 */
	public function testThrowingOnMissingChildrenElements() {
		$xml = Tester\FileMock::create(
			'<permissions><foo/></permissions>',
			'xml'
		);
		(new Authorization\XmlPermissions($xml, 'guest'))->resources();
	}

	/**
	 * @throws \InvalidArgumentException No available permissions
	 */
	public function testThrowingOnMissingAttributes() {
		$xml = Tester\FileMock::create(
			'<permissions><permission/></permissions>',
			'xml'
		);
		(new Authorization\XmlPermissions($xml, 'guest'))->resources();
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
				<permission href="first" role="guest"/>
			</permissions>',
			'xml'
		);
		Assert::exception(function() use($singleFault) {
			(new Authorization\XmlPermissions(
				$singleFault, 'guest'
			))->resources();
		}, \InvalidArgumentException::class, 'No available permissions');
		Assert::exception(function() use($hiddenSingleFault) {
			(new Authorization\XmlPermissions(
				$hiddenSingleFault, 'guest'
			))->resources();
		}, \InvalidArgumentException::class, 'No available permissions');
	}

	public function testThrowingOnMissingRoleAttribute() {
		$singleFault = Tester\FileMock::create(
			'<permissions>
				<permission href="first"/>
			</permissions>',
			'xml'
		);
		$hiddenSingleFault = Tester\FileMock::create(
			'<permissions>
				<permission href="first"/>
				<permission href="first" role="guest"/>
			</permissions>',
			'xml'
		);
		Assert::exception(function() use($singleFault) {
			(new Authorization\XmlPermissions(
				$singleFault, 'guest'
			))->resources();
		}, \InvalidArgumentException::class, 'No available permissions');
		Assert::exception(function() use($hiddenSingleFault) {
			(new Authorization\XmlPermissions(
				$hiddenSingleFault, 'guest'
			))->resources();
		}, \InvalidArgumentException::class, 'No available permissions');
	}

	public function testExtractedResourcesForParticularRole() {
		$xml = Tester\FileMock::create(
			'<permissions>
			<permission href="first" role="member"/>
			<permission href="second" role="admin"/>
			<permission href="third" role="guest"/>
			<permission href="0" role="guest"/>
			</permissions>',
			'xml'
		);
		Assert::same(
			['third', '0'],
			(new Authorization\XmlPermissions($xml, 'guest'))->resources()
		);
	}

	public function testPassingWithExtraAttributes() {
		$xml = Tester\FileMock::create(
			'<permissions>
				<permission href="first" role="guest"/>
				<permission href="second" role="guest" bar="foo"/>
				<permission href="third" role="guest" foo="bar"/>
			</permissions>',
			'xml'
		);
		Assert::same(
			['first', 'second', 'third'],
			(new Authorization\XmlPermissions($xml, 'guest'))->resources()
		);
	}

	public function testReEnabledErrorsInAllCases() {
		$validXml = Tester\FileMock::create(
			'<permissions>
				<permission href="first" role="guest"/>
				<permission href="second" role="guest"/>
				<permission href="third" role="guest"/>
			</permissions>',
			'xml'
		);
		$invalidXml = __FILE__;
		Assert::noError(function() use($validXml) {
			(new Authorization\XmlPermissions($validXml, 'guest'))->resources();
		});
		Assert::false(libxml_use_internal_errors(false));
		Assert::exception(function() use($invalidXml) {
			(new Authorization\XmlPermissions($invalidXml, 'guest'))->resources();
		}, \Throwable::class);
		Assert::false(libxml_use_internal_errors(false));
	}
}


(new XmlPermissions())->run();