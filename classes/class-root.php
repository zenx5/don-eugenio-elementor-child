<?php

require_once 'class-api-rest.php';
require_once 'class-render.php';

class EuRoot {


    public static function init() {

        EuRoot::add_shortcodes();
        add_action( 'rest_api_init', ['EuRoot','register_endpoints'] );
    }

    public static function add_shortcodes() {
        add_shortcode('all-clients', ['EuRender', 'show_client_table']);
        add_shortcode('client-graph-services', ['EuRender', 'show_graph_service']);
    }

    // Aqui se definen los enpoints
    public static function register_endpoints() {
        // Endpoint 'clients' para traer todos los usuarios de tipo clientes
        register_rest_route( 'wp/v2', '/clients', array(
            'methods' => 'GET',
            'callback' => ['EuApiRest','get_all_clients'],
            'permission_callback' => '__return_true'
        ) );
        // Endpoint 'clients/:id' para traer el usuario con el id indicado
        register_rest_route( 'wp/v2', '/clients/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => ['EuApiRest','get_unique_client'],
            'permission_callback' => '__return_true'
        ) );

        // Endpoint 'entries' para traer las entradas de los formularios
        register_rest_route( 'wp/v2', '/entries', array(
            'methods' => 'GET',
            'callback' => ['EuApiRest','get_all_entries'],
            'permission_callback' => '__return_true'
        ) );

        // Endpoint 'join-data' para hacer join de usuarios con metadata
        register_rest_route( 'wp/v2', '/join-data', array(
            'methods' => 'GET',
            'callback' => ['EuApiRest','join_meta_data'],
            'permission_callback' => '__return_true'
        ) );
    }

}