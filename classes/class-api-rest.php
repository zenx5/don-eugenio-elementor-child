<?php

class EuApiRest {

    public static function get_all_clients( $request ) {
        $users = get_users();
        $response = [];
        if ( ! empty( $users ) ) {
            foreach( $users as $user ){
                unset($user->data->user_pass);
                if( in_array( 'cliente', $user->roles ) ) {
                    $user->data->meta = get_user_meta( $user->data->ID );
                    $response[] = $user->data;
                }
            }
        }
        return rest_ensure_response( $response );
    }

    public static function get_unique_client( $request ) {
        $id = $request->get_param( 'id' );
        $user = get_user_by('ID', $id);
        $response = [];
        if ( ! empty( $user ) ) {
            unset($user->data->user_pass);
            $user->data->meta = get_user_meta( $user->data->ID );
            $response = $user->data;
        }
        return rest_ensure_response( $response );
    }
}