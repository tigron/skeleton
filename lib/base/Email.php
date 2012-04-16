<?php
/**
 * Email class
 *
 * Send emails
 *
 * @package %%PACKAGE%%
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @version $Id$
 */

require_once 'Mail.php';
require_once 'Mail/mime.php';

require_once LIB_PATH . '/base/Email/Template.php';
require_once LIB_PATH . '/base/File/Store.php';

class Email {
	/**
	 * Email type
	 *
	 * @access private
	 * @var string $type
	 */
	private $type = '';

	/**
	 * Sender
	 *
	 * @access private
	 * @var array $sender
	 */
	private $sender = null;

	/**
	 * Recipients
	 *
	 * @access private
	 * @var array $recipients
	 */
	private $recipients = array();

	/**
	 * Assigned variables
	 *
	 * @access private
	 * @var array $assigns
	 */
	private $assigns = array();

	/**
	 * Files
	 *
	 * @access private
	 * @var array $files
	 */
	private $files = array();

	/**
	 * Manual files
	 *
	 * @access private
	 * @var array $manual_files
	 */
	private $manual_files = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $type
	 */
	public function __construct($type) {
		if ($type === null) {
			throw new Exception('No email type specified');
		}
		$this->type = $type;
	}

	/**
	 * Add recipient
	 *
	 * The passed object must contain the following properties:
	 * - firstname
	 * - lastname
	 * - email
	 *
	 * Optionally, it can contain language as well, if not, the default language is used.
	 *
	 * @access public
	 * @param mixed $recipient
	 * @param string $type Recipient type, defaults to 'to'
	 */
	public function add_recipient($recipient, $type = 'to') {
		$config = Config::get();

		try {
			$language = $recipient->language;
		} catch (Exception $e) {
			$language = Language::get_by_name_short($config->default_language);
		}

		$this->add_recipient_address($recipient->firstname . ' ' . $recipient->lastname, $recipient->email, $language, $type);
	}

	/**
	 * Add recipient_address
	 *
	 * @access private
	 * @param string $name
	 * @param string $email
	 * @param Language $language
	 * @param string $type Recipient type, defaults to 'to'
	 */
	public function add_recipient_address($name = '', $email, Language $language = null, $type = 'to') {
		if ($language === null) {
			$language = Language::get_by_id(1);
		}

		$this->recipients[$type][] = array(
			'name' => $name,
			'email' => $email,
			'language' => $language
		);
	}

	/**
	 * Add a file
	 *
	 * @access public
	 * @param File $file
	 */
	public function add_file($file) {
		if (is_a($file, 'File')) {
			$this->files[] = $file;
		} else {
			$this->manual_files[] = $file;
		}
	}

	/**
	 * Set sender
	 *
	 * @param string $email
	 * @param string $address
	 */
	public function set_sender($email, $name = null) {
		$this->sender = array(
			'name' => $name,
			'email' => $email,
		);
	}

	/**
	 * Assign
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public function assign($key, $value) {
		$this->assigns[$key] = $value;
	}

	/**
	 * Send email
	 *
	 * @access public
	 */
	public function send() {
		if (!$this->validate($errors)) {
			throw new Exception('Cannot send email, Mail not validated. Errored fields: ' . implode(', ', $errors));
		}

		$language = $this->recipients['to'][0]['language'];
		$template = new Email_Template($this->type, $language);

		foreach ($this->assigns as $key => $value) {
			$template->assign($key, $value);
		}

		$mime = new Mail_Mime(PHP_EOL);

		$mime->setHtmlBody($template->render('html'));
		$mime->setTxtBody($template->render('text'));
		
		$this->add_html_images($mime);
		$this->attach_files($mime);

		$body = $mime->get(
			array (
				'head_charset' => 'UTF-8',
				'text_charset' => 'UTF-8',
				'html_charset' => 'UTF-8',
				'html_encoding' => 'base64',
			)
		);

		if (isset($this->sender['name'])) {
			$sender = mb_encode_mimeheader($this->sender['name'], 'ISO-8859-1') . ' <' . $this->sender['email'] . '>';
		} else {
			$sender = $this->sender['email'];
		}

		$headers = array(
			'From' => $sender,
			'Subject' => $template->render('subject'),
			'X-MailType' => $this->type,
		);

		$config = Config::Get();
		if ($config->archive_mailbox != '') {
			$this->add_recipient_address('', $config->archive_mailbox, null, 'bcc');
		}

		foreach ($this->recipients as $type => $recipients) {
			foreach ($recipients as $recipient) {
				$addresses[] = mb_encode_mimeheader($recipient['name'], 'ISO-8859-1') . ' <' . $recipient['email'] . '>';
			}

			$headers[ucfirst($type)] = implode(', ', $addresses);
		}

		$headers = $mime->headers($headers);

		$mail = new Mail();
		$mail = $mail->factory('mail');
		$mail->_params = '-f ' . $this->sender['email'] . ' -r ' . $this->sender['email'];

		$mail->send($headers['To'], $headers, $body);
		unset($template);
	}

	/**
	 * Validate
	 *
	 * @access private
	 * @return bool $validated
	 * @param array $errors
	 */
	public function validate(&$errors = array()) {
		if (!isset($this->type)) {
			$errors[] = 'type';
		}

		if (!isset($this->sender['email'])) {
			$errors[] = 'sender[email]';
		}

		if (!isset($this->recipients) or count($this->recipients) == 0) {
			$errors[] = 'recipients';
		}

		if (count($errors) == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add embedded HTML images (image dir)
	 *
	 * @access private
	 */
	private function add_html_images(&$mime) {
		$path = STORE_PATH . '/email/media/';
		
		$html_body = $mime->getHTMLBody();

		if ($handle = opendir($path)) {
			while (false !== ($file = readdir($handle))) {
				if (substr($file,0,1) != '.' && strpos($html_body, $file) !== false) {
					$mime->addHTMLImage($path . $file, Util::mime_type($path . $file), $file);
				}
			}
		}

		closedir($handle);
	}

	/**
	 * Attach files
	 *
	 * @access private
	 */
	private function attach_files(&$mime) {
		foreach ($this->files as $file) {
			$mime->addAttachment($file->get_contents(), $file->mimetype, $file->name, false);
		}

		foreach ($this->manual_files as $file) {
			$mime->addAttachment($file, Util::mime_type($file));
		}
	}
}
?>