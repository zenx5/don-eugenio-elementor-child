<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if (!function_exists('chld_thm_cfg_locale_css')) :
	function chld_thm_cfg_locale_css($uri)
	{
		if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
			$uri = get_template_directory_uri() . '/rtl.css';
		return $uri;
	}
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

if (!function_exists('child_theme_configurator_css')) :
	function child_theme_configurator_css()
	{
		wp_enqueue_style('chld_thm_cfg_child', trailingslashit(get_stylesheet_directory_uri()) . 'style.css', array('hello-elementor', 'hello-elementor', 'hello-elementor-theme-style'));
	}
endif;


add_action('wp_enqueue_scripts', 'child_theme_configurator_css', 99999);

function eu_endpoints() {
    register_rest_route( 'wp/v2', '/clients', array(
        'methods' => 'GET',
        'callback' => 'get_all_clients',
    ) );
	register_rest_route( 'wp/v2', '/clients/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_unique_client',
    ) );
}

add_action( 'rest_api_init', 'eu_endpoints' );

function get_all_clients( $request ) {
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

function get_unique_client( $request ) {
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


function show_client_table() {
	$page = isset($_GET['page_number']) ? $_GET['page_number'] : 1;
	$rows_per_page = isset($_GET['rows_per_page']) ? $_GET['rows_per_page'] : 0;
    $users = get_users();
	$clients = [];
	if ( ! empty( $users ) ) {
		foreach( $users as $user ){
			unset($user->data->user_pass);
			if( in_array( 'cliente', $user->roles ) ) {
			    $user->data->meta = get_user_meta( $user->data->ID );
				$clients[] = $user->data;
			}
		}
	}
	$final_rows_per_page = $rows_per_page==0 ? count($clients) : $rows_per_page;
    ob_start();
	?>
<div style="margin-bottom:20px;">
	<b>Numero de Clientes registrados:</b> <?=count($clients)?>
</div>
<table>
	<thead>
		<tr>
			<th>Nombre</th>
			<th>Email</th>
			<th>Fecha de Registro</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach(array_slice($clients, $page, $final_rows_per_page) as $client): ?>
		<tr>
			<td><?=$client->user_login?></td>
			<td><?=$client->user_email?></td>
			<td><?=$client->user_registered?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<form method="get">
	<div style="display: flex; justify-content: space-evenly; align-items: center;">
<?php
		$total_elementos = count($clients);
		$total_paginas = ceil($total_elementos / $final_rows_per_page);
		$back = $page==1 ? $page : $page - 1;
		$front = $page==$total_paginas ? $page : $page + 1;
		echo "<a href=\"?rows_per_page=$rows_per_page&page_number=$back\"><</a> ";
		for ($i = 1; $i <= $total_paginas; $i++) {
			if ($i == $page) {
				echo "<strong>$i</strong> ";
			} else {
				echo "<a href=\"?rows_per_page=$rows_per_page&page_number=$i\">$i</a> ";
			}
		}
		echo "<a href=\"?rows_per_page=$rows_per_page&page_number=$front\">></a> ";
	?>
	</div>
	<div style="margin-top:20px; margin-bottom:20px; display:flex; justify-content:space-evenly; flex-direction:row; gap:20px;">
		<label>Registros por pagina:</label>
		<select name="rows_per_page" value="<?=$rows_per_page?>">
			<option value="5" <?php if($rows_per_page==5) echo 'selected'; ?> >5</option>
			<option value="10" <?php if($rows_per_page==10) echo 'selected'; ?> >10</option>
			<option value="20" <?php if($rows_per_page==20) echo 'selected'; ?> >20</option>
			<option value="0"  <?php if($rows_per_page==0) echo 'selected'; ?> >Todos</option>
		</select>
		<button type="submit">Actualizar</button>
	</div>
</form>
<?
	$html = ob_get_contents();
    ob_end_clean();
    return $html;
}
function add_shortcodes() {
	add_shortcode('all-clients', 'show_client_table');
}

add_action('init', 'add_shortcodes');


// END ENQUEUE PARENT ACTION
