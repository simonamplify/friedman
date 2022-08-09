<div class="software">
    <?php if( get_field('windows_software_url') || get_field('macos_software_url')): ?>
        <div class="productSoftware">Software:</div>
    <?php endif; ?>
    <?php if( get_field('windows_software_url') ): ?>
        <a href="<?php the_field('windows_software_url');?>" target="_blank" class="productSoftware"><span class="material-icons">album</span> <span class="softwareLabel">Windows</span></a>
    <?php endif; ?>
    <?php if( get_field('macos_software_url') ): ?>
        <a href="<?php the_field('macos_software_url');?>" target="_blank" class="productSoftware"><span class="material-icons">album</span> <span class="softwareLabel">MacOS</span></a>
    <?php endif; ?>
</div>