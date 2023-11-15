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

    /**
     *
     *
     * @param	string	$session_id
     *
     * @return	string
     */
    public function start($session_id = '')
    {
        $this->setCookieParams();
        session_start();

        if (!$session_id) {
            if (function_exists('random_bytes')) {
                $session_id = bin2hex(random_bytes(32)); // 32 bytes = 64 characters in hexadecimal
            } else {
                $session_id = bin2hex(openssl_random_pseudo_bytes(32)); // Fallback using openssl
            }
        }

        if (preg_match('/^[a-zA-Z0-9,\-]{64}$/', $session_id)) { // Updated regex for 64 hex characters
            $this->session_id = $session_id;
        } else {
            exit('Error: Invalid session ID!');
        }

        $this->data = $this->adaptor->read($session_id);

        return $session_id;
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

    // Function to regenerate session ID
    public function regenerateId()
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    // Function to check and set cookie parameters
    private function setCookieParams()
    {
        $cookieParams = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => $cookieParams["lifetime"],
            'path' => $cookieParams["path"],
            'domain' => $cookieParams["domain"],
            'secure' => true,
            // Set to true if using HTTPS
            'httponly' => true // Prevents JavaScript access to session cookie
        ]);
    }

    // Function to implement session expiration
    public function checkSessionExpiration()
    {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
            // last request was more than 30 minutes ago
            session_unset(); // unset $_SESSION variable
            session_destroy(); // destroy session data
        }
        $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time
    }
}