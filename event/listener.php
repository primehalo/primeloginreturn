<?php
namespace primehalo\primeloginreturn\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/**
	* Constants
	*/
	const ENABLE_LOGIN	= true;	// Enable for logging in
	const ENABLE_LOGOUT	= true;	// Enable for logging out

	/**
	* Service Containers
	*/
	protected $config;
	protected $path_helper;
	protected $request;
	protected $template;
	protected $user;
	protected $root_path;
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\config\config		$config			Config object
	* @param \phpbb\path_helper			$path_helper	Path helper
	* @param \phpbb\request\request 	$request		Request object
	* @param \phpbb\template\template 	$template		Template object
	* @param \phpbb\user				$user			User object
	* @param $root_path					$root_path		phpBB root path
	* @param $phpExt					$phpExt			php file extension
	*/
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\path_helper $path_helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$phpExt)
	{
		$this->config		= $config;
		$this->path_helper	= $path_helper;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->root_path	= $root_path;
		$this->php_ext		= $phpExt;
	}

	/**
	* {@inheritDoc}
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'	=> 'page_header_after',		// 3.1.0-b3
			'core.functions.redirect'	=> 'redirect',				// 3.1.0-RC3
		);
	}

	/**
	* Update the login/logout link to include a query string redirect variable.
	*
	* @return null
	* @access public
	*/
	public function page_header_after()
	{
		$is_logged_in = $this->user->data['user_id'] != ANONYMOUS;

		if ($this->user->page['page_name'] == "ucp.{$this->php_ext}"
			|| ($is_logged_in && !self::ENABLE_LOGOUT)
			|| (!$is_logged_in && !self::ENABLE_LOGIN))
		{
			return;
		}
		if ($is_logged_in)
		{
			$u_login_logout = append_sid("{$this->root_path}ucp.{$this->php_ext}", 'mode=logout', true, $this->user->session_id);
		}
		else
		{
			$u_login_logout = append_sid("{$this->root_path}ucp.{$this->php_ext}", 'mode=login');
		}
		$redirect = 'redirect=' . urlencode(str_replace('&amp;', '&', $this->build_url()));
		$seperator = strpos($u_login_logout, '?') === false ? '?' : '&amp;';
		$u_login_logout .= $seperator . $redirect;
		$this->template->assign_var('U_LOGIN_LOGOUT', $u_login_logout);
	}

	/**
	* Returns url from the session/current page with an re-appended SID with optionally stripping vars from the url
	* Same as the build_url() function in includes/functions.php except the url is built relative to phpBB's root
	* instead of relative to the current directory since the login/logout page is located in phpBB's root path.
	*/
	public function build_url($strip_vars = false)
	{
		$user_page = ($this->user->page['page_dir'] ? $this->user->page['page_dir'] . '/' : '') . $this->user->page['page'];
		$page = $this->path_helper->get_valid_page($user_page, $this->config['enable_mod_rewrite']);

		// Append SID
		$redirect = append_sid($page, false, false, false, true);

		if ($strip_vars !== false)
		{
			$redirect = $this->path_helper->strip_url_params($redirect, $strip_vars, false);
		}
		else
		{
			$redirect = str_replace('&', '&amp;', $redirect);
		}

		return $redirect . ((strpos($redirect, '?') === false) ? '?' : '');
	}


	/**
	* On a redirect check to see if this is a logout. Login automatically
	* redirects if there's a redirect query var, so this is just for logout.
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function redirect($event)
	{
		$mode		= $this->request->variable('mode', '');
		$redirect	= $this->request->variable('redirect', '');
		$redirect	= str_replace('&amp;', '&', $redirect);
		if ($mode === 'logout' && $redirect)
		{
			$event['url'] = $redirect;
		}
	}
}
