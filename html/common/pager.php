<!-- ページャーに必要な値はコントローラーで受け取っている -->
<?php if ($pager && $pager->getTotalPage() > 1) : ?>
    <div class="pager">
        <ul>
            <?php if ($pager->hasPreviousPage()) : ?>
                <li><a href="<?php echo $pager->createUri() ?>">&laquo;</a></li>
                <li><a href="<?php echo $pager->createUri($pager->getPreviousPageNumber()) ?>">&lt;</a></li>
            <?php endif ?>
            <?php foreach ($pager->getPageNumbers() as $number) : ?>
                <?php if ($number === $pager->getCurrentPage()) : ?>
                    <li><span><?php echo $number ?></span></li>
                <?php else : ?>
                    <li><a href="<?php echo $pager->createUri($number) ?>"><?php echo $number ?></a></li>
                <?php endif ?>
            <?php endforeach ?>
            <?php if ($pager->hasNextPage()) : ?>
                <li><a href="<?php echo $pager->createUri($pager->getNextPageNumber()) ?>">&gt;</a></li>
                <li><a href="<?php echo $pager->createUri($pager->getTotalPage()) ?>">&raquo;</a></li>
            <?php endif ?>
        </ul>
    </div>
<?php endif ?>
