<?php
/**
 * File containing the ezcMaharaAquaTheme class
 *
 * @package Graph
 * @version 1.5
 * @copyright Copyright (C) 2011 Gregor Anzelj, gregor.anzelj@gmail.com. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Default color palette for ezcGraph based on Mahara Aqua theme
 *
 * @version 1.5
 * @package Graph
 */
class ezcMaharaAquaTheme extends ezcGraphPalette
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
    protected $majorGridColor = '#14336F80'; // Color1, ALPHA 50

    /**
     * Color of minor grid lines
     * 
     * @var ezcGraphColor
     */
    protected $minorGridColor = '#14336FE6';  // == chartBackground (Color1, ALPHA 20)

    /**
     * Array with colors for datasets
     * 
     * @var array
     */
    protected $dataSetColor = array(
        '#14336F', // Color1: RGB(20,51,111), ALPHA 100
        '#1D499F', // Color2: RGB(29,73,159), ALPHA 100
        '#3986C3', // Color3: RGB(57,134,195), ALPHA 100
        '#1E6297', // Color4: RGB(30,98,151), ALPHA 100
        '#0A77CB', // Color5: RGB(10,119,203), ALPHA 100
        '#404080', // Color6: RGB(64,64,128), ALPHA 100
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
    protected $chartBackground = '#14336FE6';  // Color1, ALPHA 20

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

/*
	ALPHA 0	= 255	= #FF
	ALPHA 10	= 230	= #E6
	ALPHA 20	= 204	= #CC
	ALPHA 30	= 179		= #B3
	ALPHA 40	= 153		= #99
	ALPHA 50	= 128		= #80
	ALPHA 60	= 102		= #66
	ALPHA 70	= 77		= #4D
	ALPHA 80 	= 51		= #33
	ALPHA 90	= 26		= #1A
	ALPHA 100	= 0		= #00
*/
?>
