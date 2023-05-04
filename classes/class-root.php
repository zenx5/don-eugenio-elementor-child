<?php

require 'class-api-rest.php';
require 'class-render.php';

class EuRoot {


    public static function init() {

        add_action('init', ['EuRoot','add_shortcodes']);
        add_action( 'rest_api_init', ['EuRoot','register_endpoints'] );
    }

    function add_shortcodes() {
        add_shortcode('all-clients', ['EuRender', 'show_client_table']);
    }

    // Aqui se definen los enpoints
    public static function register_endpoints() {
        // Endpoint 'clients' para traer todos los usuarios de tipo clientes
        register_rest_route( 'wp/v2', '/clients', array(
            'methods' => 'GET',
            'callback' => ['EuApiRest','get_all_clients'],
        ) );
        // Endpoint 'clients/:id' para traer el usuario con el id indicado
        register_rest_route( 'wp/v2', '/clients/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => ['EuApiRest','get_unique_client'],
        ) );
    }

}