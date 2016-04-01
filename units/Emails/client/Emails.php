<?php
	class Emails extends Unit
	{
		private $_unit = "";				//Unit name
		public $_config = "";				//Default config
		public $_emails_items = array();	// All emails from base
		
		public	$useragent		= "Lazy CMS";
		public	$smtp_timeout	= 5;		// SMTP Timeout in seconds
		public	$wordwrap		= TRUE;		// TRUE/FALSE  Turns word-wrap on/off
		public	$wrapchars		= "76";		// Number of characters to wrap at. <=76
		private	$mailtype		= "html";	// text/html  Defines email formatting
		public	$charset		= "utf-8";	// Default char set: iso-8859-1 or us-ascii
		public	$validate		= TRUE;		// TRUE/FALSE.  Enables email validation
		public	$priority		= 3;		// Default priority (1 - 5)
		private	$newline		= "\r\n";	// Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)
		private	$base64_images	= FALSE;	// Convert images in letter on src html text on base64 or not..
		
		
		private $_last_id		= 0;
		private $_last_idname	= '';
		private $_last_mailpath	= '';
		private $_last_host		= '';
		private $_last_user		= '';
		private $_last_pass		= '';
		private $_last_port		= '';
		
		private	$_smtp_connect	= "";
		public	$mailpath		= "/usr/sbin/sendmail";	// Sendmail path
		private	$protocol		= "smtp";	// mail/sendmail/smtp
		public	$smtp_host		= "";		// SMTP Server.  Example: mail.earthlink.net
		public	$smtp_user		= "";		// SMTP Username
		public	$smtp_pass		= "";		// SMTP Password
		public	$smtp_port		= "465";	// SMTP Port
		
		
		private	$_safe_mode		= FALSE;
		private	$_subject		= "";
		private	$_body			= "";
		private	$_finalbody		= "";
		private	$_alt_boundary	= "";
		private	$_atc_boundary	= "";
		private	$_header_str	= "";
		private $_header_send	= "";
		private	$_encoding		= "8bit";
		private	$_IP			= FALSE;
		private	$_smtp_auth		= FALSE;
		private	$_replyto_flag	= FALSE;
		private	$_debug_msg		= array();
		private	$_to_array		= array();
		private	$_cc_array		= array();
		private	$_bcc_array		= array();
		private	$_headers		= array();
		private	$_attach_name	= array();
		private	$_attach_type	= array();
		private	$_attach_disp	= array();
		private	$_protocols		= array('mail', 'sendmail', 'smtp');
		private	$_base_charsets	= array('us-ascii', 'iso-2022-');	// 7-bit charsets (excluding language suffix)
		private	$_bit_depths	= array('7bit', '8bit');
		private	$_priorities	= array('1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)');
		
		function __construct()
		{
			parent::__construct();
			$this->init();
		}
		
		function __destruct()
		{
			if ($this->_smtp_connect !== FALSE)
			{
				
			}
		}
		
		private function init()
		{
			$this->_unit = strtolower(get_class($this));
			$this->_config = System::$lazy['_config']['Emails']['cfg'];
			
			foreach ($this->_config as $key => $val)
			{
				if (isset($this->$key))
				{
					if (method_exists($this, $key))
					{
						$this->$key($val);
					}
					else
					{
						$this->$key = $val;
					}
				}
			}
			
			$this->_safe_mode = ((boolean)@ini_get("safe_mode") === FALSE) ? FALSE : TRUE;
			
			$this->clear();
			$this->get(1);
		}
		
		public function _debug()
		{
			_debug(get_object_vars($this));
		}
		
		public function output($info, $params = array())
		{
			System::$lazy['this_block'] = $info['folder'];
			return $this->files->block($info, $this->unit);
		}
		
		public function get($emails_id)
		{
			if (! isset($this->_emails_items[$emails_id]))
			{
				$this->db->join('emails_texts', 'emails_texts.items_id = emails_items.items_id');
				$this->db->where('emails_items.items_id', $emails_id);
				$this->db->where('langs_code', Langs::$_config['this']);
				$this->db->or_where('langs_code', Langs::$_config['default'][TYPE]);
				$this->db->where('emails_items.items_id', $emails_id);
				$this->db->order_by_field('langs_code', array(Langs::$_config['this'], Langs::$_config['default'][TYPE]));
				$this->db->limit(1);
				$result = $this->db->get('emails_items');
				
				$this->_emails_items[$emails_id] = $result->row_array();
				
			}
			
			return $this->_emails_items[$emails_id]; 
		}
		
		public function send($emails_id, $keys = array(), $langs_code = "")
		{
			
		}
		
		public function clear($clear_attachments = FALSE)
		{
			$this->_subject		= "";
			$this->_body		= "";
			$this->_finalbody	= "";
			$this->_header_str	= "";
			$this->_replyto_flag = FALSE;
			$this->_to_array	= array();
			$this->_cc_array	= array();
			$this->_bcc_array	= array();
			$this->_headers		= array();
			$this->_debug_msg	= array();
	
			$this->_headers['User-Agent'] = $this->useragent;
			$this->_headers['Date'] = $this->_set_date();
	
			if ($clear_attachments !== FALSE)
			{
				$this->_attach_name = array();
				$this->_attach_type = array();
				$this->_attach_disp = array();
			}
	
			return $this;
		}
		
		private function _build_headers()
		{
			$this->_headers['X-Sender'] = $this->_clean_email($this->_headers['From']);
			$this->_headers['X-Mailer'] = $this->useragent;
			$this->_headers['X-Priority'] = $this->_priorities[$this->priority - 1];
			$this->_headers['Message-ID'] = "<".uniqid('').strstr(str_replace(array('>', '<'), '', $this->_headers['Return-Path']), '@').">";
			$this->_headers['Mime-Version'] = '1.0';
		}
		
		/***************** main functions ******************/
		public function from($from, $name = '')
		{
			if (preg_match( "/(.*)<(.+@.+\..+)>/i", $from, $match))
			{
				$from = $match['2'];
				if ($name == '') {
					$name = $match['1'];
				}
			}
			
			if ($this->validate)
			{
				$from = $this->validate_email($from);
			}
			
			if ( ! preg_match('/[\200-\377]/', $name))
			{
				$name = '"'.addcslashes($name, "\0..\37\177'\"\\").'"';
			}
			
			if ($from != false && $from != '')
			{
				$this->_headers['From'] = $name.' <'.$from.'>';
				$this->_headers['Return-Path'] = '<'.$from.'>';
			}
			
			return $this;
		}
		
		public function reply_to($replyto, $name = '')
		{
			if (preg_match( '/(.*)<(.+@.+\..+)>/', $replyto, $match))
			{
				$replyto = $match['2'];
				if ($name == '') {
					$name = $match['1'];
				}
			}
	
			if ($this->validate)
			{
				$replyto = $this->validate_email($replyto);
			}
			
			if ( ! preg_match('/[\200-\377]/', $name))
			{
				$name = '"'.addcslashes($name, "\0..\37\177'\"\\").'"';
			}
			
			if ($replyto != false && $replyto != '')
			{
				$this->_headers['Reply-To'] = $name.' <'.$replyto.'>';
				$this->_replyto_flag = TRUE;
			}
	
			return $this;
		}
		
		public function to($to)
		{
			$to = $this->_str_to_array($to);
			$to = $this->_clean_email($to);
			
			foreach ($to as $key => $value) {
				$to[$key] = trim(strtolower($value));
			}
			if ($this->validate)
			{
				$to = $this->validate_email($to);
			}
			
			if ($to != false && count($to))
			{
				$this->_to_array = array_unique(array_merge($this->_to_array, $to));
				sort($this->_to_array);
			}
			
			return $this;
		}
		
		public function cc($cc)
		{
			$cc = $this->_str_to_array($cc);
			$cc = $this->_clean_email($cc);
			
			foreach ($cc as $key => $value) {
				$cc[$key] = trim(strtolower($value));
			}
			if ($this->validate)
			{
				$cc = $this->validate_email($cc);
			}
			
			if ($cc != false && count($cc))
			{
				$this->_cc_array = array_unique(array_merge($this->_cc_array, $cc));
				sort($this->_cc_array);
			}
			return $this;
		}
		
		public function bcc($bcc)
		{
			$bcc = $this->_str_to_array($bcc);
			$bcc = $this->_clean_email($bcc);
			
			foreach ($bcc as $key => $value) {
				$bcc[$key] = trim(strtolower($value));
			}
			if ($this->validate)
			{
				$bcc = $this->validate_email($bcc);
			}
			
			if ($cc != false && count($bcc))
			{
				$this->_bcc_array = array_unique(array_merge($this->_to_array, $bcc));
				sort($this->_bcc_array);
			}
			return $this;
		}
		
		public function subject($subject)
		{
			$subject = str_replace(array("\r", "\n"), '', $subject);
			$limit = 68 - strlen($this->charset);
			$convert = array('_', '=', '?');
			$output = '';
			$temp = '';
	
			for ($i = 0, $length = strlen($subject); $i < $length; $i++)
			{
				$char = substr($subject, $i, 1);
				$ascii = ord($char);
				if ($ascii < 32 OR $ascii > 126 OR in_array($char, $convert))
				{
					$char = '='.dechex($ascii);
				}
				if ($ascii == 32)
				{
					$char = '_';
				}
				if ((strlen($temp) + strlen($char)) >= $limit)
				{
					$output .= $temp."\r\n";
					$temp = '';
				}
				$temp .= $char;
			}
			$subject = $output.$temp;
			$this->_header["Subject"] = trim(preg_replace('/^(.*)$/m', ' =?'.$this->charset.'?Q?$1?=', $subject));
			
			return $this;
		}
		
		public function message($body)
		{
			$this->_body = stripslashes(rtrim(str_replace("\r", "", $body)));
			return $this;
		}
		
		public function attach($filename, $disposition = 'attachment')
		{
			if (file_exists($filename))
			{
				$this->_attach_name[] = $filename;
				$this->_attach_type[] = $this->_mime_types(pathinfo($filename, PATHINFO_EXTENSION));
				$this->_attach_disp[] = $disposition; // Can also be 'inline'  Not sure if it matters
			}
			return $this;
		}
		/**************  set functions ***********************/ 
		
		public function protocol($protocol)
		{
			$this->protocol = ( ! in_array($protocol, array('mail', 'sendmail', 'smtp'))) ? 'mail' : strtolower($protocol);
		}
		
		public function mailtype($mailtype)
		{
			$this->mailtype = ( $mailtype == 'html' ) ? 'html' : 'text';
		}
		
		public function priority($n = 3)
		{
			if ( ! is_numeric($n))
			{
				$this->priority = 3;
				return;
			}
	
			if ($n < 1 OR $n > 5)
			{
				$this->priority = 3;
				return;
			}
	
			$this->priority = $n;
		}
		
		public function base64_images($base64_images = FALSE)
		{
			$this->base64_images = (bool)$base64_images;
		}
		
		public function newline($newline = "\n")
		{
			if ($newline != "\n" AND $newline != "\r\n" AND $newline != "\r")
			{
				$this->newline	= "\r\n";
				return;
			}
			$this->newline	= $newline;
		}
		
		public function charsets($charset = 'utf-8')
		{
			$php_charsets = array('UCS-4', 'UCS-4BE', 'UCS-4LE', 'UCS-2', 'UCS-2BE', 'UCS-2LE', 'UTF-32', 'UTF-32BE', 'UTF-32LE', 'UTF-16', 'UTF-16BE', 'UTF-16LE', 'UTF-7', 'UTF7-IMAP', 'UTF-8', 'ASCII', 'EUC-JP', 'SJIS', 'eucJP-win', 'SJIS-win', 'ISO-2022-JP', 'ISO-2022-JP-MS', 'CP932', 'CP51932', 'SJIS-mac', 'SJIS-DOCOMO', 'SJIS-KDDI', 'SJIS-SOFTBANK', 'UTF-8-DOCOMO', 'UTF-8-KDDI', 'UTF-8-SOFTBANK', 'ISO-2022-JP-KDDI', 'JIS', 'JIS-ms', 'CP50220', 'CP50220raw', 'CP50221', 'CP50222', 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'byte2be', 'BYTE2LE', 'BYTE4BE', 'BYTE4LE', 'BASE64', 'HTML-ENTITIES', '7BIT', '8BIT', 'EUC-CN', 'CP936', 'GB18030', 'HZ', 'EUC-TW', 'CP950', 'BIG-5', 'EUC-KR', 'UHC (CP949)', 'ISO-2022-KR', 'WINDOWS-1251', 'CP1251', 'WINDOWS-1252','CP1252', 'CP866', 'KOI8-R');
			$this->charset = ( ! in_array(strtoupper($charset), $php_charsets)) ? 'UTF-8' : strtoupper($charset);
		}
		
		private function _set_date()
		{
			$timezone = date("Z");
			$operator = (strncmp($timezone, '-', 1) == 0) ? '-' : '+';
			$timezone = abs($timezone);
			$timezone = floor($timezone/3600) * 100 + ($timezone % 3600 ) / 60;
			
			return sprintf("%s %s%04d", date("D, j M Y H:i:s"), $operator, $timezone);
		}
		
		private function _str_to_array($email)
		{
			if ( ! is_array($email))
			{
				if (strpos($email, ',') !== FALSE)
				{
					$email = preg_split('/[\s,]/', $email, NULL, PREG_SPLIT_NO_EMPTY);
				}
				else
				{
					$email = trim($email);
					settype($email, "array");
				}
			}
			else
			{
				$responce = array();
				$return  = array();
				foreach ($email as $value) {
					$responce = $this->_str_to_array($value);
					foreach ($responce as $value) {
						$return[] = $value;
					}
				}
				$email = $return;
			}
			return $email;
		}
		
		private function _clean_email($email)
		{
			if ( ! is_array($email))
			{
				if (preg_match('/\<(.+@.+\..+)\>/', $email, $match))
				{
					return $match['1'];
				}
				else
				{
					return $email;
				}
			}
	
			$clean_email = array();
	
			foreach ($email as $addy)
			{
				if (preg_match( '/\<(.+@.+\..+)\>/', $addy, $match))
				{
					$clean_email[] = $match['1'];
				}
				else
				{
					$clean_email[] = $addy;
				}
			}
	
			return $clean_email;
		}
		
		public function validate_email($email)
		{
			if ( ! is_array($email))
			{
				return ( ! preg_match("/^(.+@.+\..+)$/i", $email, $match)) ? FALSE : $match[1];
			}
			else {
				$new_emails = array();
				foreach ($email as $val)
				{
					if ($val = $this->validate_email($val))
					{
						$new_emails[] = $val;
					}
				}
				
				return $new_emails;
			}
			
			return FALSE;
		}
		
		private function _check_connect($emails_id)
		{
			if ($this->mailtype == 'smpt')
			{
				if ($emails_id === $this->_last_id || $emails_id === $this->_last_idname)
				{
					
				}
				if ($this->_smtp_connect === FALSE)
				{
					
				}
			}
			
			return TRUE;
		}
		
		private function _set_boundaries()
		{
			$this->_alt_boundary = "B_ALT_".uniqid(''); // multipart/alternative
			$this->_atc_boundary = "B_ATC_".uniqid(''); // attachment boundary
		}
		
		private function _write_headers()
		{
			if ($this->protocol == 'mail')
			{
				$this->_subject = $this->_headers['Subject'];
				unset($this->_headers['Subject']);
			}
	
			reset($this->_headers);
			$this->_header_str = "";
	
			foreach ($this->_headers as $key => $val)
			{
				$val = trim($val);
				
				if ($val != "")
				{
					$this->_header_str .= $key.": ".$val.$this->newline;
				}
			}
			
			if ($this->_get_protocol() == 'mail')
			{
				$this->_header_str = rtrim($this->_header_str);
			}
		}
	
		// --------------------------------------------------------------------
	
		/**
		 * Build Final Body and attachments
		 *
		 * @access	private
		 * @return	void
		 */
		private function _build_message()
		{
			if ($this->wordwrap === TRUE  AND  $this->mailtype != 'html')
			{
				$this->_body = $this->word_wrap($this->_body);
			}
	
			$this->_set_boundaries();
			$this->_write_headers();
	
			$hdr = ($this->_get_protocol() == 'mail') ? $this->newline : '';
			$body = '';
	
			switch ($this->_get_content_type())
			{
				case 'plain' :
	
					$hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
					$hdr .= "Content-Transfer-Encoding: " . $this->_get_encoding();
	
					if ($this->_get_protocol() == 'mail')
					{
						$this->_header_str .= $hdr;
						$this->_finalbody = $this->_body;
					}
					else
					{
						$this->_finalbody = $hdr . $this->newline . $this->newline . $this->_body;
					}
	
					return;
	
				break;
				case 'html' :
	
					if ($this->send_multipart === FALSE)
					{
						$hdr .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
						$hdr .= "Content-Transfer-Encoding: quoted-printable" . $this->newline . $this->newline;
					}
					else
					{
						$hdr .= "Content-Type: multipart/alternative; boundary=\"" . $this->_alt_boundary . "\"" . $this->newline . $this->newline;
	
						$body .= $this->_get_mime_message() . $this->newline . $this->newline;
						$body .= "--" . $this->_alt_boundary . $this->newline;
	
						$body .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
						$body .= "Content-Transfer-Encoding: " . $this->_get_encoding() . $this->newline . $this->newline;
						$body .= $this->_get_alt_message() . $this->newline . $this->newline . "--" . $this->_alt_boundary . $this->newline;
	
						$body .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
						$body .= "Content-Transfer-Encoding: quoted-printable" . $this->newline . $this->newline;
					}
	
					$this->_finalbody = $body . $this->_prep_quoted_printable($this->_body) . $this->newline . $this->newline;
	
	
					if ($this->_get_protocol() == 'mail')
					{
						$this->_header_str .= $hdr;
					}
					else
					{
						$this->_finalbody = $hdr . $this->_finalbody;
					}
	
	
					if ($this->send_multipart !== FALSE)
					{
						$this->_finalbody .= "--" . $this->_alt_boundary . "--";
					}
	
					return;
	
				break;
				case 'plain-attach' :
	
					$hdr .= "Content-Type: multipart/".$this->multipart."; boundary=\"" . $this->_atc_boundary."\"" . $this->newline . $this->newline;
	
					if ($this->_get_protocol() == 'mail')
					{
						$this->_header_str .= $hdr;
					}
	
					$body .= $this->_get_mime_message() . $this->newline . $this->newline;
					$body .= "--" . $this->_atc_boundary . $this->newline;
	
					$body .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
					$body .= "Content-Transfer-Encoding: " . $this->_get_encoding() . $this->newline . $this->newline;
	
					$body .= $this->_body . $this->newline . $this->newline;
	
				break;
				case 'html-attach' :
	
					$hdr .= "Content-Type: multipart/".$this->multipart."; boundary=\"" . $this->_atc_boundary."\"" . $this->newline . $this->newline;
	
					if ($this->_get_protocol() == 'mail')
					{
						$this->_header_str .= $hdr;
					}
	
					$body .= $this->_get_mime_message() . $this->newline . $this->newline;
					$body .= "--" . $this->_atc_boundary . $this->newline;
	
					$body .= "Content-Type: multipart/alternative; boundary=\"" . $this->_alt_boundary . "\"" . $this->newline .$this->newline;
					$body .= "--" . $this->_alt_boundary . $this->newline;
	
					$body .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
					$body .= "Content-Transfer-Encoding: " . $this->_get_encoding() . $this->newline . $this->newline;
					$body .= $this->_get_alt_message() . $this->newline . $this->newline . "--" . $this->_alt_boundary . $this->newline;
	
					$body .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
					$body .= "Content-Transfer-Encoding: quoted-printable" . $this->newline . $this->newline;
	
					$body .= $this->_prep_quoted_printable($this->_body) . $this->newline . $this->newline;
					$body .= "--" . $this->_alt_boundary . "--" . $this->newline . $this->newline;
	
				break;
			}
	
			$attachment = array();
	
			$z = 0;
	
			for ($i=0; $i < count($this->_attach_name); $i++)
			{
				$filename = $this->_attach_name[$i];
				$basename = basename($filename);
				$ctype = $this->_attach_type[$i];
	
				if ( ! file_exists($filename))
				{
					$this->_set_error_message('lang:email_attachment_missing', $filename);
					return FALSE;
				}
	
				$h  = "--".$this->_atc_boundary.$this->newline;
				$h .= "Content-type: ".$ctype."; ";
				$h .= "name=\"".$basename."\"".$this->newline;
				$h .= "Content-Disposition: ".$this->_attach_disp[$i].";".$this->newline;
				$h .= "Content-Transfer-Encoding: base64".$this->newline;
	
				$attachment[$z++] = $h;
				$file = filesize($filename) +1;
	
				if ( ! $fp = fopen($filename, FOPEN_READ))
				{
					$this->_set_error_message('lang:email_attachment_unreadable', $filename);
					return FALSE;
				}
	
				$attachment[$z++] = chunk_split(base64_encode(fread($fp, $file)));
				fclose($fp);
			}
	
			$body .= implode($this->newline, $attachment).$this->newline."--".$this->_atc_boundary."--";
	
	
			if ($this->_get_protocol() == 'mail')
			{
				$this->_finalbody = $body;
			}
			else
			{
				$this->_finalbody = $hdr . $body;
			}
	
			return;
		}
	
		// --------------------------------------------------------------------
	
		/**
		 * Prep Quoted Printable
		 *
		 * Prepares string for Quoted-Printable Content-Transfer-Encoding
		 * Refer to RFC 2045 http://www.ietf.org/rfc/rfc2045.txt
		 *
		 * @access	private
		 * @param	string
		 * @param	integer
		 * @return	string
		 */
		private function _prep_quoted_printable($str, $charlim = '')
		{
			// Set the character limit
			// Don't allow over 76, as that will make servers and MUAs barf
			// all over quoted-printable data
			if ($charlim == '' OR $charlim > '76')
			{
				$charlim = '76';
			}
	
			// Reduce multiple spaces
			$str = preg_replace("| +|", " ", $str);
	
			// kill nulls
			$str = preg_replace('/\x00+/', '', $str);
	
			// Standardize newlines
			if (strpos($str, "\r") !== FALSE)
			{
				$str = str_replace(array("\r\n", "\r"), "\n", $str);
			}
	
			// We are intentionally wrapping so mail servers will encode characters
			// properly and MUAs will behave, so {unwrap} must go!
			$str = str_replace(array('{unwrap}', '{/unwrap}'), '', $str);
	
			// Break into an array of lines
			$lines = explode("\n", $str);
	
			$escape = '=';
			$output = '';
	
			foreach ($lines as $line)
			{
				$length = strlen($line);
				$temp = '';
	
				// Loop through each character in the line to add soft-wrap
				// characters at the end of a line " =\r\n" and add the newly
				// processed line(s) to the output (see comment on $crlf class property)
				for ($i = 0; $i < $length; $i++)
				{
					// Grab the next character
					$char = substr($line, $i, 1);
					$ascii = ord($char);
	
					// Convert spaces and tabs but only if it's the end of the line
					if ($i == ($length - 1))
					{
						$char = ($ascii == '32' OR $ascii == '9') ? $escape.sprintf('%02s', dechex($ascii)) : $char;
					}
	
					// encode = signs
					if ($ascii == '61')
					{
						$char = $escape.strtoupper(sprintf('%02s', dechex($ascii)));  // =3D
					}
	
					// If we're at the character limit, add the line to the output,
					// reset our temp variable, and keep on chuggin'
					if ((strlen($temp) + strlen($char)) >= $charlim)
					{
						$output .= $temp.$escape.$this->crlf;
						$temp = '';
					}
	
					// Add the character to our temporary line
					$temp .= $char;
				}
	
				// Add our completed line to the output
				$output .= $temp.$this->crlf;
			}
	
			// get rid of extra CRLF tacked onto the end
			$output = substr($output, 0, strlen($this->crlf) * -1);
	
			return $output;
		}
		
	}
	
/* End of file emails_client.php */
/* Location: ./units/emails/client/emails_client.php */