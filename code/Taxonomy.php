<?php
namespace WPOrbit\Taxonomies;

/**
 * Provides an extensible class for registering custom taxonomies to post type(s).
 *
 * Class Taxonomy
 * @package WPOrbit\Taxonomies
 */
class Taxonomy
{
    /**
     * @var string The taxonomy key.
     */
    protected $key;

    /**
     * @var string The taxonomy slug.
     */
    protected $slug;

    /**
     * @var string Singular taxonomy label.
     */
    protected $singular;

    /**
     * @var string Plural taxonomy label.
     */
    protected $plural;

    /**
     * @var string Menu name label.
     */
    protected $menuName;

    /**
     * @var bool Is hierarchical (like categories) or false like tags.
     */
    protected $hierarchical = false;

    /**
     * @var bool
     */
    protected $showUi = true;

    /**
     * @var bool
     */
    protected $showAdminColumn = true;

    /**
     * @var bool
     */
    protected $queryVar = true;

    /**
     * @var array An array of post types to inject this taxonomy.
     */
    protected $postTypes = [];

    /**
     * The taxonomy key.
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get the taxonomy key -- alias of getKey().
     * @return string
     */
    public function getTaxonomy()
    {
        return $this->getKey();
    }

    /**
     * The taxonomy slug.
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * The taxonomy singular label.
     * @return string
     */
    public function getSingular()
    {
        return $this->singular;
    }

    /**
     * The taxonomy plural label.
     * @return string
     */
    public function getPlural()
    {
        return $this->plural;
    }

    protected function validateTaxonomy()
    {
        // Class name.
        $className = static::class;

        // Required keys.
        $keys = [
            'key', 'slug', 'singular', 'plural'
        ];

        // Loop through required keys.
        foreach( $keys as $key )
        {
            // Verify that the current iteration is specified.
            if ( null === $this->{$key} )
            {
                throw new \Exception("No \${$key} specified in class {$className}.");
            }
        }
    }

    /**
     * @return array Taxonomy labels.
     */
    protected function getLabels()
    {
        // Return hierarchical labels.
        if ( $this->hierarchical )
        {
            return [
                'name'              => _x( $this->menuName ?: $this->plural, 'taxonomy general name' ),
                'singular_name'     => _x( $this->singular, 'taxonomy singular name' ),
                'search_items'      => __( 'Search ' . $this->plural ),
                'all_items'         => __( 'All ' . $this->plural ),
                'parent_item'       => __( 'Parent ' . $this->plural ),
                'parent_item_colon' => __( 'Parent ' . $this->plural . ':' ),
                'edit_item'         => __( 'Edit ' . $this->singular ),
                'update_item'       => __( 'Update ' . $this->singular ),
                'add_new_item'      => __( 'Add New ' . $this->singular ),
                'new_item_name'     => __( 'New ' . $this->singular . ' Name' ),
                'menu_name'         => __( $this->menuName ?: $this->plural ),
            ];
        }

        // Return non-hierarchical labels.
        return [
            'name'                       => _x( $this->menuName ?: $this->plural, 'taxonomy general name' ),
            'singular_name'              => _x( $this->singular, 'taxonomy singular name' ),
            'search_items'               => __( 'Search ' . $this->plural ),
            'popular_items'              => __( 'Popular ' . $this->plural ),
            'all_items'                  => __( 'All ' . $this->plural ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit ' . $this->singular ),
            'update_item'                => __( 'Update ' . $this->singular ),
            'add_new_item'               => __( 'Add New ' . $this->singular ),
            'new_item_name'              => __( 'New ' . $this->singular . ' Name' ),
            'separate_items_with_commas' => __( 'Separate ' . $this->plural . ' with commas' ),
            'add_or_remove_items'        => __( 'Add or remove ' . $this->plural),
            'choose_from_most_used'      => __( 'Choose from the most used ' . $this->plural ),
            'not_found'                  => __( 'No ' . strtolower($this->plural) . ' found.' ),
            'menu_name'                  => __( $this->menuName ?: $this->plural ),
        ];
    }

    /**
     * Hook WordPress.
     */
    public function registerTaxonomy()
    {
        add_action( 'init', function()
        {
            // Validate taxonomy configuration.
            $this->validateTaxonomy();

            // Define arguments.
            $args = [
                'hierarchical' => $this->hierarchical,
                'labels' => $this->getLabels(),
                'show_ui' => $this->showUi,
                'show_admin_column' => $this->showAdminColumn,
                'query_var' => $this->queryVar,
                'rewrite' => ['slug' => $this->slug],
            ];

            // Register the taxonomy.
            register_taxonomy($this->key, $this->postTypes, $args);
        });
    }

    /**
     * @var static
     */
    protected static $instance;

    /**
     * @return static
     */
    public static function getInstance()
    {
        return new static;
    }
}