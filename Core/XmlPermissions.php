<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Permissions extracted from the XML file
 */
final class XmlPermissions implements Permissions {
	private const QUERY = '/permissions/permission';
	private const ATTRIBUTES = ['resource', 'role'];
	private $file;

	public function __construct(string $file) {
		$this->file = $file;
	}

	public function getIterator(): \Traversable {
		$previous = libxml_use_internal_errors(true);
		try {
			$xml = new \DOMDocument();
			if($xml->load($this->file) === false)
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
		if(!$this->usable($xml))
			throw new \InvalidArgumentException('No available permissions');
		return array_map(
			function(\DOMElement $permission): array {
				return array_combine(
					self::ATTRIBUTES,
					array_map(
						function(string $attribute) use($permission): string {
							return $permission->getAttribute($attribute);
						},
						self::ATTRIBUTES
					)
				);
			},
			iterator_to_array((new \DOMXPath($xml))->query(self::QUERY))
		);
	}

	/**
	 * Is the XML composed from usable parts?
	 * @param \DOMDocument $xml
	 * @return bool
	 */
	private function usable(\DOMDocument $xml): bool {
		$expression = new class(self::QUERY, self::ATTRIBUTES) {
			private $query;
			private $attributes;

			public function __construct(string $query, array $attributes) {
				$this->query = $query;
				$this->attributes = $attributes;
			}

			public function __toString(): string {
				return sprintf(
					'%s and %s > 0',
					implode('=', array_map([$this, 'selection'], $this->attributes)),
					$this->selection(current($this->attributes))
				);
			}

			private function selection(string $attribute): string {
				return sprintf('count(%s[@%s])', $this->query, $attribute);
			}
		};
		return (new \DOMXPath($xml))->evaluate((string)$expression);
	}
}