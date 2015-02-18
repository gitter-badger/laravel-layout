<div class="container">
    <header role="banner">
        <?php if ($this->getIsHomePage()):?>
        <h1 class="logo"><strong><?php echo $this->getLogoAlt() ?></strong><a href="<?php echo $this->getUrl('') ?>" title="<?php echo $this->getLogoAlt() ?>" class="logo"><img src="<?php echo $this->getLogoSrc() ?>" alt="<?php echo $this->getLogoAlt() ?>" /></a></h1>
        <?php else:?>
        <a href="<?php echo $this->getUrl('') ?>" title="<?php echo $this->getLogoAlt() ?>" class="logo"><strong><?php echo $this->getLogoAlt() ?></strong><img src="<?php echo $this->getLogoSrc() ?>" alt="<?php echo $this->getLogoAlt() ?>" /></a>
        <?php endif?>
        <div class="quick-access">
            <div class="clearfix">
                <?php echo $this->getChildHtml('topLinks') ?>
                <?php echo $this->getChildHtml('store_language') ?>
            </div>
            <?php echo $this->getChildHtml('topSearch') ?>
            <p class="welcome-msg"><?php echo $this->getWelcome() ?> <?php echo $this->getAdditionalHtml() ?></p>
        </div>
        <?php echo $this->getChildHtml('topContainer'); ?>
    </header>
</div>
<?php echo $this->getChildHtml('topMenu') ?>