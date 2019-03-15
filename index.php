<?php
/*
 * Plugin Name: 自发布
 * Description: 自动发布文章的api
 * Author: Junhao
 * Version: 1.0
 */

add_action('admin_menu', 'perci_autopublish_admin');
function perci_autopublish_admin() {
    add_options_page("自动发布", "自动发布", "manage_options", "perci-auto-publish", "perci_autopublish_admin_page");
}
function perci_autopublish_admin_page() {
    if (!empty($_POST['perci_autopublish_ak'])) {
        update_option('perci_autopublish_ak', $_POST['perci_autopublish_ak']);
        update_option('perci_autopublish_sk', $_POST['perci_autopublish_sk']);
        echo '<br>修改成功';
    } else {

    $ak = get_option('perci_autopublish_ak');
    $sk = get_option('perci_autopublish_sk');
    if ($ak == "" && $sk == "") {
        $ak = '111111';
        $sk = '111111';
        update_option('perci_autopublish_ak', $ak);
        update_option('perci_autopublish_ak', $sk);
    }
    echo "
        <div>
            <form method='post'>
            Access Key:<br>
            <input type='text' name='perci_autopublish_ak' value='".$ak."'><br>
            Secret Key:<br>
            <input type='text' name='perci_autopublish_sk' value='".$sk."'><br>
            <input type='submit' text='提交' class='button button-primary button-large'>
            </form>
        </div>
    ";
    }
}



// 新增文章
add_action ('rest_api_init', function() {
    register_rest_route('autopublish/v1', 'post', array(
        'methods'   => 'POST',
        'callback'  => 'perci_api_autopublish',
    ));
});
function perci_api_autopublish($data) {
    $ak = $_POST['ak'];
    $sk = $_POST['sk'];

    $response = [
        'code'  => -1,
        'msg'   => '',
        'data'  => [],
    ];

    if (!($ak == get_option('perci_autopublish_ak') && $sk == get_option('perci_autopublish_sk'))) {
        $response['msg'] = 'invalid_access';

        return $response;
    }

    $postarr = [];

    $author = get_user_by('login', $_POST['author_login']);
    $postarr['post_author'] = $author->ID;

    //$postarr['post_date'] = date('Y-m-d H:i:s', $_POST['post_date']);
    $postarr['post_date'] = $_POST['post_date'];

    $postarr['post_content'] = $_POST['post_content'];

    $postarr['post_title'] = $_POST['post_title'];

    $ca = get_category_by_slug($_POST['category_slug']);
    $postarr['post_category'] = [$ca->term_id];

    try {
        // 插入文章
        $postid = wp_insert_post($postarr);

        // 发布文章
        wp_publish_post($postid);

        if (!empty($_POST['post_thumbnail'])) {
            update_post_meta($postid, 'perci_autopublish_thumbnail', $_POST['post_thumbnail']);
        }
    } catch (\Exception $e) {
        $response['msg'] = $e->getMessage();
        return $response;
    }

    $response['code'] = '1';
    $response['msg'] = 'OK';
    return $response;
}

?>
