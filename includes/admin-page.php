<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page(
        'Affiliate Links',
        'Affiliate Links',
        'manage_options',
        'afflink-manager-lite',
        'afflink_manager_lite_render_admin_page',
        'dashicons-admin-links'
    );
});

function afflink_manager_lite_render_admin_page() {
    if (!current_user_can('manage_options')) return;

    $links = get_option(AFFLINK_MANAGER_LITE_OPTION, []);
    $notes = get_option(AFFLINK_MANAGER_LITE_NOTES_OPTION, []);

    if (isset($_POST['new_slug']) && isset($_POST['new_url'])) {
        $slug = sanitize_title($_POST['new_slug']);
        $url = esc_url_raw($_POST['new_url']);
        $note = sanitize_text_field($_POST['new_note'] ?? '');
        if ($slug && $url) {
            $links[$slug] = $url;
            $notes[$slug] = $note;
            update_option(AFFLINK_MANAGER_LITE_OPTION, $links);
            update_option(AFFLINK_MANAGER_LITE_NOTES_OPTION, $notes);
            echo '<div class="updated"><p>Link saved!</p></div>';
        }
    }

    if (isset($_GET['delete_slug'])) {
        $slug = sanitize_title($_GET['delete_slug']);
        unset($links[$slug]);
        unset($notes[$slug]);
        update_option(AFFLINK_MANAGER_LITE_OPTION, $links);
        update_option(AFFLINK_MANAGER_LITE_NOTES_OPTION, $notes);
        echo '<div class="updated"><p>Link deleted!</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Affiliate Link Manager Lite</h1>
        <form method="post">
            <h2>Add New Link</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="new_slug">Slug</label></th>
                    <td><input name="new_slug" type="text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_url">Target URL</label></th>
                    <td><input name="new_url" type="url" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="new_note">Note</label></th>
                    <td><input name="new_note" type="text"></td>
                </tr>
            </table>
            <?php submit_button('Add Link'); ?>
        </form>

        <h2>Existing Links</h2>
        <table class="widefat">
            <thead>
                <tr><th>Slug</th><th>Target URL</th><th>Note</th><th>Redirect Link</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($links as $slug => $url): ?>
                    <tr>
                        <td><code><?php echo esc_html($slug); ?></code></td>
                        <td><a href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_html($url); ?></a></td>
                        <td><?php echo esc_html($notes[$slug] ?? ''); ?></td>
                        <td><a href="<?php echo home_url('/go/' . $slug); ?>" target="_blank"><?php echo home_url('/go/' . $slug); ?></a></td>
                        <td><a href="?page=afflink-manager-lite&delete_slug=<?php echo esc_attr($slug); ?>" onclick="return confirm('Delete this link?')">Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
