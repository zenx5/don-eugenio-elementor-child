<?php

class EuApiRest {

    public static function get_all_clients( $request = null, $call_is_endpoint = true, $object_vars = false ) {
        $users = get_users();
        $response = [];
        if ( ! empty( $users ) ) {
            foreach( $users as $user ){
                unset($user->data->user_pass);
                if( in_array( 'customer', $user->roles ) || in_array( 'cliente', $user->roles ) ) {
                    $user->data->meta = self::get_metas( get_user_meta( $user->data->ID ) );
                    $response[] = $object_vars ? get_object_vars($user->data) : $user->data;
                }
            }
        }
        return $call_is_endpoint ? rest_ensure_response( $response ) : $response;
    }

    public static function get_unique_client( $request, $call_is_endpoint = true, $object_vars = false ) {
        $id = $request->get_param( 'id' );
        $user = get_user_by('ID', $id);
        $response = [];
        if ( ! empty( $user ) ) {
            unset($user->data->user_pass);
            $user->data->meta = self::get_metas( get_user_meta( $user->data->ID ) );
            $response = $object_vars ? get_object_vars($user->data) : $user->data;
        }
        return $call_is_endpoint ? rest_ensure_response( $response ) : $response;
    }

    public static function get_all_entries( $request, $call_is_endpoint = true ) {
        global $wpdb;

        $result_query = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wpforms_entries  WHERE 1;"
			)
        );

        $entries = [];
        foreach( $result_query as $entry ) {
            $fields = json_decode( $entry->fields );
            // $fields->date = $entry->date;
            // $fields->date_modified = $entry->date_modified;
            $entries[] = $fields;
        }
        $response = $entries ?? [];
        return $call_is_endpoint ? rest_ensure_response( $response ) : $response;
    }

    public static function join_meta_data( $request, $call_is_endpoint = true ) {
        $clients = self::get_all_clients( $request, false );
        $entries = self::get_all_entries( $request, false );
        $response = [];
        foreach( $clients as $client ) {
            foreach( $entries as $entry ) {
                if( $client->user_email == self::get_value($entry,"Correo electrónico") ) {
                    update_user_meta( $client->ID, "referer", self::get_value($entry, "Referido por")  );
                    update_user_meta( $client->ID, "name", self::get_value($entry, "Nombre y Apellido")  );
                    update_user_meta( $client->ID, "type_id", self::get_value($entry, "Tipo de Cedula")  );
                    update_user_meta( $client->ID, "number_id", self::get_value($entry, "Número de Cedula")  );
                    update_user_meta( $client->ID, "phone", self::get_value($entry, "Teléfono")  );
                    update_user_meta( $client->ID, "email", self::get_value($entry, "Correo electrónico")  );
                    update_user_meta( $client->ID, "services", self::get_value($entry, "Servicios")  );
                    update_user_meta( $client->ID, "services_details", self::get_value($entry, "Comentarios sobre el servicio")  );
                    update_user_meta( $client->ID, "address", self::get_value($entry, "Dirección")  );
                    update_user_meta( $client->ID, "file_reference", self::get_value($entry, "Referencia Personal")  );
                    update_user_meta( $client->ID, "comments", self::get_value($entry, "Comentario extras")  );
                    $response[] = $client;
                }
            }
        }
        return $call_is_endpoint ? rest_ensure_response( $response ) : $response;
    }

    private static function get_metas( $metas ) {
        $result = [];
        foreach( $metas as $key => $value ) {
            $quantity = count( $value );
            if( $quantity==0 ) $result[$key] = "";
            else if( $quantity==1 ) $result[$key] = $value[0];
            else $result[$key] = "[".implode(",",$value)."]";
        }
        return $result;
    }

    private static function get_value( $entry, $name) {
        foreach ($entry as $key => $content) {
            if( $content->name == $name ) {
                return $content->value;
            }
        }
        return "";
    }
}