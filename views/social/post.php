<div class="borderfc">
    <div class="fr" style="width: 100%; justify-content: space-between; padding: 0px;">
        <a class="border" href="/social/profile?id=<?=$content['author'];?>">
            <img src="/social/avatar?id=<?=$content['author'];?>" height="100">
            <span><?=$this->getuser($content['author'])["username"];?></span>
        </a>
        <div class="msgbox">
            <div class="msg"><span class="msg wfa"><?=$content["content"];?></span></div>
            <div class="msgdate">
                <a href="/moderation/report?type=feed&asset=<?=$id;?>">Report</a>
                <span><?=date("Y-d-m H:i:s",(int)$content["uploadtimestamp"]);?></span>
            </div>
        </div>
    </div>
</div>