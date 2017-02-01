<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Permissions extracted from the XML file
 */
final class XmlPermissions implements Permissions {
	private const QUERY = '/permissions/permission';
	private const ATTRIBUTE = 'href';
	private $file;

	public function __construct(string $file) {
		$this->file = $file;
	}

	public function resources(): array {
		$previous = libxml_use_internal_errors(true);
		try {
			$xml = new \DOMDocument();
			if($xml->load($this->file) === false)
				throw new \RunTimeException('XML can not be loaded');
			elseif(!$this->usable($xml))
				throw new \InvalidArgumentException('No available permissions');
			return array_map(
				function(\DOMNode $node): string {
					return $node->getAttribute(self::ATTRIBUTE);
				}, iterator_to_array((new \DOMXPath($xml))->query(self::QUERY))
			);
		} finally {
			libxml_use_internal_errors($previous);
		}
	}

	/**
	 * Is the XML composed from usable parts?
	 * @param \DOMDocument $xml
	 * @return bool
	 */
	private function usable(\DOMDocument $xml): bool {
		$xpath = new \DOMXPath($xml);
		return $xpath->evaluate(
			sprintf(
				'count(%1$s) > 0 and (count(%1$s) = count(%1$s/@%2$s))',
				self::QUERY,
				self::ATTRIBUTE
			)
		);
	}
}