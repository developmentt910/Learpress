<?php
/**
 * Plugin Name: LearnPress Stats Dashboard
 * Plugin URI: https://yourwebsite.com/
 * Description: Plugin hiển thị bảng thống kê dữ liệu LearnPress ngoài Dashboard.
 * Version: 1.0.0
 * Author: Kiên
 * Text Domain: lp-stats-addon
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}
add_action( 'wp_dashboard_setup', 'lp_stats_addon_add_dashboard_widget' );

function lp_stats_addon_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'lp_stats_dashboard_widget', 
        'Thống Kê LearnPress',       
        'lp_stats_addon_render_widget' 
    );
}
function lp_stats_addon_render_widget() {
    if ( ! class_exists( 'LearnPress' ) ) {
        echo '<p style="color: red;">Vui lòng cài đặt và kích hoạt plugin LearnPress để sử dụng tính năng này.</p>';
        return;
    }

    global $wpdb;
    $table_user_items = $wpdb->prefix . 'learnpress_user_items';
    $total_courses = wp_count_posts( 'lp_course' )->publish;
    if ( empty( $total_courses ) ) {
        $total_courses = 0;
    }
    $total_students = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(DISTINCT user_id) FROM {$table_user_items} WHERE item_type = %s",
        'lp_course'
    ));
    if ( empty( $total_students ) ) {
        $total_students = 0;
    }
    $total_completed = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_user_items} WHERE item_type = %s AND status IN ('completed', 'finished', 'passed')",
        'lp_course'
    ));
    if ( empty( $total_completed ) ) {
        $total_completed = 0;
    }
    ?>
    <div class="lp-stats-container">
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                <strong>Tổng số khóa học:</strong> 
                <span class="badge" style="background: #2271b1; color: #fff; padding: 2px 8px; border-radius: 12px;"><?php echo esc_html( $total_courses ); ?></span>
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                <strong>Tổng số học viên đã đăng ký:</strong> 
                <span class="badge" style="background: #2271b1; color: #fff; padding: 2px 8px; border-radius: 12px;"><?php echo esc_html( $total_students ); ?></span>
            </li>
            <li style="padding: 10px 0; display: flex; justify-content: space-between;">
                <strong>Lượt khóa học đã hoàn thành:</strong> 
                <span class="badge" style="background: #46b450; color: #fff; padding: 2px 8px; border-radius: 12px;"><?php echo esc_html( $total_completed ); ?></span>
            </li>
        </ul>
    </div>
    <?php
}

add_shortcode( 'learnpress_stats', 'lp_stats_addon_shortcode' );

function lp_stats_addon_shortcode() {
    if ( ! class_exists( 'LearnPress' ) ) {
        return '<p>Vui lòng cài đặt LearnPress để xem thống kê.</p>';
    }

    global $wpdb;
    $table_user_items = $wpdb->prefix . 'learnpress_user_items';
    $total_courses = wp_count_posts( 'lp_course' )->publish;
    $total_courses = empty( $total_courses ) ? 0 : $total_courses;

    $total_students = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(DISTINCT user_id) FROM {$table_user_items} WHERE item_type = %s",
        'lp_course'
    ));
    $total_students = empty( $total_students ) ? 0 : $total_students;
    $total_completed = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_user_items} WHERE item_type = %s AND status IN ('completed', 'finished', 'passed')",
        'lp_course'
    ));
    $total_completed = empty( $total_completed ) ? 0 : $total_completed;
    ob_start();
    ?>
    <div class="lp-frontend-stats" style="background: #f9f9f9; padding: 20px; border-radius: 8px; max-width: 400px; border: 1px solid #ddd; margin-bottom: 20px;">
        <h3 style="text-align: center; margin-top: 0; color: #333;">Thống Kê Khóa Học</h3>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                <strong>Tổng số khóa học:</strong> 
                <span style="font-weight: bold; color: #2271b1; font-size: 18px;"><?php echo esc_html( $total_courses ); ?></span>
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                <strong>Học viên đăng ký:</strong> 
                <span style="font-weight: bold; color: #2271b1; font-size: 18px;"><?php echo esc_html( $total_students ); ?></span>
            </li>
            <li style="padding: 10px 0; display: flex; justify-content: space-between;">
                <strong>Lượt hoàn thành:</strong> 
                <span style="font-weight: bold; color: #46b450; font-size: 18px;"><?php echo esc_html( $total_completed ); ?></span>
            </li>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}