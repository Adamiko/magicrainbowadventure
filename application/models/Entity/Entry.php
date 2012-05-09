<?php

namespace Entity;

/**
 * Entry Model
 *
 * @Entity(repositoryClass="Entity\EntryRepository")
 * @Table(name="entry", indexes={@index(name="entry_type_idx", columns={"type"})})
 * @author	Joseph Wynn <joseph@wildlyinaccurate.com>
 */
class Entry extends TimestampedModel
{

	/**
	 * @Id
	 * @Column(type="integer", nullable=false)
	 * @GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @Column(type="string", length=140, nullable=false)
	 */
	protected $title;

	/**
	 * @Column(type="string", length=140, nullable=false)
	 */
	protected $url_title;

	/**
	 * @Column(type="string", length=80, nullable=false)
	 */
	protected $file_path;

	/**
	 * @Column(type="string", length=40, nullable=false)
	 */
	protected $hash;

	/**
	 * @Column(type="text", length=2000, nullable=true)
	 */
	protected $description;

	/**
	 * @Column(type="string", length=16, nullable=false)
	 */
	protected $type = 'image';

	/**
	 * @OneToMany(targetEntity="EntryRating", mappedBy="entry", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
	 */
	protected $ratings;

	/**
	 * @ManyToOne(targetEntity="User", inversedBy="entries", fetch="EAGER")
	 */
	protected $user;

	/**
	 * @Column(type="boolean", nullable=false)
	 */
	protected $approved = false;

	/**
	 * @ManyToOne(targetEntity="Administrator", inversedBy="moderated_entries", fetch="EXTRA_LAZY")
	 */
	protected $moderated_by;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->ratings = new \Doctrine\Common\Collections\ArrayCollection;
	}

	/**
	 * Return a cache key for this Entry's thumbnail
	 *
	 * @return	string
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	private function _getThumbnailCacheKey()
	{
		return "/Entity/Entry/Thumbnail/{$this->hash}";
	}

	/**
	 * Upload the entry's file to Dropbox. The file's extension must be specified.
	 *
	 * Returns the Dropbox API response.
	 *
	 * @param  string 	$file
	 * @param  string   $extension
	 * @return array
	 */
	public function uploadFile($file, $extension)
	{
		$file_hash = hash_file('sha1', $file);
		$file_name = "{$file_hash}.{$extension}";
		$file_path = \Config::get('magicrainbowadventure.dropbox_base_path') . '/' . date('Y/m');

		$this->setHash($file_hash)
			->setFilePath("{$file_path}/{$file_name}");

		$dropbox = \IoC::resolve('dropbox::api');
		$response = $dropbox->putFile($file, $file_name, "Public/{$file_path}");

		// Remove cached thumbnails
		\Cache::forget($this->_getThumbnailCacheKey());

		return $response;
	}

	/**
	 * Get the Entry's thumbnail as base64-encoded data
	 *
	 * @param	string	$size
	 * @return	string
	 */
	public function getThumbnail($size = 'medium')
	{
		$cache_key = $this->_getThumbnailCacheKey();

		if ( ! \Cache::has($cache_key))
		{
			$dropbox = \IoC::resolve('dropbox::api');
			$thumbnail = $dropbox->thumbnails("Public/{$this->file_path}", 'JPEG', $size);
			\Cache::forever($cache_key, base64_encode($thumbnail['data']));
		}

		return \Cache::get($cache_key);
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set title and url_title
	 *
	 * @param string $title
	 * @return	\Entity\Entry
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		$this->setUrlTitle(\Str::slug($title));

		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set hash
	 *
	 * @param string $hash
	 * @return  \Entity\Entry
	 */
	public function setHash($hash)
	{
		$this->hash = $hash;
		return $this;
	}

	/**
	 * Get hash
	 *
	 * @return string
	 */
	public function getHash()
	{
		return $this->hash;
	}

	/**
	 * Set url_title
	 *
	 * @param string $urlTitle
	 * @return	\Entity\Entry
	 */
	public function setUrlTitle($urlTitle)
	{
		$this->url_title = $urlTitle;
		return $this;
	}

	/**
	 * Get url_title
	 *
	 * @return string
	 */
	public function getUrlTitle()
	{
		return $this->url_title;
	}

	/**
	 * Set file_path
	 *
	 * @param string $filePath
	 * @return	\Entity\Entry
	 */
	public function setFilePath($filePath)
	{
		$this->file_path = $filePath;
		return $this;
	}

	/**
	 * Get file_path
	 *
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->file_path;
	}

	/**
	 * Set description
	 *
	 * @param text $description
	 * @return	\Entity\Entry
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * Get description
	 *
	 * @return text
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set type
	 *
	 * @param string $type
	 * @return	\Entity\Entry
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Add ratings
	 *
	 * @return	\Entity\EntryRating $ratings
	 */
	public function addRating(\Entity\EntryRating $ratings)
	{
		$this->ratings[] = $ratings;
	}

	/**
	 * Get ratings
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getRatings()
	{
		return $this->ratings;
	}

	/**
	 * Set user
	 *
	 * @return	\Entity\User $user
	 * @return	\Entity\Entry
	 */
	public function setUser(\Entity\User $user)
	{
		$this->user = $user;
		$user->addEntry($this);
		return $this;
	}

	/**
	 * Get user
	 *
	 * @return	\Entity\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set approved
	 *
	 * @param boolean $approved
	 * @return	\Entity\Entry
	 */
	public function setApproved($approved)
	{
		$this->approved = $approved;
		return $this;
	}

	/**
	 * Get approved
	 *
	 * @return boolean
	 */
	public function getApproved()
	{
		return $this->approved;
	}

	/**
	 * Alias for getApproved()
	 *
	 * @return	boolean
	 */
	public function isApproved()
	{
		return (bool) $this->approved;
	}

	/**
	 * Set moderated_by
	 *
	 * @param	\Entity\User $user
	 * @return	\Entity\Entry
	 */
	public function setModeratedBy(\Entity\User $user)
	{
		$this->moderated_by = $user;
		$user->addModeratedEntry($this);

		return $this;
	}

	/**
	 * Get moderated_by
	 *
	 * @return	\Entity\User
	 */
	public function getModeratedBy()
	{
		return $this->moderated_by;
	}

}
