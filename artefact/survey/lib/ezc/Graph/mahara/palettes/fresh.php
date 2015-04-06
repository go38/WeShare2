<?php
/**
 * File containing the ezcMaharaFreshTheme class
 *
 * @package Graph
 * @version 1.5
 * @copyright Copyright (C) 2011 Gregor Anzelj, gregor.anzelj@gmail.com. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Default color palette for ezcGraph based on Mahara Fresh theme
 *
 * @version 1.5
 * @package Graph
 */
class ezcMaharaFreshTheme extends ezcGraphPalette
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
    protected $majorGridColor = '#080D0F80'; // Color1, ALPHA 50

    /**
     * Color of minor grid lines
     * 
     * @var ezcGraphColor
     */
    protected $minorGridColor = '#080D0FE6';  // == chartBackground (Color1, ALPHA 20)

    /**
     * Array with colors for datasets
     * 
     * @var array
     */
    protected $dataSetColor = array(
        '#080D0F', // Color1: RGB(8,13,15), ALPHA 100
        '#1C3740', // Color2: RGB(28,55,64), ALPHA 100
        '#005778', // Color3: RGB(0,87,120), ALPHA 100
        '#00AEF1', // Color4: RGB(0,174,241), ALPHA 100
        '#3E7800', // Color5: RGB(62,120,0), ALPHA 100
        '#1F3C00', // Color6: RGB(31,60,0), ALPHA 100
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
    protected $chartBackground = '#080D0FE6';  // Color1, ALPHA 20

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
