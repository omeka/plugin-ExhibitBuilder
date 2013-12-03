<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2012
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package ExhibitBuilder
 */
 
/**
 * Exhibit layout model.
 *
 * @package ExhibitBuilder
 */
class ExhibitLayout
{
    /**
     * The folder name that contains all exhibit views.
     */
    const VIEW_STEM = 'exhibit_layouts';

    /**
     * The ID for the layout to use when one can't be found.
     */
    const FALLBACK_LAYOUT = 'file-text';

    /**
     * Name of the image file to use when one can't be found.
     */
    const FALLBACK_LAYOUT_IMG = 'fallback_layout.png';

    /**
     * The internal name for the layout.
     *
     * The internal name is used to store the layout being used, as well as
     * to look up the layout view files.
     *
     * @var string
     */
    public $id;

    /**
     * The display name for the layout.
     *
     * @var string
     */
    public $name;

    /**
     * The display description of the layout.
     *
     * @var string
     */
    public $description;

    /**
     * The filtered layout data.
     *
     * @see self::getLayoutArray()
     * @var array
     */
    public static $layouts;

    /**
     * Create the layout.
     *
     * This only sets the internal ID.
     *
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Set other metadata for the layout.
     *
     * @param array $data Key-value array of layout data.
     */
    public function setMetadata($data)
    {
        if (array_key_exists('name', $data)) {
            $this->name = $data['name'];
        }

        if (array_key_exists('description', $data)) {
            $this->description = $data['description'];
        }
    }

    /**
     * Get the spec for a PHP view partial for the layout.
     *
     * The default partial type is the front-side display of the layout.
     *
     * @param string $type Partial name.
     * @return string
     */
    public function getViewPartial($type = 'layout')
    {
        return self::VIEW_STEM . '/' . $this->id . '/' . $type . '.php';
    }

    /**
     * Get a web URL to a layout asset.
     *
     * @param string $name Asset filename.
     * @return string
     */
    public function getAssetUrl($name)
    {
        return web_path_to(self::VIEW_STEM . '/' . $this->id . '/' . $name);
    }

    /**
     * Get the URL for this layout's icon.
     *
     * @return string
     */
    public function getIconUrl()
    {
        try {
            return $this->getAssetUrl('layout.png');
        } catch (InvalidArgumentException $e) {
            return img(self::FALLBACK_LAYOUT_IMG);
        }
    }

    /**
     * Get the array of layout data.
     *
     * Plugins can filter this data with the exhibit_layouts filter.
     *
     * @return array
     */
    public static function getLayoutArray()
    {
        if (self::$layouts) {
            return self::$layouts;
        }
        
        $defaultLayouts = array(
            'file-text' => array(
                'name' => __('File with Text'),
                'description' => __('Default layout features files justified to left or right with text displaying to the opposite side')
            ),
            'gallery' => array(
                'name' => __('Gallery'),
                'description' => __('A gallery layout featuring file thumbnails')
            ),
            'text' => array(
                'name' => __('Text'),
                'description' => __('Layout featuring a block of text without files')
            )
        );

        $layouts = apply_filters('exhibit_layouts', $defaultLayouts);
        self::$layouts = $layouts;
        return $layouts;
    }

    /**
     * Get all the available layouts
     *
     * @return ExhibitLayout[]
     */
    public static function getLayouts()
    {
        $layouts = array();
        foreach (self::getLayoutArray() as $id => $data) {
            $layout = new ExhibitLayout($id);
            $layout->setMetadata($data);
            $layouts[] = $layout;
        }
        return $layouts;
    }

    /**
     * Get a specific layout by ID.
     *
     * @param string $id Layout internal ID
     * @param boolean $fallback Whether to return the fallback layout if the
     *  given ID isn't recognized. If false, null will be returned for an
     *  invalid layout ID.
     * @return ExhibitLayout|null
     */
    public static function getLayout($id, $fallback = true)
    {
        $layouts = self::getLayoutArray();
        if (!isset($layouts[$id])) {
            if ($fallback) {
                $originalId = $id;
                $id = self::FALLBACK_LAYOUT;
            } else {
                return null;
            }
        }

        $layout = new ExhibitLayout($id);
        $layout->setMetadata($layouts[$id]);
        return $layout;
    }
}
