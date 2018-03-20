<?php

defined( 'ABSPATH' ) or die;

$GLOBALS['processed_terms'] = array();
$GLOBALS['processed_posts'] = array();

require_once ABSPATH . 'wp-admin/includes/post.php';
require_once ABSPATH . 'wp-admin/includes/taxonomy.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function themify_import_post( $post ) {
	global $processed_posts, $processed_terms;

	if ( ! post_type_exists( $post['post_type'] ) ) {
		return;
	}

	/* Menu items don't have reliable post_title, skip the post_exists check */
	if( $post['post_type'] !== 'nav_menu_item' ) {
		$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );
		if ( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) {
			$processed_posts[ intval( $post['ID'] ) ] = intval( $post_exists );
			return;
		}
	}

	if( $post['post_type'] == 'nav_menu_item' ) {
		if( ! isset( $post['tax_input']['nav_menu'] ) || ! term_exists( $post['tax_input']['nav_menu'], 'nav_menu' ) ) {
			return;
		}
		$_menu_item_type = $post['meta_input']['_menu_item_type'];
		$_menu_item_object_id = $post['meta_input']['_menu_item_object_id'];

		if ( 'taxonomy' == $_menu_item_type && isset( $processed_terms[ intval( $_menu_item_object_id ) ] ) ) {
			$post['meta_input']['_menu_item_object_id'] = $processed_terms[ intval( $_menu_item_object_id ) ];
		} else if ( 'post_type' == $_menu_item_type && isset( $processed_posts[ intval( $_menu_item_object_id ) ] ) ) {
			$post['meta_input']['_menu_item_object_id'] = $processed_posts[ intval( $_menu_item_object_id ) ];
		} else if ( 'custom' != $_menu_item_type ) {
			// associated object is missing or not imported yet, we'll retry later
			// $missing_menu_items[] = $item;
			return;
		}
	}

	$post_parent = ( $post['post_type'] == 'nav_menu_item' ) ? $post['meta_input']['_menu_item_menu_item_parent'] : (int) $post['post_parent'];
	$post['post_parent'] = 0;
	if ( $post_parent ) {
		// if we already know the parent, map it to the new local ID
		if ( isset( $processed_posts[ $post_parent ] ) ) {
			if( $post['post_type'] == 'nav_menu_item' ) {
				$post['meta_input']['_menu_item_menu_item_parent'] = $processed_posts[ $post_parent ];
			} else {
				$post['post_parent'] = $processed_posts[ $post_parent ];
			}
		}
	}

	/**
	 * for hierarchical taxonomies, IDs must be used so wp_set_post_terms can function properly
	 * convert term slugs to IDs for hierarchical taxonomies
	 */
	if( ! empty( $post['tax_input'] ) ) {
		foreach( $post['tax_input'] as $tax => $terms ) {
			if( is_taxonomy_hierarchical( $tax ) ) {
				$terms = explode( ', ', $terms );
				$post['tax_input'][ $tax ] = array_map( 'themify_get_term_id_by_slug', $terms, array_fill( 0, count( $terms ), $tax ) );
			}
		}
	}

	$post['post_author'] = (int) get_current_user_id();
	$post['post_status'] = 'publish';

	$old_id = $post['ID'];

	unset( $post['ID'] );
	$post_id = wp_insert_post( $post, true );
	if( is_wp_error( $post_id ) ) {
		return false;
	} else {
		$processed_posts[ $old_id ] = $post_id;

		if( isset( $post['has_thumbnail'] ) && $post['has_thumbnail'] ) {
			$placeholder = themify_get_placeholder_image();
			if( ! is_wp_error( $placeholder ) ) {
				set_post_thumbnail( $post_id, $placeholder );
			}
		}

		return $post_id;
	}
}

function themify_get_placeholder_image() {
	static $placeholder_image = null;

	if( $placeholder_image == null ) {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		global $wp_filesystem;
		$upload = wp_upload_bits( $post['post_name'] . '.jpg', null, $wp_filesystem->get_contents( THEMIFY_DIR . '/img/image-placeholder.jpg' ) );

		if ( $info = wp_check_filetype( $upload['file'] ) )
			$post['post_mime_type'] = $info['type'];
		else
			return new WP_Error( 'attachment_processing_error', __( 'Invalid file type', 'themify' ) );

		$post['guid'] = $upload['url'];
		$post_id = wp_insert_attachment( $post, $upload['file'] );
		wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $upload['file'] ) );

		$placeholder_image = $post_id;
	}

	return $placeholder_image;
}

function themify_import_term( $term ) {
	global $processed_terms;

	if( $term_id = term_exists( $term['slug'], $term['taxonomy'] ) ) {
		if ( is_array( $term_id ) ) $term_id = $term_id['term_id'];
		if ( isset( $term['term_id'] ) )
			$processed_terms[ intval( $term['term_id'] ) ] = (int) $term_id;
		return (int) $term_id;
	}

	if ( empty( $term['parent'] ) ) {
		$parent = 0;
	} else {
		$parent = term_exists( $term['parent'], $term['taxonomy'] );
		if ( is_array( $parent ) ) $parent = $parent['term_id'];
	}

	$id = wp_insert_term( $term['name'], $term['taxonomy'], array(
		'parent' => $parent,
		'slug' => $term['slug'],
		'description' => $term['description'],
	) );
	if ( ! is_wp_error( $id ) ) {
		if ( isset( $term['term_id'] ) ) {
			$processed_terms[ intval($term['term_id']) ] = $id['term_id'];
			return $term['term_id'];
		}
	}

	return false;
}

function themify_get_term_id_by_slug( $slug, $tax ) {
	$term = get_term_by( 'slug', $slug, $tax );
	if( $term ) {
		return $term->term_id;
	}

	return false;
}

function themify_undo_import_term( $term ) {
	$term_id = term_exists( $term['slug'], $term['term_taxonomy'] );
	if ( $term_id ) {
		if ( is_array( $term_id ) ) $term_id = $term_id['term_id'];
		if ( isset( $term_id ) ) {
			wp_delete_term( $term_id, $term['term_taxonomy'] );
		}
	}
}

/**
 * Determine if a post exists based on title, content, and date
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array $args array of database parameters to check
 * @return int Post ID if post exists, 0 otherwise.
 */
function themify_post_exists( $args = array() ) {
	global $wpdb;

	$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
	$db_args = array();

	foreach ( $args as $key => $value ) {
		$value = wp_unslash( sanitize_post_field( $key, $value, 0, 'db' ) );
		if( ! empty( $value ) ) {
			$query .= ' AND ' . $key . ' = %s';
			$db_args[] = $value;
		}
	}

	if ( !empty ( $args ) )
		return (int) $wpdb->get_var( $wpdb->prepare($query, $args) );

	return 0;
}

function themify_undo_import_post( $post ) {
	if( $post['post_type'] == 'nav_menu_item' ) {
		$post_exists = themify_post_exists( array(
			'post_name' => $post['post_name'],
			'post_modified' => $post['post_date'],
			'post_type' => 'nav_menu_item',
		) );
	} else {
		$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );
	}
	if( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) {
		/**
		 * check if the post has been modified, if so leave it be
		 *
		 * NOTE: posts are imported using wp_insert_post() which modifies post_modified field
		 * to be the same as post_date, hence to check if the post has been modified,
		 * the post_modified field is compared against post_date in the original post.
		 */
		if( $post['post_date'] == get_post_field( 'post_modified', $post_exists ) ) {
			wp_delete_post( $post_exists, true ); // true: bypass trash
		}
	}
}

function themify_do_demo_import() {
$term = array (
  'term_id' => 3,
  'name' => 'Blog',
  'slug' => 'blog',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);
if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 4,
  'name' => 'Featured',
  'slug' => 'featured',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);
if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 6,
  'name' => 'Photos',
  'slug' => 'photos',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);
if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 11,
  'name' => 'Images',
  'slug' => 'images',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);
if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 12,
  'name' => 'News',
  'slug' => 'news',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 0,
);
if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 13,
  'name' => 'Sports',
  'slug' => 'sports',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 12,
);
if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 14,
  'name' => 'Top Stories',
  'slug' => 'top-stories',
  'term_group' => 0,
  'taxonomy' => 'category',
  'description' => '',
  'parent' => 12,
);
if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 19,
  'name' => 'gallery',
  'slug' => 'gallery-2',
  'term_group' => 0,
  'taxonomy' => 'post_tag',
  'description' => '',
  'parent' => 0,
);
if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 8,
  'name' => 'Main Menu',
  'slug' => 'main-menu',
  'term_group' => 0,
  'taxonomy' => 'nav_menu',
  'description' => '',
  'parent' => 0,
);
if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$term = array (
  'term_id' => 9,
  'name' => 'Footer Menu',
  'slug' => 'footer-menu',
  'term_group' => 0,
  'taxonomy' => 'nav_menu',
  'description' => '',
  'parent' => 0,
);
if( ERASEDEMO ) {
	themify_undo_import_term( $term );
} else {
	themify_import_term( $term );
}

$post = array (
  'ID' => 6,
  'post_date' => '2008-06-11 20:22:47',
  'post_date_gmt' => '2008-06-11 20:22:47',
  'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi ac lobortis orci, a ornare dui. Phasellus consequat vulputate dignissim. Etiam condimentum aliquam augue, a ullamcorper erat facilisis et. Proin congue augue sit amet ligula dictum porta. Integer pharetra euismod velit ac laoreet. Ut dictum vitae ligula sed fermentum. Sed dapibus purus sit amet massa faucibus varius. Proin nec malesuada libero.',
  'post_title' => 'Butterfly Light',
  'post_excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi ac lobortis orci...',
  'post_name' => 'butterfly-light',
  'post_modified' => '2017-08-24 07:22:15',
  'post_modified_gmt' => '2017-08-24 07:22:15',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=6',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'blog, images',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 8,
  'post_date' => '2008-06-11 20:27:11',
  'post_date_gmt' => '2008-06-11 20:27:11',
  'post_content' => 'Integer ultrices turpis laoreet tellus venenatis, sed luctus libero gravida. Vestibulum eu hendrerit eros. Quisque eget luctus turpis, eget cursus velit. Nullam auctor ligula velit, fringilla molestie elit mattis et. Donec volutpat adipiscing urna, at egestas odio venenatis aliquet.',
  'post_title' => 'Sunset',
  'post_excerpt' => 'Integer ultrices turpis laoreet tellus venenatis, sed luctus libero gravida.',
  'post_name' => 'sunset',
  'post_modified' => '2017-08-24 07:22:13',
  'post_modified_gmt' => '2017-08-24 07:22:13',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=8',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'blog, images',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 22,
  'post_date' => '2008-06-11 20:38:30',
  'post_date_gmt' => '2008-06-11 20:38:30',
  'post_content' => 'Vestibulum a quam nisl. Nam sagittis neque erat, sed egestas urna facilisis et. Cras interdum imperdiet est, ac porttitor sapien porttitor id. Aenean semper congue dolor, non malesuada sapien. Sed neque diam, cursus eget eros at, pretium sagittis ligula. Sed pretium urna vitae velit pharetra',
  'post_title' => 'Late Stroll',
  'post_excerpt' => 'Vestibulum a quam nisl. Nam sagittis neque erat, sed egestas urna facilisis et.',
  'post_name' => 'late-stroll',
  'post_modified' => '2017-08-24 07:22:12',
  'post_modified_gmt' => '2017-08-24 07:22:12',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=22',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'blog, images',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 25,
  'post_date' => '2008-06-11 20:41:16',
  'post_date_gmt' => '2008-06-11 20:41:16',
  'post_content' => 'Etiam ipsum ligula, mollis eu vestibulum id, ornare vel nibh. Sed sollicitudin, arcu non auctor pulvinar, velit eros viverra sapien, a mattis sem tortor sed arcu. Aenean gravida tincidunt commodo. In felis nunc, ultricies vel congue nec, congue vitae lacus.',
  'post_title' => 'Empty House',
  'post_excerpt' => 'Etiam ipsum ligula, mollis eu vestibulum id, ornare vel nibh. Sed sollicitudin, arcu non...',
  'post_name' => 'empty-house',
  'post_modified' => '2017-08-24 07:22:11',
  'post_modified_gmt' => '2017-08-24 07:22:11',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=25',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'blog, images',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 28,
  'post_date' => '2008-06-11 20:42:29',
  'post_date_gmt' => '2008-06-11 20:42:29',
  'post_content' => 'Vestibulum malesuada neque nec hendrerit lobortis. Maecenas erat diam, fringilla et hendrerit eu, laoreet vel quam. Integer sollicitudin nec eros a fringilla. Mauris sed velit sapien. Pellentesque habitant morbi tristique senectus et netus et malesuada.',
  'post_title' => 'Sweet Tooth',
  'post_excerpt' => 'Vestibulum malesuada neque nec hendrerit lobortis. Maecenas erat diam, fringilla...',
  'post_name' => 'sweet-tooth',
  'post_modified' => '2017-08-24 07:22:09',
  'post_modified_gmt' => '2017-08-24 07:22:09',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=28',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'blog, images',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 31,
  'post_date' => '2008-06-11 20:45:36',
  'post_date_gmt' => '2008-06-11 20:45:36',
  'post_content' => 'Sed pharetra fringilla venenatis. Quisque quis lobortis nibh, nec egestas leo. Cras id augue id nulla interdum feugiat. Cras quam lacus, congue at consequat sit amet, consectetur id enim. Sed id lorem id turpis ultrices mattis at a odio.',
  'post_title' => 'Lightbox Link',
  'post_excerpt' => 'Sed pharetra fringilla venenatis. Quisque quis lobortis nibh, nec egestas leo.',
  'post_name' => 'lightbox-link',
  'post_modified' => '2017-08-24 07:22:07',
  'post_modified_gmt' => '2017-08-24 07:22:07',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=31',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'lightbox_link' => 'https://themify.me/demo/themes/builder/files/2013/06/129025022.jpg',
    'lightbox_icon' => 'on',
  ),
  'tax_input' => 
  array (
    'category' => 'blog, images',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 2086,
  'post_date' => '2008-06-11 20:58:56',
  'post_date_gmt' => '2008-06-11 20:58:56',
  'post_content' => 'Sed pharetra fringilla venenatis. Quisque quis lobortis nibh, nec egestas leo. Pellentesque ornare auctor velit eget rutrum. Vivamus enim quam, commodo auctor erat sed, sodales tristique erat.

[gallery link="file" columns="6" ids="36,37,38,39,40,41"]',
  'post_title' => 'Gallery Post',
  'post_excerpt' => 'Sed pharetra fringilla venenatis. Quisque quis lobortis nibh, nec egestas leo.',
  'post_name' => 'gallery-post',
  'post_modified' => '2017-08-24 07:22:05',
  'post_modified_gmt' => '2017-08-24 07:22:05',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=34',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'blog, images',
    'post_tag' => 'gallery-2',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 46,
  'post_date' => '2008-06-11 21:08:52',
  'post_date_gmt' => '2008-06-11 21:08:52',
  'post_content' => 'Proin ornare scelerisque tellus, at porttitor urna pharetra in. Quisque mattis nibh sed dui fermentum, at porttitor nunc egestas. Vestibulum arcu eros, ultricies et ultricies scelerisque, gravida a eros. Nam eget commodo purus, quis mattis dui. Nunc vulputate rutrum odio vitae euismod.',
  'post_title' => 'Lightbox Video',
  'post_excerpt' => 'Proin ornare scelerisque tellus, at porttitor urna pharetra in. Quisque mattis nibh sed dui...',
  'post_name' => 'lightbox-video',
  'post_modified' => '2017-08-24 07:22:04',
  'post_modified_gmt' => '2017-08-24 07:22:04',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=46',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'lightbox_link' => 'http://vimeo.com/60779545',
    'lightbox_icon' => 'on',
  ),
  'tax_input' => 
  array (
    'category' => 'blog',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 50,
  'post_date' => '2008-06-11 21:16:04',
  'post_date_gmt' => '2008-06-11 21:16:04',
  'post_content' => 'Ut tempus nibh elit, eu faucibus lorem fringilla sed. Phasellus lobortis urna eget eleifend aliquet. Cras id augue id nulla interdum feugiat. Cras quam lacus, congue at consequat sit amet, consectetur id enim. Sed id lorem id turpis ultrices mattis at a odio.',
  'post_title' => 'External Link',
  'post_excerpt' => 'Ut tempus nibh elit, eu faucibus lorem fringilla sed. Phasellus lobortis urna eget.',
  'post_name' => 'external-link',
  'post_modified' => '2017-08-24 07:22:02',
  'post_modified_gmt' => '2017-08-24 07:22:02',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=50',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'external_link' => 'https://themify.me/',
  ),
  'tax_input' => 
  array (
    'category' => 'blog, images',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 53,
  'post_date' => '2008-06-11 21:19:37',
  'post_date_gmt' => '2008-06-11 21:19:37',
  'post_content' => 'Mauris faucibus, tellus sed commodo luctus, nibh libero tristique felis, a vulputate nibh tellus et purus. Donec dictum odio non magna accumsan pellentesque. Sed pharetra fringilla venenatis. Quisque quis lobortis nibh, nec egestas leo.',
  'post_title' => 'Landscape',
  'post_excerpt' => 'Mauris faucibus, tellus sed commodo luctus, nibh libero tristique felis, a vulputate nibh...',
  'post_name' => 'landscape',
  'post_modified' => '2017-08-24 07:22:02',
  'post_modified_gmt' => '2017-08-24 07:22:02',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=53',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'blog, images',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1762,
  'post_date' => '2008-06-25 02:01:43',
  'post_date_gmt' => '2008-06-25 02:01:43',
  'post_content' => 'Nam risus velit, rhoncus eget consectetur id, <a href="https://themify.me/">Themify.me</a>. Vivamus imperdiet diam ac tortor tempus posuere. Curabitur at arcu id turpis posuere bibendum. Sed commodo mauris eget diam pretium cursus. In sagittis feugiat mauris, in ultrices mauris lacinia eu. Fusce augue velit, vulputate elementum semper congue, rhoncus adipiscing nisl. Curabitur vel risus eros, sed eleifend arcu. Donec porttitor hendrerit diam et blandit.',
  'post_title' => 'Top 10 Office Entertainment Gadgets',
  'post_excerpt' => '',
  'post_name' => 'top-10-office-entertainment-gadgets',
  'post_modified' => '2017-08-24 07:21:59',
  'post_modified_gmt' => '2017-08-24 07:21:59',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=1762',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'featured',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1766,
  'post_date' => '2008-06-25 02:02:31',
  'post_date_gmt' => '2008-06-25 02:02:31',
  'post_content' => 'Aliquam erat nulla, sodales at imperdiet vitae, convallis vel dui. Sed ultrices felis ut justo suscipit vestibulum. Pellentesque nisl nisi, vehicula vitae hendrerit vel, mattis eget mauris. Donec consequat eros eget lectus dictum sit amet ultrices neque sodales. Aliquam metus diam, mattis fringilla adipiscing at, lacinia at nulla.',
  'post_title' => 'How to Stay Connected on the Go',
  'post_excerpt' => '',
  'post_name' => 'how-to-stay-connected-on-the-go',
  'post_modified' => '2017-08-24 07:21:58',
  'post_modified_gmt' => '2017-08-24 07:21:58',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=1766',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'featured',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1769,
  'post_date' => '2008-06-25 02:03:33',
  'post_date_gmt' => '2008-06-25 02:03:33',
  'post_content' => 'Nunc et pharetra enim. Praesent pharetra, neque et luctus tempor, leo sapien faucibus leo, a dignissim turpis ipsum sed libero. Sed sed luctus purus. Aliquam faucibus turpis at libero consectetur euismod. Nam nunc lectus, congue non egestas quis, condimentum ut arcu.',
  'post_title' => 'Are Your Kids Under Stress?',
  'post_excerpt' => '',
  'post_name' => 'are-your-kids-under-stress',
  'post_modified' => '2017-08-24 07:21:56',
  'post_modified_gmt' => '2017-08-24 07:21:56',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=1769',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'featured',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1772,
  'post_date' => '2008-06-25 02:04:04',
  'post_date_gmt' => '2008-06-25 02:04:04',
  'post_content' => 'Vivamus in risus non lacus vehicula vestibulum. In magna leo, malesuada eget pulvinar ut, pellentesque a arcu. Praesent rutrum feugiat nibh elementum posuere. Nulla volutpat porta enim vel consectetur.',
  'post_title' => '10 Travel Tips for Your Summer Vacation',
  'post_excerpt' => '',
  'post_name' => '10-travel-tips-for-your-summer-vacation',
  'post_modified' => '2017-08-24 07:21:54',
  'post_modified_gmt' => '2017-08-24 07:21:54',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=1772',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'featured',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1775,
  'post_date' => '2008-06-25 02:04:40',
  'post_date_gmt' => '2008-06-25 02:04:40',
  'post_content' => 'Mauris varius fermentum velit sit amet varius. Aenean consectetur lacus tellus, sed vestibulum quam. Donec lorem lectus, posuere in pharetra at, vestibulum et magna. Ut viverra, risus eu commodo interdum, nunc ipsum mollis purus, ac varius ante purus sed diam.',
  'post_title' => 'How to Shop for Healthy Fruits',
  'post_excerpt' => '',
  'post_name' => 'how-to-shop-for-healthy-fruits',
  'post_modified' => '2017-08-24 07:21:52',
  'post_modified_gmt' => '2017-08-24 07:21:52',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=1775',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'featured',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1802,
  'post_date' => '2008-06-26 00:43:06',
  'post_date_gmt' => '2008-06-26 00:43:06',
  'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam lobortis ac tellus id tempor. Aliquam pellentesque nibh quis justo commodo tristique. Aliquam erat volutpat. Etiam ut justo aliquam, euismod dolor eget, ullamcorper tortor. Aliquam eu ipsum a urna lacinia aliquam id non dui.',
  'post_title' => 'Travel the world',
  'post_excerpt' => '',
  'post_name' => 'travel-the-world',
  'post_modified' => '2017-08-24 07:21:51',
  'post_modified_gmt' => '2017-08-24 07:21:51',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=1802',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
    'category' => 'top-stories',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1805,
  'post_date' => '2008-06-26 00:44:06',
  'post_date_gmt' => '2008-06-26 00:44:06',
  'post_content' => 'Fusce hendrerit adipiscing diam vitae sodales. Sed faucibus venenatis lectus sed laoreet. Sed in libero ac nisi placerat dictum. Donec dui neque, aliquam non nunc nec, porttitor tempor leo. Maecenas non sagittis neque.',
  'post_title' => 'Morning News',
  'post_excerpt' => '',
  'post_name' => 'morning-news',
  'post_modified' => '2017-08-24 07:21:49',
  'post_modified_gmt' => '2017-08-24 07:21:49',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=1805',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
  ),
  'tax_input' => 
  array (
    'category' => 'top-stories',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1808,
  'post_date' => '2008-06-26 00:45:33',
  'post_date_gmt' => '2008-06-26 00:45:33',
  'post_content' => 'Duis laoreet tortor magna, sit amet viverra elit dignissim sit amet. Aenean tempor et tortor eget blandit. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Sed aliquam, sapien et tincidunt sodales, risus lectus rutrum turpis.',
  'post_title' => 'Greenhouse Plants',
  'post_excerpt' => '',
  'post_name' => 'greenhouse-plants',
  'post_modified' => '2017-10-27 17:27:13',
  'post_modified_gmt' => '2017-10-27 17:27:13',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=1808',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'hide_post_title' => 'yes',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full\\"}]}]',
  ),
  'tax_input' => 
  array (
    'category' => 'top-stories',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1811,
  'post_date' => '2008-06-26 00:46:53',
  'post_date_gmt' => '2008-06-26 00:46:53',
  'post_content' => 'Duis diam urna, aliquam id mauris nec, tristique ultrices turpis. Nam non ante in nunc euismod rutrum. Cras tristique feugiat neque sed vestibulum.',
  'post_title' => 'Shop on the Run',
  'post_excerpt' => '',
  'post_name' => 'shop-on-the-run',
  'post_modified' => '2017-08-24 07:21:45',
  'post_modified_gmt' => '2017-08-24 07:21:45',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=1811',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
  ),
  'tax_input' => 
  array (
    'category' => 'top-stories',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1815,
  'post_date' => '2008-06-26 00:51:24',
  'post_date_gmt' => '2008-06-26 00:51:24',
  'post_content' => 'Sed eu urna quis lacus aliquet fermentum vel sed risus. Integer laoreet pretium interdum. Proin consequat consequat feugiat. Integer pellentesque faucibus aliquet.',
  'post_title' => 'The Desert Run',
  'post_excerpt' => '',
  'post_name' => 'the-desert-run',
  'post_modified' => '2017-08-24 07:21:43',
  'post_modified_gmt' => '2017-08-24 07:21:43',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/builder/?p=1815',
  'menu_order' => 0,
  'post_type' => 'post',
  'meta_input' => 
  array (
    'builder_switch_frontend' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"modules\\":[{\\"mod_name\\":\\"fancy-heading\\",\\"mod_settings\\":{\\"heading\\":\\"Hello\\",\\"sub_heading\\":\\"Welcome\\",\\"heading_tag\\":\\"h1\\",\\"text_alignment\\":\\"themify-text-center\\",\\"background_image-type\\":\\"image\\",\\"background_image-type_image\\":\\"image\\",\\"background_image-type_gradient\\":\\"gradient\\",\\"background_image-gradient-type\\":\\"linear\\",\\"background_image-gradient-angle\\":\\"180\\",\\"background_image-gradient\\":\\"0% rgb(0, 0, 0)|100% rgb(255, 255, 255)\\",\\"background_image-css\\":\\"background-image: -moz-linear-gradient(180deg,rgb(0, 0, 0) 0%, rgb(255, 255, 255) 100%);\\\\nbackground-image: -webkit-linear-gradient(180deg,rgb(0, 0, 0) 0%, rgb(255, 255, 255) 100%);\\\\nbackground-image: -o-linear-gradient(180deg,rgb(0, 0, 0) 0%, rgb(255, 255, 255) 100%);\\\\nbackground-image: -ms-linear-gradient(180deg,rgb(0, 0, 0) 0%, rgb(255, 255, 255) 100%);\\\\nbackground-image: linear-gradient(180deg,rgb(0, 0, 0) 0%, rgb(255, 255, 255) 100%);\\\\n\\",\\"padding_top_unit\\":\\"px\\",\\"padding_right_unit\\":\\"px\\",\\"padding_bottom_unit\\":\\"px\\",\\"padding_left_unit\\":\\"px\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_padding_apply_all_padding\\":\\"padding\\",\\"margin_top_unit\\":\\"px\\",\\"margin_right_unit\\":\\"px\\",\\"margin_bottom_unit\\":\\"px\\",\\"margin_left_unit\\":\\"px\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"checkbox_margin_apply_all_margin\\":\\"margin\\",\\"border_top_style\\":\\"solid\\",\\"border_right_style\\":\\"solid\\",\\"border_bottom_style\\":\\"solid\\",\\"border_left_style\\":\\"solid\\",\\"checkbox_border_apply_all\\":\\"border\\",\\"checkbox_border_apply_all_border\\":\\"border\\",\\"font_family\\":\\"default\\",\\"font_size_unit\\":\\"px\\",\\"line_height_unit\\":\\"px\\",\\"font_family_subheading\\":\\"default\\",\\"font_size_subheading_unit\\":\\"px\\",\\"line_height_subheading_unit\\":\\"px\\",\\"custom_parallax_scroll_reverse\\":\\"|\\",\\"custom_parallax_scroll_reverse_reverse\\":\\"reverse\\",\\"custom_parallax_scroll_fade\\":\\"|\\",\\"custom_parallax_scroll_fade_fade\\":\\"fade\\",\\"visibility_desktop\\":\\"show\\",\\"visibility_desktop_show\\":\\"show\\",\\"visibility_desktop_hide\\":\\"hide\\",\\"visibility_tablet\\":\\"show\\",\\"visibility_tablet_show\\":\\"show\\",\\"visibility_tablet_hide\\":\\"hide\\",\\"visibility_mobile\\":\\"show\\",\\"visibility_mobile_show\\":\\"show\\",\\"visibility_mobile_hide\\":\\"hide\\"}}],\\"styling\\":[]}],\\"styling\\":[]},{\\"row_order\\":\\"1\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"modules\\":[],\\"styling\\":[]}],\\"styling\\":[]}]',
  ),
  'tax_input' => 
  array (
    'category' => 'sports',
  ),
  'has_thumbnail' => true,
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 408,
  'post_date' => '2010-10-06 21:16:00',
  'post_date_gmt' => '2010-10-07 01:16:00',
  'post_content' => '<h3>Buttons</h3>
[button style="orange" link="https://themify.me"]Orange[/button] [button style="blue"]Blue[/button] [button style="pink"]Pink[/button] [button style="green"]Green[/button] [button style="red"]Red[/button] [button style="black"]Black[/button]

[hr]

[button style="small"]Small[/button]

[button]Default[/button]

[button style="large"]Large[/button] [button style="xlarge"]Xlarge[/button]

[hr]

[button style="orange small"]Orange Small[/button] [button style="blue"]Blue[/button] [button style="green large"]Green Large[/button] [button style="red xlarge"]Red Xlarge[/button]

[hr]
<h3>Columns</h3>
[col grid="2-1 first"]
<h4>col 2-1</h4>
Sed sagittis, elit egestas rutrum vehicula, neque dolor fringilla lacus, ut rhoncus turpis augue vitae libero. Nam risus velit, rhoncus eg.

[/col]

[col grid="2-1"]
<h4>col 2-1</h4>
Curabitur vel risus eros, sed eleifend arcu. Donec porttitor hendrerit diam et blandit. Curabitur vitae velit ligula, vitae lobortis massa.

[/col]

[hr]

[col grid="3-1 first"]
<h4>col 3-1</h4>
Sed sagittis, elit egestas rutrum vehicula, neque dolor fringilla lacus, ut rhoncus turpis augue vitae libero. Nam risus velit, rhoncus eg.

[/col]

[col grid="3-1"]
<h4>col 3-1</h4>
Curabitur vel risus eros, sed eleifend arcu. Donec porttitor hendrerit diam et blandit. Curabitur vitae velit ligula, vitae lobortis massa.

[/col]

[col grid="3-1"]
<h4>col 3-1</h4>
Vivamus dignissim, ligula velt pretium leo, vel placerat ipsum risus luctus purus. Tos, sed eleifend arcu. Donec porttitor hendrerit.

[/col]

[hr]

[col grid="4-1 first"]
<h4>col 4-1</h4>
Sed sagittis, elit egestas rutrum vehicula, neque dolor fringilla lacus, ut rhoncus turpis augue vitae libero. Nam risus velit, rhoncus eget co.

[/col]

[col grid="4-1"]
<h4>col 4-1</h4>
Curabitur vel risus eros, sed eleifend arcu. Donec porttitor hendrerit diam et blandit. Curabitur vitae velit ligula, vitae lobortis mas.

[/col]

[col grid="4-1"]
<h4>col 4-1</h4>
Vivamus dignissim, ligula velt pretium leo, vel placerat ipsum risus luctus purus. Tos, sed eleifend arcu. Donec porttitor hendrerit diam.

[/col]

[col grid="4-1"]
<h4>col 4-1</h4>
Donec porttitor hendrerit diam et blandit. Curabitur vel risus eros, sed eleifend arcu. Curabitur vitae velit ligula, vitae lobortis mas.

[/col]

[hr]

[col grid="4-2 first"]
<h4>col 4-2</h4>
Sed sagittis, elit egestas rutrum vehicula, neque dolor fringilla lacus, ut rhoncus turpis augue vitae libero. Nam risus velit, rhoncus eget cout rhoncus turpis augue vitae libero.

[/col]

[col grid="4-1"]
<h4>col 4-1</h4>
Curabitur vel risus eros, sed eleifend arcu. Donec porttitor hendrerit diam et blandit. Curabitur vitae velit ligula, vitae lobortis mas.

[/col]

[col grid="4-1"]
<h4>col 4-1</h4>
Vivamus dignissim, ligula velt pretium leo, vel placerat ipsum risus luctus purus. Tos, sed eleifend arcu. Donec porttitor hendrerit diam.

[/col]
<h3>Horizontal Rules</h3>
[hr]

[hr color="pink"]

[hr color="red"]

[hr color="light-gray"]

[hr color="dark-gray"]

[hr color="black"]

[hr color="orange"]

[hr color="yellow"]

[hr color="white"]
<h3>Quote</h3>
[quote]Vivamus in risus non lacus vehicula vestibulum. In magna leo, malesuada eget pulvinar ut, pellentesque a arcu. Praesent rutrum feugiat nibh elementum posuere. Nulla volutpat porta enim vel consectetur. Etiam orci eros, blandit nec egestas eget, pharetra eget leo. Morbi lobortis adipiscing massa tincidunt dignissim. Nulla lobortis laoreet risus, tempor accumsan sem congue vitae. Cras laoreet hendrerit erat, id porttitor nunc blandit adipiscing. [/quote]
<h3>Map</h3>
[map address="Yonge St. and Eglinton Ave, Toronto, Ontario, Canada" width=100% height=400px]
<h3></h3>',
  'post_title' => 'Shortcodes',
  'post_excerpt' => '',
  'post_name' => 'shortcodes',
  'post_modified' => '2017-08-24 07:25:09',
  'post_modified_gmt' => '2017-08-24 07:25:09',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'http://demo.themify.me/bizco/?page_id=101',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'layout' => 'default',
    'display_content' => 'none',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 409,
  'post_date' => '2010-10-08 11:15:14',
  'post_date_gmt' => '2010-10-08 15:15:14',
  'post_content' => '',
  'post_title' => 'Layouts',
  'post_excerpt' => '',
  'post_name' => 'layouts',
  'post_modified' => '2017-08-24 07:24:50',
  'post_modified_gmt' => '2017-08-24 07:24:50',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'http://demo.themify.me/bizco',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'query_category' => '0',
    'layout' => 'list-thumb-image',
    'image_width' => '240',
    'image_height' => '160',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 542,
  'post_date' => '2011-04-03 00:52:10',
  'post_date_gmt' => '2011-04-03 00:52:10',
  'post_content' => '',
  'post_title' => 'Fullwidth',
  'post_excerpt' => '',
  'post_name' => 'fullwidth',
  'post_modified' => '2017-10-25 18:49:19',
  'post_modified_gmt' => '2017-10-25 18:49:19',
  'post_content_filtered' => '',
  'post_parent' => 409,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=542',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full\\"}]}]',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 547,
  'post_date' => '2011-04-03 00:53:59',
  'post_date_gmt' => '2011-04-03 00:53:59',
  'post_content' => '',
  'post_title' => 'Full - 4 Column',
  'post_excerpt' => '',
  'post_name' => '4-column',
  'post_modified' => '2017-08-24 07:23:57',
  'post_modified_gmt' => '2017-08-24 07:23:57',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=547',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'layout' => 'grid4',
    'posts_per_page' => '8',
    'display_content' => 'none',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 551,
  'post_date' => '2011-04-03 00:56:20',
  'post_date_gmt' => '2011-04-03 00:56:20',
  'post_content' => '',
  'post_title' => 'Full - 3 Column',
  'post_excerpt' => '',
  'post_name' => '3-column',
  'post_modified' => '2017-08-24 07:23:55',
  'post_modified_gmt' => '2017-08-24 07:23:55',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=551',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'layout' => 'grid3',
    'posts_per_page' => '9',
    'display_content' => 'none',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 553,
  'post_date' => '2011-04-03 00:56:45',
  'post_date_gmt' => '2011-04-03 00:56:45',
  'post_content' => '',
  'post_title' => 'Full - 2 Column',
  'post_excerpt' => '',
  'post_name' => '2-column',
  'post_modified' => '2017-10-25 18:57:20',
  'post_modified_gmt' => '2017-10-25 18:57:20',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=553',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'layout' => 'grid2',
    'posts_per_page' => '8',
    'display_content' => 'none',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full\\"}]}]',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 555,
  'post_date' => '2011-04-03 00:57:19',
  'post_date_gmt' => '2011-04-03 00:57:19',
  'post_content' => '',
  'post_title' => 'Full - Large Image List',
  'post_excerpt' => '',
  'post_name' => 'large-image-list',
  'post_modified' => '2017-08-24 07:23:58',
  'post_modified_gmt' => '2017-08-24 07:23:58',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=555',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'layout' => 'list-large-image',
    'posts_per_page' => '5',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 558,
  'post_date' => '2011-04-03 00:59:31',
  'post_date_gmt' => '2011-04-03 00:59:31',
  'post_content' => '',
  'post_title' => 'Full - Thumb Image List',
  'post_excerpt' => '',
  'post_name' => 'thumb-image-list',
  'post_modified' => '2017-08-24 07:24:00',
  'post_modified_gmt' => '2017-08-24 07:24:00',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=557',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'layout' => 'list-thumb-image',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 559,
  'post_date' => '2011-04-03 01:00:03',
  'post_date_gmt' => '2011-04-03 01:00:03',
  'post_content' => '',
  'post_title' => 'Full - 2 Column Thumb',
  'post_excerpt' => '',
  'post_name' => '2-column-thumb',
  'post_modified' => '2017-08-24 07:23:53',
  'post_modified_gmt' => '2017-08-24 07:23:53',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=559',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'layout' => 'grid2-thumb',
    'posts_per_page' => '8',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 561,
  'post_date' => '2011-04-03 01:01:38',
  'post_date_gmt' => '2011-04-03 01:01:38',
  'post_content' => '',
  'post_title' => 'Sidebar Left',
  'post_excerpt' => '',
  'post_name' => 'sidebar-left',
  'post_modified' => '2017-08-24 07:24:52',
  'post_modified_gmt' => '2017-08-24 07:24:52',
  'post_content_filtered' => '',
  'post_parent' => 409,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=561',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1 sidebar-left',
    'query_category' => '0',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 563,
  'post_date' => '2011-04-03 01:02:18',
  'post_date_gmt' => '2011-04-03 01:02:18',
  'post_content' => '',
  'post_title' => 'SB Left - 4 Column',
  'post_excerpt' => '',
  'post_name' => '4-column',
  'post_modified' => '2017-08-24 07:24:16',
  'post_modified_gmt' => '2017-08-24 07:24:16',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=563',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1 sidebar-left',
    'query_category' => '0',
    'layout' => 'grid4',
    'posts_per_page' => '8',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 566,
  'post_date' => '2011-04-03 01:03:05',
  'post_date_gmt' => '2011-04-03 01:03:05',
  'post_content' => '',
  'post_title' => 'SB Left - 3 Column',
  'post_excerpt' => '',
  'post_name' => '3-column',
  'post_modified' => '2017-08-24 07:24:14',
  'post_modified_gmt' => '2017-08-24 07:24:14',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=566',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1 sidebar-left',
    'query_category' => '0',
    'layout' => 'grid3',
    'posts_per_page' => '9',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 568,
  'post_date' => '2011-04-03 01:03:28',
  'post_date_gmt' => '2011-04-03 01:03:28',
  'post_content' => '',
  'post_title' => 'SB Left - 2 Column',
  'post_excerpt' => '',
  'post_name' => '2-column',
  'post_modified' => '2017-08-24 07:24:12',
  'post_modified_gmt' => '2017-08-24 07:24:12',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=568',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'query_category' => '0',
    'layout' => 'grid2',
    'posts_per_page' => '8',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 570,
  'post_date' => '2011-04-03 01:03:48',
  'post_date_gmt' => '2011-04-03 01:03:48',
  'post_content' => '',
  'post_title' => 'SB Left - Large Image List',
  'post_excerpt' => '',
  'post_name' => 'large-image-list',
  'post_modified' => '2017-08-24 07:24:54',
  'post_modified_gmt' => '2017-08-24 07:24:54',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=570',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1 sidebar-left',
    'query_category' => '0',
    'layout' => 'list-large-image',
    'posts_per_page' => '5',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 572,
  'post_date' => '2011-04-03 01:04:11',
  'post_date_gmt' => '2011-04-03 01:04:11',
  'post_content' => '',
  'post_title' => 'SB Left - Thumb Image List',
  'post_excerpt' => '',
  'post_name' => 'thumb-image-list',
  'post_modified' => '2017-08-24 07:24:56',
  'post_modified_gmt' => '2017-08-24 07:24:56',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=572',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1 sidebar-left',
    'query_category' => '0',
    'layout' => 'list-thumb-image',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 574,
  'post_date' => '2011-04-03 01:08:16',
  'post_date_gmt' => '2011-04-03 01:08:16',
  'post_content' => '',
  'post_title' => 'SB Left - 2 Column Thumb',
  'post_excerpt' => '',
  'post_name' => '2-column-thumb',
  'post_modified' => '2017-08-24 07:24:13',
  'post_modified_gmt' => '2017-08-24 07:24:13',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=574',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1 sidebar-left',
    'query_category' => '0',
    'layout' => 'grid2-thumb',
    'posts_per_page' => '8',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 576,
  'post_date' => '2011-04-03 01:09:14',
  'post_date_gmt' => '2011-04-03 01:09:14',
  'post_content' => '',
  'post_title' => 'Sidebar Right',
  'post_excerpt' => '',
  'post_name' => 'sidebar-right',
  'post_modified' => '2017-08-24 07:24:57',
  'post_modified_gmt' => '2017-08-24 07:24:57',
  'post_content_filtered' => '',
  'post_parent' => 409,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=576',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1',
    'query_category' => '0',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 578,
  'post_date' => '2011-04-03 01:09:38',
  'post_date_gmt' => '2011-04-03 01:09:38',
  'post_content' => '',
  'post_title' => 'SB Right - 4 Column',
  'post_excerpt' => '',
  'post_name' => '4-column',
  'post_modified' => '2017-08-24 07:25:03',
  'post_modified_gmt' => '2017-08-24 07:25:03',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=578',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1',
    'query_category' => '0',
    'layout' => 'grid4',
    'posts_per_page' => '8',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 580,
  'post_date' => '2011-04-03 01:10:05',
  'post_date_gmt' => '2011-04-03 01:10:05',
  'post_content' => '',
  'post_title' => 'SB Right - 3 Column',
  'post_excerpt' => '',
  'post_name' => '3-column',
  'post_modified' => '2017-08-24 07:25:02',
  'post_modified_gmt' => '2017-08-24 07:25:02',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=580',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'query_category' => '0',
    'layout' => 'grid3',
    'posts_per_page' => '9',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 582,
  'post_date' => '2011-04-03 01:10:24',
  'post_date_gmt' => '2011-04-03 01:10:24',
  'post_content' => '',
  'post_title' => 'SB Right - 2 Column',
  'post_excerpt' => '',
  'post_name' => '2-column',
  'post_modified' => '2017-08-24 07:24:59',
  'post_modified_gmt' => '2017-08-24 07:24:59',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=582',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'query_category' => '0',
    'layout' => 'grid2',
    'posts_per_page' => '8',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 584,
  'post_date' => '2011-04-03 01:10:47',
  'post_date_gmt' => '2011-04-03 01:10:47',
  'post_content' => '',
  'post_title' => 'SB Right - Large Image List',
  'post_excerpt' => '',
  'post_name' => 'large-image-list',
  'post_modified' => '2017-08-24 07:25:05',
  'post_modified_gmt' => '2017-08-24 07:25:05',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=584',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1',
    'query_category' => '0',
    'layout' => 'list-large-image',
    'posts_per_page' => '5',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 586,
  'post_date' => '2011-04-03 01:11:13',
  'post_date_gmt' => '2011-04-03 01:11:13',
  'post_content' => '',
  'post_title' => 'SB Right - Thumb Image List',
  'post_excerpt' => '',
  'post_name' => 'thumb-image-list',
  'post_modified' => '2017-08-24 07:25:07',
  'post_modified_gmt' => '2017-08-24 07:25:07',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=586',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1',
    'query_category' => '0',
    'layout' => 'list-thumb-image',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 588,
  'post_date' => '2011-04-03 01:11:50',
  'post_date_gmt' => '2011-04-03 01:11:50',
  'post_content' => '',
  'post_title' => 'SB Right - 2 Column Thumb',
  'post_excerpt' => '',
  'post_name' => '2-column-thumb',
  'post_modified' => '2017-08-24 07:25:01',
  'post_modified_gmt' => '2017-08-24 07:25:01',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=588',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar1',
    'query_category' => '0',
    'layout' => 'grid2-thumb',
    'posts_per_page' => '8',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 594,
  'post_date' => '2011-04-03 01:15:51',
  'post_date_gmt' => '2011-04-03 01:15:51',
  'post_content' => 'Download this contact plugin: <a href="http://wordpress.org/extend/plugins/contact-form-7">Contact Form 7</a>.

[contact-form 1 "Contact form 1"]',
  'post_title' => 'Contact',
  'post_excerpt' => '',
  'post_name' => 'contact',
  'post_modified' => '2017-08-24 07:23:42',
  'post_modified_gmt' => '2017-08-24 07:23:42',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=594',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 596,
  'post_date' => '2011-04-03 01:17:48',
  'post_date_gmt' => '2011-04-03 01:17:48',
  'post_content' => '',
  'post_title' => 'Location',
  'post_excerpt' => '',
  'post_name' => 'location',
  'post_modified' => '2017-08-25 02:55:26',
  'post_modified_gmt' => '2017-08-25 02:55:26',
  'post_content_filtered' => '',
  'post_parent' => 594,
  'guid' => 'https://themify.me/demo/themes/bizco/?page_id=596',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    '_themify_builder_settings_json' => '[{\\"row_order\\":\\"0\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col3-2 first\\",\\"modules\\":[{\\"mod_name\\":\\"map\\",\\"mod_settings\\":{\\"map_display_type\\":\\"dynamic\\",\\"address_map\\":\\"Yonge St. and Eglinton Ave, Toronto, Ontario, Canada\\",\\"zoom_map\\":\\"15\\",\\"w_map\\":\\"100\\",\\"unit_w\\":\\"%\\",\\"h_map\\":\\"600\\",\\"unit_h\\":\\"px\\",\\"b_style_map\\":\\"solid\\",\\"type_map\\":\\"ROADMAP\\",\\"scrollwheel_map\\":\\"disable\\",\\"draggable_map\\":\\"enable\\",\\"draggable_disable_mobile_map\\":\\"yes\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"border_top_style\\":\\"solid\\",\\"border_right_style\\":\\"solid\\",\\"border_bottom_style\\":\\"solid\\",\\"border_left_style\\":\\"solid\\",\\"checkbox_border_apply_all\\":\\"border\\"}}],\\"styling\\":[]},{\\"column_order\\":\\"1\\",\\"grid_class\\":\\"col3-1 last\\",\\"modules\\":[{\\"mod_name\\":\\"text\\",\\"mod_settings\\":{\\"content_text\\":\\"<h3>Direction</h3><p>We are located at Aliquam faucibus turpis at libero consectetur euismod. Nam nunc lectus, congue non egestas quis, condimentum ut arcu. Nulla placerat, tortor non egestas rutrum, mi turpis adipiscing dui, et mollis turpis tortor vel orci. Cras a fringilla nunc. Suspendisse volutpat, eros congue scelerisque iaculis, magna odio sodales dui, vitae vulputate elit metus ac arcu.</p><h3>Address</h3><p>123 Street Name,<br /> City, Province<br /> 23446</p><h3>Phone</h3><p>236-298-2828</p><h3>Hours</h3><p>Mon - Fri : 11:00am - 10:00pm<br /> Sat : 11:00am - 2:00pm<br /> Sun : 12:00am - 11:00pm</p>\\",\\"column_divider_style\\":\\"solid\\",\\"checkbox_padding_apply_all\\":\\"padding\\",\\"checkbox_margin_apply_all\\":\\"margin\\",\\"border_top_style\\":\\"solid\\",\\"border_right_style\\":\\"solid\\",\\"border_bottom_style\\":\\"solid\\",\\"border_left_style\\":\\"solid\\",\\"checkbox_border_apply_all\\":\\"border\\"}}],\\"styling\\":[]}],\\"column_alignment\\":\\"\\",\\"styling\\":[]},{\\"row_order\\":\\"1\\",\\"cols\\":[{\\"column_order\\":\\"0\\",\\"grid_class\\":\\"col-full first last\\",\\"modules\\":[],\\"styling\\":[]}],\\"styling\\":[]}]',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 652,
  'post_date' => '2011-04-04 05:33:51',
  'post_date_gmt' => '2011-04-04 05:33:51',
  'post_content' => '',
  'post_title' => 'Section 4 Column',
  'post_excerpt' => '',
  'post_name' => 'section-4-column',
  'post_modified' => '2017-08-24 07:24:08',
  'post_modified_gmt' => '2017-08-24 07:24:08',
  'post_content_filtered' => '',
  'post_parent' => 662,
  'guid' => 'https://themify.me/demo/themes/blogfolio/?page_id=652',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'section_categories' => 'yes',
    'layout' => 'grid4',
    'posts_per_page' => '4',
    'display_content' => 'none',
    'hide_meta' => 'yes',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 654,
  'post_date' => '2011-04-04 05:34:36',
  'post_date_gmt' => '2011-04-04 05:34:36',
  'post_content' => '',
  'post_title' => 'Section 3 Column',
  'post_excerpt' => '',
  'post_name' => 'section-3-column',
  'post_modified' => '2017-08-24 07:24:06',
  'post_modified_gmt' => '2017-08-24 07:24:06',
  'post_content_filtered' => '',
  'post_parent' => 662,
  'guid' => 'https://themify.me/demo/themes/blogfolio/?page_id=654',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'section_categories' => 'yes',
    'layout' => 'grid3',
    'posts_per_page' => '3',
    'display_content' => 'none',
    'hide_meta' => 'yes',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 660,
  'post_date' => '2011-04-04 05:38:34',
  'post_date_gmt' => '2011-04-04 05:38:34',
  'post_content' => '',
  'post_title' => 'Section 2 Column',
  'post_excerpt' => '',
  'post_name' => 'section-2-column',
  'post_modified' => '2017-08-24 07:24:03',
  'post_modified_gmt' => '2017-08-24 07:24:03',
  'post_content_filtered' => '',
  'post_parent' => 662,
  'guid' => 'https://themify.me/demo/themes/blogfolio/?page_id=660',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'section_categories' => 'yes',
    'layout' => 'grid2',
    'posts_per_page' => '2',
    'display_content' => 'none',
    'hide_meta' => 'yes',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 662,
  'post_date' => '2011-04-04 05:39:35',
  'post_date_gmt' => '2011-04-04 05:39:35',
  'post_content' => '',
  'post_title' => 'Sections',
  'post_excerpt' => '',
  'post_name' => 'sections',
  'post_modified' => '2017-08-24 07:24:01',
  'post_modified_gmt' => '2017-08-24 07:24:01',
  'post_content_filtered' => '',
  'post_parent' => 409,
  'guid' => 'https://themify.me/demo/themes/blogfolio/?page_id=662',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'section_categories' => 'yes',
    'layout' => 'list-thumb-image',
    'posts_per_page' => '2',
    'hide_meta' => 'yes',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 668,
  'post_date' => '2011-04-29 21:16:48',
  'post_date_gmt' => '2011-04-29 21:16:48',
  'post_content' => '',
  'post_title' => 'Section 2 Column Thumb',
  'post_excerpt' => '',
  'post_name' => 'section-2-column-thumb',
  'post_modified' => '2017-08-24 07:24:05',
  'post_modified_gmt' => '2017-08-24 07:24:05',
  'post_content_filtered' => '',
  'post_parent' => 662,
  'guid' => 'https://themify.me/demo/themes/blogfolio/?page_id=668',
  'menu_order' => 0,
  'post_type' => 'page',
  'meta_input' => 
  array (
    'page_layout' => 'sidebar-none',
    'query_category' => '0',
    'section_categories' => 'yes',
    'layout' => 'grid2-thumb',
    'posts_per_page' => '2',
  ),
  'tax_input' => 
  array (
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1024,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => 'Download this contact plugin: Contact Form 7. [contact-form 1 "Contact form 1"]',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1024',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1024/',
  'menu_order' => 1,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '0',
    '_menu_item_object_id' => '594',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1053,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => 'Download this contact plugin: Contact Form 7. [contact-form 1 "Contact form 1"]',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1053',
  'post_modified' => '2017-07-25 21:40:11',
  'post_modified_gmt' => '2017-07-25 21:40:11',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1053/',
  'menu_order' => 1,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '0',
    '_menu_item_object_id' => '594',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'footer-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1023,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => 'Buttons [button style="orange" link="https://themify.me"]Orange[/button] [button style="blue"]Blue[/button] [button style="pink"]Pink[/button] [button style="green"]Green[/button] [button style="red"]Red[/button] [button style="black"]Black[/button] [hr] [button style="small"]Small[/button] [button]Default[/button] [button style="large"]Large[/button] [button style="xlarge"]Xlarge[/button] [hr] [button style="orange small"]Orange Small[/button] [button style="blue"]Blue[/button] [button style="green large"]Green Large[/button] [button style="red xlarge"]Red Xlarge[/button] [hr] Columns [col grid="2-1 first"] col 2-1 Sed sagittis, elit egestas rutrum vehicula, neque dolor fringilla lacus, ut rhoncus turpis augue vitae libero. Nam risus velit, rhoncus eg. [/col] [col grid="2-1"] col 2-1 Curabitur vel risus eros, sed eleifend arcu. Donec porttitor hendrerit diam et blandit. Curabitur vitae velit ligula, vitae lobortis massa. [/col] [hr] [col grid="3-1 first"] col 3-1 Sed sagittis, elit egestas rutrum vehicula, neque dolor fringilla lacus, ut rhoncus turpis augue vitae libero. Nam risus velit, rhoncus eg. [/col] [col grid="3-1"] col 3-1 Curabitur vel risus eros, sed eleifend arcu. Donec porttitor hendrerit diam et blandit. Curabitur vitae velit ligula, vitae lobortis massa. [/col] [col grid="3-1"] col 3-1 Vivamus dignissim, ligula velt pretium leo, vel placerat ipsum risus luctus purus. Tos, sed eleifend arcu. Donec porttitor hendrerit. [/col] [hr] [col grid="4-1 first"] col 4-1 Sed sagittis, elit egestas rutrum vehicula, neque dolor fringilla lacus, ut rhoncus turpis augue vitae libero. Nam risus velit, rhoncus eget co. [/col] [col grid="4-1"] col…',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1023',
  'post_modified' => '2017-07-25 21:40:11',
  'post_modified_gmt' => '2017-07-25 21:40:11',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1023/',
  'menu_order' => 2,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '0',
    '_menu_item_object_id' => '408',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'footer-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1025,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => '[col grid=”3-2 first”] [map address=”Yonge St. and Eglinton Ave, Toronto, Ontario, Canada” width=100% height=600px] [/col] [col grid=”3-1″] Direction We are located at Aliquam faucibus turpis at libero consectetur euismod. Nam nunc lectus, congue non egestas quis, condimentum ut arcu. Nulla placerat, tortor non egestas rutrum, mi turpis adipiscing dui, et mollis turpis tortor vel orci. Cras a fringilla nunc. Suspendisse volutpat, eros congue scelerisque iaculis, magna odio sodales dui, vitae vulputate elit metus ac arcu. Address 123 Street Name, City, Province 23446 Phone 236-298-2828 Hours Mon – Fri : 11:00am – 10:00pm Sat : 11:00am – 2:00pm Sun : 12:00am – 11:00pm [/col]',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1025',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 594,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1025/',
  'menu_order' => 2,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1024',
    '_menu_item_object_id' => '596',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1026,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1026',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1026/',
  'menu_order' => 3,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '0',
    '_menu_item_object_id' => '409',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1027,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1027',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 409,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1027/',
  'menu_order' => 4,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1026',
    '_menu_item_object_id' => '542',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1028,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => '',
  'post_title' => 'Full – 2 Column',
  'post_excerpt' => '',
  'post_name' => '1028',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1028/',
  'menu_order' => 5,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1027',
    '_menu_item_object_id' => '553',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1029,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => '',
  'post_title' => 'Full – 2 Column Thumb',
  'post_excerpt' => '',
  'post_name' => '1029',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1029/',
  'menu_order' => 6,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1027',
    '_menu_item_object_id' => '559',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1030,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => '',
  'post_title' => 'Full – 3 Column',
  'post_excerpt' => '',
  'post_name' => '1030',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1030/',
  'menu_order' => 7,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1027',
    '_menu_item_object_id' => '551',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1031,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => '',
  'post_title' => 'Full – 4 Column',
  'post_excerpt' => '',
  'post_name' => '1031',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1031/',
  'menu_order' => 8,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1027',
    '_menu_item_object_id' => '547',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1032,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => '',
  'post_title' => 'Full – Large Image List',
  'post_excerpt' => '',
  'post_name' => '1032',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1032/',
  'menu_order' => 9,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1027',
    '_menu_item_object_id' => '555',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1033,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => '',
  'post_title' => 'Full – Thumb Image List',
  'post_excerpt' => '',
  'post_name' => '1033',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 542,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1033/',
  'menu_order' => 10,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1027',
    '_menu_item_object_id' => '558',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1034,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1034',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 409,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1034/',
  'menu_order' => 11,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1026',
    '_menu_item_object_id' => '662',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1035,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1035',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 662,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1035/',
  'menu_order' => 12,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1034',
    '_menu_item_object_id' => '660',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1036,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1036',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 662,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1036/',
  'menu_order' => 13,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1034',
    '_menu_item_object_id' => '668',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1037,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1037',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 662,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1037/',
  'menu_order' => 14,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1034',
    '_menu_item_object_id' => '654',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1038,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1038',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 662,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1038/',
  'menu_order' => 15,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1034',
    '_menu_item_object_id' => '652',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1039,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1039',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 409,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1039/',
  'menu_order' => 16,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1026',
    '_menu_item_object_id' => '561',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1040,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Left – 2 Column',
  'post_excerpt' => '',
  'post_name' => '1040',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1040/',
  'menu_order' => 17,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1039',
    '_menu_item_object_id' => '568',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1041,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Left – 2 Column Thumb',
  'post_excerpt' => '',
  'post_name' => '1041',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1041/',
  'menu_order' => 18,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1039',
    '_menu_item_object_id' => '574',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1042,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Left – 3 Column',
  'post_excerpt' => '',
  'post_name' => '1042',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1042/',
  'menu_order' => 19,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1039',
    '_menu_item_object_id' => '566',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1043,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Left – 4 Column',
  'post_excerpt' => '',
  'post_name' => '1043',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1043/',
  'menu_order' => 20,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1039',
    '_menu_item_object_id' => '563',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1044,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Left – Large Image List',
  'post_excerpt' => '',
  'post_name' => '1044',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1044/',
  'menu_order' => 21,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1039',
    '_menu_item_object_id' => '570',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1045,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Left – Thumb Image List',
  'post_excerpt' => '',
  'post_name' => '1045',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 561,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1045/',
  'menu_order' => 22,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1039',
    '_menu_item_object_id' => '572',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1046,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => ' ',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1046',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 409,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1046/',
  'menu_order' => 23,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1026',
    '_menu_item_object_id' => '576',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1047,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Right – 2 Column',
  'post_excerpt' => '',
  'post_name' => '1047',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1047/',
  'menu_order' => 24,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1046',
    '_menu_item_object_id' => '582',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1048,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Right – 2 Column Thumb',
  'post_excerpt' => '',
  'post_name' => '1048',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1048/',
  'menu_order' => 25,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1046',
    '_menu_item_object_id' => '588',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1049,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Right – 3 Column',
  'post_excerpt' => '',
  'post_name' => '1049',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1049/',
  'menu_order' => 26,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1046',
    '_menu_item_object_id' => '580',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1050,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Right – 4 Column',
  'post_excerpt' => '',
  'post_name' => '1050',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1050/',
  'menu_order' => 27,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1046',
    '_menu_item_object_id' => '578',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1051,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Right – Large Image List',
  'post_excerpt' => '',
  'post_name' => '1051',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1051/',
  'menu_order' => 28,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1046',
    '_menu_item_object_id' => '584',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1052,
  'post_date' => '2011-12-01 04:58:22',
  'post_date_gmt' => '2011-12-01 04:58:22',
  'post_content' => '',
  'post_title' => 'SB Right – Thumb Image List',
  'post_excerpt' => '',
  'post_name' => '1052',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 576,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1052/',
  'menu_order' => 29,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1046',
    '_menu_item_object_id' => '586',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}

$post = array (
  'ID' => 1022,
  'post_date' => '2011-12-01 04:58:21',
  'post_date_gmt' => '2011-12-01 04:58:21',
  'post_content' => 'Buttons [button style=”orange” link=”https://themify.me”]Orange[/button] [button style=”blue”]Blue[/button] [button style=”pink”]Pink[/button] [button style=”green”]Green[/button] [button style=”red”]Red[/button] [button style=”black”]Black[/button] [hr] [button style=”small”]Small[/button] [button]Default[/button] [button style=”large”]Large[/button] [button style=”xlarge”]Xlarge[/button] [hr] [button style=”orange small”]Orange Small[/button] [button style=”blue”]Blue[/button] [button style=”green large”]Green Large[/button] [button style=”red xlarge”]Red Xlarge[/button] [hr] Columns [col grid=”2-1 first”] col 2-1 Sed sagittis, elit egestas rutrum vehicula, neque dolor fringilla lacus, ut rhoncus turpis augue vitae libero. Nam risus velit, rhoncus eg. [/col] [col grid=”2-1″] col 2-1 Curabitur vel risus eros, sed eleifend arcu. Donec porttitor hendrerit diam et blandit. Curabitur vitae velit ligula, vitae lobortis massa. [/col] [hr] [col grid=”3-1 first”] col 3-1 Sed sagittis, elit egestas rutrum vehicula, neque dolor fringilla lacus, ut rhoncus turpis augue vitae libero. Nam risus velit, rhoncus eg. [/col] [col grid=”3-1″] col 3-1 Curabitur vel risus eros, sed eleifend arcu. Donec porttitor hendrerit diam et blandit. Curabitur vitae velit ligula, vitae lobortis massa. [/col] [col grid=”3-1″] col 3-1 Vivamus dignissim, ligula velt pretium leo, vel placerat ipsum risus luctus purus. Tos, sed eleifend arcu. Donec porttitor hendrerit. [/col] [hr] [col grid=”4-1 first”] col 4-1 Sed sagittis, elit egestas rutrum vehicula, neque dolor fringilla lacus, ut rhoncus turpis augue vitae libero. Nam risus velit, rhoncus eget co. [/col] [col grid=”4-1″] col…',
  'post_title' => '',
  'post_excerpt' => '',
  'post_name' => '1022',
  'post_modified' => '2017-07-25 21:41:31',
  'post_modified_gmt' => '2017-07-25 21:41:31',
  'post_content_filtered' => '',
  'post_parent' => 0,
  'guid' => 'https://themify.me/demo/themes/basic/2011/12/01/1022/',
  'menu_order' => 30,
  'post_type' => 'nav_menu_item',
  'meta_input' => 
  array (
    '_menu_item_type' => 'post_type',
    '_menu_item_menu_item_parent' => '1026',
    '_menu_item_object_id' => '408',
    '_menu_item_object' => 'page',
    '_menu_item_classes' => 
    array (
      0 => '',
    ),
  ),
  'tax_input' => 
  array (
    'nav_menu' => 'main-menu',
  ),
);
if( ERASEDEMO ) {
	themify_undo_import_post( $post );
} else {
	themify_import_post( $post );
}


function themify_import_get_term_id_from_slug( $slug ) {
	$menu = get_term_by( "slug", $slug, "nav_menu" );
	return is_wp_error( $menu ) ? 0 : (int) $menu->term_id;
}

	$widgets = get_option( "widget_themify-feature-posts" );
$widgets[1002] = array (
  'title' => 'Recent Posts',
  'category' => '0',
  'show_count' => '3',
  'show_date' => 'on',
  'show_thumb' => 'on',
  'show_excerpt' => NULL,
  'hide_title' => NULL,
  'thumb_width' => '35',
  'thumb_height' => '35',
  'excerpt_length' => '55',
);
update_option( "widget_themify-feature-posts", $widgets );

$widgets = get_option( "widget_themify-twitter" );
$widgets[1003] = array (
  'title' => 'Latest Tweets',
  'username' => 'themify',
  'show_count' => '3',
  'hide_timestamp' => NULL,
  'show_follow' => 'on',
  'follow_text' => '→ Follow me',
);
update_option( "widget_themify-twitter", $widgets );

$widgets = get_option( "widget_themify-social-links" );
$widgets[1004] = array (
  'title' => '',
  'show_link_name' => NULL,
  'thumb_width' => '',
  'thumb_height' => '',
);
update_option( "widget_themify-social-links", $widgets );

$widgets = get_option( "widget_search" );
$widgets[1005] = array (
  'title' => '',
);
update_option( "widget_search", $widgets );

$widgets = get_option( "widget_recent-posts" );
$widgets[1006] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-posts", $widgets );

$widgets = get_option( "widget_recent-comments" );
$widgets[1007] = array (
  'title' => '',
  'number' => 5,
);
update_option( "widget_recent-comments", $widgets );

$widgets = get_option( "widget_archives" );
$widgets[1008] = array (
  'title' => '',
  'count' => 0,
  'dropdown' => 0,
);
update_option( "widget_archives", $widgets );

$widgets = get_option( "widget_categories" );
$widgets[1009] = array (
  'title' => '',
  'count' => 0,
  'hierarchical' => 0,
  'dropdown' => 0,
);
update_option( "widget_categories", $widgets );

$widgets = get_option( "widget_meta" );
$widgets[1010] = array (
  'title' => '',
);
update_option( "widget_meta", $widgets );

$widgets = get_option( "widget_themify-flickr" );
$widgets[1011] = array (
  'title' => 'Recent Photos',
  'username' => '52839779@N02',
  'show_count' => '8',
);
update_option( "widget_themify-flickr", $widgets );



$sidebars_widgets = array (
  'sidebar-main' => 
  array (
    0 => 'themify-feature-posts-1002',
    1 => 'themify-twitter-1003',
  ),
  'social-widget' => 
  array (
    0 => 'themify-social-links-1004',
  ),
  'orphaned_widgets_1' => 
  array (
    0 => 'search-1005',
    1 => 'recent-posts-1006',
    2 => 'recent-comments-1007',
    3 => 'archives-1008',
    4 => 'categories-1009',
    5 => 'meta-1010',
  ),
  'orphaned_widgets_2' => 
  array (
    0 => 'themify-flickr-1011',
  ),
); 
update_option( "sidebars_widgets", $sidebars_widgets );

$menu_locations = array();
set_theme_mod( "nav_menu_locations", $menu_locations );



	ob_start(); ?>a:47:{s:16:"setting-page_404";s:1:"0";s:21:"setting-webfonts_list";s:11:"recommended";s:22:"setting-default_layout";s:8:"sidebar1";s:27:"setting-default_post_layout";s:5:"grid2";s:30:"setting-default_layout_display";s:7:"excerpt";s:25:"setting-default_more_text";s:4:"More";s:21:"setting-index_orderby";s:4:"date";s:19:"setting-index_order";s:4:"DESC";s:31:"setting-image_post_feature_size";s:5:"blank";s:32:"setting-default_page_post_layout";s:8:"sidebar1";s:38:"setting-image_post_single_feature_size";s:5:"blank";s:27:"setting-default_page_layout";s:8:"sidebar1";s:53:"setting-customizer_responsive_design_tablet_landscape";s:4:"1024";s:43:"setting-customizer_responsive_design_tablet";s:3:"768";s:43:"setting-customizer_responsive_design_mobile";s:3:"480";s:33:"setting-mobile_menu_trigger_point";s:4:"1200";s:24:"setting-gallery_lightbox";s:8:"lightbox";s:31:"setting-lightbox_content_images";s:2:"on";s:26:"setting-page_builder_cache";s:2:"on";s:27:"setting-script_minification";s:7:"disable";s:27:"setting-page_builder_expiry";s:1:"2";s:19:"setting-entries_nav";s:8:"numbered";s:22:"setting-footer_widgets";s:17:"footerwidget-3col";s:27:"setting-global_feature_size";s:5:"large";s:22:"setting-link_icon_type";s:10:"image-icon";s:32:"setting-link_type_themify-link-0";s:10:"image-icon";s:33:"setting-link_title_themify-link-0";s:7:"Twitter";s:32:"setting-link_link_themify-link-0";s:26:"http://twitter.com/twitter";s:31:"setting-link_img_themify-link-0";s:91:"https://themify.me/demo/themes/basic/wp-content/themes/basic/themify/img/social/twitter.png";s:32:"setting-link_type_themify-link-1";s:10:"image-icon";s:33:"setting-link_title_themify-link-1";s:8:"Facebook";s:32:"setting-link_link_themify-link-1";s:27:"http://facebook.com/themify";s:31:"setting-link_img_themify-link-1";s:92:"https://themify.me/demo/themes/basic/wp-content/themes/basic/themify/img/social/facebook.png";s:32:"setting-link_type_themify-link-2";s:10:"image-icon";s:33:"setting-link_title_themify-link-2";s:7:"Google+";s:31:"setting-link_img_themify-link-2";s:95:"https://themify.me/demo/themes/basic/wp-content/themes/basic/themify/img/social/google-plus.png";s:32:"setting-link_type_themify-link-3";s:10:"image-icon";s:33:"setting-link_title_themify-link-3";s:7:"YouTube";s:31:"setting-link_img_themify-link-3";s:91:"https://themify.me/demo/themes/basic/wp-content/themes/basic/themify/img/social/youtube.png";s:32:"setting-link_type_themify-link-4";s:10:"image-icon";s:33:"setting-link_title_themify-link-4";s:9:"Pinterest";s:31:"setting-link_img_themify-link-4";s:93:"https://themify.me/demo/themes/basic/wp-content/themes/basic/themify/img/social/pinterest.png";s:22:"setting-link_field_ids";s:171:"{"themify-link-0":"themify-link-0","themify-link-1":"themify-link-1","themify-link-2":"themify-link-2","themify-link-3":"themify-link-3","themify-link-4":"themify-link-4"}";s:23:"setting-link_field_hash";s:1:"5";s:30:"setting-page_builder_is_active";s:6:"enable";s:46:"setting-page_builder_animation_parallax_scroll";s:6:"mobile";s:4:"skin";s:85:"https://themify.me/demo/themes/basic/wp-content/themes/basic/themify/img/non-skin.gif";}<?php $themify_data = unserialize( ob_get_clean() );

	// fix the weird way "skin" is saved
	if( isset( $themify_data['skin'] ) ) {
		$parsed_skin = parse_url( $themify_data['skin'], PHP_URL_PATH );
		$basedir_skin = basename( dirname( $parsed_skin ) );
		$themify_data['skin'] = trailingslashit( get_template_directory_uri() ) . 'skins/' . $basedir_skin . '/style.css';
	}

	themify_set_data( $themify_data );
	
}
themify_do_demo_import();