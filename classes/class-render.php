<?php

require_once 'class-api-rest.php';

class EuRender {

    public static function eu_show_graph($attr=[]) {
        $mode = isset( $attr['mode'] ) ? $attr['mode'] : ( isset($_GET['mode']) ? $_GET['mode'] : 'services' );
        $type = isset( $attr['type'] ) ? $attr['type'] : ( isset($_GET['type']) ? $_GET['type'] : 'bar' );
        $width = isset( $attr['width'] ) ? $attr['width'] : '50%';

        $clients = EuApiRest::get_all_clients(null, false, true);

        return self::insert_graph($clients, $mode, $type, $width);
    }

    public static function insert_graph($clients, $mode, $type, $width) {
        $fields = [];
        $labels = [];
        $values = [];
        foreach( $clients as $client ) {
            $current_fields = explode("\n", $client['meta'][$mode] ?? "" );
            if( is_array( $current_fields ) ){
                foreach( $current_fields as $field ) {
                    if( $field == '' ) $field = 'Otro';
                    if( isset($fields[$field]) ) {
                        $fields[$field] += 1;
                    } else {
                        $fields[$field] = 1;
                        $labels[] = $field;
                    }
                }
            }
        }
        foreach( $labels as $label ) {
            $values[] = $fields[$label];
        }
        ob_start(); ?>
            <div style="width:<?=$width?>; margin:auto;">
                <canvas id="eu_show_graph"></canvas>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const ctx = document.getElementById('eu_show_graph');

                new Chart(ctx, {
                    type: "<?=$type?>",
                    data: {
                        labels: <?=json_encode($labels)?>,
                        datasets: [{
                            label: 'Clientes por <?=$mode?>',
                            data: <?=json_encode($values)?>,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public static function render_pagination($elements = [], $page = 1, $rows_per_page = 5) {
        $total = count($elements);
        $pages = ceil($total / $rows_per_page);
        $back = $page==1 ? $page : $page - 1;
        $front = $page==$pages ? $page : $page + 1;
        ob_start(); ?>
            <form method="get">
                <div style="display: flex; justify-content: space-evenly; align-items: center;">
                    <a href="?rows_per_page=<?=$rows_per_page?>&page_number=<?=$back?>"> arrow left </a>
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <strong><?=$i?></strong>
                        <?php else: ?>
                            <a href="?rows_per_page=<?=$rows_per_page?>&page_number=<?=$i?>"><?=$i?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <a href="?rows_per_page=<?=$rows_per_page?>&page_number=<?=$front?>"> arrow right </a>
                </div>
                <div style="margin-top:20px; margin-bottom:20px; display:flex; justify-content:space-evenly; flex-direction:row; gap:20px;">
                    <label>Registros por pagina:</label>
                    <select name="rows_per_page" value="<?=$rows_per_page?>">
                        <option value="5" <?php if($rows_per_page==5) echo 'selected'; ?> >5</option>
                        <option value="10" <?php if($rows_per_page==10) echo 'selected'; ?> >10</option>
                        <option value="20" <?php if($rows_per_page==20) echo 'selected'; ?> >20</option>
                        <option value="<?=$total?>"  <?php if($rows_per_page==$total) echo 'selected'; ?> >Todos</option>
                    </select>
                    <button type="submit">Actualizar</button>
                </div>
            </form>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public static function render_table($data = [], $fields = []) {
        ob_start();?>
            <table>
                <thead>
                    <tr>
                        <?php foreach( $fields as $field ): ?>
                            <th><?=$field['label']?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data as $item): ?>
                        <tr>
                            <?php foreach( $fields as $field ): ?>
                                <?php if(is_array( $field['key'] )): ?>
                                    <td><?=$item[ $field['key'][0] ][ $field['key'][1] ] ?? "" ?></td>
                                <?php else: ?>
                                    <td><?=$item[ $field['key'] ] ?? ""?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public static function show_client_table() {
        $page = isset($_GET['page_number']) ? $_GET['page_number'] : 1;
        $rows_per_page = isset($_GET['rows_per_page']) ? $_GET['rows_per_page'] : 5;
        $clients = EuApiRest::get_all_clients(null, false, true);
        if( count( $clients )==0 ) {
            $rows_per_page = 5;
        }
		usort($clients, function($item1, $item2){
			return strcmp($item1['user_login'], $item2['user_login']);
		});
        ob_start();  ?>
            <div style="margin-bottom:20px;">
                <b>Numero de Clientes registrados:</b> <?=count($clients)?>
            </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        $fields = [
            [ "label" => "Nombre", "key" => "user_login" ],
            [ "label" => "Email", "key" => "user_email" ],
            [ "label" => "Numero de Cedula", "key" => ["meta","number_id"] ],
            [ "label" => "Telefono", "key" => ["meta","phone"] ],
            [ "label" => "Servicios", "key" => ["meta","services"] ],
            [ "label" => "Registrado por", "key" => ["meta","referer"] ],
            [ "label" => "Fecha de Registro", "key" => "user_registered" ],
        ];
		$offset = ($page - 1) * $rows_per_page;
		$show_clients = array_slice($clients, $offset, $rows_per_page);
        $html_table = EuRender::render_table($show_clients, $fields);
        $html_pagination = EuRender::render_pagination($clients, $page, $rows_per_page);
        return $html.$html_table.$html_pagination;
    }
}