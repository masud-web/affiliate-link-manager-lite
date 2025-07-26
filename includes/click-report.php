<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_submenu_page(
        'afflink-manager-lite',
        'Click Reports',
        'Click Reports',
        'manage_options',
        'afflink-click-report',
        'afflink_click_report_page'
    );
});

function afflink_click_report_page() {
    global $wpdb;
    $table = $wpdb->prefix . AFFLINK_MANAGER_LITE_LOG_TABLE;

    $tabs = ['today' => 'Today','week' => 'This Week','month' => 'This Month','year' => 'This Year','all' => 'All Time'];
    $current_tab = $_GET['tab'] ?? 'today';

    $where = '';
    switch ($current_tab) {
        case 'today': $where = "WHERE DATE(clicked_at) = CURDATE()"; break;
        case 'week': $where = "WHERE YEARWEEK(clicked_at, 1) = YEARWEEK(CURDATE(), 1)"; break;
        case 'month': $where = "WHERE MONTH(clicked_at) = MONTH(CURDATE()) AND YEAR(clicked_at) = YEAR(CURDATE())"; break;
        case 'year': $where = "WHERE YEAR(clicked_at) = YEAR(CURDATE())"; break;
    }

    $results = $wpdb->get_results("SELECT slug, COUNT(*) as clicks FROM $table $where GROUP BY slug ORDER BY clicks DESC");

    echo '<div class="wrap"><h1>Affiliate Link Click Reports</h1>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach ($tabs as $key => $label) {
        $active = ($current_tab == $key) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$active' href='?page=afflink-click-report&tab=$key'>$label</a>";
    }
    echo '</h2>';

    echo '<canvas id="affChart" height="100"></canvas>';
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById("affChart").getContext("2d");
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: [' . implode(",", array_map(fn($r) => '"' . $r->slug . '"', $results)) . '],
                datasets: [{
                    label: "Clicks",
                    data: [' . implode(",", array_map(fn($r) => $r->clicks, $results)) . '],
                    backgroundColor: "rgba(54, 162, 235, 0.6)"
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
    </script>';

    echo '<table class="widefat"><thead><tr><th>Slug</th><th>Clicks</th><th>Link</th></tr></thead><tbody>';
    if ($results) {
        foreach ($results as $row) {
            echo "<tr>
                <td>{$row->slug}</td>
                <td>{$row->clicks}</td>
                <td><a href='" . home_url('/go/' . $row->slug) . "' target='_blank'>" . home_url('/go/' . $row->slug) . "</a></td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No clicks found.</td></tr>";
    }
    echo '</tbody></table></div>';
}
