<?php
/**
 * File containing the ezcMaharaDefaultTheme class
 *
 * @package Graph
 * @version 1.5
 * @copyright Copyright (C) 2011 Gregor Anzelj, gregor.anzelj@gmail.com. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Default color palette for ezcGraph based on Mahara Default theme
 *
 * @version 1.5
 * @package Graph
 */
class ezcMaharaDefaultTheme extends ezcGraphPalette
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
    protected $majorGridColor = '#4C711D80'; // Color1, ALPHA 50

    /**
     * Color of minor grid lines
     * 
     * @var ezcGraphColor
     */
    protected $minorGridColor = '#4C711DE6';  // == chartBackground (Color1, ALPHA 20)

    /**
     * Array with colors for datasets
     * 
     * @var array
     */
    protected $dataSetColor = array(
        '#4C711D', // Color1: RGB(76,113,29), ALPHA 100
        '#6E8E00', // Color2: RGB(110,142,0), ALPHA 100
        '#84AA00', // Color3: RGB(132,170,0), ALPHA 100
        '#1E6297', // Color4: RGB(30,98,151), ALPHA 100
        '#0A77CB', // Color5: RGB(10,119,203), ALPHA 100
        '#474220', // Color6: RGB(71,66,32), ALPHA 100
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
    protected $chartBackground = '#4C711DE6';  // Color1, ALPHA 20

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
