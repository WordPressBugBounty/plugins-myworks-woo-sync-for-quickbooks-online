<?php

/**
 * 
 *
 * Copyright (c) 2010 Keith Palmer / ConsoliBYTE, LLC.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.opensource.org/licenses/eclipse-1.0.php
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 */

/**
 * 
 * 
 * 
 */
class QuickBooks_ErrorHandler
{
	/**
	 * 
	 */
	static public function handle($errno, $errstr, $errfile, $errline)
	{
		print('
			ERROR: [' . esc_html($errno) . '] ' . esc_html($errstr) . '
        	Fatal error on line ' . esc_html($errline) . ' in file ' . esc_html($errfile) . ', PHP v' . PHP_VERSION . ' (' . esc_html(PHP_OS) . ')
		');
		
		exit(1);
	}
}

