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
		(new Authorization\XmlPermissions($xml))->resources();
	}

	/**
	 * @throws \RuntimeException XML can not be loaded
	 */
	public function testThrowingOnUnknownFile() {
		(new Authorization\XmlPermissions(__FILE__))->resources();
	}

	/**
	 * @throws \InvalidArgumentException No available permissions
	 */
	public function testThrowingOnNoDesiredRootElement() {
		$xml = Tester\FileMock::create(
			'<foo><permission/></foo>',
			'xml'
		);
		(new Authorization\XmlPermissions($xml))->resources();
	}

	/**
	 * @throws \InvalidArgumentException No available permissions
	 */
	public function testThrowingOnNoDesiredChildrenElements() {
		$xml = Tester\FileMock::create(
			'<permissions><foo/></permissions>',
			'xml'
		);
		(new Authorization\XmlPermissions($xml))->resources();
	}

	/**
	 * @throws \InvalidArgumentException No available permissions
	 */
	public function testThrowingOnNoDesiredAttributes() {
		$xml = Tester\FileMock::create(
			'<permissions><permission/></permissions>',
			'xml'
		);
		(new Authorization\XmlPermissions($xml))->resources();
	}

	/**
	 * @throws \InvalidArgumentException No available permissions
	 */
	public function testThrowingOnSomeIncludedMandatoryAttributes() {
		$xml = Tester\FileMock::create(
			'<permissions>
				<permission href="foo/bar"/>
				<permission/>
				<permission href="bar/foo"/>
			</permissions>',
			'xml'
		);
		(new Authorization\XmlPermissions($xml))->resources();
	}

	public function testAllExtractedResources() {
		$xml = Tester\FileMock::create(
			'<permissions>
				<permission href="first"/>
				<permission href="second"/>
				<permission href="third"/>
				<permission href="0"/>
			</permissions>',
			'xml'
		);
		Assert::same(
			['first', 'second', 'third', '0'],
			(new Authorization\XmlPermissions($xml))->resources()
		);
	}

	public function testPassingWithSomeExtraAttributes() {
		$xml = Tester\FileMock::create(
			'<permissions>
				<permission href="first"/>
				<permission href="second" role="guest"/>
				<permission href="third" role="master"/>
			</permissions>',
			'xml'
		);
		Assert::same(
			['first', 'second', 'third'],
			(new Authorization\XmlPermissions($xml))->resources()
		);
	}

	public function testReEnabledErrorsInAllCases() {
		$validXml = Tester\FileMock::create(
			'<permissions>
				<permission href="first"/>
				<permission href="second" role="guest"/>
				<permission href="third" role="master"/>
			</permissions>',
			'xml'
		);
		$invalidXml = __FILE__;
		Assert::noError(function() use($validXml) {
			(new Authorization\XmlPermissions($validXml))->resources();
		});
		Assert::false(libxml_use_internal_errors(false));
		Assert::exception(function() use($invalidXml) {
			(new Authorization\XmlPermissions($invalidXml))->resources();
		}, \Throwable::class);
		Assert::false(libxml_use_internal_errors(false));
	}
}


(new XmlPermissions())->run();