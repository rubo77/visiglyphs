<?php
/*
 * Visiglyphs
 *
 * (C) 2008 Charles Darke @ digitalconsumption.com
 *
 * All rights reserved.
 *
 * Redistribution and use in source and/or binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  
 *
 * TO THE MAXIMUM EXTENT AUTHORIZED BY LAW, IN NO EVENT SHALL
 * THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

/*
*
* How to use:
*
* 1. Define the relevant constants below
* 2. Create the visiglyph cache and ensure it is writeable by the webserver
* 3. Set an appropriate cache-miss code to generate non-cached glyphs e.g.
* 		RewriteRule ^([0-9]+)-([a-z0-9]{24})-([a-z0-9]{8}).png$ ./visiglyph.php?$size=$1&ip=$2&key=$3[L]
* 4. Tweak any additional settings required (see code/comments below).
*
*/

define ("VISIGLYPH",'put-a-very-long-random-string-here');
define ("VISIUPLOAD",'/put/your/cache/directory/here');

/*
 * Version 2.
 *
 * Changes since first release:
 *
 * 1. Re-written so that imagerotate() is not required. This also fixes some of the drawing artefacts
 * 2. Released under BSD-like licence (see above).
 *
 * Planned features in future releases:
 *
 * 1. Use of more blocks e.g. 16-blocks instead of 9
 * 2. Add additional blocks
 * 3. Add configuration options so no need to change code
 * 4. Tidy up code
 *
 */

define ('EREG_REP_MD5','[^A-Fa-f0-9]+');
define ('PREG_DIGIT_FILTER','/[\D]/');

function rotatepoints($pointarray, $blocksize, $rotation=1){
	switch($rotation){
		case 1:
			for ($i=0; $i<count($pointarray);$i = $i+2){
				$x = $pointarray[$i];
				$y = $pointarray[$i+1];
				$pointarray[$i]  = $blocksize*3 - $y;
				$pointarray[$i+1]  = $x;
			}
		break;
		case 2:
			for ($i=0; $i<count($pointarray);$i = $i+2){
				$x = $pointarray[$i];
				$y = $pointarray[$i+1];
				$pointarray[$i]  = $blocksize*3 - $x;
				$pointarray[$i+1]  = $blocksize*3 - $y;
			}
		break;
		case 3:
			for ($i=0; $i<count($pointarray);$i = $i+2){
				$x = $pointarray[$i];
				$y = $pointarray[$i+1];
				$pointarray[$i]  = $y;
				$pointarray[$i+1]  = $blocksize*3 - $x;
			}
		break;
		default:
	}
	return $pointarray;
}


// This function draws a visiglyph then saves to a file (cache).
// To be called on cache miss
function glyph($blocksize,$i,$j,$k,$rot1,$rot2,$fgr,$fgg,$fgb,$fgr2,$fgg2,$fgb2,$bgr,$bgg,$bgb,$filename){

// set a minimum blocksize below which we draw bigger and then resample downwards for a better look
$resize=0;
$minblocksize=36;
if ($blocksize<$minblocksize){
	$resize=$blocksize;
	$blocksize=$minblocksize;
}

$imgsize=$blocksize*3;
$quarter=$blocksize/4;
$quarter3=$quarter*3;
$half=$blocksize/2;
$third=$blocksize/3;
$centre=$imgsize/2;

$temp_block = imagecreatetruecolor($blocksize*2,$blocksize);
$im = imagecreate($imgsize,$imgsize);

$backgroundcolor = imagecolorallocate($im, $bgr, $bgg, $bgb);
$backgroundcolor = imagecolorallocate($temp_block, $bgr, $bgg, $bgb);
$red = imagecolorallocate($im, $fgr, $fgg, $fgb);

$originx=0;
$originy=0;

switch($i){
	case 1: // #1 mountains
		$points = array(
			$originx, $originy,
			$originx+$quarter, $originy+$blocksize,
			$originx+$half, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		$points = array(
			$originx+$half, $originy,
			$originx+$quarter3, $originy+$blocksize,
			$originx+$blocksize, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 2: // #2 half triangle
		$points = array(
			$originx, $originy,
			$originx+$blocksize, $originy,
			$originx, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 3: // #3 centre triangle
		$points = array(
			$originx, $originy,
			$originx+$half, $originy+$blocksize,
			$originx+$blocksize, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 4: // #4 half block
		$points = array(
			$originx, $originy,
			$originx, $originy+$blocksize,
			$originx+$half, $originy+$blocksize,
			$originx+$half, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 5: // #5 half diamond
		$points = array(
			$originx+$quarter, $originy,
			$originx, $originy+$half,
			$originx+$quarter, $originy+$blocksize,
			$originx+$half, $originy+$half
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 6: // #6 spike
		$points = array(
			$originx, $originy,
			$originx+$blocksize, $originy+$half,
			$originx+$blocksize, $originy+$blocksize,
			$originx+$half, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 7: // #7 quarter triangle
		$points = array(
			$originx, $originy,
			$originx+$half, $originy+$blocksize,
			$originx, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 8: // #8 diag triangle
		$points = array(
			$originx, $originy,
			$originx+$blocksize, $originy+$half,
			$originx+$half, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 9: // #9 centre mini triangle
		$points = array(
			$originx+$quarter, $originy+$quarter,
			$originx+$quarter3, $originy+$quarter,
			$originx+$quarter, $originy+$quarter3
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 10: // #10 diag mountains
		$points = array(
			$originx, $originy,
			$originx+$half, $originy,
			$originx+$half, $originy+$half
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		$points = array(
			$originx+$half, $originy+$half,
			$originx+$blocksize, $originy+$half,
			$originx+$blocksize, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 11: // #11 quarter block
		$points = array(
			$originx, $originy,
			$originx, $originy+$half,
			$originx+$half, $originy+$half,
			$originx+$half, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 12: // #12 point out triangle
		$points = array(
			$originx, $originy+$half,
			$originx+$half, $originy+$blocksize,
			$originx+$blocksize, $originy+$half
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 13: // #13 point in triangle
		$points = array(
			$originx, $originy,
			$originx+$half, $originy+$half,
			$originx+$blocksize, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 14: // #14 diag point in
		$points = array(
			$originx+$half, $originy+$half,
			$originx, $originy+$half,
			$originx+$half, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 15: // #15 diag point out
		$points = array(
			$originx, $originy,
			$originx+$half, $originy,
			$originx, $originy+$half,
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 16:	// #16 diag side point out
	default:
		$points = array(
			$originx, $originy,
			$originx+$half, $originy,
			$originx+$half, $originy+$half
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
} // end switch

$originx=$blocksize;
$originy=0;
$red = imagecolorallocate($im, $fgr2, $fgg2, $fgb2);

switch($j){
	case 1: // #1 mountains
		$points = array(
			$originx, $originy,
			$originx+$quarter, $originy+$blocksize,
			$originx+$half, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		$points = array(
			$originx+$half, $originy,
			$originx+$quarter3, $originy+$blocksize,
			$originx+$blocksize, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 2: // #2 half triangle
		$points = array(
			$originx, $originy,
			$originx+$blocksize, $originy,
			$originx, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 3: // #3 centre triangle
		$points = array(
			$originx, $originy,
			$originx+$half, $originy+$blocksize,
			$originx+$blocksize, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 4: // #4 half block
		$points = array(
			$originx, $originy,
			$originx, $originy+$blocksize,
			$originx+$half, $originy+$blocksize,
			$originx+$half, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 5: // #5 half diamond
		$points = array(
			$originx+$quarter, $originy,
			$originx, $originy+$half,
			$originx+$quarter, $originy+$blocksize,
			$originx+$half, $originy+$half
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 6: // #6 spike
		$points = array(
			$originx, $originy,
			$originx+$blocksize, $originy+$half,
			$originx+$blocksize, $originy+$blocksize,
			$originx+$half, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 7: // #7 quarter triangle
		$points = array(
			$originx, $originy,
			$originx+$half, $originy+$blocksize,
			$originx, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 8: // #8 diag triangle
		$points = array(
			$originx, $originy,
			$originx+$blocksize, $originy+$half,
			$originx+$half, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 9: // #9 centre mini triangle
		$points = array(
			$originx+$quarter, $originy+$quarter,
			$originx+$quarter3, $originy+$quarter,
			$originx+$quarter, $originy+$quarter3
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 10: // #10 diag mountains
		$points = array(
			$originx, $originy,
			$originx+$half, $originy,
			$originx+$half, $originy+$half
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		$points = array(
			$originx+$half, $originy+$half,
			$originx+$blocksize, $originy+$half,
			$originx+$blocksize, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 11: // #11 quarter block
		$points = array(
			$originx, $originy,
			$originx, $originy+$half,
			$originx+$half, $originy+$half,
			$originx+$half, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 12: // #12 point out triangle
		$points = array(
			$originx, $originy+$half,
			$originx+$half, $originy+$blocksize,
			$originx+$blocksize, $originy+$half
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 13: // #13 point in triangle
		$points = array(
			$originx, $originy,
			$originx+$half, $originy+$half,
			$originx+$blocksize, $originy
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 14: // #14 diag point in
		$points = array(
			$originx+$half, $originy+$half,
			$originx, $originy+$half,
			$originx+$half, $originy+$blocksize
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;

	case 15: // #15 diag point out
		$points = array(
			$originx, $originy,
			$originx+$half, $originy,
			$originx, $originy+$half,
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);
		break;


	case 16:	// #16 diag side point out
	default:
		$points = array(
			$originx, $originy,
			$originx+$half, $originy,
			$originx+$half, $originy+$half
		);
		$num = count($points) / 2;
		for ($i=0;$i<4;$i++) imagefilledpolygon($im, rotatepoints($points,$blocksize,$i), $num, $red);



} // end switch

$red = imagecolorallocate($im, $fgr, $fgg, $fgb);

$originx=$blocksize;
$originy=$blocksize;

// draw centre
switch($k){
	case 1: // circle
		imagefilledellipse($im, $centre, $centre, $quarter3, $quarter3, $red);
		break;

	case 2: // quarter square
		imagefilledrectangle ( $im, $originx+$quarter, $originy+$quarter, $originx+$quarter3, $originy+$quarter3, $red);
		break;

	case 3: // full square
		imagefilledrectangle ( $im, $originx, $originy, $originx+$blocksize, $originy+$blocksize, $red);
		break;

	case 4: // quarter diamond
		$points = array(
			$originx+$half, $originy+$quarter,
			$originx+$quarter3, $originy+$half,
			$originx+$half, $originy+$quarter3,
			$originx+$quarter, $originy+$half
		);
		$num = count($points) / 2;
		imagefilledpolygon($im, $points, $num, $red);
		break;
		
	case 5: // diamond
		$points = array(
			$originx+$half, $originy,
			$originx, $originy+$half,
			$originx+$half, $originy+$blocksize,
			$originx+$blocksize, $originy+$half
		);
		$num = count($points) / 2;
		imagefilledpolygon($im, $points, $num, $red);
		break;

	default:
		// empty space

}

header('Content-type: image/png');

if ($resize>0){ // if we need to resample down
	$blocksize=$resize;
	$imgsizeR=$blocksize*3;
	$imresize = imagecreatetruecolor($imgsizeR,$imgsizeR);
	$backgroundcolor = imagecolorallocate($imresize, $bgr, $bgg, $bgb);
	imagecopyresampled ( $imresize, $im, 0, 0, 0, 0, $imgsizeR, $imgsizeR, $imgsize, $imgsize );
	imagepng($imresize);
	imagepng($imresize,VISIUPLOAD.$filename.'.png'); //comment out to remove caching
} else {
	imagepng($im);
	imagepng($im,VISIUPLOAD.$filename.'.png');//comment out to remove caching
}
}

//RewriteRule ^([0-9]+)-([a-z0-9]{24})-([a-z0-9]{8}).png$ ./visiglyph.php?$size=$1&ip=$2&key=$3[L]
if (array_key_exists('size', $_REQUEST)) $size=preg_replace(PREG_DIGIT_FILTER,'',$_REQUEST['size']); else exit;
if (array_key_exists('ip', $_REQUEST)) $ip=ereg_replace(EREG_REP_MD5,'',$_REQUEST['ip']); else exit;
if (array_key_exists('key', $_REQUEST)) $key=ereg_replace(EREG_REP_MD5,'',$_REQUEST['key']); else exit;

$visiglyph_core=$size.'-'.$ip;
$visiglyph_code=substr(md5($visiglyph_core.VISIGLYPH),0,8);
$filename=$visiglyph_core.'-'.$visiglyph_code;
//$ip=md5(rand(0,300)); // uncomment to generate random glyphs
if ($visiglyph_code==$key){
	glyph(
		$size,
		hexdec(substr($ip,0,1)), //block 1
		hexdec(substr($ip,1,1)), //block 2
		hexdec(substr($ip,2,1))&7, //centre
		hexdec(substr($ip,3,1))&3, //rot 1
		hexdec(substr($ip,4,1))&3, //rot 2
		hexdec(substr($ip, 5,2))&239, //fg
		hexdec(substr($ip, 7,2))&239, // note FG is AND'ed to make sure that it is not too 'white' to give decent contrast. you may wish to adjust this depending on your requirements.
		hexdec(substr($ip, 9,2))&239, 
		hexdec(substr($ip,11,2))&239, //fg2
		hexdec(substr($ip,13,2))&239, 
		hexdec(substr($ip,15,2))&239, 
		hexdec(substr($ip,17,2)), //bg
		hexdec(substr($ip,19,2)),
		hexdec(substr($ip,21,2)),
		$filename
		);
}
