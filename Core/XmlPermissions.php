<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Permissions extracted from the XML file
 */
final class XmlPermissions implements Permissions {
	private const QUERY = '/permissions/permission';
	private $file;

	public function __construct(string $file) {
		$this->file = $file;
	}

	public function all(): \Traversable {
		$previous = libxml_use_internal_errors(true);
		try {
			$xml = new \DOMDocument();
			if ($xml->load($this->file) === false)
				throw new \RunTimeException('XML can not be loaded');
			return new \ArrayIterator($this->matches($xml));
		} finally {
			libxml_use_internal_errors($previous);
		}
	}

	/**
	 * All the extracted permissions
	 * @param \DOMDocument $xml
	 * @return array
	 */
	private function matches(\DOMDocument $xml): array {
		if (!$this->usable($xml))
			throw new \InvalidArgumentException('No available permissions');
		return array_map(
			function(\DOMElement $permission): Permission {
				$attributes = iterator_to_array($permission->attributes);
				return new ResurrectedPermission(
					array_combine(
						array_column($attributes, 'name'),
						array_column($attributes, 'value')
					)
				);
			},
			iterator_to_array((new \DOMXPath($xml))->query(self::QUERY))
		);
	}

	/**
	 * Is the XML usable for the permissions?
	 * @param \DOMDocument $xml
	 * @return bool
	 */
	private function usable(\DOMDocument $xml): bool {
		return (new \DOMXPath($xml))->evaluate(
			sprintf('count(%1$s[not(@*)]) = 0 and count(%1$s) > 0', self::QUERY)
		);
	}
}