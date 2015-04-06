<?php
/**
 * File containing the ezcMaharaSunsetTheme class
 *
 * @package Graph
 * @version 1.5
 * @copyright Copyright (C) 2011 Gregor Anzelj, gregor.anzelj@gmail.com. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Default color palette for ezcGraph based on Mahara Sunset theme
 *
 * @version 1.5
 * @package Graph
 */
class ezcMaharaSunsetTheme extends ezcGraphPalette
{
    /**
     * Axiscolor 
     * 
     * @var ezcGraphColor
     */
    protected $axisColor = '#000000';

    /**
     * Color of grid lines
     * 
     * @var ezcGraphColor
     */
    protected $majorGridColor = '#51130480'; // Color1, ALPHA 50

    /**
     * Color of minor grid lines
     * 
     * @var ezcGraphColor
     */
    protected $minorGridColor = '#511304E6';  // == chartBackground (Color1, ALPHA 20)

    /**
     * Array with colors for datasets
     * 
     * @var array
     */
    protected $dataSetColor = array(
        '#511304', // Color1: RGB(81,19,4), ALPHA 100
        '#8E1901', // Color2: RGB(142,25,1), ALPHA 100
        '#C66C14', // Color3: RGB(198,108,20), ALPHA 100
        '#DE991C', // Color4: RGB(222,153,28), ALPHA 100
        '#7C5F12', // Color5: RGB(124,95,18), ALPHA 100
        '#BA8E1B', // Color6: RGB(186,142,27), ALPHA 100
    );

    /**
     * Array with symbols for datasets 
     * 
     * @var array
     */
    protected $dataSetSymbol = array(
        ezcGraph::NO_SYMBOL,
        //ezcGraph::BULLET,
    );

    /**
     * Name of font to use
     * 
     * @var string
     */
    protected $fontName = 'sans-serif';

    /**
     * Fontcolor 
     * 
     * @var ezcGraphColor
     */
    protected $fontColor = '#000000';

    /**
     * Backgroundcolor 
     * 
     * @var ezcGraphColor
     */
    protected $chartBackground = '#511304E6';  // Color1, ALPHA 20

    /**
     * Border color for chart elements
     * 
     * @var string
     */
    //protected $elementBorderColor = '#555753';

    /**
     * Border width for chart elements
     * 
     * @var integer
     */
    protected $elementBorderWidth = 1;

    /**
     * Padding in elements
     * 
     * @var integer
     */
    protected $padding = 1;

    /**
     * Margin of elements
     * 
     * @var integer
     */
    protected $margin = 1;
}

?>
