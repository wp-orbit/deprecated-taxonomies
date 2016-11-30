<?php
namespace Zawntech\WordPress\Orbit\Taxonomy\FeaturedImage;

/**
 * Adds a "featured image" to taxonomy terms.
 * Class TaxonomyFeaturedImage
 * @package Zawntech\WordPress\Orbit\Taxonomy
 */
class TaxonomyFeaturedImage
{
    /**
     * @var TaxonomyFeaturedImage
     */
    protected static $instance;

    /**
     * TaxonomyFeaturedImage constructor.
     */
    protected function __construct()
    {
        // Defer initialization to 'init' hook.
        add_action('init', function () {
            $this->addFormFields();
            $this->saveFormFields();
        });
    }

    /**
     * @var TaxonomyFeaturedImageBinding[]
     */
    protected $bindings = [];

    protected function _script()
    {
        ?>
        <script>
            (function($) {

                $(document).ready(function() {

                    // WP Media Frame.
                    var imageContainer = $('#image-container'),
                        imageIdInput = $('#taxonomy_featured_image'),
                        uploadButton = $('#select_taxonomy_image'),
                        frame = new wp.media.view.MediaFrame.Select({
                            title: 'Select taxonomy image',
                            multiple: false,
                            library: {
                                order: 'ASC',
                                orderby: 'title',
                                type: 'image'
                            },
                            button: {
                                text: 'Set taxonomy image'
                            }
                        });

                    // Initialize frame state.
                    frame.state();
                    frame.lastState();

                    frame.on( 'select', function() {
                        var selectionCollection = frame.state().get('selection'),
                            models = selectionCollection.models,
                            image = models[0],
                            imageId = image.attributes.id,
                            imageUrl = 'undefined' == typeof( image.attributes.sizes.medium ) ?
                                image.attributes.sizes.full.url : image.attributes.sizes.medium.url;

                        imageContainer.html('<img src="' + imageUrl + '">');
                        imageIdInput.val(imageId);
                        imageIdInput.attr('value', imageId);
                    });

                    uploadButton.on( 'click', function() {
                        frame.open();
                    });
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * HTML for add taxonomy pages.
     */
    protected function _addFormFields()
    {
        wp_enqueue_media();
        ?>
        <div class="form-field">
            <label for="taxonomy_featured_image"><?php _e( 'Taxonomy Featured Image:', 'wp-orbit' ); ?></label>
            <input type="hidden" name="taxonomy_featured_image" id="taxonomy_featured_image" value="">
            <div id="image-container"></div>
            <input class="button button-primary" id="select_taxonomy_image" type="button" value="Select/Upload Image" />
        </div>
        <?php
        $this->_script();
    }

    /**
     * HTML for edit taxonomy pages.
     */
    protected function _editFormFields( $term )
    {
        // Enqueue the media.
        wp_enqueue_media();

        $imageId = get_term_meta( $term->term_id, '_featured_image_id', true );
        $imageUrl = wp_get_attachment_image_url( $imageId, 'medium' );
        ?>

        <tr class="form-field term-slug-wrap">
            <th scope="row"><label for="slug">Featured Image</label></th>
            <td>
                <input type="hidden" name="taxonomy_featured_image" id="taxonomy_featured_image" value="">
                <div id="image-container">
                    <?php if ( $imageId ) : ?>
                        <img src="<?php echo $imageUrl; ?>">
                    <?php endif; ?>
                </div>
                <input class="button button-primary" id="select_taxonomy_image" type="button" value="Select/Upload Image" />
            </td>
        </tr>
        <?php
        $this->_script();
    }

    /**
     * @param $termId
     */
    protected function _saveMeta( $termId )
    {
        if ( isset( $_POST['taxonomy_featured_image'] ) )
        {
            // Reference the image ID.
            $imageId = $_POST['taxonomy_featured_image'];

            // Set term meta.
            update_term_meta( $termId, '_featured_image_id', $imageId );
        }
    }

    protected function addFormFields()
    {
        // Loop through bindings.
        foreach( $this->bindings as $binding )
        {
            // Hook new taxonomy menu page.
            add_action( "{$binding->taxonomy}_add_form_fields", function() {
                $this->_addFormFields();
            });

            // Hook edit taxonomy menu page.
            add_action( "{$binding->taxonomy}_edit_form_fields", function( $term ) {
                $this->_editFormFields( $term );
            });
        }
    }

    protected function saveFormFields()
    {
        // Loop through bindings.
        foreach( $this->bindings as $binding )
        {
            add_action( "create_{$binding->taxonomy}", function( $termId ) {
                $this->_saveMeta( $termId );
            });
            add_action( "edited_{$binding->taxonomy}", function( $termId ) {
                $this->_saveMeta( $termId );
            });
        }
    }

    /**
     * @param string $taxonomy
     * @return TaxonomyFeaturedImageBinding[]
     */
    public function getBindingsByTaxonomy( $taxonomy )
    {
        // Declare an array of bindings to return if they match the supplied filter.
        $bindings = [];

        // Loop through bindings.
        foreach( $this->bindings as $binding )
        {
            // Match the taxonomy name against
            if ( $binding->taxonomy == $taxonomy )
            {
                // Push this binding.
                $bindings[] = $bindings;
            }
        }

        return $bindings;
    }

    /**
     * @return TaxonomyFeaturedImage
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Binds featured image support for a given taxonomy (and post types, if supplied).
     * @param string $taxonomy The taxonomy key we want to add featured images to.
     * @param array $postTypes Specifies which specific post types should per taxonomy should
     * get featured image support. If left empty, will support all post types.
     */
    public static function bind( $taxonomy, array $postTypes = [] )
    {
        $self = static::getInstance();

        // Get bindings by taxonomy.
        $bindings = $self->getBindingsByTaxonomy( $taxonomy );

        // No bindings found.
        if ( empty ( $bindings ) )
        {
            // Create the binding.
            $binding = new TaxonomyFeaturedImageBinding( $taxonomy, $postTypes );

            // Push to object.
            $self->bindings[] = $binding;

            // We're done.
            return;
        }

        // Loop through bindings.
        foreach( $bindings as &$binding )
        {
            // This binding matches our taxonomy term.
            if ( $taxonomy == $binding->taxonomy )
            {
                // Loop through current binding's post types.
                foreach( $postTypes as $postType )
                {
                    // Add this post type to the binding.
                    if ( ! in_array( $postType, $binding->postTypes ) )
                    {
                        $binding->postTypes[] = $postType;
                    }
                }
            }
        }
    }

    /**
     * @param string $taxonomy The taxonomy key we want to remove featured images from.
     * @param array $postTypes
     */
    public static function unbind( $taxonomy, array $postTypes = [] )
    {
        $self = static::getInstance();

        // Loop through bindings.
        foreach( $self->bindings as $key => $binding )
        {
            // Match the taxonomy.
            if ( $taxonomy === $binding->taxonomy )
            {
                // No post types were supplied, so remove any matching instances of the taxonomy.
                if ( empty( $postTypes ) )
                {
                    // Unset the item.
                    unset( $self->bindings[$key] );
                }

                // We need to check against post types in addition to the taxonomy key.
                else
                {
                    // Loop through post types.
                    foreach( $postTypes as $postType )
                    {
                        // Is the post type in the binding?
                        if ( in_array( $postType, $binding->postTypes ) )
                        {
                            // Unset the item.
                            unset( $self->bindings[$key] );
                        }
                    }
                }
            }
        }
    }
}