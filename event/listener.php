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
	protected $request;
	protected $template;
	protected $user;
	protected $board_url;
	protected $root_path;
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\request\request 	$request		Request object
	* @param \phpbb\template\template 	$template	Template object
	* @param \phpbb\user				$user		User object
	* @param $root_path					$root_path	phpBB root path
	* @param $phpExt					$phpExt		php file extension
	*/
	public function __construct(
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$phpExt)
	{
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->root_path	= $root_path;
		$this->php_ext		= $phpExt;

		$this->board_url = generate_board_url(true);
		$this->board_url = utf8_case_fold_nfc($this->board_url);
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
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function page_header_after($event)
	{
		$is_logged_in = $this->user->data['user_id'] != ANONYMOUS;

		if ($this->user->page['page_name'] == "ucp.{$this->php_ext}"
			|| $this->user->page['page_dir']
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
		$redirect = 'redirect=' . urlencode(str_replace('&amp;', '&', build_url(array('_f_'))));
		$seperator = strpos($u_login_logout, '?') === false ? '?' : '&amp;';
		$u_login_logout .= $seperator . $redirect;
		$this->template->assign_var('U_LOGIN_LOGOUT', $u_login_logout);
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
