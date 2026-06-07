<?php
/**
 * Footer: site footer and document close.
 *
 * @package eateggs
 */

?>
<!-- ============ FOOTER ============ -->
<footer class="s-foot">
  <div class="container">
    <p class="eyebrow"><?php esc_html_e( 'Stay in the loop', 'eateggs' ); ?></p>
    <h2><?php esc_html_e( 'New posts, no schedule.', 'eateggs' ); ?> <a href="<?php echo esc_url( home_url( '/subscribe/' ) ); ?>"><?php esc_html_e( 'Subscribe by email.', 'eateggs' ); ?></a></h2>
    <p class="sub"><?php esc_html_e( 'Trip reports from the Divide, mountain-town notes from Bozeman, and the occasional bread post. One email when something new lands.', 'eateggs' ); ?></p>
    <div class="foot-links">
      <a href="mailto:<?php echo esc_attr( antispambot( get_option( 'admin_email' ) ) ); ?>"><?php esc_html_e( 'Email', 'eateggs' ); ?></a>
      <a href="<?php echo esc_url( get_feed_link() ); ?>"><?php esc_html_e( 'RSS', 'eateggs' ); ?></a>
      <a href="#"><?php esc_html_e( 'Instagram', 'eateggs' ); ?></a>
      <a href="https://egge.us" target="_blank" rel="noopener">egge.us &#8599;</a>
    </div>
    <div class="foot-bottom">
      <span><b class="brand-word"><?php bloginfo( 'name' ); ?></b> &middot; <?php esc_html_e( 'Bozeman, Montana', 'eateggs' ); ?></span>
      <span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Mark Egge &middot; <?php esc_html_e( 'A blog about bikes, mountains & bread', 'eateggs' ); ?></span>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
