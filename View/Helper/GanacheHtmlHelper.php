<?php
/**
 * CakeGanache : (https://github.com/gezeresolutionsweb/cakeganache)
 * Copyright (c) Sylvain Lévesque (http://www.gezere.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Sylvain Lévesque (http://www.gezere.com)
 * @link          http://www.gezere.com
 * @package       CakeGanache.View.Helper
 * @since         CakeGanache v 0.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::import('Helper', 'Html') ;

class GanacheHtmlHelper extends HtmlHelper {
    /**
     * Library icon prefix use by icon().
     */
    protected $prefix = 'glyphicon';

    public function __construct(View $View, $settings = array()) {
        if( isset( $settings[ 'prefix' ] ) ) {
            $this->prefix = $settings[ 'prefix' ];
            unset( $settings[ 'prefix' ] );
        }

        parent::__construct($View, $settings);
    }


    private function _extractOption ($key, $options, $default = null) {
        if (isset($options[$key])) {
            return $options[$key] ;
        }
        return $default ;
    }

    /**
     * 
     * Check type values in $options, returning null if no option is found or if
     * option found is equal to $default or if otion is not in $avail.
     * 
     **/
    private function _extractType ($options, $key = 'type', $default = GA_INFO, $avail = array(GA_INFO, GA_SUCCESS, GA_WARNING, GA_ERROR)) {
            $type = $this->_extractOption($key, $options, $default) ;
            if ($type == $default) {
                return null ;
            }
            if (!in_array($type, $avail)) {
                return null ;
            }
            return $type ;
        }

    /**
     * Create an icon.
     * Compatible for Bootstrap 2.3.2 and 3.X glyphicons and FontAwesome by changin prefix for "fa"
     *
     * @access public
     * @author Sylvain Lévesque <slevesque@gezere.com>
     * @param string $icon label of the icon
     * @param array $options Options array.
     *
     * Extra options
     *   - ga_prefix : Library prefix. (default: "glyphicon")
     *   - ga_size   : Icon size. Add size to html class attribute. (default: GA_1X)
     *      values:
     *          GA_FW : Fixed with
     *          GA_LG : Large icon
     *          GA_1X : 1x size
     *          GA_2X : 2x size
     *          GA_3X : 3x size
     *          GA_4X : 4x size
     *          GA_5X : 5x size
     *   - ga_spin   : If this icon must spin. Add GA_SPIN to html class attribute. true|false (default: false)
     *
     * @return string Html element string.
     * @todo Try to implement stack icon in there.
     */
    public function icon($icon, $options = [])
    {
        $defaults = [
            'ga_prefix' => $this->prefix,
            'class' => [$this->prefix]
            ];


        // If class attribute is define we must force it as array.
        if(isset($options['class'])) {
            $classes = [];
            if(is_string($options['class'])) {
                $classes = explode(' ', $options['class']);
            } elseif(is_array($options['class'])) {
                $classes = $options['class'];
            }
            unset($options['class']);
        }

        $options = array_merge($defaults, $options);

        array_push($options['class'], $options['ga_prefix'] . '-' . $icon);

        // Merging regular classes.
        if(isset($classes)) {
            $options['class'] = array_merge($options['class'], $classes);
        }

        // If ga_size is defined
        if(isset($options['ga_size'])) {
            array_push($options['class'], $options['ga_prefix'] . '-' . $options['ga_size']);
            unset($options['ga_size']);
        }

        // If ga_spin is true
        if(isset($options['ga_spin']) && $options['ga_spin'] === true) {
            array_push($options['class'], $options['ga_prefix'] . '-' . GA_SPIN);
            unset($options['ga_spin']);
        }

        // Clean options before passing it to standard HtmlHelper::tag() function.
        unset($options['ga_prefix']);

        return $this->tag('i', '', $options);
    }

    /**
     * Create a bootstrap link button.
     *
     * @access public
     * @author Sylvain Lévesque <slevesque@gezere.com>
     * @param string $title The content to be wrapped by <a> tags.
     * @param string|array $url Cake-relative URL or array of URL parameters, or external URL (starts with http://)
     * @param array $options Array of options and HTML attributes.
     * @return string An `<a />` element or bootstraped button link.
     * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::link
     *
     * Extra options
     * - ga_type         : string GA_DEFAULT|GA_PRIMARY|GA_INFO|GA_SUCCESS|GA_WARNING|GA_DANGER|GA_INVERSE|GA_LINK (default: GA_DEFAULT)
     * - ga_size         : string GA_MINI|GA_SMALL|GA_LARGE|GA_BLOCK
     * - ga_icon         : string GanacheHtml::icon() icon string result.
     * - ga_icon_options : array|string GanacheHtml::icon() icon options.
     *
     * @todo Escape title from to avoid html tags.
     * @todo Add ga_icon_after option to put icon after the label. There is one case.
     */
    public function linkButton($title, $url = NULL, $options = [])
    {
        $defaults = array(
            'ga_type' => GA_DEFAULT,
            'class' => [GA_BTN]
        );

        // If class attribute is define we must force it as array.
        if(isset($options['class'])) {
            $classes = [];
            if (is_string($options['class'])) {
                $classes = explode(' ', $options['class']);
            } elseif (is_array($options['class'])) {
                $classes = $options['class'];
            }
            unset($options['class']);
        }

        $options = array_merge($defaults, $options);

        // Is title attribute is not define, define it by default.
        if(!isset($options['title'])) {
            $options['title'] = $title;
        }

        array_push($options['class'], GA_BTN . '-' . $options['ga_type']);
        unset($options['ga_type']);

        // Merging regular classes.
        if(isset($classes)) {
            $options['class'] = array_merge($options['class'], $classes);
        }

        // Button size processing (GA_BTN_LARGE, GA_BTN_SMALL, etc.)
        if(isset($options['ga_size'])) {
            array_push($options['class'], GA_BTN . '-' . $options['ga_size']);
            unset($options['ga_size']);
        }

        // @todo We surely can generalize this in a function later.
        // Button icon
        if(isset($options['ga_icon'])) {
            $options['escape'] = false;

            // Icon options
            $iconOptions = [];
            if(isset($options['ga_icon_options'])) {
                $iconOptions = $options['ga_icon_options'];
                unset($options['ga_icon_options']);
            }
            $title = $this->icon($options['ga_icon'], $iconOptions) . ' ' . $title;
            unset($options['ga_icon']);
        }
        
        return parent::link($title, $url, $options);
    }

    /**
     * Create a Twitter Bootstrap span label.
     * 
     * @param text The label text
     * @param options Options for span
     *
     * Extra options
     * - ga_type         : The type of the label GA_DEFAULT|GA_SUCCESS|GA_WARNING|GA_INFO|GA_IMPORTANT|GA_INVERSE (default: GA_DEFAULT)
     * - ga_icon         : string GanacheHtml::icon() icon string result.
     * - ga_icon_options : array|string GanacheHtml::icon() icon options.
     *
     */
    public function label ($text, $options = [])
    {
        $types = [GA_DEFAULT, GA_SUCCESS, GA_WARNING, GA_INFO, GA_IMPORTANT, GA_INVERSE];

        $type = GA_DEFAULT;
        if(!empty($options['ga_type'])) {
            if(in_array($options['ga_type'], $types)) {
                $type = $options['ga_type'];
            }
            unset($options['ga_type']);
        }

        // @todo We surely can generalize this in a function later.
        // Button icon
        if(isset($options['ga_icon'])) {
            // Icon options
            $iconOptions = [];
            if(isset($options['ga_icon_options'])) {
                $iconOptions = $options['ga_icon_options'];
                unset($options['ga_icon_options']);
            }
            $text = $this->icon($options['ga_icon'], $iconOptions) . ' ' . $text;
            unset($options['ga_icon']);
        }

        $options = $this->addClass($options, GA_LABEL);
        $options = $this->addClass($options, GA_LABEL . '-' . $type) ;

        return $this->tag('span', $text, $options) ;
    }

    /**
     * Create a Twitter Bootstrap span badge.
     * 
     * @param text The badge text
     * @param options Options for span
     *
     * Extra options
     * - ga_type         : string The type of the label GA_DEFAULT|GA_SUCCESS|GA_WARNING|GA_INFO|GA_IMPORTANT|GA_INVERSE (default: GA_DEFAULT)
     * - ga_icon         : string GanacheHtml::icon() icon string result.
     * - ga_icon_options : array|string GanacheHtml::icon() icon options.
     *
     * @todo This function is almost the same as label(). Generalization.
     */
    public function badge ($text, $options = []) {
        $types = [GA_DEFAULT, GA_SUCCESS, GA_WARNING, GA_INFO, GA_IMPORTANT, GA_INVERSE];

        $type = GA_DEFAULT;
        if(!empty($options['ga_type'])) {
            if(in_array($options['ga_type'], $types)) {
                $type = $options['ga_type'];
            }
            unset($options['ga_type']);
        }

        // @todo We surely can generalize this in a function later.
        // Button icon
        if(isset($options['ga_icon'])) {
            // Icon options
            $iconOptions = [];
            if(isset($options['ga_icon_options'])) {
                $iconOptions = $options['ga_icon_options'];
                unset($options['ga_icon_options']);
            }
            $text = $this->icon($options['ga_icon'], $iconOptions) . ' ' . $text;
            unset($options['ga_icon']);
        }

        $options = $this->addClass($options, GA_BADGE);
        $options = $this->addClass($options, GA_BADGE . '-' . $type) ;

        return $this->tag('span', $text, $options) ;
    }

    /**
     * Get crumb lists in a HTML list, with bootstrap like style.
     *
     * @param $options Options for list
     * @param $startText Text to insert before list
     * 
     * Extra options:
     * 	- ga_separator : string Crumb separator character.
     */
    public function getCrumbList($options = [], $startText = null) {
        $separator = '/';
        if(!empty($options['ga_separator'])) {
            $separator = $options['ga_separator'];
            unset($options['ga_separator']);
        }
        $options['separator'] = $this->tag('span', $separator, ['class' => GA_DIVIDER]);

        $options = $this->addClass($options, GA_BREADCRUMB) ;

        return parent::getCrumbList ($options, $startText) ;
    }

    /**
     * Create a Twitter Bootstrap style alert block, containing text.
     *  
     * @param $text The alert text
     * @param $options Options that will be passed to Html::div method
     * 
     * 
     * Extra options
     * - ga_block : boolean specify if alert should have 'alert-block' class (default: false)
     * - ga_type  : string The type of the alert GA_DEFAULT|GA_SUCCESS|GA_WARNING|GA_ERROR|GA_INFO|GA_IMPORTANT|GA_INVERSE (default: GA_WARNING)
     */
    public function alert($text, $options = [])
    {
        $button = $this->tag('button', '&times;', ['class' => GA_CLOSE, 'data-dismiss' => GA_ALERT]);

        $types = [GA_DEFAULT, GA_SUCCESS, GA_WARNING, GA_ERROR, GA_INFO, GA_IMPORTANT, GA_INVERSE];

        $type = GA_WARNING;
        if(!empty($options['ga_type'])) {
            if(in_array($type, $types)) {
                $type = $options['ga_type'];
            }
            unset($options['ga_type']);
        }

        $block = false;
        if(!empty($options['ga_block'])) {
            $block = true;
            unset($options['ga_block']);
        }

        $options = $this->addClass($options, GA_ALERT) ;
        if ($block) {
            $options = $this->addClass($options, GA_ALERT_BLOCK);
        }

        $options = $this->addClass($options, GA_ALERT . '-' . $type) ;

        $class = '';
        if(!empty($options['class'])) {
            $class = $options['class'] ;
            unset($options['class']);
        }

        return $this->div($class, $button.$text, $options);
    }

    /**
     * Create a Twitter Bootstrap style progress bar.
     * 
     * @param $widths 
     * 	- The width (in %) of the bar
     * 	- An array of bar, with ga_width and ga_type (GA_INFO|GA_DANGER|GA_SUCCESS|GA_WARNING) specified for each bar. (default: GA_INFO)
     * 	examples:
     * 	    [10,45] or
     * 	    [10,['ga_width' => 56]] or
     * 	    [['ga_width' => 10], ['ga_width' => 56]] or
     * 	    [90,['ga_width' => 10, 'ga_type' => GA_DANGER]]
     * @param $options Options that will be passed to Html::div method (only for main div)
     *  
     * Extra options
     * - ga_type    : string, Type of the progress bar. GA_INFO|GA_SUCCESS|GA_WARNING|GA_DANGER (default: GA_INFO)
     * - ga_striped : boolean, specify if progress bar should be striped
     * - ga_active  : boolean, specify if progress bar should be active
     *
     * @todo Should we verify that we are not busting 100%.
     * @todo Should we recompute the percentage to attribute ?
     */
    public function progress($widths, $options = []) {
        $types = [GA_INFO, GA_SUCCESS, GA_WARNING, GA_DANGER];

        $type = GA_INFO;
        if(!empty($options['ga_type'])) {
            if(in_array($options['ga_type'], $types)) {
                $type = $options['ga_type'];
            }
            unset($options['ga_type']);
        }

        $striped = false;
        if(!empty($options['ga_striped'])) {
            $striped = true;
            unset($options['ga_striped']);
        }

        $active = false;
        if(!empty($options['ga_active'])) {
            $active = true;
            unset($options['ga_active']);
        }

        $bars = '' ;
        if (is_array($widths)) {
            foreach ($widths as $w) {
                $class = GA_BAR;
                $type = GA_INFO;
                if(!empty($w['ga_type'])) {
                    if(in_array($w['ga_type'], $types)) {
                        $type = $w['ga_type'];
                    }
                }

                $width = 0;
                if(!empty($w)) {
                    if(is_array($w)) {
                        if(!empty($w['ga_width'])) {
                            $width = $w['ga_width'];
                        }
                    } else {
                        $width = (int) $w;
                    }
                }

                $class .= ' ' . GA_BAR . '-' . $type;
                $bars .= $this->div($class, '', ['style' => 'width: ' . $width . '%;']);
            }
        } else {
            $bars = $this->div(GA_BAR, '', ['style' => 'width: ' . $widths . '%;']);
        }
        $options = $this->addClass($options, GA_PROGRESS);

        if (!is_array($widths)) {
            $options = $this->addClass($options, GA_PROGRESS . '-' . $type);
        }

        if ($striped) {
            $options = $this->addClass($options, GA_PROGRESS_STRIPED);
        }

        if ($active) {
            $options = $this->addClass($options, GA_ACTIVE);
        }

        $classes = $options['class'];
        unset($options['class']);
        return $this->div($classes, $bars, $options) ;
    }

    /**
     * Create a Bootstrap close icon button.
     * As button by default or as a link.
     *
     * @param boolean $asLink Generate close icon as link. (default: false)
     * @param array $options Array of attributes to pass to HtmlHelper::tag() function.
     * @return string
     *
     * @todo Can we only always generate it as link to support iOS events ? @see http://getbootstrap.com/2.3.2/components.html#misc
     */
    public function closeIcon($asLink = false, $options = [])
    {
        $defaultOptions = [
            'class' => GA_CLOSE
        ];

        $currentOptions = array_merge($defaultOptions, $options);

        if(!empty($options['class'])) {
            if(is_array($options['class'])) {
                $currentOptions['class'] .= ' ' . implode(' ', $options['class']);
            } else {
                $currentOptions['class'] .= ' ' . $options['class'];
            }
        }

        $button = $this->tag('button', '&times;', $currentOptions);
        if ($asLink === true) {
            $currentOptions['escape'] = false;
            $button = $this->link('&times;', '#', $currentOptions);
        }
        return $button;
    }


    /**
     * Initialize an HTML document and the head
     *
     * @param string $titre The name of the current page
     * @param string $description The description of the current page
     * @param string $lang The language of the current page. (default: 'en')
     * @param string $charset Charset of the page. (default: 'utf-8')
     * @param string $doctype The page doctype (default: 'html5')
     * @return string
     *
     * @todo Add doctype constants.
     */
    public function html($titre = '' , $description = '' , $lang = 'en', $charset = 'utf-8', $doctype = 'html5')
    {
        $out = $this->doctype($doctype) . PHP_EOL;
        $out .= $this->tag('html', null, ['lang' => $lang]) . PHP_EOL;
        $out .= $this->tag('head', null) . PHP_EOL;
        $out .= $this->meta('', null, ['charset' => $charset]) . PHP_EOL;
        $out .= $this->tag('title', $titre) . PHP_EOL;
        $out .= $this->meta('', null, ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no']) . PHP_EOL;
        $out .= $this->meta('description', $description) . PHP_EOL;

        return $out;
    }

    public function h1($title, $options = [])
    {
        return $this->tag('h1', $title, $options);
    }

    public function h2($title, $options = [])
    {
        return $this->tag('h2', $title, $options);
    }

    public function h3($title, $options = [])
    {
        return $this->tag('h3', $title, $options);
    }

    public function h4($title, $options = [])
    {
        return $this->tag('h4', $title, $options);
    }

    public function h5($title, $options = [])
    {
        return $this->tag('h5', $title, $options);
    }

    public function h6($title, $options = [])
    {
        return $this->tag('h6', $title, $options);
    }


    /**
     * Close the head element and initialize the body element
     *
     * @return string
     */
    public function body()
    {
        $out =  $this->tag('/head', null) . PHP_EOL;
        $out .= $this->tag('body', null) . PHP_EOL;

        return $out;
    }

    /**
     * Close the body element and the html element
     *
     * @return string
     */
    public function end()
    {
        $out =  $this->tag('/body', null) . PHP_EOL;
        $out .= $this->tag('/html', null);

        return $out;
    }

    /**
     * Open a Bootstrap container
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'container'
     */
    public function container($options = [])
    {
        $class = GA_CONTAINER;
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }
        return $this->div($class, null, $options) . PHP_EOL;
    }

    /**
     * Open a Bootstrap container fluid
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'container-fluid'
     */
    public function containerFluid($options = [])
    {
        $class = GA_CONTAINER_FLUID;
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }
        return $this->div($class, null, $options) . PHP_EOL;
    }

    /**
     * Open a Bootstrap row
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'row'
     */
    public function row($options = [])
    {
        $class = GA_ROW;
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }
        return $this->div($class, null, $options) . PHP_EOL;
    }

    /**
     * Open a Bootstrap row fluid
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'row-fluid'
     */
    public function rowFluid($options = [])
    {
        $class = GA_ROW_FLUID;
        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }
        return $this->div($class, null, $options) . PHP_EOL;
    }

    private function __span($class, $options = []) {
        $classes = [
            GA_SPAN1,
            GA_SPAN2,
            GA_SPAN3,
            GA_SPAN4,
            GA_SPAN5,
            GA_SPAN6,
            GA_SPAN7,
            GA_SPAN8,
            GA_SPAN9,
            GA_SPAN10,
            GA_SPAN11,
            GA_SPAN12,
        ];

        if(!in_array($class, $classes)) {
            $class = GA_SPAN1;
        }

        if(isset($options['class'])) {
            $class .= ' ' . $options['class'];
        }

        return $this->div($class, null, $options) . PHP_EOL;
    }

    /**
     * Open a Bootstrap cell span1.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span1'
     */
    public function span1($options = [])
    {
        return $this->__span(GA_SPAN1, $options);
    }

    /**
     * Open a Bootstrap cell span2.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span2'
     */
    public function span2($options = [])
    {
        return $this->__span(GA_SPAN2, $options);
    }

    /**
     * Open a Bootstrap cell span3.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span3'
     */
    public function span3($options = [])
    {
        return $this->__span(GA_SPAN3, $options);
    }

    /**
     * Open a Bootstrap cell span4.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span4'
     */
    public function span4($options = [])
    {
        return $this->__span(GA_SPAN4, $options);
    }

    /**
     * Open a Bootstrap cell span5.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span5'
     */
    public function span5($options = [])
    {
        return $this->__span(GA_SPAN5, $options);
    }

    /**
     * Open a Bootstrap cell span6.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span6'
     */
    public function span6($options = [])
    {
        return $this->__span(GA_SPAN6, $options);
    }

    /**
     * Open a Bootstrap cell span7.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span7'
     */
    public function span7($options = [])
    {
        return $this->__span(GA_SPAN7, $options);
    }

    /**
     * Open a Bootstrap cell span8.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span8'
     */
    public function span8($options = [])
    {
        return $this->__span(GA_SPAN8, $options);
    }

    /**
     * Open a Bootstrap cell span9.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span9'
     */
    public function span9($options = [])
    {
        return $this->__span(GA_SPAN9, $options);
    }

    /**
     * Open a Bootstrap cell span10.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span10'
     */
    public function span10($options = [])
    {
        return $this->__span(GA_SPAN10, $options);
    }

    /**
     * Open a Bootstrap cell span11.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span11'
     */
    public function span11($options = [])
    {
        return $this->__span(GA_SPAN11, $options);
    }

    /**
     * Open a Bootstrap cell span12.
     *
     * @param array $options Options of the div element
     * @return string Div element with the class 'span12'
     */
    public function span12($options = [])
    {
        return $this->__span(GA_SPAN12, $options);
    }

    /**
     * Close div elements
     *
     * @param int $nb Number of div you want to close
     * @return string End tags div
     */
    public function close($nb = 1)
    {
        return str_repeat($this->tag('/div', null) . PHP_EOL, $nb);
    }

    /**
     * Open a header element
     *
     * @param array $options Options of the header element
     * @return string Tag header
     */
    public function header($options = [])
    {
        return $this->tag('header', null, $options). PHP_EOL;
    }

    /**
     * Close the header element
     *
     * @return string End tag header
     */
    public function closeHeader()
    {
        return $this->tag('/header', null) . PHP_EOL;
    }

    /**
     * Picture responsive
     *
     * Extends from HtmlHelper:image()
     *    
     * @param string $path Path to the image file, relative to the app/webroot/img/ directory.
     * @param array $options Array of HTML attributes. See above for special options.
     * @return string End tag header
     *
     * Extra options
     * - ga_responsive : boolean Image should be responsive. (default: false)
     * - ga_shape      : string Shape of the images GA_ROUNDED|GA_CIRCLE|GA_POLAROID
     */
    public function image($path, $options = [])
    {
        if(!empty($options['ga_responsive']) && $options['ga_responsive'] === true) {
            $options = $this->addClass($options, GA_IMG_RESPONSIVE);
            unset($options['ga_responsive']);
        }

        $shapes = [GA_ROUNDED, GA_CIRCLE, GA_POLAROID];

        if(!empty($options['ga_shape'])) {
            if(in_array($options['ga_shape'], $shapes)) {
                $options = $this->addClass($options, GA_IMG . '-' . $options['ga_shape']);
            }
            unset($options['ga_shape']);
        }

        return parent::image($path, $options);
    }

    /**
     * IMPORTANT: Bootstrap 3 related function. Not use right now.
     *
     * Create a <div class="col"> element.
     * 
     * Differents layouts with options.
     *
     * ### Construction
     *
     * $this->Bs->col('xs3 of1 ph9', 'md3');
     *
     * It means : - For XS layout, a column size of 3, offset of 1 and push of 9.
     *             - For MD layout, a column size of 3.
     *
     * You can give all parameters you want before $attributes. The rule of params is :
     *
     * 'LAYOUT+SIZE OPTIONS+SIZE'
     *
     * LAYOUT -> not obligatory for the first param ('xs' by default).
     * SIZE -> size of the column in a grid of 12 columns.
     * OPTIONS -> Not obligatory. Offset, push or pull. Called like this : 'of', 'ph' or 'pl'.
     * SIZE -> size of the option.
     *
     *
     * ### Attributes
     *
     * Same options that HtmlHelper::div();
     *
     * @param string layout, size and options (offset, push and/or pull)
     * @param array $attributes Options of the div element
     * @return string DIV tag element 
     */
    /*
    public function col() {
        $class = '';
        $devices = array();
        $attributes = array();
        $args = func_get_args();
        foreach ($args as $arg) {
            if (!is_array($arg)) {
                $devices[] = $arg;
            } else {
                $attributes = $arg;
            }
        }
        $arrayDevice = array('xs' , 'sm' , 'md' , 'lg');
        $arrayOptions = array('of' , 'ph' , 'pl');
        foreach ($devices as $device) {
            $ecran = null;
            $taille = null;
            $opt = null;
            $replace = array('(', ')', '-', '_', '/', '\\', ';', ',', ':', ' ');
            $device = str_replace($replace, '.', $device);
            $device = explode('.', $device);
            // On doit obligatoirement définir un écran en premier sinon ça ne marche pas
            foreach ($device as $elem) {
                if (!$ecran) {
                    $nom = substr($elem, 0, 2);
                    if(in_array($nom , $arrayDevice)) {
                        $ecran = $nom;
                        $taille = substr($elem, 2);
                    }
                }else{
                    if ($opt) {
                        $opt .= ' '.$this->optCol($elem, $ecran);
                    }
                    else{
                        $opt = $this->optCol($elem, $ecran);
                    }
                }
            }
            if (isset($ecran) && $taille) {
                if ($opt) {
                    $class .= 'col-'.$ecran.'-'.$taille.' '.$opt.' ';
                }else{
                    $class .= 'col-'.$ecran.'-'.$taille.' ';
                }
            }
        }
        $class = substr($class,0,-1);
        if(isset($attributes['class']))
            $class .= SP . $attributes['class'];
        $out = parent::div($class , null, $attributes). BL;
        return $out;
    }
     */
    /**
     * IMPORTANT: Bootstrap 3 related function. Not use right now.
     *
     * Complementary function with BsHelper::col()
     *
     * Add the correct class for the option in parameter
     *
     * @param array $elem // classe appliquée sur l'élément col {PARAMETRE OBLIGATOIRE}
     * @param string $ecran // layout concerné {PARAMETRE OBLIGATOIRE}
     * @return string The class corresponding to the option
     */
    /*
    private function optCol($elem, $ecran){
        $attr = substr($elem, 0, 2);
        $taille = substr($elem, 2);
        if (is_integer($taille) || !($taille == 0 && $ecran == 'sm'))  {
            switch ($attr) {
            case 'pl':
                return 'col-'.$ecran.'-pull-'.$taille;
                break;
            case 'ph':
                return 'col-'.$ecran.'-push-'.$taille;
                break;

            case 'of':
                return 'col-'.$ecran.'-offset-'.$taille;
                break;
            default:
                return null;
                break;
            }
        }
    }
     */
}

