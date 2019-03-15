<?php
/*
 * Plugin Name: 自发布
 * Description: 自动发布文章的api
 * Author: Junhao
 * Version: 1.0
 */

add_action ('rest_api_init', function() {
    register_rest_route('autopublish/v1', 'post', array(
        'methods'   => 'POST',
        'callback'  => 'perci_api_autopublish',
    ));
});

function perci_api_autopublish($data) {
    
}

?>
