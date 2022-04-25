<?php
/**
 * Elasticsearch Extensions Adapters: VIP Enterprise Search Adapter
 *
 * @package Elasticsearch_Extensions
 */

namespace Elasticsearch_Extensions\Adapters;

use Elasticsearch_Extensions\Facets\Category;
use Elasticsearch_Extensions\Facets\Post_Type;
use Elasticsearch_Extensions\Facets\Tag;

/**
 * An adapter for WordPress VIP Enterprise Search.
 *
 * @package Elasticsearch_Extensions
 */
class VIP_Enterprise_Search extends Adapter {

	/**
	 * Filters ElasticPress request query args to apply registered customizations.
	 *
	 * @param array  $request_args Request arguments.
	 * @param string $path         Request path.
	 * @param string $index        Index name.
	 * @param string $type         Index type.
	 *
	 * @return array New request arguments.
	 */
	public function filter_ep_query_request_args( $request_args, $path, $index, $type ): array {
		// Try to convert the request body to an array, so we can work with it.
		$dsl = json_decode( $request_args['body'], true );
		if ( ! is_array( $dsl ) ) {
			return $request_args;
		}

		// Add our aggregations.
		if ( $this->get_aggregate_post_types() ) {
			$post_type_facet = new Post_Type();
			$dsl['aggs']     = array_merge( $dsl['aggs'], $post_type_facet->request() );
		}

		if ( $this->get_aggregate_categories() ) {
			$category_facet = new Category();
			$dsl['aggs']    = array_merge( $dsl['aggs'], $category_facet->request() );
		}

		if ( $this->get_aggregate_tags() ) {
			$tag_facet   = new Tag();
			$dsl['aggs'] = array_merge( $dsl['aggs'], $tag_facet->request() );
		}

		$agg_taxonomies = $this->get_aggregate_taxonomies();
		if ( ! empty( $agg_taxonomies ) ) {
			foreach ( $agg_taxonomies as $agg_taxonomy ) {
				$dsl['aggs'][ "taxonomy_{$agg_taxonomy}" ] = [
					'terms' => [
						'size'  => 1000,
						'field' => "terms.{$agg_taxonomy}.slug",
					],
				];
			}
		}

		// Re-encode the body into the request args.
		$request_args['body'] = wp_json_encode( $dsl );

		return $request_args;
	}

	/**
	 * Sets the field map for this adapter.
	 *
	 * @return void
	 */
	public function set_field_map(): void {
		$this->field_map['post_author']                   = 'post_author.id';
		$this->field_map['post_author.user_nicename']     = 'post_author.login.raw';
		$this->field_map['post_date']                     = 'post_date';
		$this->field_map['post_date.year']                = 'date_terms.year';
		$this->field_map['post_date.month']               = 'date_terms.month';
		$this->field_map['post_date.week']                = 'date_terms.week';
		$this->field_map['post_date.day']                 = 'date_terms.day';
		$this->field_map['post_date.day_of_year']         = 'date_terms.dayofyear';
		$this->field_map['post_date.day_of_week']         = 'date_terms.dayofweek';
		$this->field_map['post_date.hour']                = 'date_terms.hour';
		$this->field_map['post_date.minute']              = 'date_terms.minute';
		$this->field_map['post_date.second']              = 'date_terms.second';
		$this->field_map['post_date_gmt']                 = 'post_date_gmt';
		$this->field_map['post_date_gmt.year']            = 'date_gmt_terms.year';
		$this->field_map['post_date_gmt.month']           = 'date_gmt_terms.month';
		$this->field_map['post_date_gmt.week']            = 'date_gmt_terms.week';
		$this->field_map['post_date_gmt.day']             = 'date_gmt_terms.day';
		$this->field_map['post_date_gmt.day_of_year']     = 'date_gmt_terms.day_of_year';
		$this->field_map['post_date_gmt.day_of_week']     = 'date_gmt_terms.day_of_week';
		$this->field_map['post_date_gmt.hour']            = 'date_gmt_terms.hour';
		$this->field_map['post_date_gmt.minute']          = 'date_gmt_terms.minute';
		$this->field_map['post_date_gmt.second']          = 'date_gmt_terms.second';
		$this->field_map['post_content']                  = 'post_content';
		$this->field_map['post_content.analyzed']         = 'post_content';
		$this->field_map['post_title']                    = 'post_title.raw';
		$this->field_map['post_title.analyzed']           = 'post_title';
		$this->field_map['post_type']                     = 'post_type.raw';
		$this->field_map['post_excerpt']                  = 'post_excerpt';
		$this->field_map['post_password']                 = 'post_password';  // This isn't indexed on VIP.
		$this->field_map['post_name']                     = 'post_name.raw';
		$this->field_map['post_modified']                 = 'post_modified';
		$this->field_map['post_modified.year']            = 'modified_date_terms.year';
		$this->field_map['post_modified.month']           = 'modified_date_terms.month';
		$this->field_map['post_modified.week']            = 'modified_date_terms.week';
		$this->field_map['post_modified.day']             = 'modified_date_terms.day';
		$this->field_map['post_modified.day_of_year']     = 'modified_date_terms.day_of_year';
		$this->field_map['post_modified.day_of_week']     = 'modified_date_terms.day_of_week';
		$this->field_map['post_modified.hour']            = 'modified_date_terms.hour';
		$this->field_map['post_modified.minute']          = 'modified_date_terms.minute';
		$this->field_map['post_modified.second']          = 'modified_date_terms.second';
		$this->field_map['post_modified_gmt']             = 'post_modified_gmt';
		$this->field_map['post_modified_gmt.year']        = 'modified_date_gmt_terms.year';
		$this->field_map['post_modified_gmt.month']       = 'modified_date_gmt_terms.month';
		$this->field_map['post_modified_gmt.week']        = 'modified_date_gmt_terms.week';
		$this->field_map['post_modified_gmt.day']         = 'modified_date_gmt_terms.day';
		$this->field_map['post_modified_gmt.day_of_year'] = 'modified_date_gmt_terms.day_of_year';
		$this->field_map['post_modified_gmt.day_of_week'] = 'modified_date_gmt_terms.day_of_week';
		$this->field_map['post_modified_gmt.hour']        = 'modified_date_gmt_terms.hour';
		$this->field_map['post_modified_gmt.minute']      = 'modified_date_gmt_terms.minute';
		$this->field_map['post_modified_gmt.second']      = 'modified_date_gmt_terms.second';
		$this->field_map['post_parent']                   = 'post_parent';
		$this->field_map['menu_order']                    = 'menu_order';
		$this->field_map['post_mime_type']                = 'post_mime_type';
		$this->field_map['comment_count']                 = 'comment_count';
		$this->field_map['post_meta']                     = 'meta.%s.value.sortable';
		$this->field_map['post_meta.analyzed']            = 'meta.%s.value';
		$this->field_map['post_meta.long']                = 'meta.%s.long';
		$this->field_map['post_meta.double']              = 'meta.%s.double';
		$this->field_map['post_meta.binary']              = 'meta.%s.boolean';
		$this->field_map['term_id']                       = 'terms.%s.term_id';
		$this->field_map['term_slug']                     = 'terms.%s.slug';
		$this->field_map['term_name']                     = 'terms.%s.name.sortable';
		$this->field_map['category_id']                   = 'terms.category.term_id';
		$this->field_map['category_slug']                 = 'terms.category.slug';
		$this->field_map['category_name']                 = 'terms.category.name.sortable';
		$this->field_map['tag_id']                        = 'terms.post_tag.term_id';
		$this->field_map['tag_slug']                      = 'terms.post_tag.slug';
		$this->field_map['tag_name']                      = 'terms.post_tag.name.sortable';
	}

	/**
	 * Setup function. Registers action and filter hooks.
	 */
	public function setup(): void {
		// Set field mappings.
		$this->set_field_map();

		// Filter request args.
		add_filter( 'ep_query_request_args', [ $this, 'filter_ep_query_request_args' ], 10, 4 );

		// Set Results and aggregations.
		add_action( 'ep_valid_response', [ $this, 'set_results' ], 10, 1 );

		// Parse face data.
		add_action( 'ep_valid_response', [ $this, 'parse_facets' ], 11, 0 );
	}

	/**
	 * Set results from last query.
	 *
	 * @param array $response Response from ES.
	 * @return void
	 */
	public function set_results( $response ) {
		// Set aggregations if applicable.
		if ( ! empty( $response['aggregations'] ) ) {
			$this->set_aggregations( $response['aggregations'] );
		}

		// TODO ensure this is a search and this isn't too broad.
		if ( apply_filters( 'elasticsearch_extensions_should_set_results', true ) ) {
			$this->results = $response;
		}
	}
}
