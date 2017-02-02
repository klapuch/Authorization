<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Permissions extracted from the XML file
 */
final class XmlPermissions implements Permissions {
	private const QUERY = '/permissions/permission';
	private const RESOURCE = 'href';
	private const ROLE = 'role';
	private $file;
	private $role;

	public function __construct(string $file, string $role) {
		$this->file = $file;
		$this->role = $role;
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
					return $node->getAttribute(self::RESOURCE);
				}, iterator_to_array($this->matches($xml, $this->role))
			);
		} finally {
			libxml_use_internal_errors($previous);
		}
	}

	private function matches(\DOMDocument $xml, string $role) {
		return (new \DOMXPath($xml))->query(
			sprintf('%s[@%s="%s"]', self::QUERY, self::ROLE, $role)
		);
	}

	/**
	 * Is the XML composed from usable parts?
	 * @param \DOMDocument $xml
	 * @return bool
	 */
	private function usable(\DOMDocument $xml): bool {
		return (new \DOMXPath($xml))->evaluate(
			sprintf(
				'%1$s > 0 and %2$s > 0 and %1$s = %2$s',
				sprintf('count(%s[@%s])', self::QUERY, self::RESOURCE),
				sprintf('count(%s[@%s])', self::QUERY, self::ROLE)
			)
		);
	}
}