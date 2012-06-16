<?php

namespace Entity;

use Doctrine\ORM\EntityRepository,
	Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * EntryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EntryRepository extends EntityRepository
{

	/**
	 * Result cache TTL for entry queries (latest, by rating, etc)
	 * @var int
	 */
	const RESULTS_CACHE_TTL = 600;

	/**
	 * Get all Entries
	 *
	 * @param	int		$offset
	 * @param	int		$entries_per_page
	 * @param	string	$search
	 * @return	array
	 */
	public function getAllEntries($offset, $entries_per_page, $search = '')
	{
		$dql = 'SELECT e FROM Entity\Entry e ';

		if ($search !== '')
		{
			$dql .= "WHERE e.title LIKE '%{$search}%' OR e.description LIKE '%{$search}%' ";
		}

		$dql .= 'ORDER BY e.created_date DESC';

		$query = $this->_em->createQuery($dql)
			->setFirstResult($offset)
			->setMaxResults($entries_per_page);

		return new Paginator($query);
	}

	/**
	 * Return the latest approved Entries
	 *
	 * @param	int		$offset
	 * @param	int		$entries_per_page
	 * @return	array
	 */
	public function getLatestEntries($offset, $entries_per_page)
	{
		// Count the total number of results for pagination
		$this->total_query_results = $this->countApprovedEntries();

		// Build the query with our offset and limit
		$query_builder = $this->_em->createQueryBuilder();
		$query_builder->select('e, u')
			->from('Entity\Entry', 'e')
			->join('e.user', 'u')
			->where('e.approved = 1')
			->addOrderBy('e.created_date', 'DESC')
			->setFirstResult($offset)
			->setMaxResults($entries_per_page);

		$query = $query_builder->getQuery();

		return new Paginator($query);
	}

	/**
	 * Return the number of entries that have not yet been moderated
	 *
	 * @return	int
	 */
	public function countEntriesToModerate()
	{
		// Run the query and save the cache
		$query = $this->_em->createQuery('SELECT COUNT(e.id) FROM Entity\Entry e WHERE e.moderated_by IS NULL');
		$query->useResultCache(true, self::RESULTS_CACHE_TTL);

		return $query->getSingleScalarResult();
	}

	/**
	 * Return the latest to-be-moderated Entries
	 *
	 * @param	int		$offset
	 * @param	int		$entries_per_page
	 * @return	array
	 */
	public function getEntriesToModerate($offset = 0, $entries_per_page = 20)
	{
		$query = $this->_em->createQuery('SELECT e FROM Entity\Entry e WHERE e.moderated_by IS NULL ORDER BY e.created_date DESC')
			->setFirstResult($offset)
			->setMaxResults($entries_per_page);

		return new Paginator($query);
	}

	/**
	 * Count the total number of approved Entries in the database
	 *
	 * @return int
	 */
	protected function countApprovedEntries()
	{
		return $this->_em->createQuery('SELECT COUNT(e.id) FROM Entity\Entry e WHERE e.approved = 1')
			->getSingleScalarResult();
	}

}
