<?php
namespace WPOrbit\Taxonomies\Pivoter;

/**
 * Class TaxonomyPivoter
 *
 * @package WPOrbit\Taxonomies
 */
class TaxonomyPivoter
{
    /**
     * @var int The parent ID post from which we orient relationships.
     */
    protected $parentId;

    /**
     * @var string
     */
    protected $taxonomy;

    /**
     * @var bool
     */
    protected $inversePivot = false;

    /**
     * @var string
     */
    protected $inversePostType;

    /**
     * @var string The type of pivot context-- 'post' or 'user'.
     */
    protected $pivotContext = 'post';

    /**
     * @var string
     */
    protected $postType = 'post';

    /**
     * Establish post relationships.
     * @return $this
     */
    public function pivotPosts( $postType = 'post' )
    {
        $this->pivotContext = 'post';
        $this->postType = $postType;
        return $this;
    }

    /**
     * Establish user relationships.
     * @return $this
     */
    public function pivotUsers()
    {
        $this->pivotContext = 'user';
        return $this;
    }

    /**
     * @param string $postType
     */
    public function inversePivot( $postType ) {
        $this->inversePivot = true;
        $this->inversePostType = $postType;
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasRelation( $id )
    {
        if ( $this->inversePivot ) {
            return has_term( $this->parentId, $this->taxonomy, (string) $id );
        } else {
            return has_term( (string) $id, $this->taxonomy, $this->parentId );
        }
    }

    /**
     * Establish a relationship.
     * @param $id
     * @return $this
     */
    public function addRelation( $id )
    {
        if ( $this->hasRelation( $id ) )
        {
            return $this;
        }

        if ( $this->inversePivot ) {
            $parentId = (string) $id;
            $childId = $this->parentId;
        } else {
            $parentId = $this->parentId;
            $childId = (string) $id;
        }

        // Get the term we want to add.
        $term = get_term_by( 'name', $childId, $this->taxonomy );

        // Invalid term.
        if ( ! $term )
        {
            // Create the term.
            $term = wp_insert_term(
                $childId,
                $this->taxonomy
            );

            // Get term ID.
            $termId = $term['term_id'];
        }

        else {
            $termId = $term->term_id;
        }

        // Add the term ID.
        wp_add_object_terms( $parentId, $termId, $this->taxonomy );

        return $this;
    }

    /**
     * Remove a relationship.
     * @param $id
     * @return $this
     */
    public function removeRelation( $id )
    {
        if ( ! $this->hasRelation( $id ) )
        {
            return $this;
        }

        if ( $this->inversePivot )
        {
            $parentId = (string) $id;
            $childId = $this->parentId;
        } else {
            $parentId = $this->parentId;
            $childId = (string) $id;
        }

        // Get the term we want to add.
        $term = get_term_by( 'name', $childId, $this->taxonomy );

        if ( ! $term ) {
            return $this;
        }

        wp_remove_object_terms( $parentId, $term->term_id, $this->taxonomy );

        return $this;
    }

    /**
     * Returns a list of related IDs.
     * @return array
     */
    public function getIds()
    {
        // Array for output.
        $relatedIds = [];

        // Get direct relationship IDs.
        if ( ! $this->inversePivot )
        {
            // Get terms.
            $terms = wp_get_object_terms( $this->parentId, $this->taxonomy );

            // Error fetching terms.
            if ( is_wp_error( $terms ) )
            {
                return [];
            }

            // Extract IDs stored as term names.
            foreach ( $terms as $term )
            {
                /** @var $term \WP_Term */
                $relatedIds[] = (int) $term->name;
            }
        }

        // Get inverse relationship IDs.
        else
        {
            $posts = static::getPivotedParentPosts(
                $this->parentId,
                $this->taxonomy,
                $this->postType
            );

            foreach( $posts as $post ) {
                $relatedIds[] = $post->ID;
            }
        }

        // Return array.
        return $relatedIds;
    }

    /**
     * TaxonomyPivoter constructor.
     *
     * @param $postId int Parent post ID.
     * @param $taxonomy string The taxonomy name through which relationships are established.
     * @param $context string The data type of the pivoted IDs ('post' or 'user').
     */
    public function __construct( $postId, $taxonomy, $context = 'post' )
    {
        $this->parentId = $postId;
        $this->taxonomy = $taxonomy;

        if ( 'post' == $context ) {
            $this->pivotPosts();
        }

        if ( 'user' == $context ) {
            $this->pivotUsers();
        }
    }

    /**
     * Returns posts whose relationship exists through the presence of taxonomy terms.
     * @param $postId int The post ID whose related posts we want.
     * @param $taxonomy string The taxonomy through which relationships are established
     * @param $postType string The post type of the related posts.
     * @return \WP_Post[]
     */
    public static function getPivotedParentPosts( $postId, $taxonomy, $postType )
    {
        $args = [
            'post_type' => $postType,
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'name',
                    'terms'    => (string) $postId
                ]
            ],
            'orderby'   => 'title',
            'order'     => 'ASC',
            'nopaging'  => true
        ];

        // Provide a filter for fetching pivotable posts.
        $args = apply_filters( 'get_pivoted_post_args', $args, $postId, $taxonomy, $postType );

        // Get WP Query.
        $query = new \WP_Query( $args );

        // No people.
        if ( empty( $query->posts ) )
        {
            return [];
        }

        return $query->posts;
    }

    /**
     * Returns an array of child pivoted posts.
     * @param $postId
     * @param $taxonomy
     * @param $postType
     * @return \WP_Post[]
     */
    public static function getPivotedPosts( $postId, $taxonomy, $postType )
    {
        $pivoter = new TaxonomyPivoter( $postId, $taxonomy );

        // Get IDs.
        $postIds = $pivoter->getIds();

        if ( empty( $postIds ) ) {
            return [];
        }

        $args = [
            'post_type' => $postType,
            'post__in'  => $postIds,
            'orderby'   => 'title',
            'nopaging'  => true
        ];

        // Provide a filter for fetching pivotable posts.
        $args = apply_filters( 'get_pivoted_direct_post_args', $args, $postId, $taxonomy, $postType );

        // Prepare WordPress query.
        $query = new \WP_Query( $args );

        // No people.
        if ( empty( $query->get_posts() ) )
        {
            return [];
        }



        return $query->posts;
    }

}