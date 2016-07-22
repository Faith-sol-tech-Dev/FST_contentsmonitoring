<div class="pager">
    <p>
<?php if ($this->currentpage == 1) { ?>
        <span class="first active">&lt;&lt; 最初</span>
        <span class="prev active">前へ</span>
<?php } else { ?>
        <!-- <span class="first"><a href="<?php echo $this->createPageTurnerHref(1) ?>">&lt;&lt; 最初</a></span>
        <span class="prev"><a href="<?php echo $this->createPageTurnerHref($this->currentpage - 1) ?>">前へ</a></span> -->
        <span class="first"><a href="#" onclick="sendpage(<?php echo 1 ?>); return false;">&lt;&lt; 最初</a></span>
        <span class="prev"><a href="#" onclick="sendpage(<?php echo $this->currentpage - 1 ?>); return false;">前へ</a></span>
<?php } ?>
<?php for ($i = $this->intPageTurnerViewStartPage; $i <= $this->intPageTurnerViewEndPage; $i++) { ?>
<?php 	if ($this->currentpage == $i) { ?>
        <span class="active"><?php echo $i ?></span>
<?php 	} else { ?>
        <!-- <span><a href="<?php echo $this->createPageTurnerHref($i) ?>"><?php echo $i ?></a></span> -->
        <span ><a href="#" onclick="sendpage(<?php echo $i ?>); return false;"><?php echo $i ?></a></span>
<?php 	} ?>
<?php } ?>
<?php if ($this->currentpage == $this->maxpage) { ?>
		<span class="next active">次へ</span>
		<span class="last active">最後 &gt;&gt;</span>
<?php } else { ?>
        <!-- <span class="next"><a href="#" onclick="sendpage(<?php echo $this->currentpage + 1 ?>); return false;">次へ</a></span> -->
        <span class="last"><a href="#" onclick="sendpage(<?php echo $this->currentpage + 1 ?>); return false;">次へ</a></span>
        <span class="last"><a href="#" onclick="sendpage(<?php echo $this->maxpage ?>); return false;">最後 &gt;&gt;</a></span>
<?php } ?>
    </p>
</div>