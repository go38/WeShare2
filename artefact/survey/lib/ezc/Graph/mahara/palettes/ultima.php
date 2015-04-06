<?php
/**
 * File containing the ezcMaharaUltimaTheme class
 *
 * @package Graph
 * @version 1.5
 * @copyright Copyright (C) 2011 Gregor Anzelj, gregor.anzelj@gmail.com. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Default color palette for ezcGraph based on Mahara Ultima theme
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
    protected $majorGridColor = '#36043580'; // Color1, ALPHA 50

    /**
     * Color of minor grid lines
     * 
     * @var ezcGraphColor
     */
    protected $minorGridColor = '#360435E6';  // == chartBackground (Color1, ALPHA 20)

    /**
     * Array with colors for datasets
     * 
     * @var array
     */
    protected $dataSetColor = array(
        '#360435', // Color1: RGB(54,4,53), ALPHA 100
        '#5C0758', // Color2: RGB(92,7,88), ALPHA 100
        '#A551A9', // Color3: RGB(165,81,169), ALPHA 100
        '#841C7F', // Color4: RGB(132,28,127), ALPHA 100
        '#B75BAD', // Color5: RGB(183,91,173), ALPHA 100
        '#E99ADB', // Color6: RGB(233,154,219), ALPHA 100
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
    protected $chartBackground = '#360435E6';  // Color1, ALPHA 20

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

