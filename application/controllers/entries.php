<?php

/**
 * Entries Controller
 *
 * View, submit and edit entries.
 *
 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
 */
class Entries_Controller extends Base_Controller
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->filter('before', 'auth')->only(array(
			'submit',
			'favourite',
		));

		Basset::inline('assets')->add('entries', 'assets/js/entries.js');
	}

	/**
	 * Index/home page - show latest entries
	 *
	 * @return	void
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	public function get_index()
	{
		$page = Input::get('page') ?: 1;
		$entries_per_page = Config::get('magicrainbowadventure.entries_page_page');
		$offset = $entries_per_page * ($page - 1);

		$entries = $this->em->getRepository('Entity\Entry')->getLatestEntries($offset, $entries_per_page);

		Basset::inline('assets')->add('lazyload', 'assets/js/lazyload.js');

		$this->layout->title = Lang::line('general.latest_entries');
		$this->layout->content = View::make('entries/index', array(
			'entries' => $entries,
		));
	}

	/**
	 * GET alias for post_favourite
	 *
	 * @param	int		$id
	 * @return	\Laravel\Redirect|\Laravel\Response
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	public function get_favourite($id)
	{
		return $this->post_favourite($id);
	}

	/**
	 * Add or remove an entry from the user's favourites
	 *
	 * @param	int		$id
	 * @return	\Laravel\Redirect|\Laravel\Response
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	public function post_favourite($id)
	{
		$entry = $this->em->find('Entity\Entry', $id);

		if ( ! $entry)
		{
			return Response::error('404');
		}

		$favourite = Input::get('favourite');

		if ($favourite)
		{
			$return_message = Lang::line('entries.added_to_favourites');
			$this->user->addFavourite($entry);
		}
		else
		{
			$return_message = Lang::line('entries.removed_from_favourites');
			$this->user->getFavourites()->removeElement($entry);
			$entry->getFavouritedBy()->removeElement($this->user);
		}

		$this->em->flush();

		if (Request::ajax())
		{
			return Response::json(array(
				'message' => sprintf($return_message, $entry->getTitle()),
				'favourites_count' => $entry->getFavouritedBy()->count(),
				'favourite' => (bool) $favourite,
			));
		}
		else
		{
			return Redirect::to("{$entry->getId()}/{$entry->getUrlTitle()}");
		}
	}

	/**
	 * Comment on an entry
	 *
	 * @param	int		$entry_id
	 * @return	void
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	public function post_comment($entry_id)
	{
	}

	/**
	 * Show the entries submission form
	 *
	 * @return	void
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	public function get_submit()
	{
		$this->layout->title = 'Submit an Entry';
		$this->layout->content = View::make('entries/submit', array(
			'max_upload_size' => Config::get('magicrainbowadventure.max_upload_size'),
		));
	}

	/**
	 * Save a new Entry
	 *
	 * @return Laravel\Redirect
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	public function post_submit()
	{
		$validation_rules = array(
			'title' => 'required|max:140',
			'description' => 'max:2000',
		);

		$validation_messages = array(
			'valid_image_url' => 'You need to either upload an image or enter an image URL.',
		);

		$entry_image_error = Input::file('entry_image.error');

		if ($entry_image_error === null || $entry_image_error === 4)
		{
			// Validate the image URL
			$validation_rules['image_url'] = 'required|valid_image_url';
		}
		else
		{
			// Validate the uploaded file
			$validation_rules['entry_image'] = 'image|max:' . Config::get('magicrainbowadventure.max_upload_size');
		}

		$validation = \Validators\EntryValidator::make(Input::all(), $validation_rules, $validation_messages);

		if ($validation->fails())
		{
			if (isset($validation->errors->messages['image_url']) && count($validation->errors->messages['image_url']) === 1)
			{
				// In this case we know that the image_url field has failed
				// the 'required' validation. We'll set a custom message instead.
				$validation->errors->messages['image_url'][0] = $validation_messages['valid_image_url'];
			}

			return Redirect::to('entries/submit')->with_input()->with_errors($validation);
		}

		$entry = new \Entity\Entry;
		$entry->setTitle(Input::get('title'))
			->setDescription(Input::get('description'))
			->setUser($this->user);

		if (Input::get('image_url') !== '')
		{
			// Retrieve the image with cURL and store it in a temporary file
			$entry_file_path = tempnam(sys_get_temp_dir(), Config::get('magicrainbowadventure.temp_file_prefix'));

			$curl = new \EasyCurl(Input::get('image_url'));
			$curl->execute(true, $entry_file_path);
			$content_type = $curl->get_content_type();
		}
		else
		{
			$entry_file_path = Input::file('entry_image.tmp_name');
			$content_type = Input::file('entry_image.type');
		}

		// Determine the extension of the file so that we can save it correctly
		$mimes = Config::get('mimes');
		$extension = \Helpers\ArrayHelper::recursive_array_search($content_type, $mimes);

		// Upload the file to Dropbox
		$entry->setFile($entry_file_path, $extension);

		if ($this->user->isAdmin())
		{
			// Administrators don't need their entries approved
			$entry->setApproved(true)
				->setModeratedBy($this->user);
		}

		$this->em->persist($entry);
		$this->em->flush();

		return Redirect::to("{$entry->getId()}/{$entry->getUrlTitle()}")
						->with('success_message', Lang::line('entries.entry_submit_success'));
	}

	/**
	 * View an Entry
	 *
	 * @param	integer	$id
	 * @return	\Laravel\Response
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	public function get_view($id)
	{
		$this->layout->with('content_layout', 'layouts/one-column');

		// Try and find the Entry
		$entry = $this->em->find('\Entity\Entry', $id);

		// Only show the entries if it has been approved, or if the user is the owner or an administrator
		if ( ! $entry || ( ! $entry->isApproved() && ! $this->user->isAdmin() && $entry->getUser() !== $this->user))
		{
			$this->layout->title = Lang::line('general.not_found');
			$this->layout->content = View::make('entries/not-found');
		}
		elseif ($entry->isApproved() || $this->user->isAdmin())
		{
			$this->layout->title = $entry->getTitle();
			$this->layout->content = View::make('entries/view', array(
				'entry' => $entry
			));
		}
		else
		{
			$this->layout->title = $entry->getTitle();
			$this->layout->content = View::make('entries/view-preview', array(
				'entry' => $entry
			));
		}
	}

}
