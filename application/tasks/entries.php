<?php

use MagicRainbowAdventure\Task\EntryTask,
	MagicRainbowAdventure\Tools\EntryThumbnailTool,
	MagicRainbowAdventure\Helpers\ImageHelper;

/**
 * Entries Task
 *
 * Provides various tasks for managing entries
 *
 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
 */
class Entries_Task extends EntryTask
{

	/**
	 * Default entries task
	 *
	 * @return	void
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	public function run()
	{
		echo <<<EOL

Usage:
	entries:generate_thumbnails
		(Re-)generate the thumbnails for ALL entries

	entries:generate_thumbnails ID [ID ...]
		(Re-)generate the thumbnails for specific entries

	entries:detect_gifs
		Loop through ALL entries and identify any animated GIFs

	entries:detect_gifs ID [ID ...]
		For all specified entries, identify any animated GIFs
EOL;
	}

	/**
	 * Run the entries:generate_thumbnails task
	 *
	 * @param	array	$entry_ids
	 * @return	void
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	public function generate_thumbnails($entry_ids)
	{
		Bundle::start('resizer');
		$thumbnail_sizes = Config::get('magicrainbowadventure.entry_thumbnails');

		echo "Generating thumbnails...\n";

		$this->_process_entries($entry_ids, function($entry) use ($thumbnail_sizes)
		{
			$thumbnail_tool = new EntryThumbnailTool($entry);
			$thumbnail_tool->generateFromArray($thumbnail_sizes);
		});

		echo "Done!";
	}


	/**
	 * Run the entries:detect_gifs task
	 *
	 * @param	array	$entry_ids
	 * @return	void
	 * @author  Joseph Wynn <joseph@wildlyinaccurate.com>
	 */
	public function detect_gifs($entry_ids)
	{
		echo "Detecting animated GIFs...\n";

		$em = \Laravel\IoC::resolve('doctrine::manager');
		$base_path = \Laravel\Config::get('magicrainbowadventure.entry_uploads_path');

		$this->_process_entries($entry_ids, function($entry, $index) use ($em, $base_path)
		{
			$type = (ImageHelper::isAnimatedGif($base_path . '/' . $entry->getFilePath())) ? 'gif' : 'image';
			$entry->setType($type);
			$em->persist($entry);

			if ($index % 100 == 0)
			{
				// Flush the EntityManager every 100 operations
				$em->flush();
			}
		});

		$em->flush();

		echo "Done!";
	}

}
