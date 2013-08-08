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
     * The default layout data.
     *
     * New layouts can be added through filtering, with this same structure.
     *
     * @var array
     */
    public static $defaultLayouts = array(
        'file-text' => array(
            'name' => 'File with Text',
            'description' => 'The standard layout'
        ),
        'gallery' => array(
            'name' => 'Gallery',
            'description' => 'A gallery'
        ),
        'text' => array(
            'name' => 'Text',
            'description' => 'A plain block of text'
        )
    );

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
        return $this->getAssetUrl('layout.gif');
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
        return apply_filters('exhibit_layouts', self::$defaultLayouts);
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
     * @return ExhibitLayout
     */
    public static function getLayout($id)
    {
        $layouts = self::getLayoutArray();
        if (isset($layouts[$id])) {
            $layout = new ExhibitLayout($id);
            $layout->setMetadata($layouts[$id]);
            return $layout;
        } else {
            return null;
        }
    }
}
