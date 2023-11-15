<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
 */

/**
 * Session class
 */
class Session
{
    protected $adaptor;
    protected $session_id;
    public $data = array();

    /**
     * Constructor
     *
     * @param	string	$adaptor
     * @param	object	$registry
     */
    public function __construct($adaptor, $registry = '')
    {
        $class = 'Session\\' . $adaptor;

        if (class_exists($class)) {
            if ($registry) {
                $this->adaptor = new $class($registry);
            } else {
                $this->adaptor = new $class();
            }

            register_shutdown_function(array($this, 'close'));
        } else {
            trigger_error('Error: Could not load cache adaptor ' . $adaptor . ' session!');
            exit();
        }
    }

    /**
     *
     *
     * @return	string
     */
    public function getId()
    {
        return $this->session_id;
    }

    public function start($session_id = '')
    {
        if (!$session_id) {
            $session_id = $this->generateSessionId();
        }

        if ($this->isValidSessionId($session_id)) {
            $this->session_id = $session_id;
            $this->data = $this->adaptor->read($session_id);

            // Manually set the session cookie
            $this->setSessionCookie($session_id);
        } else {
            exit('Error: Invalid session ID!');
        }

        return $session_id;
    }

    private function setSessionCookie($session_id)
    {
        $cookieParams = session_get_cookie_params();
        setcookie(
            $this->session_name,
            // or another name if you have a specific session cookie name
            $session_id,
            [
                'expires' => time() + $cookieParams["lifetime"],
                'path' => $cookieParams["path"],
                'domain' => $cookieParams["domain"],
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }

    private function generateSessionId()
    {
        // Generate a cryptographically secure session ID
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(32)); // 32 bytes = 64 characters in hexadecimal
        } else {
            return bin2hex(openssl_random_pseudo_bytes(32)); // Fallback using openssl
        }
    }

    private function isValidSessionId($session_id)
    {
        // Validate the session ID format
        return preg_match('/^[a-zA-Z0-9,\-]{64}$/', $session_id);
    }

    public function regenerateId()
    {
        // Regenerate the session ID
        $this->session_id = $this->generateSessionId();
        // Update the session data with the new ID
        $this->data = $this->adaptor->write($this->session_id, $this->data);
    }

    /**
     *
     */
    public function close()
    {
        $this->adaptor->write($this->session_id, $this->data);
    }

    /**
     *
     */
    public function destroy()
    {
        $this->adaptor->destroy($this->session_id);
    }
}
