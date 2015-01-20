<?php

namespace Jigoshop\Helper;

use Jigoshop\Core;

class ProductCategory
{
	/**
	 * Returns thumbnail data for selected category ID.
	 *
	 * @param int $id Category term ID.
	 * @return array `image` and `thumbnail_id` fields.
	 */
	public static function getImage($id)
	{
		if (empty($id)) {
			return array(
				'image' => JIGOSHOP_URL.'/assets/images/placeholder.png',
				'thumbnail_id' => false,
			);
		}

		$thumbnail = get_metadata(Core::TERMS, $id, 'thumbnail_id', true);
		$image = $thumbnail ? wp_get_attachment_url($thumbnail) : JIGOSHOP_URL.'/assets/images/placeholder.png';

		return array(
			'image' => $image,
			'thumbnail_id' => $thumbnail,
		);
	}
}
