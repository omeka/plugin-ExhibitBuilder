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
    const VIEW_STEM = 'exhibit_layouts';
    
    public $id;
    public $name;
    public $description;

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
    
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function setMetadata($data)
    {
        if (array_key_exists('name', $data)) {
            $this->name = $data['name'];
        }

        if (array_key_exists('description', $data)) {
            $this->description = $data['description'];
        }
    }

    public function getViewPartial($type = 'layout')
    {
        return self::VIEW_STEM . '/' . $this->id . '/' . $type . '.php';
    }

    public function getIconUrl()
    {
        return web_path_to(self::VIEW_STEM . '/' . $this->id . '/layout.gif');
    }

    public static function getLayoutArray()
    {
        return apply_filters('exhibit_layouts', self::$defaultLayouts);
    }
    
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
