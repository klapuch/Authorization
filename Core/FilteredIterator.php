<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * array_filter for iterator
 */
final class FilteredIterator extends \FilterIterator {
	private $callback;

	public function __construct(\Traversable $iterator, callable $callback) {
		parent::__construct($iterator);
		$this->callback = $callback;
	}

	public function accept(): bool {
		return call_user_func($this->callback, parent::current());
	}
}