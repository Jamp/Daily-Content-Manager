<?php
/**
 * Dailyscript - app | web | media
 * Modelo para manejar almacenar y obtener el user_id y el access_token
 * del usuario de Facebook
 * Fecha 05/02/2012
 *
 * @category    API
 * @package     Models
 * @author      Jaro Marval
 * @copyright   Copyright (c) 2013 Icterus Team (http://www.icter.us)
 * @version     2.0
 */

// Cargamos las librerias necesarias para trabajar con Facebook
Load::lib('redes_sociales/facebook/facebook');

Load::model('usuario');

class Apifacebook {

    public $twitterdata;
    protected $consumer_key;
    protected $consumer_secret;

    /**
    * Modelo para manejar cuentas twitter
    */
    public function __construct(){
        $config = Api::facebook();
        $this->facebookLink = "";
        $this->app_id = $config['app_id'];
        $this->app_secret = $config['app_secret'];
    }

    public function cuenta() {
    	$usuario = new Usuario();
	    // Verificamos que tengamos una sesión guardada de usuario Facebook
	    $this->facebookData = $usuario->getFacebook(Session::get('id'));
		$facebook = new Facebook(array(
		  'appId'  => $this->app_id,
		  'secret' => $this->app_secret
		));

		// Verificamos que la identidad en bbdd de datos sea valida
		if ( !$this->facebookData ) {
			// Solicitamos una identidad al api de facebook
			$user = $facebook->getUser();

			if ($user) {
				try {
					// Solicitamos los datos de Usuario
					$facebook->api('/me');
					// Recogemos los datos de la sesión
					$user_fb = $_SESSION['fb_'.$this->app_id.'_user_id'];
					$access_token = $_SESSION['fb_'.$this->app_id.'_access_token'];

					// Los guardamos en la base de datos
					$rs = $usuario->setFacebook(Session::get('id'), $user_fb, $access_token);

					if ( $rs ) {
						// destruimos las variables de session
						unset($_SESSION['fb_'.$this->app_id.'_code']);
						unset($_SESSION['fb_'.$this->app_id.'_user_id']);
						unset($_SESSION['fb_'.$this->app_id.'_access_token']);
				        // Parametrizamos con la identidad que tenemos guardada
				        $this->facebookData['id'] = Session::get('id');
						$this->facebookData['image'] = 'https://graph.facebook.com/'.$user_fb.'/picture';
            			$this->facebookData['profile'] = 'http://www.facebook.com/'.$user_fb;
					}
				} catch ( FacebookApiException $e ) {
					Flash::error($e);
					$user = null;
				}
			} else { // De no ser asi solicitamos un url de logeo en Facebook
				$this->facebookLink = $facebook->getLoginUrl();
			}
		} else {
	        // Parametrizamos con la identidad que tenemos guardada
			$this->facebookData['image'] = 'https://graph.facebook.com/'.$this->facebookData['user_id'].'/picture';
            $this->facebookData['profile'] = 'http://www.facebook.com/'.$this->facebookData['user_id'];
		}

    }

    // FIXME: prepara para que funcione con Facebook
    public function outputError($tmhOAuth) {
		if ( isset($tmhOAuth->response['error']) && $tmhOAuth->response['error'] == '') {
			$error = "Llaves inválidas";
		} else {
			$error = $tmhOAuth->response['error'];
		}
		Flash::error($error);
    }

    public function getData() {
        return $this->facebookData;
    }

    public function getLink() {
        return ( $this->facebookLink != '') ? $this->facebookLink : False;
    }
}