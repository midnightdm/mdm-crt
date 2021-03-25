<?php
defined('BASEPATH') OR exit('No direct script access allowed');



class Images extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 *  Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 *
	 *
     *  This page will server up images stored in Amazon storage as 
     *  if they were local similar to how Livescanjson.php works. Its to
     *  get around the "mixed content" error which prevents Chrome
     *  browsers from displaying images from the Amazon bucket because
     *  they are http and the rest of the site is https.
     * 
     * 
     * 
     * 
	 */


	public function index()	{
				$data["items"] = $items;
		$this->load->vars($data);
		$this->load->view('template');
	}
