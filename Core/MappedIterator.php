<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * Mapped iterator to the callback
 */
final class MappedIterator extends \IteratorIterator {
	private $callback;

	/**
	 * @param \Traversable $iterator
	 * @param \Closure|array $callback
	 */
    public function __construct(\Traversable $iterator, $callback) {
		parent::__construct($iterator);
		$this->callback = $callback;
	}

    public function current() {
		return call_user_func($this->callback, parent::current());
	}
}