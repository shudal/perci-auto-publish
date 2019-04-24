<?php
/*
 * Plugin Name: 自发布
 * Description: 自动发布文章的api
 * Author: Junhao
 * Version: 1.0
 * Author URI: https://github.com/shudal/perci-auto-publish
 */

add_action('admin_menu', 'perci_autopublish_admin');
function perci_autopublish_admin() {
    add_options_page("自动发布", "自动发布", "manage_options", "perci-auto-publish", "perci_autopublish_admin_page");
}
function perci_autopublish_admin_page() {
    echo "<div>使用文档参见<a href='https://github.com/shudal/perci-auto-publish'>这里</a></div>";

    if (!empty($_POST['perci_autopublish_ak'])) {
        update_option('perci_autopublish_ak', $_POST['perci_autopublish_ak']);
        update_option('perci_autopublish_sk', $_POST['perci_autopublish_sk']);
        echo '<br>修改成功';
    } else {

    $ak = get_option('perci_autopublish_ak');
    $sk = get_option('perci_autopublish_sk');
    if ($ak == "" || $sk == "") {
        $ak = '111111';
        $sk = '111111';
        update_option('perci_autopublish_ak', $ak);
        update_option('perci_autopublish_sk', $sk);
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
	
	// 文章别名，用于url。必须含有，否则文章无链接
	$postarr['post_name']  = $postarr['post_title'];	

    $ca = get_category_by_slug($_POST['category_slug']);
    $postarr['post_category'] = [$ca->term_id];

	try {
		$postarr['tags_input'] = explode("|", $_POST['tags']);
		$response['data']['tags_code'] = "OK";
		$response['data']['tags_msg'] = $postarr['tags_input'];
	} catch (\Exception $e) {
		$response['data']['tags_code'] = "-1";
		$response['data']['tags_msg'] = $e->getMessage();
		if (isset($postarr)) {
			unset($postarr);
		}
	}

    try {
        // 插入文章
		remove_filter('content_save_pre', 'wp_filter_post_kses');
		remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');
        $postid = wp_insert_post($postarr);
		add_filter('content_save_pre', 'wp_filter_post_kses');
		add_filter('content_filtered_save_pre', 'wp_filter_post_kses');

        // 发布文章
        wp_publish_post($postid);

        if (!empty($_POST['post_thumbnail'])) {
			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
			$tmp = download_url($_POST['post_thumbnail']);
			$file_array = array(
				'name' 			=> basename($_POST['post_thumbnail']),
				'tmp_name'		=> $tmp,
			);

			if ( is_wp_error( $tmp )) {
				$response['m2'] = $_POST['post_thumbnail'];
				$response['m'] = $tmp;
				$response['msg'] = 'download_error';
				return $response;
			}

			$attachment_id = media_handle_sideload( $file_array, $postid);

			if ( is_wp_error( $attachment_id ) ) {
				@unlink( $file_array['tmp_name']);
				$response['msg'] = 'upload_error';
				return $response;
			}			
			update_post_meta($postid, '_thumbnail_id', $attachment_id);
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
