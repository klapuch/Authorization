<?php
declare(strict_types = 1);
namespace Klapuch\Authorization;

/**
 * array_filter for iterator
 */
final class FilteredIterator extends \FilterIterator {
	private $callback;

	/**
	 * @param \Traversable $iterator
	 * @param string|\Closure $callback
	 */
	public function __construct(\Traversable $iterator, $callback) {
		parent::__construct($iterator);
		$this->callback = $callback;
	}

	public function accept() {
		return call_user_func($this->callback, $this->getInnerIterator()->current());
	}
}