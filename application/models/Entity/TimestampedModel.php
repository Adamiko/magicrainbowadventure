<?php

namespace Entity;

/**
 * TimestampedModel Class
 *
 * Provides an easy way to add created_date and modified_date to models
 *
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 * @author	Joseph Wynn <joseph@wildlyinaccurate.com>
 */
class TimestampedModel
{

	/**
	 * @var	\DateTime
	 *
	 * @column(type="datetime")
	 */
	protected $created_date;

	/**
	 * @var	\DateTime
	 *
	 * @column(type="datetime", nullable=true)
	 */
	protected $modified_date;

	/**
	 * Constructor Ahoy!
	 */
	public function __construct()
	{
		// Nothing here yet!
	}

	/**
	 * Set created_date to the current date/time before persisting an object
	 *
	 * @PrePersist
	 * @return	null
	 */
	public function setCreatedDate()
	{
		$this->created_date = new \DateTime;
	}

	/**
	 * Get created_date
	 *
	 * @return	DateTime
	 */
	public function getCreatedDate()
	{
		return $this->created_date;
	}

	/**
	 * Set created_date to the current date/time before persisting an object
	 *
	 * @PreUpdate
	 * @return	null
	 */
	public function setModifiedDate()
	{
		$this->modified_date = new \DateTime;
	}

	/**
	 * Get modified_date
	 *
	 * @return	DateTime
	 */
	public function getModifiedDate()
	{
		return $this->modified_date;
	}

}
