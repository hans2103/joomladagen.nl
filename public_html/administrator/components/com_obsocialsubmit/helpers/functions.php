<?php
/**
 * @package        obSocialSubmit
 * @author         foobla.com.
 * @copyright      Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

if ( ! function_exists( 'json_decode' ) ) {
	function json_decode( $json ) {
		$comment = false;
		$out     = '$x=';

		for ( $i = 0; $i < strlen( $json ); $i ++ ) {
			if ( ! $comment ) {
				if ( ( $json[$i] == '{' ) || ( $json[$i] == '[' ) ) {
					$out .= ' array(';
				} else {
					if ( ( $json[$i] == '}' ) || ( $json[$i] == ']' ) ) {
						$out .= ')';
					} else {
						if ( $json[$i] == ':' ) {
							$out .= '=>';
						} else {
							$out .= $json[$i];
						}
					}
				}
			} else {
				$out .= $json[$i];
			}
			if ( $json[$i] == '"' && $json[( $i - 1 )] != "\\" ) {
				$comment = ! $comment;
			}
		}
		eval( $out . ';' );

		return $out;
	}
}

class obSSHelper {
	function obssGetFirstImgSrc( $text, $absolute = true ) {
		preg_match( '/<img\s.*?\/>/', $text, $matches_img );
		$img = '';
		if ( isset( $matches_img[0] ) ) {
			$imgtag = $matches_img[0];
			preg_match( '/(src)=("([^"]*)")/', $imgtag, $result );
			$img = JURI::root() . $result[3];
		};
		if ( ! $img ) {
			return null;
		}
		if ( JURI::isInternal( $img ) ) {
			$img = JURI::root() . trim( $img, "/ " );
		}

		return $img;
	}

	function date_convert( $dt, $tz1, $df1, $tz2, $df2 ) {
		$res = '';
		if ( ! in_array( $tz1, timezone_identifiers_list() ) ) { // check source timezone
			trigger_error( __FUNCTION__ . ': Invalid source timezone ' . $tz1, E_USER_ERROR );
		} elseif ( ! in_array( $tz2, timezone_identifiers_list() ) ) { // check destination timezone
			trigger_error( __FUNCTION__ . ': Invalid destination timezone ' . $tz2, E_USER_ERROR );
		} else {
			// create DateTime object
			$d = DateTime::createFromFormat( $df1, $dt, new DateTimeZone( $tz1 ) );
			// check source datetime
			$date_error = DateTime::getLastErrors();
			if ( $d && $date_error["warning_count"] == 0 && $date_error["error_count"] == 0 ) {
				// convert timezone
				$d->setTimeZone( new DateTimeZone( $tz2 ) );
				// convert dateformat
				$res = $d->format( $df2 );
			} else {
				trigger_error( __FUNCTION__ . ': Invalid source datetime ' . $dt . ', ' . $df1, E_USER_ERROR );
			}
		}

		return $res;
	}
}