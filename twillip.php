<?php

	/*
	Copyright (c) 2010 John M. Hobbs

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
	*/

	class Twillip {

		protected static $ErrorStack = array();

		// Sourced From http://gdatatips.blogspot.com/2008/11/xml-php-pretty-printer.html
		/** Prettifies an XML string into a human-readable and indented work of art
		*  @param string $xml The XML as a string
		*  @param boolean $html_output True if the output should be escaped (for use in HTML)
		*/
		public static function xmlpp($xml, $html_output=false) {
				$xml_obj = new SimpleXMLElement($xml);
				$level = 4;
				$indent = 0; // current indentation level
				$pretty = array();

				// get an array containing each XML element
				$xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

				// shift off opening XML tag if present
				if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {
					$pretty[] = array_shift($xml);
				}

				foreach ($xml as $el) {
					if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {
							// opening tag, increase indent
							$pretty[] = str_repeat(' ', $indent) . $el;
							$indent += $level;
					} else {
						if (preg_match('/^<\/.+>$/', $el)) {
							$indent -= $level;  // closing tag, decrease indent
						}
						if ($indent < 0) {
							$indent += $level;
						}
						$pretty[] = str_repeat(' ', $indent) . $el;
					}
				}
				$xml = implode("\n", $pretty);
				return ($html_output) ? htmlentities($xml) : $xml;
		}

		public static function ErrorHandler ( $errno, $errstr, $errfile, $errline ) {
			if( ! ( error_reporting() & $errno ) ) { return; }

			switch ( $errno ) {
				case E_WARNING:
				case E_USER_WARNING:
						self::$ErrorStack[] = "<b>WARNING</b> [$errno] $errstr <b>($errfile, $errline)</b>\n";
						break;
				case E_NOTICE:
				case E_USER_NOTICE:
						self::$ErrorStack[] = "<b>NOTICE</b> [$errno] $errstr <b>($errfile, $errline)</b>\n";
						break;
				case E_RECOVERABLE_ERROR:
						self::$ErrorStack[] = "<b>ERROR</b> [$errno] $errstr <b>($errfile, $errline)</b>\n";
						break;
				case E_DEPRECATED:
				case E_USER_DEPRECATED:
						self::$ErrorStack[] = "<b>DEPRECATED</b> [$errno] $errstr <b>($errfile, $errline)</b>\n";
						break;
				default:
						self::$ErrorStack[] = "<b>UNKNOWN</b> [$errno] $errstr <b>($errfile, $errline)</b>\n";
						break;
			}
			return true;
		} // Twillip::ErrorHandler

		public static function Start () {
			ini_set( 'display_errors', true );
			set_error_handler( "Twillip::ErrorHandler" );
			ob_start();
		} // Twillip::Start

		public static function End () {
			$xml = ob_get_contents();
			ob_end_clean();
			header( 'Content-Type: text/html' );
?>
<html>
	<head>
		<title>Twillip - The Twilio PHP Developer Tool</title>
		<style type="text/css">
			html, body {
				margin: 0;
				padding: 0;
				height: 100%;
			}
			body {
				font-family: Helvetica, Arial, sans-serif;
			}
			#container {
				min-height:100%;
				position:relative;
			}
			#content {
				padding: 10px;
				padding-bottom: 25px;
			}
			#footer {
				width: 100%;
				text-align: center;
				font-size: 10px;
				position: absolute;
				bottom: 5px;
				height: 20px;
			}
			h1 {
				background: #E80000;
				padding: 10px;
				color: #FFF;
				margin: 0;
				border-bottom: 2px solid #777;
				text-shadow: 1px 1px 2px #777;
			}
			h2 {
				border-bottom: 1px solid #999;
				clear: both;
			}
			h2 > div {
				font-size: 12px;
				float: right;
				margin-top: 10px;
			}
			a {
				text-decoration: none;
			}
			label {
				font-weight: bold;
				display: inline-block;
				width: 100px;
				text-align: right;
				margin-right: 5px;
			}
			input {
				width: 150px;
				margin-right: 5px;
			}
			.parameter {
				float: left;
				width: 275px;
				background: #CECECE;
				margin: 5px;
				padding: 2px 3px;
				border: 1px solid #444;
			}
			.parameter a {
				color: #000;
			}
			.parameter a:hover {
				color: #FFF;
			}
			pre {
				background: #FFF;
				border: 1px solid #777;
				padding: 5px;
				color: #000;
				overflow: auto;
			}
			pre a {
				color: #000;
			}
			pre a:hover {
				text-decoration: underline;
			}
			.xml-tag {
				font-weight: bold;
				color: #800080;
			}
			.xml-attribute {
				color: #C48640;
			}
			.xml-attribute-value, .xml-attribute-value a {
				color: #00F !important;
			}
		</style>
		<script type="text/javascript">
			var Twillip = {
				Redirect: function ( url ) {
					form = document.getElementById( 'dev-form' );
					form.action = url;
					form.submit();
				},
				AddInput: function () {
					var i = prompt( "Name Of The Input?" );
					if( null == i ) { return; }

					var j = prompt( "Value Of The Input?" );
					if( null == j ) { return; }

					var container = document.createElement( 'div' );
					container.setAttribute( 'class', 'parameter' );
					container.setAttribute( 'id', 'input-' + i );

					var label = document.createElement( 'label' );
					label.innerHTML = i;
					label.setAttribute( 'for', i );

					var input = document.createElement( 'input' );
					input.setAttribute( 'type', 'text' );
					input.setAttribute( 'name', i );
					input.setAttribute( 'id', i );
					input.setAttribute( 'value', j );

					var link = document.createElement( 'a' );
					link.setAttribute( 'href', '#' );
					link.onclick = function () { Twillip.RemoveInput( i ); return false; }
					link.innerHTML = 'x';

					container.appendChild( label );
					container.appendChild( input );
					container.appendChild( link );

					document.getElementById( 'dev-form' ).appendChild( container );
				},
				RemoveInput: function ( name ) {
					var container = document.getElementById( 'input-' + name );
					container.parentNode.removeChild( container );
				},
				Reload: function () {
					Twillip.Redirect( '<?php $parts = explode( '?', $_SERVER['REQUEST_URI'] ); echo $parts[0]; ?>' );
				}
			};
		</script>
	</head>
	<body>
		<div id="container">
			<h1>Twillip - The Twilio PHP Developer Tool</h1>
			<div id="content">
				<h2>
					Input
					<div>
						<a href="#" onclick="Twillip.AddInput(); return false;">Add New Input</a> |
						<a href="#" onclick="Twillip.Reload(); return false;">Reload Page</a>
					</div>
				</h2>
				<form id="dev-form" method="GET">
				<?php
					ksort( $_REQUEST );
					foreach( $_REQUEST as $key => $value ) {
						print '
							<div class="parameter" id="input-' . htmlspecialchars( $key ) .'">
								<label for="' . htmlspecialchars( $key ) . '">' . htmlspecialchars( $key ) . '</label>
								<input type="text" name="' . htmlspecialchars( $key ) . '" id="' . htmlspecialchars( $key ) . '" value="' . htmlspecialchars( $value ) . '" />
								<a href="#" onclick="Twillip.RemoveInput( \'' . htmlspecialchars( $key ) .'\' ); return false;">x</a>
							</div>
						';
					}
				?>
				</form>
				<?php
					if( 0 != count( self::$ErrorStack ) ) {
						print '<h2>Errors</h2><pre>';
						foreach( self::$ErrorStack as $Error ) { print $Error; }
						print '</pre>';
					}
				?>
				<h2>Output</h2>
				<pre><?php
					// Escape HTML Codes
					$out = htmlspecialchars( self::xmlpp( $xml ) );
					// Make the Redirect tags into links
					$out = preg_replace( "/&lt;Redirect&gt;(.*?)&lt;\/Redirect&gt;/", "&lt;Redirect&gt;<a href=\"#\" onclick=\"Twillip.Redirect('$1'); return false;\">$1</a>&lt;/Redirect&gt;", $out );
					// Make Conference waitURL's into links
					$out = preg_replace( "/waitUrl=&quot;(.*?)&quot;/", "waitUrl=&quot;<a href=\"#\" onclick=\"Twillip.Redirect('$1'); return false;\">$1</a>&quot;", $out );
					// Make Play tags open in a new window
					$out = preg_replace( '/&lt;Play(.*?)&gt;(.*?)&lt;\/Play&gt;/', '&lt;Play$1&gt;<a href="$2" target="_blank">$2</a>&lt;/Play&gt;', $out );
					// Add some classes to our pretty XML
					$out = preg_replace( '/&lt;(.*?)&gt;/', '<span class="xml-tag">&lt;$1&gt;</span>', $out );
					$out = preg_replace( '/([^ ]*?)=&quot;(.*?)&quot;/', '<span class="xml-attribute">$1=&quot;<span class="xml-attribute-value">$2</span>&quot;</span>', $out );
					echo $out;
				?></pre>
			</div><!--// #content //-->
			<div id="footer">Twillip &copy; 2010 John Hobbs - <a href="http://github.com/jmhobbs/Twillip">http://github.com/jmhobbs/Twillip</a></div>
		</div><!--// #container //-->
	</body>
</html>
<?php
	} // Twillip::End
} // class Twillip