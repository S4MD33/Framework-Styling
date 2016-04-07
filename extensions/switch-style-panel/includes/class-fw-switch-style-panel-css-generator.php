<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Switch_Style_Panel_Css_Generator {

	private static $tags = array(
		'typography' => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'a' ),
		'links'      => array( 'links', 'links_hover', 'links_active' ),
	);

	private static $initialized = false;

	private static $google_fonts;

	private static $remote_fonts = array();

	static public function get_css( $blocks, $style_options ) {

		if ( ! self::$initialized ) {
			self::$google_fonts = fw_get_google_fonts();
			self::$initialized  = true;
		}

		return array(
			'css'          => self::generate_css( $blocks, $style_options['blocks'] ),
			'google_fonts' => self::get_remote_fonts()
		);
	}

	private static function generate_css( $blocks, $style_options ) {

		$css = '';

		foreach ( $blocks as $block_id => $block_settings ) {
			if ( empty( $block_settings['css_selector'] ) || empty($style_options[ $block_id ])) {
				continue;
			}
			$css_selectors  = (array) $block_settings['css_selector'];
			$block_elements = (array) $block_settings['elements'];

			foreach ( $block_elements as $element ) {
				if ( in_array( $element, self::$tags['typography'] ) ) {
					$css .= self::generate_typography_css( $css_selectors, $element, $style_options[ $block_id ][ $element ] );
				} elseif ( in_array( $element, self::$tags['links'] ) ) {
					$css .= self::generate_links_css( $css_selectors, $element, $style_options[ $block_id ][ $element ] );
				} elseif ( $element === 'background' ) {
					$css .= self::generate_background_css( $css_selectors, $style_options[ $block_id ][ $element ] );
				}
			}
		}

		return $css;
	}

	private static function generate_typography_css( $selectors, $tag, $options ) {
		$css = '';

		$current_family = $options['family'];

		$current_style = $options['style'];

		if ( $current_style === 'regular' ) {
			$current_style = '400';
		}
		if ( $current_style == 'italic' ) {
			$current_style = '400italic';
		}

		$font_style  = ( strpos( $current_style, 'italic' ) ) ? 'font-style: italic;' : '';
		$font_weight = 'font-weight: ' . intval( $current_style ) . ';';

		self::insert_remote_font( $current_family, $current_style );

		foreach ( $selectors as $selector ) {

			if( $tag == 'p' && $selector == 'body' ) {
				$css .= "body p a:hover, body p a:focus, .entry-meta a:hover, .entry-meta a:focus {
						color: " . $options['color'] . ";
				}";
				
				$tag .= ', .panel-collapse > .panel-body, .panel-collapse > .list-group';
			}
			if( $tag == 'h1' && $selector == 'body' ) {
				$css .= "body h1 a:hover, body h2 a:hover, body h3 a:hover, body h4 a:hover,
					body h1 a:focus, body h2 a:focus, body h3 a:focus, body h4 a:focus {
						color: " . $options['color'] . ";
				}";
				$css .= "body h1, body h2, body h3, body h4, body h5 {
						color: " . $options['color'] . ";
						font-family: '" . $current_family . "';"
						. $font_style . " " . $font_weight . "
				}\n";
			}
			if( $tag == 'a' && $selector == '.navbar-inverse.scroll-fixed-navbar' ) {
				$css .= ".navbar-inverse .navbar-nav li a {
						font-size: " . $options['size'] . "px;
						font-family: '" . $current_family . "';"
					. $font_style . "" . $font_weight . "
				}\n";
			}
			$css .= $selector . ' ' . $tag . "{
						color: " . $options['color'] . ";
						font-size: " . $options['size'] . "px;
						font-family: '" . $current_family . "';"
					. $font_style . "" . $font_weight . "

					 }\n";
		}

		return $css;
	}

	private static function insert_remote_font( $font, $style ) {

		if ( ! isset( self::$google_fonts[ $font ] ) ) {
			return false;
		}

		if ( ! isset( self::$remote_fonts[ $font ] ) ) {
			self::$remote_fonts[ $font ] = array();
		}

		if ( ! in_array( $style, self::$remote_fonts[ $font ] ) ) {
			self::$remote_fonts[ $font ][] = $style;
		}

		return true;
	}

	private static function generate_links_css( $selectors, $tag, $color ) {
		$css = '';
		if ( ! is_string( $color ) || ! self::is_valid_hex_color( $color ) ) {
			return $css;
		}
		if ( $tag === 'links_hover' ) {
			$tag = 'a:hover';
		} elseif ( $tag === 'links_active' ) {
			$tag = '.active a';
		} else {
			$tag = 'a';
		}
		foreach ( $selectors as $selector ) {
			if( $selector == '.navbar-inverse.scroll-fixed-navbar' ) {
				if ( $tag == 'a:hover' ) {
					$css .= "\n";
					$css .= '.navbar-inverse .navbar-toggle:focus, .navbar-inverse .navbar-toggle:hover{';
					$css .= 'background:' . $color . '}';
				} elseif ( $tag == '.active a' ) {
					$css .= "\n";
					$css .= '.navbar-inverse .navbar-toggle {border-color:' . $color . '}';
				}
				$css .= "\n";
				$css .= '@media (max-width: 767px) {';
				if ( $tag == 'a:hover' ) {
					$css .= '	.navbar-inverse .navbar-toggle span.icon-bar,' . "\n";
					$css .= '	.navbar-inverse .navbar-collapse ul.navbar-nav li a:hover' . "\n";
					$css .= '	{ background:'.$color.' }' . "\n";
				} elseif ( $tag == '.active a' ) {
					$css .= '	.navbar-inverse .navbar-collapse ul.navbar-nav li.active,' . "\n";
					$css .= '	.navbar-inverse .navbar-collapse ul.navbar-nav li a:focus' . "\n";
					$css .= '	{ color:'.$color.' }' . "\n";
				}
				$css .= "}\n";
			}
			if( $selector == '.navbar-inverse.scroll-fixed-navbar .navbar-nav li' ) {
				if( $tag == '.active a' ) {
					$selector = '.navbar-inverse.scroll-fixed-navbar .navbar-nav';
				} elseif( $tag == 'a:hover' ) {
					$selector = '.navbar-inverse.scroll-fixed-navbar .navbar-nav .active a:hover, ' . $selector;
				}
			}
			if( $selector == 'body' ) {
				$css .= "\n";
				if( $tag == '.active a' ) {
					$css .= '.pagination-wrap .pagination li a.active, .def-btn:focus, .btn:focus, ' . "\n";
					$css .= '.portfoliofilter a.current .filterbutton, ' . "\n";
					$css .= '.radio input[type="radio"]:checked + label::before,.radio input[type="radio"]:checked + label::after, ' . "\n";
					$css .= '.checkbox input[type="checkbox"]:checked + label::before, ' . "\n";
					$css .= '.checkbox input[type="radio"]:checked + label::before {' . "\n";
					$css .= '	background:' . $color . ';' . "\n";
					$css .= '	border-color:' . $color . ';' . "\n";
					$css .= "}\n";
					$css .= '.tabs-default > .nav-tabs > li.active > a {' . "\n";
					$css .= '	border-color: ' . $color . ";\n";
					$css .= "}\n";
					$css .= 'body a:focus, body a:active {' . "\n";
					$css .= '	color: ' . $color . ";\n";
					$css .= "}\n";
				} elseif( $tag == 'a:hover' ) {
					$css .= '.woocommerce .overlay a:hover, .woocommerce nav.woocommerce-pagination ul li:hover,' . "\n";
					$css .= '.woocommerce nav.woocommerce-pagination ul li:hover a, .woocommerce nav.woocommerce-pagination ul li:hover span, ' . "\n";
					$css .= '.single-product .related .yith-wcwl-add-button.btn:hover a {' . "\n";
					$css .= '	background-color: ' . $color . ";\n";
					$css .= '	border-color: ' . $color . ";\n";
					$css .= '	color: #ffffff;' . "\n";
					$css .= "}\n";
					$css .= '.pagination-wrap .pagination li a:hover, .pricing-table .list .def-btn:hover, ' . "\n";
					$css .= 'body .slick-slider .slick-prev:hover, body .slick-slider .slick-next:hover, .def-btn:hover, .btn:hover, ' . "\n";
					$css .= '.portfoliofilter a:hover .filterbutton, .portfoliocontent .content .overlay:hover, ' . "\n";
					$css .= '.panel-default > .panel-heading:hover, .single-fw-portfolio .def-btn:hover, ' . "\n";
					$css .= '.carousel .carousel-control .control-button:hover, .description .def-btn:hover, ' . "\n";
					$css .= '.tabs-default > .nav-tabs > li > a:hover, .tabs-default > .nav-tabs > li.active > a:hover,' . "\n";
					$css .= '.widget_tag_cloud a:hover, .widget_tag_cloud a:focus {' . "\n";
					$css .= '	background-color: ' . $color . ";\n";
					$css .= '	border-color: ' . $color . ";\n";
					$css .= "}\n";
					$css .= '.journal-content .detail .title h3:hover, .journal-content .detail .info a:hover, ' . "\n";
					$css .= 'body .more ul a:hover, .carousel .carousel-control .control-circle:hover .fa, ' . "\n";
					$css .= '.portfoliofilter a:hover .filterbutton, .widget li:hover, .widget li:focus {' . "\n";
					$css .= '	color: ' . $color . ";\n";
					$css .= "}\n";
					$css .= '.carousel .carousel-control .control-circle:hover, .form-control:focus, textarea:focus,' . "\n";
					$css .= '.widget li:hover, .widget li:focus {' . "\n";
					$css .= '	border-color: ' . $color . ";\n";
					$css .= "}\n";
				} elseif( $tag == 'a' ) {
					$css .= '.skillbars .bar-container .meter, .timeline > li .timeline-icon, .pricing-table .price, ' . "\n";
					$css .= '.journal-content .media .quote, .journal-content .detail .category, .map .map-title, ' . "\n";
					$css .= '.bg-default, .sidebar .title .shape, .widget_calendar tbody a, .cta-default-bg, ' . "\n";
					$css .= '.radio input[type="radio"] + label::after, .wishlist-count {' . "\n";
					$css .= '	background-color: ' . $color . ";\n";
					$css .= "}\n";
					$css .= 'body .slick-slider .slick-prev, body .slick-slider .slick-next, ' . "\n";
					$css .= '.skillbars .bar-container .meter:after, .pricing-table .list .def-btn, ' . "\n";
					$css .= '.panel-default > .panel-heading .badge, .panel-default > .panel-heading,' . "\n";
					$css .= '.panel-default, .border-default, .tabs-default > .nav-tabs, .tabs-default > .tab-content,' . "\n";
					$css .= '.panel-default > .panel-heading + .panel-collapse > .panel-body,' . "\n";
					$css .= '.panel-default > .panel-footer + .panel-collapse > .panel-body,' . "\n";
					$css .= '.text-default, .tabs-default > .nav-tabs > li > a > h5,' . "\n";
					$css .= '.journal-content .detail, .btn {' . "\n";
					$css .= '	border-color: ' . $color . ";\n";
					$css .= "}\n";
					$css .= '.icon i, .slick-slider .slick-prev:before, .slick-slider .slick-next:before, ' . "\n";
					$css .= '.counter h3, .timeline > li .timeline-label .place, ' . "\n";
					$css .= '.panel-default > .panel-heading .badge, .panel-default > .panel-heading,' . "\n";
					$css .= '.text-default, .tabs-default > .nav-tabs > li > a > h5,' . "\n";
					$css .= '.pricing-table .list .def-btn, .journal-content .detail .title h3, .btn {' . "\n";
					$css .= '	color: ' . $color . ";\n";
					$css .= '}';
				}
			}
			if( $selector == '#colophon' ) {
				$css .= "\n";
				if ( $tag == 'a:hover' ) {
					$css .= '.footer .links .social-icons .circle:hover {' . "\n";
					$css .= '	background-color: ' . $color . ";\n";
					$css .= '	border-color: ' . $color . ";\n";
					$css .= "}\n";
				} elseif ( $tag == 'a' ) {
					$css .= '.footer .top-text {' . "\n";
					$css .= '	background-color: ' . $color . ";\n";
					$css .= "}\n";
					$css .= '.footer, .footer .links .social-icons .circle {' . "\n";
					$css .= '	border-color: ' . $color . ";\n";
					$css .= "}\n";
					$css .= '.footer .links .social-icons .circle .fa {' . "\n";
					$css .= '	color: ' . $color . ";\n";
					$css .= '}';
				}
			}
			if( $tag == 'a' && $selector == '.section-title' ) {
				$css .= "\n";
				$css .= $selector . ' .title-icon-container .title-icon {' . "\n";
				$css .= '	background: ' . $color . ';' . "\n";
				$css .= '}';
			}
			$css .= "\n";
			$css .= $selector . ' ' . $tag . '{' . "\n";
			$css .= '	color: ' . $color . ';' . "\n";
			$css .= '	border-color: ' . $color . ';' . "\n";
			$css .= '}';
		}

		return $css;
	}

	private static function is_valid_hex_color( $color ) {
		return preg_match( '/^#[a-f0-9]{6}$/i', $color );
	}

	private static function generate_background_css( $selectors, $options ) {
		$css        = '';
		$bgImageCss = '';
		$noBgCss		= array(
			'.navbar-inverse .navbar-brand',
			'.navbar-inverse.scroll-fixed-navbar .navbar-nav li',
			'.banner .banner-content',
			'.banner .banner-content .buttons',
			'.section-title',
			'.title'
		);

		if ( isset($options['background-image']) && ! empty( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-image'] ) ) {
			$bgImageCss .= $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-image'];
			if ( ! empty( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-repeat'] ) ) {
				$bgImageCss .= ' ' . $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-repeat'];
			}
			$bgImageCss .= ', ';
		}
		$fallback = 'background-color: ' . $options['background-color']['primary'] . ';';
		if( isset($options['background-image']) ){
			$fallback .= ( ! empty( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-image'] ) ) ? 'background-image: ' . $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-image'] . ';' : '';
			$fallback .= ( ! empty( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-repeat'] ) ) ? 'background-repeat: ' . $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-repeat'] . ';' : '';
		}

		foreach ( $selectors as $selector ) {
			
			if ( in_array( $selector, $noBgCss ) ) return $css;

			if ( '#colophon' === $selector ) {
				list($r, $g, $b) = sscanf($options['background-color']['primary'], "#%02x%02x%02x");
				$css .= '#supplementary {
					background-color: rgba('.$r.','.$g.','.$b.',0.1);
				}';
			}

			if ( $options['background-color']['primary'] != $options['background-color']['secondary'] ) {
			//Gradient http://css-tricks.com/examples/CSS3Gradient/
			$css .= $selector . ' ' . '{
						 /* fallback  */
						 ' . $fallback . '
						  /* Safari 4-5, Chrome 1-9 */
						  background: ' . $bgImageCss . '-webkit-gradient(linear, left top, right top, from(' . $options['background-color']['primary'] . '), to(' . $options['background-color']['secondary'] . '));

						  /* Safari 5.1, Chrome 10+ */
						  background: ' . $bgImageCss . '-webkit-linear-gradient(left, ' . $options['background-color']['primary'] . ', ' . $options['background-color']['secondary'] . ');

						  /* Firefox 3.6+ */
						  background: ' . $bgImageCss . '-moz-linear-gradient(left, ' . $options['background-color']['primary'] . ', ' . $options['background-color']['secondary'] . ');

						  /* IE 10 */
						  background: ' . $bgImageCss . '-ms-linear-gradient(left, ' . $options['background-color']['primary'] . ', ' . $options['background-color']['secondary'] . ');

						  /* Opera 11.10+ */
						  background: ' . $bgImageCss . '-o-linear-gradient(left, ' . $options['background-color']['primary'] . ', ' . $options['background-color']['secondary'] . ');
					}';
			} else {
				$css .= $selector . ' ' . '{' . $fallback . '}';
			}
			//Background-image
			if( isset($options['background-image']) ){
				unset( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-image'] );
				unset( $options['background-image']['choices'][ $options['background-image']['value'] ]['css']['background-repeat'] );
				if ( sizeof( $options['background-image']['choices'][ $options['background-image']['value'] ]['css'] ) ) {
					$css .= $selector . ' ' . '{';
					foreach ( $options['background-image']['choices'][ $options['background-image']['value'] ]['css'] as $css_property => $css_value ) {
						$css .= $css_property . ': ' . $css_value . ';';
					}
					$css .= '}';
				}
			}
		}


		return $css;
	}

	private static function get_remote_fonts() {
		if ( ! sizeof( self::$remote_fonts ) ) {
			return '';
		}

		$html = "<link href='https://fonts.googleapis.com/css?family=";

		foreach ( self::$remote_fonts as $font => $styles ) {
			$html .= str_replace( ' ', '+', $font ) . ':' . implode( ',', $styles ) . '|';
		}

		$html = substr( $html, 0, - 1 );
		$html .= "' rel='stylesheet' type='text/css'>";

		return $html;
	}
}
