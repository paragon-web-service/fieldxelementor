<?php
new STM_LMS_Courses();

class STM_LMS_Courses {

	public function __construct() {
		add_filter( 'stm_lms_archive_filter_args', array( $this, 'filter' ) );
		add_action( 'wp_trash_post', array( $this, 'trash_course' ) );
		add_filter( 'stm_lms_filter_courses', array( $this, 'filter_courses' ), 10, 4 );
	}

	public function trash_course( $post_id ) {
		if ( current_user_can( 'manage_options' ) ) {
			$post_type = get_post_type( $post_id );
			if ( 'stm-courses' == $post_type ) {
				stm_lms_get_delete_courses( $post_id );
			}
		}
	}

	public static function stm_lms_rows_to_cols_pivot( $meta_keys ) {
		$result = '';
		foreach ( $meta_keys as $meta_key ) {
			$result = $result . " max(case when pm.meta_key='{$meta_key}' then meta_value end) as {$meta_key},";
		}
		if ( ! empty( $result ) ) {
			return substr( $result, 0, -1 );
		}
	}

	public static function get_courses_metas( $courses ) {
		global $wpdb;
		$meta_keys  = array(
			'curriculum',
			'current_students',
			'views',
			'level',
			'price',
			'sale_price',
			'not_single_sale',
			'featured',
			'duration_info',
			'course_marks',
		);
		$meta_query = self::stm_lms_rows_to_cols_pivot( $meta_keys );
		$courses    = implode( ',', $courses );
		$result     = $wpdb->get_results(
			/* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared */
			'SELECT p.id, p.post_title, p.post_author, p.post_excerpt, ' . $meta_query . ' FROM ' . $wpdb->prefix . 'posts AS p LEFT JOIN ' . $wpdb->prefix . "postmeta as pm ON pm.post_id = p.ID
			WHERE p.post_type = 'stm-courses' AND p.post_status = 'publish' AND p.ID IN ( $courses ) GROUP BY p.ID ORDER BY FIELD( p.ID, $courses )", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);
		return $result ?? array();
	}

	public static function get_course_submetas( $course ) {
		$course['current_status'] = STM_LMS_Course::get_post_status( $course['id'] );
		$course['rating']         = STM_LMS_Course::course_average_rate( maybe_unserialize( $course['course_marks'] ) );
		$course['lectures']       = STM_LMS_Course::curriculum_info( $course['curriculum'] );
		$course['image']          = get_the_post_thumbnail_url( $course['id'], 'img-300-225' );
		$course['author_info']    = STM_LMS_User::get_current_user( $course['post_author'] );
		$course['url']            = get_post_permalink( $course['id'] );
		$categories               = stm_lms_get_terms_array( $course['id'], 'stm_lms_course_taxonomy', false, true );
		$course['terms']          = ! empty( $categories[0] ) ? $categories[0] : '';
		$progress                 = 0;
		if ( is_user_logged_in() ) {
			$my_progress = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_course( get_current_user_id(), $course['id'], array( 'progress_percent' ) ) );
			if ( ! empty( $my_progress['progress_percent'] ) ) {
				$progress = $my_progress['progress_percent'];
			}
			if ( $progress > 100 ) {
				$progress = 100;
			}
		}
		$course['progress'] = $progress;

		return $course;
	}

	public static function get_all_courses( $args ) {
		$default_args = array(
			'post_type' => 'stm-courses',
			'fields'    => 'ids',
		);
		$query        = new WP_Query( wp_parse_args( $args, $default_args ) );
		if ( $query->have_posts() ) {
			$query->posts = self::get_courses_metas( $query->posts );
			return array(
				'posts'       => $query->posts,
				'total_pages' => $query->max_num_pages,
				'total_posts' => $query->found_posts,
			);
		}
		return array();
	}

	public function sorting_options( $value ) {
		$sorting_options = array(
			'date_low'   => array(
				'meta_key' => '',
				'orderby'  => 'date',
				'order'    => 'ASC',
			),
			'date_high'  => array(
				'meta_key' => '',
				'orderby'  => 'date',
				'order'    => 'DESC',
			),
			'rating'     => array(
				'meta_key' => 'course_mark_average',
				'orderby'  => 'meta_value_num',
				'order'    => 'DESC',
			),
			'popular'    => array(
				'meta_key' => 'views',
				'orderby'  => 'meta_value_num',
				'order'    => 'DESC',
			),
			'price_high' => array(
				'meta_key' => 'price',
				'orderby'  => 'meta_value_num',
				'order'    => 'DESC',
			),
			'price_low'  => array(
				'meta_key' => 'price',
				'orderby'  => 'meta_value_num',
				'order'    => 'ASC',
			),
		);
		if ( ! empty( $value ) ) {
			$array = array_filter(
				$sorting_options,
				function( $a ) use ( $value ) {
					return $a === $value;
				},
				ARRAY_FILTER_USE_KEY
			);
			return $array[ $value ];
		}
	}

	public function price_options( $value ) {
		$price_options = array(
			'free_courses' => array(
				'relation' => 'AND',
				array(
					'key'     => 'price',
					'value'   => array( 0, '' ),
					'compare' => 'in',
				),
				array(
					'key'     => 'not_single_sale',
					'value'   => 'on',
					'compare' => '!=',
				),
			),
			'paid_courses' => array(
				'key'     => 'price',
				'value'   => 0,
				'compare' => '>',
			),
			'subscription' => array(
				'key'     => 'not_single_sale',
				'value'   => 'on',
				'compare' => '=',
			),
		);
		if ( ! empty( $value ) ) {
			$array = array_filter(
				$price_options,
				function( $a ) use ( $value ) {
					return $a === $value;
				},
				ARRAY_FILTER_USE_KEY
			);
			return $array[ $value ];
		}
	}

	public function filter_courses( $default_args, $terms, $metas, $sort_by ) {
		if ( is_array( $default_args ) ) {
			if ( ! empty( $terms ) && is_array( $terms ) ) {
				$default_args['tax_query'] = array(
					array(
						'taxonomy' => 'stm_lms_course_taxonomy',
						'field'    => 'term_id',
						'terms'    => $terms,
					),
				);
			}
			if ( ! empty( $metas ) && is_array( $metas ) ) {
				foreach ( $metas as $key => $value ) {
					switch ( $key ) {
						case 'search':
							$default_args['s'] = $value;
							break;
						case 'instructor':
							$default_args['author__in'] = $value;
							break;
						case 'price':
							$default_args['meta_query'][ $key ] = array( 'relation' => 'OR' );
							foreach ( $value as $item ) {
								array_push(
									$default_args['meta_query'][ $key ],
									$this->price_options( $item ),
								);
							}
							break;
						case 'status':
							$default_args['meta_query'][ $key ] = array( 'relation' => 'OR' );
							foreach ( $value as $item ) {
								array_push(
									$default_args['meta_query'][ $key ],
									array(
										'key'     => $key,
										'value'   => $item,
										'compare' => '=',
									)
								);
							}
							break;
						case 'level':
							$default_args['meta_query'][ $key ] = array(
								'relation' => 'OR',
								array(
									'key'     => $key,
									'value'   => $value,
									'compare' => 'IN',
								),
							);
							break;
						case 'rating':
							if ( ! empty( $value ) ) {
								$default_args['meta_query'][ $key ] = array(
									'relation' => 'OR',
									array(
										'key'     => 'course_mark_average',
										'value'   => $value,
										'compare' => '>=',
									),
								);
							}
							break;
					}
				}
			}
			if ( ! empty( $sort_by ) ) {
				$sorting_options          = $this->sorting_options( $sort_by );
				$default_args['meta_key'] = $sorting_options['meta_key'];
				$default_args['orderby']  = $sorting_options['orderby'];
				$default_args['order']    = $sorting_options['order'];
			}
		}
		return $default_args;
	}

	public static function get_query_metas_from_url() {
		$queries = array(
			'status'     => array(),
			'level'      => array(),
			'rating'     => '',
			'price'      => array(),
			'instructor' => array(),
		);
		$metas   = array();
		foreach ( array_keys( $queries ) as $key ) {
			if ( isset( $_GET[ $key ] ) ) {
				$metas[ $key ] = ( is_array( $_GET[ $key ] ) ) ? STM_LMS_Helpers::array_sanitize( wp_unslash( $_GET[ $key ] ) ) : floatval( $_GET[ $key ] );
			}
		}
		if ( isset( $_GET['search'] ) ) {
			$metas['search'] = sanitize_text_field( wp_unslash( $_GET['search'] ) );
		}
		return $metas;
	}

	public static function get_courses_child_terms( $parents ) {
		if ( ! empty( $parents ) ) {
			$terms = array();
			$index = 0;
			foreach ( $parents as $parent ) {
				$category_terms = get_terms(
					'stm_lms_course_taxonomy',
					array(
						'orderby'  => 'count',
						'order'    => 'DESC',
						'child_of' => $parent,
					)
				);
				if ( ! empty( $category_terms ) ) {
					$terms[ $index ]['parent_name']    = get_term( $parent )->name;
					$terms[ $index ]['category_terms'] = $category_terms;
				}
				$index ++;
			}
			return $terms;
		}
		return array();
	}

	/* all further functions will be removed after archive courses page update */

	public static function filter_enabled() {
		return STM_LMS_Options::get_option( 'enable_courses_filter', '' );
	}

	public function filter( $args ) {
		$this->filter_categories( $args );
		$this->filter_statuses( $args );
		$this->filter_level( $args );
		$this->filter_rating( $args );
		$this->filter_instructor( $args );
		$this->filter_price( $args );
		return $args;
	}

	public function filter_categories( &$args ) {
		if ( ! empty( $_GET['category'] ) ) {

			$categories = array();

			foreach ( $_GET['category'] as $category ) {
				$categories[] = intval( $category );
			}

			if ( empty( $args['tax_query'] ) ) {
				$args['tax_query'] = array();
			}

			$args['tax_query']['category'] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'stm_lms_course_taxonomy',
					'field'    => 'term_id',
					'terms'    => $categories,
				),
			);

			if ( ! empty( $_GET['subcategory'] ) ) {

				$subcategories = array();

				foreach ( $_GET['subcategory'] as $subcategory ) {
					$subcategories[] = intval( $subcategory );
				}

				if ( empty( $args['tax_query'] ) ) {
					$args['tax_query'] = array();
				}
				if ( empty( $args['tax_query']['category'] ) ) {
					$args['tax_query']['category'] = array();
				}

				$args['tax_query']['category'][] = array(
					'taxonomy' => 'stm_lms_course_taxonomy',
					'field'    => 'term_id',
					'terms'    => $subcategories,
				);

			}
		}

	}

	public function filter_statuses( &$args ) {
		$status = ! empty( $_GET['status'] ) ? $_GET['status'] : array();

		if ( ! empty( $args['featured'] ) && $args['featured'] && ! STM_LMS_Options::get_option( 'disable_featured_courses', false ) ) {

			$per_row                = STM_LMS_Options::get_option( 'courses_per_row', 3 );
			$number_of_featured     = STM_LMS_Options::get_option( 'number_featured_in_archive', $per_row );
			$args['posts_per_page'] = $number_of_featured;
			$args['orderby']        = 'rand';
			if ( empty( $status ) ) {
				$status = array( 'featured' );
			} elseif ( ! empty( $status ) && is_array( $status ) && ! in_array( 'featured', $status ) ) {
				$status[] = 'featured';
			}
		}
		if ( ! empty( $status ) && is_array( $status ) ) {

			if ( empty( $args['meta_query'] ) ) {
				$args['meta_query'] = array(
					'relation' => 'AND',
					'status'   => array(
						'relation' => 'OR',
					),
				);
			}

			if ( in_array( 'featured', $status ) ) {
				$args['meta_query']['status'][] = array(
					'key'     => 'featured',
					'value'   => 'on',
					'compare' => '=',
				);
			}

			if ( in_array( 'hot', $status ) ) {
				$args['meta_query']['status'][] = array(
					'key'     => 'status',
					'value'   => 'hot',
					'compare' => '=',
				);
			}

			if ( in_array( 'new', $status ) ) {
				$args['meta_query']['status'][] = array(
					'key'     => 'status',
					'value'   => 'new',
					'compare' => '=',
				);
			}

			if ( in_array( 'special', $status ) ) {
				$args['meta_query']['status'][] = array(
					'key'     => 'status',
					'value'   => 'special',
					'compare' => '=',
				);
			}
		}

	}

	public function filter_level( &$args ) {
		if ( ! empty( $_GET['levels'] ) && is_array( $_GET['levels'] ) ) {

			if ( empty( $args['meta_query'] ) ) {
				$args['meta_query'] = array(
					'relation' => 'AND',
					'level'    => array(
						'relation' => 'OR',
					),
				);
			}

			if ( ! empty( $_GET['levels'] ) ) {
				foreach ( $_GET['levels'] as $level ) {
					$args['meta_query']['level'][] = array(
						'key'     => 'level',
						'value'   => sanitize_text_field( $level ),
						'compare' => '=',
					);
				}
			}
		}

	}

	public function filter_rating( &$args ) {
		if ( ! empty( $_GET['rating'] ) ) {

			if ( empty( $args['meta_query'] ) ) {
				$args['meta_query'] = array(
					'relation' => 'AND',
				);
			}

			$args['meta_query'][] = array(
				'key'     => 'course_mark_average',
				'value'   => floatval( $_GET['rating'] ),
				'compare' => '>=',
			);

		}
	}

	public function filter_instructor( &$args ) {
		if ( ! empty( $_GET['instructor'] ) ) {

			$authors = array();

			foreach ( $_GET['instructor'] as $instructor ) {
				$authors[] = intval( $instructor );
			}

			$args['author__in'] = $authors;

		}
	}

	public function filter_price( &$args ) {
		if ( ! empty( $_GET['price'] ) ) {

			if ( empty( $args['meta_query'] ) ) {
				$args['meta_query'] = array(
					'relation' => 'OR',
				);
			}

			if ( in_array( 'free_courses', $_GET['price'] ) && in_array( 'paid_courses', $_GET['price'] ) ) {
				$args['meta_query']['prices'][] = array(
					array(
						'relation' => 'AND',
						array(
							'key'     => 'price',
							'compare' => 'EXISTS',
						),
						array(
							'relation' => 'OR',
							array(
								'key'     => 'not_single_sale',
								'value'   => 'on',
								'compare' => '!=',
							),
							array(
								'key'     => 'not_single_sale',
								'compare' => 'NOT EXISTS',
							),
						),
					),
				);
			} else {
				if ( in_array( 'free_courses', $_GET['price'] ) ) {
					$args['meta_query']['free_price'][] = array(
						array(
							'relation' => 'AND',
							array(
								'relation' => 'OR',
								array(
									'key'     => 'price',
									'value'   => array( 0, '' ),
									'compare' => 'in',
								),
								array(
									'key'     => 'price',
									'compare' => 'NOT EXISTS',
								),
							),
							array(
								'relation' => 'OR',
								array(
									'key'     => 'not_single_sale',
									'value'   => 'on',
									'compare' => '!=',
								),
								array(
									'key'     => 'not_single_sale',
									'compare' => 'NOT EXISTS',
								),
							),
						),
					);
				}

				if ( in_array( 'paid_courses', $_GET['price'] ) ) {
					$args['meta_query']['paid_price'][] = array(
						array(
							'relation' => 'AND',
							array(
								'key'     => 'price',
								'value'   => 0,
								'compare' => '>',
							),
							array(
								'relation' => 'OR',
								array(
									'key'     => 'not_single_sale',
									'value'   => 'on',
									'compare' => '!=',
								),
								array(
									'key'     => 'not_single_sale',
									'compare' => 'NOT EXISTS',
								),
							),
						),
					);
				}
			}

			if ( in_array( 'subscription', $_GET['price'] ) ) {
				$args['meta_query']['subscription'][] = array(
					array(
						'key'     => 'not_single_sale',
						'value'   => 'on',
						'compare' => '=',
					),
				);
			}
		}
	}
}
