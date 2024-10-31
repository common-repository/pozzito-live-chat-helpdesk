<?php
/**
 * WP POZZITO Plugin
 *
 * Copyright (c) 2017 POZZITO (www.pozzito.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package WordPress
 */

 /*
    Plugin Name: Pozzito - Live Chat & Helpdesk
    Plugin URI: http://pozzito.com
    Description: Engage with your customer! Add clean and easy-to-use plugin for your WordPress website; a live chat, helpdesk, online customer service and support.
    Version: 1.1.0
    Author: Team Pozzito
    Text Domain: wp-pozzito-plugin
    License: MIT
*/

// Current version number
if ( ! defined( 'WP_POZZITO_VERSION' ) ) {
	define( 'WP_POZZITO_VERSION', '1.1' );
}

require_once( 'pozzito.php' );

/**
* POZZITO PUBLIC WIDGET
*/
if ( ! is_admin() ) {
	$pozzito_plugin = new PozzitoPlugin();
}


/**
* POZZITO ADMIN DASHBOARD OPTIONS MENU
*/
if ( is_admin() ) {
	$pozzito_admin = new PozzitoAdmin();
}
